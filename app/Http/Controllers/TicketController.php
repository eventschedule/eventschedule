<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportAttendeesRequest;
use App\Http\Requests\TicketCheckoutRequest;
use App\Jobs\NotifyWaitlist;
use App\Mail\FeedbackRequest;
use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\GiftCard;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleInstallmentPlan;
use App\Models\SaleTicket;
use App\Models\TicketWaitlist;
use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\InstallmentService;
use App\Services\PassBookingService;
use App\Services\PassRedemptionService;
use App\Services\Payments\CheckoutContext;
use App\Services\RoleMailerService;
use App\Services\SaleSettlementService;
use App\Services\TicketVolumeDiscount;
use App\Services\UsageTrackingService;
use App\Services\WebhookService;
use App\Utils\CsvUtils;
use App\Utils\HoneypotUtils;
use App\Utils\MoneyUtils;
use App\Utils\QrCodeUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class TicketController extends Controller
{
    use \App\Traits\HandlesSaleStatusActions;

    public function tickets()
    {
        $user = auth()->user();
        $past = request()->query('past') == 1;

        $sortBy = request()->get('sort_by', 'event_date');
        $sortDir = request()->get('sort_dir', '');
        $allowedSortColumns = ['event_date', 'status', 'event_name'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'event_date';
        }

        $query = Sale::with('event', 'saleTickets')
            ->where('user_id', $user->id)
            ->where('is_deleted', false);

        if ($past) {
            $query->where(function ($q) {
                $q->where('event_date', '<', now()->subDay()->startOfDay())
                    ->orWhereHas('event', function ($eq) {
                        $eq->where('starts_at', '<', now()->subDay()->startOfDay());
                    });
            })
                ->whereDoesntHave('event', function ($eq) {
                    $eq->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                });
            $defaultDir = 'desc';
            $sortDir = $sortDir ? (strtolower($sortDir) === 'asc' ? 'asc' : 'desc') : $defaultDir;
            if ($sortBy === 'event_name') {
                $query->orderBy(
                    Event::select('name')->whereColumn('events.id', 'sales.event_id'),
                    $sortDir
                );
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
            $sales = $query->get();
        } else {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('event_date', '>=', now()->subDay()->startOfDay())
                        ->whereHas('event', function ($eq) {
                            $eq->where('starts_at', '>=', now()->subDay()->startOfDay());
                        });
                })->orWhereHas('event', function ($eq) {
                    $eq->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                });
            });
            $defaultDir = 'asc';
            $sortDir = $sortDir ? (strtolower($sortDir) === 'asc' ? 'asc' : 'desc') : $defaultDir;
            if ($sortBy === 'event_name') {
                $query->orderBy(
                    Event::select('name')->whereColumn('events.id', 'sales.event_id'),
                    $sortDir
                );
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
            $sales = $query->get();

            $hasPastTickets = Sale::where('user_id', $user->id)
                ->where('is_deleted', false)
                ->where(function ($q) {
                    $q->where('event_date', '<', now()->subDay()->startOfDay())
                        ->orWhereHas('event', function ($eq) {
                            $eq->where('starts_at', '<', now()->subDay()->startOfDay());
                        });
                })
                ->whereDoesntHave('event', function ($eq) {
                    $eq->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                })
                ->exists();
        }

        return view('ticket.index', compact('sales', 'past', 'sortBy', 'sortDir') + ($past ? [] : compact('hasPastTickets')));
    }

    public function sales()
    {
        $filter = strtolower(request()->filter ?? '');
        $includePast = request()->query('include_past') == 1;
        $query = $this->salesQuery($filter, includePast: $includePast);

        $sortBy = request()->get('sort_by', '');
        $sortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['name', 'status', 'created_at', 'payment_amount', 'transaction_reference', 'event_name'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = '';
        }

        $count = $query->count();

        if ($sortBy) {
            if ($sortBy === 'event_name') {
                $query->orderBy(
                    Event::select('name')->whereColumn('events.id', 'sales.event_id'),
                    $sortDir
                );
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $sales = $query->paginate(50, ['*'], 'page')->withQueryString();

        // Eager-load guest sales for grouped primaries
        $sales->load(['guestSales' => function ($q) {
            $q->where('is_deleted', false)->with('saleTickets.ticket', 'feedback');
        }]);

        // Derive group counts from eager-loaded guests
        $groupCounts = $sales->filter(fn ($s) => $s->isPrimarySale())
            ->mapWithKeys(fn ($s) => [$s->id => $s->guestSales->count()])
            ->filter(fn ($count) => $count > 0);

        if (request()->ajax()) {
            $tab = request()->query('tab');
            if ($tab === 'feedback') {
                $user = auth()->user();
                $hasPro = $user->roles()->get()->contains(fn ($role) => $role->isPro());
                if (! $hasPro) {
                    abort(403);
                }

                return view('ticket.feedback_table', $this->getFeedbackData());
            }

            return view('ticket.sales_table', compact('sales', 'groupCounts', 'sortBy', 'sortDir'));
        } else {
            $user = auth()->user();
            $waitlistCount = TicketWaitlist::whereHas('event', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['waiting', 'notified'])->count();

            $waitlistEntries = collect();

            $hasPro = $user->roles()->get()->contains(fn ($role) => $role->isPro());

            $subscriptions = $this->getSubscriptionsData();
            $subscriptionsCount = $subscriptions->count();

            $giftCards = $this->getGiftCardsData();
            $giftCardsCount = $giftCards->count();

            $ticketQuotas = $this->getTicketQuotas();

            $installmentData = $this->getInstallmentsData();
            $installments = $installmentData['installments'];
            $installmentsCount = $installments->count();
            $installmentTotals = $installmentData['installmentTotals'];
            $installmentForecast = $installmentData['installmentForecast'];

            return view('ticket.sales', compact('sales', 'count', 'waitlistCount', 'waitlistEntries', 'hasPro', 'groupCounts', 'sortBy', 'sortDir', 'subscriptions', 'subscriptionsCount', 'giftCards', 'giftCardsCount', 'ticketQuotas', 'installments', 'installmentsCount', 'installmentTotals', 'installmentForecast'));
        }
    }

    /**
     * Free-plan ticket allowances for the schedules this user OWNS, for the banner on the Sales page.
     *
     * This page aggregates across every schedule, so there is no single $role to read. Returned for
     * any free schedule that has sold at least one paid ticket this month, at any percentage:
     * staying quiet below some threshold would mean the first time an organizer ever sees the meter
     * is when half of it is already gone, on the one page whose whole subject is sales.
     *
     * @return \Illuminate\Support\Collection<int, array{role: Role, used: int, limit: int}>
     */
    private function getTicketQuotas()
    {
        if (! config('app.hosted')) {
            return collect();
        }

        return auth()->user()->roles()
            ->wherePivot('level', 'owner')
            // isPro() reads the subscriptions relation; without this each role lazy-loads its own.
            ->with('subscriptions')
            ->get()
            ->map(function ($role) {
                $limit = $role->ticketSaleLimit();

                // Null limit short-circuits before any counting, so paid schedules cost nothing here.
                if (is_null($limit)) {
                    return null;
                }

                $used = $role->ticketsSoldThisMonth();

                return $used > 0 ? ['role' => $role, 'used' => $used, 'limit' => $limit] : null;
            })
            ->filter()
            ->sortByDesc(fn ($row) => $row['used'] / max(1, $row['limit']))
            ->values();
    }

    /**
     * Build the rows for the Gift cards tab: every card sold on schedules the
     * user owns, its balance, and where it has been redeemed.
     */
    private function getGiftCardsData()
    {
        $user = auth()->user();

        return GiftCard::whereHas('role', fn ($q) => $q->where('user_id', $user->id))
            ->with(['role:id,name,subdomain', 'sales' => function ($q) {
                $q->where('is_deleted', false)->whereNotNull('gift_card_amount')->with('event:id,name');
            }])
            ->orderByDesc('id')
            ->get()
            ->map(function ($giftCard) {
                return [
                    'id' => UrlUtils::encodeId($giftCard->id),
                    'code' => $giftCard->formattedCode(),
                    'schedule' => $giftCard->role->name,
                    'purchaser_name' => $giftCard->purchaser_name,
                    'purchaser_email' => $giftCard->purchaser_email,
                    'recipient_name' => $giftCard->recipient_name,
                    'recipient_email' => $giftCard->recipient_email,
                    'message' => $giftCard->message,
                    'amount' => $giftCard->amount,
                    'remaining_amount' => $giftCard->remaining_amount,
                    'currency_code' => $giftCard->currency_code,
                    'status' => $giftCard->displayStatus(),
                    'payment_method' => $giftCard->payment_method,
                    'expires_at' => $giftCard->expires_at ? $giftCard->expires_at->format('M j, Y') : null,
                    'created_at' => $giftCard->created_at->format('M j, Y'),
                    'view_url' => $giftCard->getViewUrl(),
                    'can_mark_paid' => in_array($giftCard->status, ['unpaid', 'amount_mismatch']),
                    'can_cancel' => in_array($giftCard->status, ['unpaid', 'active', 'amount_mismatch']),
                    'can_refund' => $giftCard->status === 'active',
                    'can_resend' => $giftCard->status === 'active',
                    'redemptions' => $giftCard->sales->sortByDesc('id')->values()->map(fn ($sale) => [
                        'event' => $sale->event?->name ?? '-',
                        'date' => $sale->created_at->format('M j, Y'),
                        'amount' => (float) $sale->gift_card_amount,
                        'status' => $sale->status,
                    ]),
                ];
            });
    }

    /**
     * Build the per-subscriber subscription/pass usage rows for the Subscriptions
     * tab: who holds a pass, how many visits they've used, and at which events.
     */
    private function getSubscriptionsData()
    {
        $user = auth()->user();

        $saleTickets = SaleTicket::whereHas('ticket', fn ($q) => $q->where('is_pass', true))
            ->whereHas('sale', function ($q) use ($user) {
                $q->where('is_deleted', false)
                    ->where('status', 'paid')
                    ->whereHas('event', fn ($eq) => $eq->where('user_id', $user->id));
            })
            ->with(['sale:id,name,email,event_id,secret', 'ticket:id,type,pass_usage_type,pass_max_uses'])
            ->get();

        // Resolve names for every event referenced in a usage log, in one query.
        $eventIds = $saleTickets->flatMap(fn ($st) => collect($st->pass_usages ?? [])
            ->map(fn ($u) => (int) ($u['event_id'] ?? 0)))->filter()->unique();
        $eventNames = Event::whereIn('id', $eventIds)->pluck('name', 'id');

        return $saleTickets->map(function ($st) use ($eventNames) {
            $usages = collect($st->pass_usages ?? [])->sortByDesc('at')->values();
            $count = $usages->count();
            $type = $st->ticket->pass_usage_type;
            $max = $st->ticket->pass_max_uses;

            if ($type === 'total' && $max) {
                $limitLabel = $count.' / '.$max;
            } else {
                $limitLabel = trans_choice('messages.visits_count', $count, ['count' => $count]);
            }

            if ($st->passIsExpired()) {
                $status = 'expired';
            } elseif ($type === 'total' && $max && $count >= $max) {
                $status = 'used_up';
            } else {
                $status = 'active';
            }

            return [
                'name' => $st->sale->name,
                'email' => $st->sale->email,
                'ticket_type' => $st->ticket->type,
                'limit_label' => $limitLabel,
                'expires_at' => $st->pass_expires_at ? $st->pass_expires_at->format('M j, Y') : null,
                'status' => $status,
                'ticket_url' => route('ticket.view', [
                    'event_id' => UrlUtils::encodeId($st->sale->event_id),
                    'secret' => $st->sale->secret,
                ]),
                'usages' => $usages->map(fn ($u) => [
                    'event' => $eventNames[(int) ($u['event_id'] ?? 0)] ?? '—',
                    'date' => $u['date'] ?? '',
                    'time' => isset($u['at']) ? Carbon::createFromTimestamp((int) $u['at'])->format('M j, g:i A') : '',
                    'kind' => match ($u['kind'] ?? 'redemption') {
                        'booking' => 'booked',
                        'forfeited' => 'forfeited',
                        default => 'attended',
                    },
                ])->values(),
            ];
        })->sortBy('name')->values();
    }

    /**
     * The Installments tab.
     *
     * Scoped like the rest of this page: every event the signed-in user owns, across all of their
     * schedules. Two things it returns beyond the plan rows themselves:
     *
     * - Per-CURRENCY totals. /sales aggregates across schedules, so an organizer with a Rome event
     *   and a London one has two currencies and a single summed figure would simply be wrong. The
     *   gift-cards partial already solves this the same way.
     * - A cash-flow forecast by month, which is the thing an organizer selling months ahead
     *   actually wants to know and what turns this from a page you visit when something breaks
     *   into one you open on purpose.
     */
    private function getInstallmentsData(): array
    {
        $user = auth()->user();

        $plans = SaleInstallmentPlan::query()
            ->whereHas('sale', function ($q) use ($user) {
                // Paid sales only, matching getSubscriptionsData(). An abandoned Stripe session
                // leaves an `active` plan on an `unpaid` sale, and without this it showed as a
                // live plan reading "On track, 0 / 4" and its scheduled rows inflated the
                // "Expected by month" forecast with money nobody ever committed to paying.
                $q->where('is_deleted', false)
                    ->where('status', 'paid')
                    ->whereHas('event', fn ($eq) => $eq->where('user_id', $user->id));
            })
            ->with(['installments', 'sale:id,name,email,event_id,secret', 'sale.event:id,name'])
            ->get();

        $rows = $plans->map(function ($plan) {
            $next = $plan->nextDueInstallment();
            $overdue = $plan->isDelinquent()
                || $plan->installments->contains(fn ($i) => $i->isOverdue());

            return [
                'name' => $plan->sale?->name,
                'email' => $plan->sale?->email,
                'event' => $plan->sale?->event?->name,
                'currency' => $plan->currency,
                'progress' => $plan->paidCount().' / '.$plan->installment_count,
                'collected' => (float) $plan->amount_paid,
                'outstanding' => $plan->amountRemaining(),
                'next_due' => $next?->due_at?->format('M j, Y'),
                'next_amount' => $next ? (float) $next->amount : null,
                // 'delinquent' is a schema value; the organizer reads "overdue".
                'status' => $plan->status === 'delinquent' ? 'overdue' : $plan->status,
                'is_overdue' => $overdue,
                'card' => $plan->card_brand && $plan->card_last4
                    ? __('messages.installment_card_on_file', ['brand' => ucfirst($plan->card_brand), 'last4' => $plan->card_last4])
                    : null,
                'card_expiring' => $plan->cardExpiresBeforeFinalPayment(),
                'error' => $next?->humanErrorKey() ? __($next->humanErrorKey()) : null,
                // Money that arrived and could not be applied to a row: an overpayment, or a
                // payment for a plan that had already been cancelled. It is deliberately never
                // auto-applied, so it sits here until the organizer looks. Without this it was
                // written to the database and read by nothing.
                'unmatched' => (float) $plan->unmatched_amount > 0 ? (float) $plan->unmatched_amount : null,
                // A charge whose outcome we never learned. Not retried on purpose: a retry after
                // Stripe's idempotency key expires is how a timeout becomes a double charge, so
                // the resolution is a human comparing the reference against their dashboard.
                'needs_check' => $plan->installments
                    ->where('status', 'awaiting_reconciliation')
                    ->isNotEmpty(),
                // Every payment reference the organizer needs to refund by hand on their own
                // Stripe dashboard: nothing in this app refunds a Connect ticket sale, and the
                // sale's single transaction_reference cannot identify N charges.
                'payments' => $plan->installments->map(fn ($i) => [
                    'sequence' => $i->sequence,
                    'amount' => (float) $i->amount,
                    'due_at' => $i->due_at?->format('M j, Y'),
                    'status' => $i->status,
                    'reference' => $i->transaction_reference,
                ])->values(),
            ];
        })
            // An action queue, not a ledger: the rows that need doing something about come first.
            // Money that arrived and could not be applied, and a charge with no known outcome,
            // outrank an overdue payment - they are the two the organizer alone can resolve.
            ->sortBy(fn ($r) => [
                $r['unmatched'] || $r['needs_check'] ? 0 : 1,
                $r['is_overdue'] ? 0 : 1,
                $r['next_due'] ?? '9999',
            ])
            ->values();

        $totals = $plans->groupBy('currency')->map(fn ($group, $currency) => [
            'currency' => $currency,
            'count' => $group->count(),
            'collected' => (float) $group->sum(fn ($p) => (float) $p->amount_paid),
            'outstanding' => (float) $group->sum(fn ($p) => $p->amountRemaining()),
        ])->values();

        // Everything still owed, not merely everything still on schedule. Filtering to 'scheduled'
        // silently dropped overdue and parked payments out of the figure the organizer plans
        // against, so a plan going wrong made the forecast look better rather than worse.
        $forecast = $plans->flatMap(fn ($p) => $p->installments
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->map(fn ($i) => [
                'month' => $i->due_at?->format('Y-m'),
                'label' => $i->due_at?->format('M Y'),
                'amount' => (float) $i->amount,
                'currency' => $p->currency,
            ]))
            ->filter(fn ($r) => $r['month'])
            ->groupBy(fn ($r) => $r['currency'].'|'.$r['month'])
            ->map(fn ($group) => [
                'label' => $group->first()['label'],
                'currency' => $group->first()['currency'],
                'amount' => $group->sum('amount'),
                'count' => $group->count(),
            ])
            ->sortBy('label')
            ->values();

        return [
            'installments' => $rows,
            'installmentTotals' => $totals,
            'installmentForecast' => $forecast,
        ];
    }

    private function salesQuery(?string $filter, bool $primaryOnly = true, bool $includePast = false)
    {
        $user = auth()->user();

        $query = Sale::with('event.creatorRole', 'saleTickets.ticket', 'promoCode', 'feedback')
            ->where('is_deleted', false)
            ->whereHas('event', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        if (! $includePast) {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('event_date', '>=', now()->subDay()->startOfDay())
                        ->whereHas('event', function ($eq) {
                            $eq->where('starts_at', '>=', now()->subDay()->startOfDay());
                        });
                })->orWhereHas('event', function ($eq) {
                    $eq->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                });
            });
        }

        if ($primaryOnly) {
            $query->where(function ($q) {
                $q->whereNull('group_id')
                    ->orWhereColumn('group_id', 'id');
            });
        }

        if ($filter) {
            $query->where(function ($q) use ($filter, $primaryOnly) {
                $q->where('status', 'LIKE', "%{$filter}%")
                    ->orWhere('transaction_reference', 'LIKE', "%{$filter}%")
                    ->orWhere('email', 'LIKE', "%{$filter}%")
                    ->orWhere('name', 'LIKE', "%{$filter}%")
                    ->orWhere('phone', 'LIKE', "%{$filter}%")
                    ->orWhereHas('event', function ($q) use ($filter) {
                        $q->where('name', 'LIKE', "%{$filter}%");
                    });

                if ($primaryOnly) {
                    $q->orWhereExists(function ($sub) use ($filter) {
                        $sub->select(DB::raw(1))
                            ->from('sales as guest')
                            ->whereColumn('guest.group_id', 'sales.id')
                            ->whereColumn('guest.id', '!=', 'guest.group_id')
                            ->where('guest.is_deleted', false)
                            ->where(function ($gq) use ($filter) {
                                $gq->where('guest.name', 'LIKE', "%{$filter}%")
                                    ->orWhere('guest.email', 'LIKE', "%{$filter}%")
                                    ->orWhere('guest.phone', 'LIKE', "%{$filter}%");
                            });
                    });
                }
            });
        }

        return $query;
    }

    private function getFeedbackData()
    {
        $user = auth()->user();

        // --- Submitted feedback (existing) ---
        $sortBy = request()->get('sort_by', 'created_at');
        $sortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['rating', 'created_at', 'event_date', 'event_name', 'attendee_name'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $feedbackQuery = EventFeedback::whereHas('event', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereHas('sale', fn ($q) => $q->where('is_deleted', false))
            ->with(['event', 'sale']);

        if ($sortBy === 'event_name') {
            $feedbackQuery->orderBy(
                Event::select('name')->whereColumn('events.id', 'event_feedbacks.event_id'),
                $sortDir
            );
        } elseif ($sortBy === 'attendee_name') {
            $feedbackQuery->orderBy(
                Sale::select('name')->whereColumn('sales.id', 'event_feedbacks.sale_id'),
                $sortDir
            );
        } else {
            $feedbackQuery->orderBy($sortBy, $sortDir);
        }

        $feedbacks = $feedbackQuery->paginate(50, ['*'], 'feedback_page')->withQueryString();

        $stats = EventFeedback::whereHas('event', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('sale', fn ($q) => $q->where('is_deleted', false)->where('status', 'paid'))
            ->selectRaw('COUNT(*) as feedback_count, AVG(rating) as avg_rating')
            ->first();

        $feedbackCount = $stats->feedback_count;
        $averageRating = $stats->avg_rating ? round($stats->avg_rating, 1) : null;

        $totalEligibleSales = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->where(function ($q) {
                $q->where(fn ($q2) => $q2->whereNotNull('feedback_sent_at')->where('feedback_sent_at', '>', '2000-01-02'))
                    ->orWhereHas('feedback');
            })
            ->whereHas('event', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        $responseRate = $totalEligibleSales > 0 ? round(($feedbackCount / $totalEligibleSales) * 100) : 0;

        // --- Pending sales (eligible but not yet sent) ---
        // Pre-load roles by subdomain for eligibility checks (mirrors SendFeedbackRequests command)
        $rolesBySubdomain = Role::where('is_deleted', false)
            ->get()
            ->filter(fn ($r) => $r->isPro())
            ->keyBy('subdomain');

        $pendingSales = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->whereNull('feedback_sent_at')
            ->whereDoesntHave('feedback')
            ->where(fn ($q) => $q->whereNull('group_id')->orWhereColumn('group_id', 'id'))
            ->excludeTestEmails()
            ->whereHas('event', fn ($q) => $q->where('user_id', $user->id))
            ->with(['event.creatorRole'])
            ->get();

        $pendingGroups = collect();
        $nextSendAt = null;

        foreach ($pendingSales as $sale) {
            $event = $sale->event;
            if (! $event) {
                continue;
            }

            // Resolve role from sale's subdomain (same as command)
            $saleRole = $rolesBySubdomain->get($sale->subdomain);
            if (! $saleRole) {
                continue;
            }

            if (is_demo_role($saleRole)) {
                continue;
            }

            // Check email sending capability (same as command)
            if (config('app.hosted')) {
                if (! $saleRole->hasEmailSettings()) {
                    continue;
                }
            } else {
                $mailer = config('mail.default');
                if (in_array($mailer, ['log', 'array'])) {
                    continue;
                }
            }

            // Check feedback enabled using event's own setting or role fallback
            if (! is_null($event->feedback_enabled)) {
                if (! $event->feedback_enabled) {
                    continue;
                }
            } else {
                if (! $saleRole->feedback_enabled) {
                    continue;
                }
            }

            $endDateTime = $event->getEndDateTime($sale->event_date, true, $event->scheduleTimezone());
            if ($endDateTime->isFuture() || $endDateTime->copy()->addDays(30)->isPast()) {
                continue;
            }

            $delayHours = (int) ($saleRole->feedback_delay_hours ?? 24);
            $estimatedSendAt = $endDateTime->copy()->addHours($delayHours);

            // Ceil to next hour boundary (cron runs hourly)
            if ($estimatedSendAt->minute > 0 || $estimatedSendAt->second > 0) {
                $estimatedSendAt->startOfHour()->addHour();
            }

            $groupKey = $event->id.'-'.($sale->event_date ?? 'default');

            if (! $pendingGroups->has($groupKey)) {
                $pendingGroups[$groupKey] = (object) [
                    'event_name' => $event->name,
                    'event_date' => $sale->event_date,
                    'estimated_send_at' => $estimatedSendAt,
                    'count' => 0,
                    'sales' => collect(),
                ];
            }

            $pendingGroups[$groupKey]->count++;
            if ($pendingGroups[$groupKey]->sales->count() < 100) {
                $pendingGroups[$groupKey]->sales->push($sale);
            }

            if (! $nextSendAt || $estimatedSendAt->lt($nextSendAt)) {
                $nextSendAt = $estimatedSendAt;
            }
        }

        $pendingGroups = $pendingGroups->sortBy('estimated_send_at')->values();
        $pendingCount = $pendingGroups->sum('count');
        $readyToSendCount = $pendingGroups->filter(fn ($g) => $g->estimated_send_at->isPast())->sum('count');

        // --- Sent awaiting response (exclude cancelled with sentinel date) ---
        $awaitingQuery = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->whereNotNull('feedback_sent_at')
            ->where('feedback_sent_at', '>', '2000-01-02')
            ->whereDoesntHave('feedback')
            ->whereHas('event', fn ($q) => $q->where('user_id', $user->id));

        $awaitingCount = $awaitingQuery->count();

        $awaitingSales = (clone $awaitingQuery)
            ->with('event')
            ->orderByDesc('feedback_sent_at')
            ->limit(50)
            ->get();

        // --- Excluded count (for debugging) ---
        // Only count exclusions for events that have feedback enabled
        $feedbackEnabledEventIds = $pendingGroups->flatMap(fn ($g) => $g->sales->pluck('event_id'))
            ->merge($awaitingSales->pluck('event_id'))
            ->unique();

        $excludedCount = 0;
        if ($feedbackEnabledEventIds->isNotEmpty()) {
            $excludedCount = Sale::where('status', 'paid')
                ->where('is_deleted', false)
                ->whereNull('feedback_sent_at')
                ->whereDoesntHave('feedback')
                ->whereIn('event_id', $feedbackEnabledEventIds)
                ->where(function ($q) {
                    $q->whereNull('email')
                        ->orWhere('email', '')
                        ->orWhere('email', 'like', '%@example.com')
                        ->orWhere('email', 'like', '%@example.org')
                        ->orWhere('email', 'like', '%@example.net')
                        ->orWhere('email', 'like', '%@test.com')
                        ->orWhere('email', 'like', '%@test.org')
                        ->orWhere('email', 'like', '%@test.net')
                        ->orWhere('email', 'like', '%@localhost')
                        ->orWhere(function ($q2) {
                            $q2->whereNotNull('group_id')
                                ->whereColumn('group_id', '!=', 'id');
                        });
                })
                ->count();
        }

        return compact(
            'feedbacks', 'feedbackCount', 'averageRating', 'responseRate', 'sortBy', 'sortDir',
            'pendingGroups', 'pendingCount', 'readyToSendCount', 'nextSendAt',
            'awaitingSales', 'awaitingCount',
            'excludedCount'
        );
    }

    public function sendFeedbackNow()
    {
        $user = auth()->user();
        $count = 0;

        $rolesBySubdomain = Role::where('is_deleted', false)
            ->get()
            ->filter(fn ($r) => $r->isPro())
            ->keyBy('subdomain');

        $pendingSales = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->whereNull('feedback_sent_at')
            ->whereDoesntHave('feedback')
            ->where(fn ($q) => $q->whereNull('group_id')->orWhereColumn('group_id', 'id'))
            ->excludeTestEmails()
            ->whereHas('event', fn ($q) => $q->where('user_id', $user->id))
            ->with(['event'])
            ->get();

        foreach ($pendingSales as $sale) {
            try {
                $event = $sale->event;
                if (! $event) {
                    continue;
                }

                $saleRole = $rolesBySubdomain->get($sale->subdomain);
                if (! $saleRole) {
                    continue;
                }

                if (is_demo_role($saleRole)) {
                    continue;
                }

                if (config('app.hosted')) {
                    if (! $saleRole->hasEmailSettings()) {
                        continue;
                    }
                } else {
                    $mailer = config('mail.default');
                    if (in_array($mailer, ['log', 'array'])) {
                        continue;
                    }
                }

                // Check feedback enabled
                if (! is_null($event->feedback_enabled)) {
                    if (! $event->feedback_enabled) {
                        continue;
                    }
                } else {
                    if (! $saleRole->feedback_enabled) {
                        continue;
                    }
                }

                $endDateTime = $event->getEndDateTime($sale->event_date, true, $event->scheduleTimezone());
                if ($endDateTime->isFuture() || $endDateTime->copy()->addDays(30)->isPast()) {
                    continue;
                }

                $delayHours = (int) ($saleRole->feedback_delay_hours ?? 24);
                if ($endDateTime->copy()->addHours($delayHours)->isFuture()) {
                    continue;
                }

                $sale->feedback_sent_at = now();
                $sale->save();

                \App\Jobs\SendFeedbackEmail::dispatch(
                    $sale->id,
                    $event->id,
                    $saleRole->id,
                    $saleRole->language_code ?? app()->getLocale()
                );

                $count++;
            } catch (\Exception $e) {
                $sale->feedback_sent_at = null;
                $sale->save();
                report($e);
            }
        }

        return redirect()->route('sales', ['tab' => 'feedback'])->with('message', __('messages.feedback_sent_count', ['count' => $count]));
    }

    public function cancelFeedback()
    {
        $user = auth()->user();
        $count = 0;

        $rolesBySubdomain = Role::where('is_deleted', false)
            ->get()
            ->filter(fn ($r) => $r->isPro())
            ->keyBy('subdomain');

        $pendingSales = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->whereNull('feedback_sent_at')
            ->whereDoesntHave('feedback')
            ->where(fn ($q) => $q->whereNull('group_id')->orWhereColumn('group_id', 'id'))
            ->excludeTestEmails()
            ->whereHas('event', fn ($q) => $q->where('user_id', $user->id))
            ->with(['event'])
            ->get();

        // Use a sentinel date to distinguish cancelled from actually sent
        $cancelledAt = \Carbon\Carbon::create(2000, 1, 1);

        foreach ($pendingSales as $sale) {
            $event = $sale->event;
            if (! $event) {
                continue;
            }

            $saleRole = $rolesBySubdomain->get($sale->subdomain);
            if (! $saleRole || is_demo_role($saleRole)) {
                continue;
            }

            if (config('app.hosted')) {
                if (! $saleRole->hasEmailSettings()) {
                    continue;
                }
            } else {
                $mailer = config('mail.default');
                if (in_array($mailer, ['log', 'array'])) {
                    continue;
                }
            }

            if (! is_null($event->feedback_enabled)) {
                if (! $event->feedback_enabled) {
                    continue;
                }
            } elseif (! $saleRole->feedback_enabled) {
                continue;
            }

            // Only cancel feedback for events that have already ended
            $endDateTime = $event->getEndDateTime($sale->event_date, true, $event->scheduleTimezone());
            if ($endDateTime->isFuture() || $endDateTime->copy()->addDays(30)->isPast()) {
                continue;
            }

            $sale->feedback_sent_at = $cancelledAt;
            $sale->save();
            $count++;
        }

        return redirect()->route('sales', ['tab' => 'feedback'])->with('message', __('messages.feedback_cancelled_count', ['count' => $count]));
    }

    public function exportSales()
    {
        // Pro only. The export already carries Pro-only columns (promo code, discount, gift card,
        // check-in status, pass usage), and it needed no gate while only Pro schedules had sales to
        // export. $hasPro here is user-level on purpose, matching the Sales page it is reached from:
        // the export spans every schedule the user owns, so a per-schedule check has nothing to bind
        // to. It is the same shape as the Feedback tab guard above.
        $hasPro = auth()->user()->roles()->get()->contains(fn ($role) => $role->isPro());

        if (! $hasPro) {
            abort(403);
        }

        $filter = strtolower(request()->filter ?? '');
        $includePast = request()->query('include_past') == 1;
        $sales = $this->salesQuery($filter, primaryOnly: false, includePast: $includePast)->orderBy('created_at', 'DESC')->get();

        // First pass: collect unique custom field names
        $customFieldNames = [];
        foreach ($sales as $sale) {
            if ($sale->event->custom_fields && count($sale->event->custom_fields) > 0) {
                $fallbackIdx = 1;
                foreach ($sale->event->custom_fields as $fieldConfig) {
                    $idx = $fieldConfig['index'] ?? $fallbackIdx;
                    $fallbackIdx++;
                    if ($idx >= 1 && $idx <= 10 && ! in_array($fieldConfig['name'], $customFieldNames)) {
                        $customFieldNames[] = $fieldConfig['name'];
                    }
                }
            }
            foreach ($sale->saleTickets as $saleTicket) {
                if ($saleTicket->ticket && $saleTicket->ticket->custom_fields && count($saleTicket->ticket->custom_fields) > 0) {
                    $fallbackIdx = 1;
                    foreach ($saleTicket->ticket->custom_fields as $fieldConfig) {
                        $idx = $fieldConfig['index'] ?? $fallbackIdx;
                        $fallbackIdx++;
                        if ($idx >= 1 && $idx <= 10 && ! in_array($fieldConfig['name'], $customFieldNames)) {
                            $customFieldNames[] = $fieldConfig['name'];
                        }
                    }
                }
            }
        }

        $filename = 'sales-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($sales, $customFieldNames) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            $headers = ['Name', 'Email', 'Phone', 'Event', 'Event Date', 'Tickets', 'Add-ons', 'Quantity', 'Amount', 'Currency', 'Promo Code', 'Discount', 'Gift Card', 'Gift Card Amount', 'Transaction Reference', 'Payment Method', 'Status', 'Date', 'Group ID', 'Order ID', 'Check-in Status', 'Check-in Time', 'Pass Type', 'Pass Visits Used', 'Pass Expires'];
            $headers = array_merge($headers, $customFieldNames);
            fputcsv($handle, $headers);

            // Second pass: write data rows
            foreach ($sales as $sale) {
                $tickets = $sale->saleTickets->filter(fn ($st) => $st->ticket && ! $st->ticket->is_addon)->map(function ($st) {
                    return ($st->ticket->type ?: '').' x'.$st->quantity;
                })->implode(', ');

                $addons = $sale->saleTickets->filter(fn ($st) => $st->ticket && $st->ticket->is_addon)->map(function ($st) {
                    return ($st->ticket->type ?: '').' x'.$st->quantity;
                })->implode(', ');

                // Free-text cells (attendee-supplied or owner-supplied) are run
                // through CsvUtils::sanitizeCell to neutralize CSV/formula injection
                // when the export is opened in a spreadsheet app. App-formatted
                // numeric/date/encoded columns are left as-is.
                $row = [
                    CsvUtils::sanitizeCell($sale->name),
                    CsvUtils::sanitizeCell($sale->email),
                    CsvUtils::sanitizeCell($sale->phone),
                    CsvUtils::sanitizeCell($sale->event->name),
                    $sale->event_date,
                    CsvUtils::sanitizeCell($tickets),
                    CsvUtils::sanitizeCell($addons),
                    $sale->quantity(),
                    number_format($sale->payment_amount, 2, '.', ''),
                    $sale->event->ticket_currency_code,
                    CsvUtils::sanitizeCell($sale->promoCode ? $sale->promoCode->code : ''),
                    $sale->discount_amount ? number_format($sale->discount_amount, 2, '.', '') : '',
                    CsvUtils::sanitizeCell($sale->giftCard ? $sale->giftCard->formattedCode() : ''),
                    $sale->gift_card_amount ? number_format($sale->gift_card_amount, 2, '.', '') : '',
                    CsvUtils::sanitizeCell($sale->transaction_reference),
                    CsvUtils::sanitizeCell($sale->payment_method),
                    $sale->status,
                    $sale->created_at->timezone($sale->event->creatorRole?->timezone ?? config('app.timezone'))->format('Y-m-d H:i'),
                    $sale->group_id ? UrlUtils::encodeId($sale->group_id) : '',
                    $sale->order_id ? UrlUtils::encodeId($sale->order_id) : '',
                ];

                // Check-in: find the first non-null timestamp across all SaleTicket seats
                $checkinTimestamp = null;
                foreach ($sale->saleTickets as $saleTicket) {
                    $seats = $saleTicket->seats ? json_decode($saleTicket->seats, true) : [];
                    if (is_array($seats)) {
                        foreach ($seats as $timestamp) {
                            if ($timestamp !== null && ($checkinTimestamp === null || $timestamp < $checkinTimestamp)) {
                                $checkinTimestamp = $timestamp;
                            }
                        }
                    }
                }
                $row[] = $checkinTimestamp !== null ? 'Yes' : 'No';
                $row[] = $checkinTimestamp !== null
                    ? \Carbon\Carbon::createFromTimestamp($checkinTimestamp)->timezone($sale->event->creatorRole?->timezone ?? config('app.timezone'))->format('Y-m-d H:i')
                    : '';

                // Pass / subscription columns
                $passSt = $sale->saleTickets->first(fn ($st) => $st->ticket && $st->ticket->is_pass);
                $row[] = $passSt ? $passSt->ticket->pass_usage_type : '';
                $row[] = $passSt ? $passSt->passUsageCount() : '';
                $row[] = ($passSt && $passSt->pass_expires_at) ? $passSt->pass_expires_at->format('Y-m-d') : '';

                // Build custom field values
                $customValues = array_fill(0, count($customFieldNames), '');

                // Event-level custom fields
                if ($sale->event->custom_fields && count($sale->event->custom_fields) > 0) {
                    $fallbackIdx = 1;
                    foreach ($sale->event->custom_fields as $fieldConfig) {
                        $idx = $fieldConfig['index'] ?? $fallbackIdx;
                        $fallbackIdx++;
                        if ($idx >= 1 && $idx <= 10) {
                            $value = $sale->{"custom_value{$idx}"};
                            if ($value) {
                                $colIndex = array_search($fieldConfig['name'], $customFieldNames);
                                if ($colIndex !== false) {
                                    $customValues[$colIndex] = $value;
                                }
                            }
                        }
                    }
                }

                // Ticket-level custom fields
                foreach ($sale->saleTickets as $saleTicket) {
                    if (! $saleTicket->ticket || ! $saleTicket->ticket->custom_fields) {
                        continue;
                    }
                    $fallbackIdx = 1;
                    foreach ($saleTicket->ticket->custom_fields as $fieldConfig) {
                        $idx = $fieldConfig['index'] ?? $fallbackIdx;
                        $fallbackIdx++;
                        if ($idx >= 1 && $idx <= 10) {
                            $value = $saleTicket->{"custom_value{$idx}"};
                            if ($value) {
                                $colIndex = array_search($fieldConfig['name'], $customFieldNames);
                                if ($colIndex !== false) {
                                    if ($customValues[$colIndex] !== '') {
                                        $customValues[$colIndex] .= '; '.$value;
                                    } else {
                                        $customValues[$colIndex] = $value;
                                    }
                                }
                            }
                        }
                    }
                }

                $row = array_merge($row, array_map([CsvUtils::class, 'sanitizeCell'], $customValues));
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function checkout(TicketCheckoutRequest $request, $subdomain)
    {
        // Honeypot. withInput() matters: a bare back() on a false positive returns the
        // buyer to an emptied form with no explanation, and the next click looks dead.
        if (HoneypotUtils::isTripped($request)) {
            return back()->withInput()->with('error', __('messages.invalid_request'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());

        // One leg per event in the cart. A request without legs[] is a single leg built from the
        // flat fields, which is every request the single-event form has ever sent.
        $legs = $this->resolveCheckoutLegs($request);

        // A cart is held in the browser and is built to outlive the visit, so by the time it is
        // submitted a leg's event may have been unpublished, made private, taken off the schedule
        // or deleted. On the single-event path those states cannot be reached with a stale form, so
        // they still abort; from a cart they are ordinary and have to come back as something the
        // buyer can act on. Aborting sent them a bare 404 with no idea which of their events was at
        // fault - and the cart still held it, so every retry 404'd again.
        //
        // Keyed on the request carrying legs[], not on the leg COUNT: a one-item cart posts through
        // the same panel and deserves the same treatment, while the flat single-event form is
        // normalised into one leg here and must keep its aborts.
        $isCart = is_array($request->input('legs')) && $request->input('legs') !== [];

        // Marks this request as the cart's, so the panel only claims errors it actually caused.
        // $errors is a shared bag and the single-event form on the same page posts to this same
        // route, so without a marker its validation errors surfaced inside the cart popover.
        // Flashed once here rather than at each refusal, so no bail-out path can forget it; on a
        // successful checkout it rides to the ticket page, which does not render the cart.
        if ($isCart) {
            session()->flash('cart_submitted', true);
        }

        foreach ($legs as $index => $leg) {
            // canSellTickets() below reads both relations; without this they lazy-load on the
            // checkout POST.
            $event = Event::with(['tickets', 'roles'])->find(UrlUtils::decodeId($leg['event_id']));

            $unavailable = ! $event
                || ! $event->roles()->wherePivot('role_id', $role->id)->exists()
                || ($event->is_draft && ! $isMemberOrAdmin)
                || ($event->is_private
                    && ! $isMemberOrAdmin
                    && ! ($event->isPasswordProtected() && session()->has('event_password_'.$event->id)));

            if ($unavailable) {
                if ($isCart) {
                    return $this->refuseCartLeg($leg, $event?->translatedName());
                }

                abort($event && ! $event->roles()->wherePivot('role_id', $role->id)->exists() ? 403 : 404);
            }

            // Per-attendee events cannot be bought from the cart: it collects one name and email
            // for the whole purchase and carries no guest list, so the order would silently become
            // one anonymous multi-seat sale. The Add to cart button is hidden for them, so this
            // only catches a hand-built request - but it is the difference between refusing and
            // quietly discarding the attendee details the organizer asked for.
            if ($isCart && $event->individual_tickets) {
                // Its own message: the event is perfectly available, it just cannot be carted, and
                // "no longer available" would send the buyer to delete something they can still buy
                // from its own page.
                return $this->refuseCartLeg($leg, $event->translatedName(), 'messages.cart_event_needs_own_checkout');
            }

            // Verify event can sell tickets (checks past dates, tickets_enabled, and plan allowance)
            if (! $event->canSellTickets($leg['event_date'])) {
                return $isCart
                    ? $this->refuseCartLeg($leg, $event->translatedName())
                    : back()->withInput()->with('error', __('messages.tickets_not_available'));
            }

            // Per-row allowance check. canSellTickets() above answers "is this event selling at all",
            // which stays true while a free tier remains; this refuses the individual PAID rows the
            // schedule's monthly allowance can no longer cover, so a cart of free tiers still goes
            // through on a capped schedule.
            //
            // Whose allowance, whether offline payment is exempt and whether the 48-hour grace applies
            // are all decided inside Ticket::isSellable() -> Event::hasTicketAllowance(), which is the
            // same code the guest form filters on. Keeping one definition is what stops the buy button
            // and the write path disagreeing.
            //
            // Checked once, up front, then the whole cart is allowed through - the same way
            // canSendNewsletter() lets an entire newsletter overshoot. Refusing a guest mid-cart
            // because the organizer is one short is worse than a small, bounded overage. Across a
            // multi-event order that bound widens once per leg, which is accepted for the same reason.
            $unsellable = collect($leg['tickets'])
                ->filter(fn ($quantity) => (int) $quantity > 0)
                ->keys()
                ->map(fn ($hash) => $event->tickets->firstWhere('id', UrlUtils::decodeId($hash)))
                ->filter()
                ->contains(fn ($ticket) => ! $ticket->isSellable($leg['event_date']));

            if ($unsellable) {
                return $isCart
                    ? $this->refuseCartLeg($leg, $event->translatedName())
                    : back()->withInput()->with('error', __('messages.tickets_not_available'));
            }

            $legs[$index]['event'] = $event;
        }

        if ($error = $this->cartEligibilityError($legs)) {
            return back()->withInput()->with('error', $error);
        }

        // Deterministic lock order. Each leg row-locks its own tickets inside the one transaction,
        // so two overlapping carts holding the same two events in opposite orders would deadlock.
        //
        // Sorted HERE, before $event is taken, so that $event is the same leg the transaction below
        // returns as the order primary. Sorting afterwards left the two pointing at different
        // events whenever the cart's first event was not the lowest-id one, and every order-level
        // side effect keyed on $event - the audit log, the free-path analytics - was then filed
        // against an event the sale had nothing to do with.
        usort($legs, fn ($a, $b) => $a['event']->id <=> $b['event']->id);

        // Order-level fields below read from the first leg's event, which the guard above has
        // proven shares its owner, currency and payment method with every other leg.
        $event = $legs[0]['event'];

        if (! $user && $request->create_account && config('app.hosted')) {

            $utmParams = session('utm_params', []);

            // Fall back to cookie if session has no UTM data
            if (empty($utmParams) && $request->cookie('utm_params')) {
                $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'timezone' => $event->user->timezone,
                'language_code' => $event->user->language_code,
                'utm_source' => $utmParams['utm_source'] ?? null,
                'utm_medium' => $utmParams['utm_medium'] ?? null,
                'utm_campaign' => $utmParams['utm_campaign'] ?? null,
                'utm_content' => $utmParams['utm_content'] ?? null,
                'utm_term' => $utmParams['utm_term'] ?? null,
                'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
                'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
                'signup_intent' => 'ticket',
            ]);

            session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page']);

            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        // In payment link mode, quantities are selected on the Invoice Ninja purchase page
        $isPaymentLink = $event->payment_method === 'invoiceninja'
            && $event->user->invoiceninja_mode === 'payment_link';

        // Use database transaction with row locking to prevent race conditions
        // that could lead to overselling tickets
        try {
            $sale = DB::transaction(function () use ($request, $legs, $user, $subdomain, $isPaymentLink) {
                $sales = [];

                // Locked once for the whole order, then drawn down leg by leg. See the method.
                $giftCard = $this->resolveOrderGiftCard($request, $legs);

                foreach ($legs as $leg) {
                    $this->assertLegTicketsAvailable($leg, $leg['event'], $isPaymentLink);

                    $legSale = $this->newSaleForLeg($request, $leg, $leg['event'], $user, $subdomain);
                    $legSale->save();

                    $this->priceSaleLeg($legSale, $request, $leg, $leg['event'], $subdomain, $isPaymentLink, $giftCard);

                    $legSale->save();

                    $sales[] = $legSale;
                }

                // A single-event checkout stays exactly as it was: no order_id, nothing to group.
                if (count($sales) > 1) {
                    $legIds = array_map(fn ($legSale) => $legSale->id, $sales);
                    $primaryId = $sales[0]->id;

                    // Guest rows carry order_id too - priceSaleLeg() nested them under their leg
                    // via group_id, and the cascades need one flat set.
                    Sale::where(function ($query) use ($legIds) {
                        $query->whereIn('id', $legIds)->orWhereIn('group_id', $legIds);
                    })->update(['order_id' => $primaryId]);

                    $sales[0]->order_id = $primaryId;
                }

                return $sales[0];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        } catch (\App\Exceptions\BusinessException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        }

        // The free-order check below must consider the whole group: a gift card can zero out the
        // primary seat while guest seats still owe, and marking the primary paid would cascade
        // paid status to the unpaid guests.
        // Across a multi-event order this has to be the order total for the same reason: a gift
        // card can zero out one leg while another still owes, and taking the free path would mark
        // the order primary paid and cascade that to legs nobody has paid for.
        $total = $sale->isOrderPrimary() ? $sale->orderTotalPayment() : $sale->legTotalPayment();

        AuditService::log(AuditService::SALE_CHECKOUT, $sale->user_id, 'Sale', $sale->id, null, null, 'event_id:'.$event->id);

        // Dispatch sale.created webhook (outside transaction). One delivery per row, across every
        // leg of the order - a subscriber told only about the anchoring leg never learns the other
        // events were bought at all.
        foreach ($sale->orderLegs() as $leg) {
            WebhookService::dispatch('sale.created', $leg);
            foreach ($leg->guestSales()->get() as $gs) {
                WebhookService::dispatch('sale.created', $gs);
            }
        }

        $isEmbed = $request->boolean('embed');

        if ($total == 0 && ! $isPaymentLink) {
            // Nothing to collect, so this settles immediately. No reference (there is no gateway to
            // get one from) and no amount to reconcile against.
            //
            // The revenue booked is still zero, exactly as the hand-written version's literal 0 was:
            // every payment_amount write is max(0, ...), so a zero order total forces every leg in the
            // order to zero, and legTotalPayment() therefore returns 0 for all of them. What this does
            // add is an audit row and, on a boosted event, a zero-value conversion - both of which a
            // free registration should have been recording all along.
            app(SaleSettlementService::class)->settle($sale, null, null, 'free');

            return $this->redirectToPurchaseLanding($sale, $event, $isEmbed);
        } else {
            // Installment plan, created HERE rather than inside the pricing transaction above.
            // A gift card can zero an order, in which case the branch above marks the sale paid
            // and returns - a plan created earlier would be left `active` forever, with
            // installments that are never charged, against a sale that owes nothing.
            //
            // Every eligibility condition is re-derived from the database. The guest form's copy
            // of this logic is a convenience for the buyer, never the authority.
            if ($request->filled('installments')
                && payment_gateways()->get($event->payment_method)?->supportsInstallments()
                && count($legs) === 1) {

                $installments = app(InstallmentService::class);
                $currency = $event->ticket_currency_code ?: 'USD';
                $hasPass = $sale->saleTickets->contains(fn ($st) => $st->ticket?->is_pass);

                $reason = $hasPass
                    ? 'messages.installments_not_offered'
                    : $installments->ineligibleReason($event, (float) $total, $currency, $sale->event_date);

                if ($reason === null) {
                    $installments->createPlan(
                        $sale, $event, (float) $total, $currency,
                        $request->ip(), $request->boolean('installments_consent'),
                    );
                }
            }

            // Every gateway drives its own checkout. This used to be a switch on a payment-method
            // string with one arm per rail, each arm's body a few hundred lines further down this
            // file; the bodies now live with their drivers.
            //
            // An unknown method falls back to cash, exactly as the switch's default always did:
            // CashGateway inherits the base startCheckout(), which lands the buyer on their ticket
            // with the sale left unpaid. Reachable for a legacy row whose method an installation has
            // since removed from config('payments.gateways').
            $gateway = payment_gateways()->get($event->payment_method)
                ?? payment_gateways()->get('cash');

            return $gateway->startCheckout(new CheckoutContext($sale, $event, $subdomain, $isEmbed));
        }
    }

    /**
     * Normalise a checkout request into one leg per event.
     *
     * Without legs[] this returns a single leg built from the flat fields - every request the
     * single-event form has ever sent - so the multi-leg path is additive rather than a rewrite of
     * the wire format.
     *
     * Order-level fields (name, email, phone, gift card, account creation) stay on the request;
     * only what differs per event lives here.
     */
    private function resolveCheckoutLegs($request): array
    {
        $raw = $request->input('legs');

        if (! is_array($raw) || $raw === []) {
            $raw = [[
                'event_id' => $request->input('event_id'),
                'event_date' => $request->input('event_date'),
                'tickets' => $request->input('tickets', []),
                'addons' => $request->input('addons', []),
                'guests' => $request->input('guests', []),
                'event_custom_values' => $request->input('event_custom_values', []),
                'ticket_custom_values' => $request->input('ticket_custom_values', []),
                'guest_ticket_custom_values' => $request->input('guest_ticket_custom_values', []),
                'promo_code' => $request->input('promo_code'),
            ]];
        }

        return array_map(fn ($leg) => [
            // Scalar or nothing: an array here would reach UrlUtils::decodeId() and throw.
            'event_id' => is_array($leg) && (is_string($leg['event_id'] ?? null) || is_numeric($leg['event_id'] ?? null))
                ? $leg['event_id']
                : null,
            'event_date' => $leg['event_date'] ?? null,
            'tickets' => array_filter((array) ($leg['tickets'] ?? []), fn ($qty) => (int) $qty > 0),
            'addons' => (array) ($leg['addons'] ?? []),
            'guests' => (array) ($leg['guests'] ?? []),
            'event_custom_values' => (array) ($leg['event_custom_values'] ?? []),
            'ticket_custom_values' => (array) ($leg['ticket_custom_values'] ?? []),
            'guest_ticket_custom_values' => (array) ($leg['guest_ticket_custom_values'] ?? []),
            'promo_code' => $leg['promo_code'] ?? null,
        ], array_values($raw));
    }

    /**
     * Refuse a cart because one leg cannot be sold, naming the event and pointing the cart at it.
     *
     * The flashed key is what lets the panel mark the offending entry and offer to drop it. Without
     * it the buyer is told only that "something" is unavailable, and since the cart persists they
     * have to remove legs one at a time to find out which - if they even realise that is the fix.
     */
    private function refuseCartLeg(array $leg, ?string $eventName, string $messageKey = 'messages.cart_event_unavailable')
    {
        return back()
            ->withInput()
            ->with('error', __($messageKey, [
                'event' => $eventName ?: __('messages.event'),
            ]))
            ->with('cart_invalid_legs', [$leg['event_id'].'|'.(string) $leg['event_date']]);
    }

    /**
     * Why a cart cannot be paid for in one go, or null when it can.
     *
     * One Stripe Checkout Session cannot span Connect accounts, currencies or payment rails, and
     * those three live on the EVENT: events.user_id resolves the destination account,
     * ticket_currency_code the currency, payment_method the rail. A curator schedule genuinely
     * aggregates events from different owners, so none of this is theoretical.
     *
     * Invoice Ninja is excluded outright: payment-link mode stores its subscription on the event
     * and redirects to that event's purchase page, so it cannot represent an order.
     */
    private function cartEligibilityError(array $legs): ?string
    {
        if (count($legs) < 2) {
            return null;
        }

        $first = $legs[0]['event'];

        foreach ($legs as $leg) {
            $event = $leg['event'];

            if ($event->user_id !== $first->user_id
                || $event->ticket_currency_code !== $first->ticket_currency_code
                || $event->payment_method !== $first->payment_method) {
                return __('messages.cart_events_incompatible');
            }
        }

        // Only rails that settle a whole order in one go. Asked of the gateway rather than matched
        // against a literal list, so a new gateway that can do it says so once in its driver
        // instead of needing this line and the mirror of it in event/tickets.blade.php edited.
        if (! payment_gateways()->supportsCart($first->payment_method)) {
            return __('messages.cart_payment_method_unsupported');
        }

        return null;
    }

    /**
     * Row-lock every selected ticket and add-on for one event and refuse the order if any of them
     * cannot cover the requested quantity. Throws BusinessException on a shortfall.
     *
     * Scoped to a single event on purpose: a cart spanning several events must call this once per
     * leg, and must do so in a stable event order, or two overlapping carts deadlock on the locks.
     */
    private function assertLegTicketsAvailable(array $leg, Event $event, bool $isPaymentLink): void
    {
        // Check ticket availability with row locking (skip for payment link mode)
        if (! $isPaymentLink) {
            // Resolve event_date for one-time events (hidden field may be empty)
            $eventDate = $leg['event_date'];
            if (! $eventDate && $event->starts_at) {
                $eventDate = $event->saleEventDateFromStartsAt();
            }

            // A season pass (valid for all dates) can't be combined with single-date
            // tickets in one order - the scan window would otherwise be ambiguous.
            $selectedTicketIds = collect($leg['tickets'])
                ->filter(fn ($q) => (int) $q > 0)
                ->keys()
                ->map(fn ($id) => UrlUtils::decodeId($id));
            $selectedTickets = $event->tickets->whereIn('id', $selectedTicketIds);
            if ($selectedTickets->contains(fn ($t) => $t->is_pass) && $selectedTickets->contains(fn ($t) => ! $t->is_pass)) {
                throw new \App\Exceptions\BusinessException(__('messages.pass_cannot_combine'));
            }

            foreach ($leg['tickets'] as $ticketId => $quantity) {
                if ($quantity > 0) {
                    // Lock the ticket row to prevent concurrent modifications
                    $ticketModel = $event->tickets()->lockForUpdate()->find(UrlUtils::decodeId($ticketId));

                    if (! $ticketModel) {
                        throw new \App\Exceptions\BusinessException(__('messages.ticket_not_found'));
                    }

                    if ($ticketModel->isSalesEnded()) {
                        throw new \App\Exceptions\BusinessException(__('messages.tickets_not_available'));
                    }

                    if ($ticketModel->isSalesNotStarted()) {
                        throw new \App\Exceptions\BusinessException(__('messages.tickets_not_available'));
                    }

                    if ($ticketModel->max_per_order && $quantity > $ticketModel->max_per_order) {
                        throw new \App\Exceptions\BusinessException(__('messages.exceeded_max_per_order', [
                            'max' => $ticketModel->max_per_order,
                        ]));
                    }

                    if ($ticketModel->quantity > 0) {
                        // Handle combined mode logic (passes always use their own pool, never combined)
                        if (! $ticketModel->is_pass && $event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
                            // Lock all tickets for combined mode
                            $lockedTickets = $event->tickets()->lockForUpdate()->get();
                            $totalSold = $lockedTickets->filter(fn ($ticket) => ! $ticket->is_pass)->sum(function ($ticket) use ($eventDate) {
                                return $ticket->soldCountFor($eventDate);
                            });
                            // Pass holders who booked this occurrence in advance occupy shared seats too.
                            $totalSold += $eventDate ? $event->passReservedSeats($eventDate) : 0;
                            // In combined mode, the total quantity is the same as individual quantity
                            $totalQuantity = $event->getSameTicketQuantity();
                            $remainingTickets = $totalQuantity - $totalSold;

                            // Check if the total requested quantity exceeds remaining tickets
                            $totalRequested = array_sum($leg['tickets']);
                            if ($totalRequested > $remainingTickets) {
                                throw new \App\Exceptions\BusinessException(__('messages.tickets_not_available'));
                            }
                        } else {
                            $soldCount = $ticketModel->soldCountFor($eventDate);
                            $remainingTickets = $ticketModel->quantity - $soldCount;

                            if ($quantity > $remainingTickets) {
                                throw new \App\Exceptions\BusinessException(__('messages.tickets_not_available'));
                            }
                        }
                    }
                }
            }

            // Pass advance-bookings share the per-occurrence seat pool with regular
            // sales, so a non-pass order can't exceed what's left after reservations.
            // The combined+equal-quantity case is already capped per-ticket above; this
            // covers individual / single-ticket modes. No-op when nothing is reserved
            // (the house equals the sum of per-ticket limits).
            $orderIsPass = $selectedTickets->contains(fn ($t) => $t->is_pass);
            if (! $orderIsPass && $eventDate
                && ! ($event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities())) {
                $event->setRelation('tickets', $event->tickets()->lockForUpdate()->get());
                $houseRemaining = $event->occurrenceSeatsRemaining($eventDate);
                if ($houseRemaining !== null && array_sum($leg['tickets']) > $houseRemaining) {
                    throw new \App\Exceptions\BusinessException(__('messages.tickets_not_available'));
                }
            }

            // Check add-on availability. Add-ons are Pro, and a lapsed schedule keeps its
            // rows (they are made dormant, never deleted), so the sell path needs its own
            // check rather than relying on the row's absence.
            $addonSelections = $event->isPro() ? $leg['addons'] : [];
            foreach ($addonSelections as $addonId => $addonQty) {
                $addonQty = (int) $addonQty;
                if ($addonQty > 0) {
                    $addonModel = $event->addons()->lockForUpdate()->find(UrlUtils::decodeId($addonId));

                    if (! $addonModel) {
                        throw new \App\Exceptions\BusinessException(__('messages.ticket_not_found'));
                    }

                    if ($addonModel->max_per_order && $addonQty > $addonModel->max_per_order) {
                        throw new \App\Exceptions\BusinessException(__('messages.exceeded_max_per_order', [
                            'max' => $addonModel->max_per_order,
                        ]));
                    }

                    if ($addonModel->quantity > 0) {
                        $soldCount = $addonModel->soldCountFor($eventDate);
                        $remaining = $addonModel->quantity - $soldCount;

                        if ($addonQty > $remaining) {
                            throw new \App\Exceptions\BusinessException(__('messages.tickets_not_available'));
                        }
                    }
                }
            }
        }
    }

    /**
     * The identity half of a sale row for one event: buyer details, attribution and the
     * event-level custom field answers. Unsaved; the caller saves it, then prices it.
     */
    private function newSaleForLeg($request, array $leg, Event $event, $user, string $subdomain): Sale
    {
        $sale = new Sale;
        $sale->name = $request->input('name');
        $sale->email = $request->input('email');
        $sale->phone = $request->input('phone') ? strip_tags(trim($request->input('phone'))) : null;
        $sale->event_date = $leg['event_date'];
        $sale->subdomain = $subdomain;
        $sale->event_id = $event->id;
        $sale->user_id = $user ? $user->id : null;
        $sale->secret = strtolower(Str::random(32));
        $sale->payment_method = $event->payment_method;

        // Capture UTM attribution
        $utmParams = $request->session()->get('utm_params', []);
        if (empty($utmParams) && $request->cookie('utm_params')) {
            $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
        }
        $sale->utm_source = $utmParams['utm_source'] ?? null;
        $sale->utm_medium = $utmParams['utm_medium'] ?? null;
        $sale->utm_campaign = $utmParams['utm_campaign'] ?? null;
        if (($utmParams['utm_source'] ?? null) === 'boost' && ($utmParams['utm_campaign'] ?? null)) {
            // Verify the campaign exists before assigning it: sales.boost_campaign_id
            // carries a foreign key, so a crafted utm_campaign that decodes to an unknown
            // id would fail the INSERT and take the whole checkout down with it.
            $sale->boost_campaign_id = \App\Models\BoostCampaign::whereKey(
                UrlUtils::decodeId($utmParams['utm_campaign'])
            )->value('id');
        }
        if (($utmParams['utm_source'] ?? null) === 'newsletter' && ($utmParams['utm_campaign'] ?? null)) {
            $sale->newsletter_id = UrlUtils::decodeId($utmParams['utm_campaign']);
        }

        if (! $sale->event_date) {
            $sale->event_date = $event->saleEventDateFromStartsAt();
        }

        // Store event-level custom field values using stable indices
        // Fallback to iteration order for backward compatibility with fields without index
        $eventCustomValues = $leg['event_custom_values'];
        $eventCustomFields = $event->custom_fields ?? [];
        $fallbackIndex = 1;
        foreach ($eventCustomFields as $fieldKey => $fieldConfig) {
            $index = $fieldConfig['index'] ?? $fallbackIndex;
            $fallbackIndex++;
            if ($index >= 1 && $index <= 10) {
                $value = $eventCustomValues[$fieldKey] ?? null;
                // Handle multiselect values (submitted as array)
                if (is_array($value)) {
                    $value = implode(', ', array_map('trim', $value));
                }
                // Sanitize custom field values to prevent stored XSS
                if ($value !== null) {
                    $value = trim(strip_tags($value));
                }
                $sale->{"custom_value{$index}"} = $value;
            }
        }

        return $sale;
    }

    /**
     * The gift card this checkout is paying with, locked for the transaction, or null when no code
     * was supplied. Throws BusinessException when a code was given but cannot be used.
     *
     * Resolved ONCE for the whole order rather than per leg. A card is an order-level payment
     * instrument, and re-validating it on every leg meant that the moment one leg spent the last of
     * the balance the next leg saw a depleted card, judged it invalid and threw - rolling back a
     * checkout that was entirely legitimate. Legs now just draw down whatever is left.
     *
     * Owner and currency are read from the first leg, which cartEligibilityError() has proven every
     * other leg matches. The card itself may belong to any leg's schedule.
     */
    private function resolveOrderGiftCard($request, array $legs): ?GiftCard
    {
        if (! $request->gift_card_code) {
            return null;
        }

        $event = $legs[0]['event'];
        $roleIds = collect($legs)
            ->flatMap(fn ($leg) => $leg['event']->roles()->pluck('roles.id'))
            ->unique();

        $giftCard = GiftCard::whereIn('role_id', $roleIds)
            ->where('code', GiftCard::normalizeCode($request->gift_card_code))
            ->lockForUpdate()
            ->first();

        // Cards are sold through the schedule owner's payment account but redemption
        // reduces the event owner's payout, so both must be the same user.
        if (! $giftCard || $event->user_id !== $giftCard->role->user_id
            || ! $giftCard->isRedeemable($event->ticket_currency_code)) {
            throw new \App\Exceptions\BusinessException(__('messages.gift_card_invalid'));
        }

        return $giftCard;
    }

    /**
     * The money half: ticket rows, add-ons, volume discount, promo code, gift card and - in
     * individual-tickets mode - the per-guest sale rows nested under this one.
     *
     * Mutates $sale in place and leaves it unsaved; the caller saves.
     */
    private function priceSaleLeg(Sale $sale, $request, array $leg, Event $event, string $subdomain, bool $isPaymentLink, ?GiftCard $giftCard = null): void
    {
        if ($isPaymentLink) {
            // Payment link mode: quantities selected on IN, SaleTickets created by webhook
            $sale->payment_amount = 0;
        } else {
            // Check if individual tickets mode is active
            $guests = $leg['guests'];
            $isIndividualTickets = $event->individual_tickets && count($guests) > 1;

            if ($isIndividualTickets) {
                // Validate no duplicate emails among guests
                $guestEmails = collect($guests)->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)));
                if ($guestEmails->count() !== $guestEmails->unique()->count()) {
                    throw new \App\Exceptions\BusinessException(__('messages.duplicate_guest_emails'));
                }

                // Build flat list of ticket assignments for guests
                $ticketAssignments = [];
                $seatPrices = [];
                $subtotal = 0;
                foreach ($leg['tickets'] as $ticketId => $quantity) {
                    if ($quantity > 0) {
                        $decodedId = UrlUtils::decodeId($ticketId);
                        $ticketModel = $event->tickets()->findOrFail($decodedId);
                        $subtotal += $ticketModel->price * $quantity;
                        for ($i = 0; $i < $quantity; $i++) {
                            $ticketAssignments[] = $decodedId;
                            $seatPrices[] = (float) $ticketModel->price;
                        }
                    }
                }

                // Validate guest count matches ticket count
                if (count($ticketAssignments) !== count($guests)) {
                    throw new \App\Exceptions\BusinessException(__('messages.error'));
                }

                // Primary sale gets the first ticket (qty=1)
                $primarySaleTicketData = [
                    'ticket_id' => $ticketAssignments[0],
                    'quantity' => 1,
                    'seats' => json_encode([1 => null]),
                ];

                // Store per-guest ticket custom fields for primary guest
                if ($event->individual_ticket_fields) {
                    $guestTicketCustomValues = $leg['guest_ticket_custom_values'];
                    $primaryTicketModel = $event->tickets()->find($ticketAssignments[0]);
                    $primaryTicketCustomFields = $primaryTicketModel->custom_fields ?? [];
                    $ticketFallbackIndex = 1;
                    foreach ($primaryTicketCustomFields as $fieldKey => $fieldConfig) {
                        $index = $fieldConfig['index'] ?? $ticketFallbackIndex;
                        $ticketFallbackIndex++;
                        if ($index >= 1 && $index <= 10) {
                            $value = $guestTicketCustomValues[0][$fieldKey] ?? null;
                            if (is_array($value)) {
                                $value = implode(', ', array_map('trim', $value));
                            }
                            if ($value !== null) {
                                $value = trim(strip_tags($value));
                            }
                            $primarySaleTicketData["custom_value{$index}"] = $value;
                        }
                    }
                }

                $sale->saleTickets()->create($primarySaleTicketData);

                $sale->group_id = $sale->id;
                // payment_amount allocated per-seat after volume + promo below
            } else {
                // Standard flow: create SaleTickets with full quantities
                $ticketCustomValues = $leg['ticket_custom_values'];

                foreach ($leg['tickets'] as $ticketId => $quantity) {
                    if ($quantity > 0) {
                        $ticketModel = $event->tickets()->findOrFail(UrlUtils::decodeId($ticketId));
                        $ticketCustomFields = $ticketModel->custom_fields ?? [];

                        $saleTicketData = [
                            'sale_id' => $sale->id,
                            'ticket_id' => UrlUtils::decodeId($ticketId),
                            'quantity' => $quantity,
                            'seats' => json_encode(array_fill(1, $quantity, null)),
                        ];

                        // Store ticket-level custom field values using stable indices
                        $ticketFallbackIndex = 1;
                        foreach ($ticketCustomFields as $fieldKey => $fieldConfig) {
                            $index = $fieldConfig['index'] ?? $ticketFallbackIndex;
                            $ticketFallbackIndex++;
                            if ($index >= 1 && $index <= 10) {
                                $value = $ticketCustomValues[$ticketId][$fieldKey] ?? null;
                                if (is_array($value)) {
                                    $value = implode(', ', array_map('trim', $value));
                                }
                                if ($value !== null) {
                                    $value = trim(strip_tags($value));
                                }
                                $saleTicketData["custom_value{$index}"] = $value;
                            }
                        }

                        $sale->saleTickets()->create($saleTicketData);
                    }
                }

                $subtotal = $sale->calculateTotal();
                $sale->payment_amount = $subtotal;
            }

            // Capture seat-only subtotal (before add-ons) for per-seat allocation
            $seatsSubtotal = $isIndividualTickets ? $subtotal : 0;

            // Create SaleTickets for add-ons (attach to primary sale only). Pro-gated, same
            // as the availability check above.
            $addonSelections = $event->isPro() ? $leg['addons'] : [];
            $hasAddons = false;
            $addonTotal = 0;
            foreach ($addonSelections as $addonId => $addonQty) {
                $addonQty = (int) $addonQty;
                if ($addonQty > 0) {
                    $addonModel = $event->addons()->findOrFail(UrlUtils::decodeId($addonId));
                    $sale->saleTickets()->create([
                        'ticket_id' => $addonModel->id,
                        'quantity' => $addonQty,
                        'seats' => json_encode(array_fill(1, $addonQty, null)),
                    ]);
                    $hasAddons = true;
                }
            }

            // Add add-on total to subtotal if add-ons were added
            if ($hasAddons) {
                $sale->load('saleTickets.ticket');
                $addonTotal = $sale->saleTickets->filter(fn ($st) => $st->ticket->is_addon)
                    ->sum(fn ($st) => $st->ticket->price * $st->quantity);
                $subtotal += $addonTotal;
            }
            if (! $isIndividualTickets) {
                $sale->payment_amount = $subtotal;
            }

            $sale->loadMissing(['saleTickets.ticket']);
            foreach ($sale->saleTickets as $st) {
                if ($st->ticket) {
                    $st->ticket->setRelation('event', $event);
                }
            }

            $volumeTotal = $isIndividualTickets
                ? TicketVolumeDiscount::totalVolumeDiscountForTicketQuantities($event, $leg['tickets'])
                : TicketVolumeDiscount::totalVolumeDiscountForSaleTickets($sale->saleTickets);
            $subtotalAfterVolume = $subtotal - $volumeTotal;

            // Apply promo code if provided (eligible subtotal is post-volume; see PromoCode::calculateDiscount)
            $promoCodeId = null;
            $discountTotal = 0;
            if ($leg['promo_code']) {
                $promoCode = PromoCode::where('event_id', $event->id)
                    ->whereRaw('LOWER(code) = ?', [strtolower($leg['promo_code'])])
                    ->lockForUpdate()
                    ->first();

                if ($promoCode && $promoCode->isValid()) {
                    if ($isIndividualTickets) {
                        // Calculate discount from all ticket selections
                        $allSaleTickets = collect();
                        foreach ($leg['tickets'] as $ticketId => $quantity) {
                            if ($quantity > 0) {
                                $decodedId = UrlUtils::decodeId($ticketId);
                                $ticketModel = $event->tickets()->find($decodedId);
                                if ($ticketModel) {
                                    $ticketModel->setRelation('event', $event);
                                    $fakeSaleTicket = new \App\Models\SaleTicket(['ticket_id' => $decodedId, 'quantity' => $quantity]);
                                    $fakeSaleTicket->setRelation('ticket', $ticketModel);
                                    $allSaleTickets->push($fakeSaleTicket);
                                }
                            }
                        }
                        $promoCode->setRelation('event', $event);
                        $discountAmount = $promoCode->calculateDiscount($allSaleTickets);
                    } else {
                        $promoCode->setRelation('event', $event);
                        $discountAmount = $promoCode->calculateDiscount($sale->saleTickets);
                    }
                    if ($discountAmount > 0) {
                        $promoCodeId = $promoCode->id;
                        $discountTotal = (float) $discountAmount;
                        $promoCode->increment('times_used');
                    }
                }
            }

            // Draw down the gift card resolved for the whole order (deducted after volume + promo).
            // The card is validated once, up front, in resolveOrderGiftCard(); each leg simply
            // spends whatever is left, and a leg that finds nothing left just pays its own way.
            $giftCardId = null;
            $giftCardApplied = 0.0;
            if ($giftCard) {
                // Re-read through the locked instance: earlier legs of this order have already
                // decremented it in memory and in the row.
                $orderTotal = max(0, $subtotalAfterVolume - $discountTotal);
                $giftCardApplied = min((float) $giftCard->remaining_amount, $orderTotal);

                // A gateway with a floor (Stripe refuses under ~50 smallest units) must be left
                // either nothing to charge or at least its minimum; the sliver stays on the card.
                // Asked of the driver rather than hardcoded, so the mirror of this in the guest form
                // and this authority cannot drift apart.
                $minCharge = payment_gateways()->get($event->payment_method)
                    ?->amountLimits($event->ticket_currency_code ?: 'USD')[0] ?? 0;

                if ($minCharge > 0) {
                    $remainder = $orderTotal - $giftCardApplied;
                    if ($remainder > 0 && $remainder < $minCharge) {
                        $giftCardApplied = max(0, $orderTotal - $minCharge);
                    }
                }

                if ($giftCardApplied > 0) {
                    $giftCard->decrement('remaining_amount', $giftCardApplied);
                    $giftCardId = $giftCard->id;
                }
            }

            // Per-seat allocation for individual tickets, group total for standard flow
            if ($isIndividualTickets) {
                $seatCount = count($ticketAssignments);
                $seatVolumeShares = array_fill(0, $seatCount, 0.0);
                $seatPromoShares = array_fill(0, $seatCount, 0.0);

                if ($seatsSubtotal > 0 && $seatCount > 0) {
                    $volumeAccum = 0.0;
                    $promoAccum = 0.0;
                    for ($i = 0; $i < $seatCount - 1; $i++) {
                        $share = $seatPrices[$i] / $seatsSubtotal;
                        $seatVolumeShares[$i] = round((float) $volumeTotal * $share, 2);
                        $seatPromoShares[$i] = round($discountTotal * $share, 2);
                        $volumeAccum += $seatVolumeShares[$i];
                        $promoAccum += $seatPromoShares[$i];
                    }
                    // Last seat absorbs rounding residual so group totals reconcile exactly
                    $seatVolumeShares[$seatCount - 1] = round((float) $volumeTotal - $volumeAccum, 2);
                    $seatPromoShares[$seatCount - 1] = round($discountTotal - $promoAccum, 2);
                }

                // Gift card allocation: greedy waterfall over per-seat nets (primary first).
                // Proportional shares could push a seat negative when a ticket-restricted
                // promo skews the nets; greedy sums exactly with no rounding residue.
                $primaryNet = $seatPrices[0] - $seatVolumeShares[0] - $seatPromoShares[0] + $addonTotal;
                $seatGiftShares = array_fill(0, $seatCount, 0.0);
                if ($giftCardApplied > 0) {
                    $remainingGift = $giftCardApplied;
                    $seatGiftShares[0] = round(min(max(0, $primaryNet), $remainingGift), 3);
                    $remainingGift = round($remainingGift - $seatGiftShares[0], 3);
                    for ($i = 1; $i < $seatCount && $remainingGift > 0; $i++) {
                        $net = max(0, $seatPrices[$i] - $seatVolumeShares[$i] - $seatPromoShares[$i]);
                        $seatGiftShares[$i] = round(min($net, $remainingGift), 3);
                        $remainingGift = round($remainingGift - $seatGiftShares[$i], 3);
                    }
                }

                // Primary holds seat[0] + all add-ons
                $sale->payment_amount = max(0, $primaryNet - $seatGiftShares[0]);
                $sale->volume_discount_amount = $seatVolumeShares[0] > 0 ? $seatVolumeShares[0] : null;
                $sale->discount_amount = $seatPromoShares[0] > 0 ? $seatPromoShares[0] : null;
                if ($promoCodeId) {
                    $sale->promo_code_id = $promoCodeId;
                }
                if ($giftCardId && $seatGiftShares[0] > 0) {
                    $sale->gift_card_id = $giftCardId;
                    $sale->gift_card_amount = $seatGiftShares[0];
                }
            } else {
                $sale->volume_discount_amount = $volumeTotal > 0 ? $volumeTotal : null;
                if ($promoCodeId) {
                    $sale->promo_code_id = $promoCodeId;
                    $sale->discount_amount = $discountTotal;
                }
                if ($giftCardId) {
                    $sale->gift_card_id = $giftCardId;
                    $sale->gift_card_amount = $giftCardApplied;
                }
                $sale->payment_amount = max(0, $subtotalAfterVolume - $discountTotal - $giftCardApplied);
            }

            // Create guest sales for individual tickets
            if ($isIndividualTickets) {
                for ($g = 1; $g < count($guests); $g++) {
                    $guestData = $guests[$g];
                    $guestSale = new Sale;
                    $guestSale->name = strip_tags(trim($guestData['name'] ?? ''));
                    $guestSale->email = strip_tags(trim($guestData['email'] ?? ''));
                    $guestSale->phone = ! empty($guestData['phone']) ? strip_tags(trim($guestData['phone'])) : null;
                    $guestSale->event_date = $sale->event_date;
                    $guestSale->subdomain = $subdomain;
                    $guestSale->event_id = $event->id;
                    $guestSale->user_id = null;
                    $guestSale->secret = strtolower(Str::random(32));
                    $guestSale->payment_method = $sale->payment_method;
                    $guestSale->payment_amount = max(0, $seatPrices[$g] - $seatVolumeShares[$g] - $seatPromoShares[$g] - $seatGiftShares[$g]);
                    $guestSale->status = $sale->status ?? 'unpaid';
                    $guestSale->group_id = $sale->id;
                    if ($promoCodeId) {
                        $guestSale->promo_code_id = $promoCodeId;
                    }
                    if ($seatPromoShares[$g] > 0) {
                        $guestSale->discount_amount = $seatPromoShares[$g];
                    }
                    if ($seatVolumeShares[$g] > 0) {
                        $guestSale->volume_discount_amount = $seatVolumeShares[$g];
                    }
                    if ($giftCardId && $seatGiftShares[$g] > 0) {
                        $guestSale->gift_card_id = $giftCardId;
                        $guestSale->gift_card_amount = $seatGiftShares[$g];
                    }

                    // Copy event-level custom values from primary sale
                    for ($cv = 1; $cv <= 10; $cv++) {
                        $guestSale->{"custom_value{$cv}"} = $sale->{"custom_value{$cv}"};
                    }

                    $guestSale->save();

                    // Assign ticket to guest
                    if (isset($ticketAssignments[$g])) {
                        $guestSaleTicketData = [
                            'ticket_id' => $ticketAssignments[$g],
                            'quantity' => 1,
                            'seats' => json_encode([1 => null]),
                        ];

                        // Store per-guest ticket custom fields
                        if ($event->individual_ticket_fields) {
                            $guestTicketCustomValues = $leg['guest_ticket_custom_values'];
                            $guestTicketModel = $event->tickets()->find($ticketAssignments[$g]);
                            $guestTicketCustomFields = $guestTicketModel->custom_fields ?? [];
                            $guestTicketFallbackIndex = 1;
                            foreach ($guestTicketCustomFields as $fieldKey => $fieldConfig) {
                                $index = $fieldConfig['index'] ?? $guestTicketFallbackIndex;
                                $guestTicketFallbackIndex++;
                                if ($index >= 1 && $index <= 10) {
                                    $value = $guestTicketCustomValues[$g][$fieldKey] ?? null;
                                    if (is_array($value)) {
                                        $value = implode(', ', array_map('trim', $value));
                                    }
                                    if ($value !== null) {
                                        $value = trim(strip_tags($value));
                                    }
                                    $guestSaleTicketData["custom_value{$index}"] = $value;
                                }
                            }
                        }

                        $guestSale->saleTickets()->create($guestSaleTicketData);
                    }
                }
            }
        }
    }

    public function rsvp(Request $request, $subdomain)
    {
        // Honeypot. See checkout() for why this returns the input.
        if (HoneypotUtils::isTripped($request)) {
            return back()->withInput()->with('error', __('messages.invalid_request'));
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'event_id' => 'required|string',
            'event_date' => 'required|date_format:Y-m-d',
        ];

        $event = Event::find(UrlUtils::decodeId($request->event_id));
        if ($event && $event->ask_phone) {
            $rules['phone'] = $event->require_phone
                ? 'required|string|max:50'
                : 'nullable|string|max:50';
        }

        if ($event && $event->individual_tickets && $request->has('guests') && is_array($request->input('guests')) && count($request->input('guests')) > 1) {
            $rules['guests.*.name'] = ['required', 'string', 'max:255'];
            $rules['guests.*.email'] = ['required', 'string', 'email', 'max:255'];
            if ($event->ask_phone) {
                $rules['guests.*.phone'] = $event->require_phone
                    ? ['required', 'string', 'max:50']
                    : ['nullable', 'string', 'max:50'];
            }
        }

        $request->validate($rules);

        // Turnstile CAPTCHA validation
        if (\App\Utils\TurnstileUtils::isActiveForRequest()) {
            $request->validate([
                'cf-turnstile-response' => 'required',
            ]);

            $turnstileValid = \App\Utils\TurnstileUtils::verify($request->input('cf-turnstile-response'));
            if (! $turnstileValid) {
                return back()->withInput()->with('error', __('messages.turnstile_verification_failed'));
            }
        }

        if (! $event) {
            abort(404);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        if (! $event->roles()->wherePivot('role_id', $role->id)->exists()) {
            abort(403);
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if ($event->is_draft && ! $isMemberOrAdmin) {
            abort(404);
        }
        if ($event->is_private
            && ! $isMemberOrAdmin
            && ! ($event->isPasswordProtected() && session()->has('event_password_'.$event->id))
        ) {
            abort(404);
        }

        if (! $event->canAcceptRsvp($request->event_date)) {
            return back()->withInput()->with('error', __('messages.rsvp_unavailable'));
        }

        if (! $user && $request->create_account && config('app.hosted')) {
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class, new NoFakeEmail],
                'password' => ['required', 'string', 'min:8'],
                'terms' => ['accepted'],
            ]);

            $utmParams = session('utm_params', []);
            if (empty($utmParams) && $request->cookie('utm_params')) {
                $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'timezone' => $event->user->timezone,
                'language_code' => $event->user->language_code,
                'utm_source' => $utmParams['utm_source'] ?? null,
                'utm_medium' => $utmParams['utm_medium'] ?? null,
                'utm_campaign' => $utmParams['utm_campaign'] ?? null,
                'utm_content' => $utmParams['utm_content'] ?? null,
                'utm_term' => $utmParams['utm_term'] ?? null,
                'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
                'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
                'signup_intent' => 'ticket',
            ]);

            session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page']);
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        try {
            $sale = DB::transaction(function () use ($request, $event, $user, $subdomain) {
                // Lock the event row to prevent race conditions
                $event = Event::lockForUpdate()->find($event->id);

                // Check capacity
                $guests = $request->input('guests', []);
                $rsvpQuantity = $event->individual_tickets ? max(1, count($guests)) : 1;
                $rsvpSoldCount = $event->rsvpSoldCount($request->event_date);
                if ($event->rsvp_limit && ($rsvpSoldCount + $rsvpQuantity) > $event->rsvp_limit) {
                    throw new \App\Exceptions\BusinessException(__('messages.rsvp_full'));
                }

                // Check for duplicate registration
                $duplicate = Sale::where('event_id', $event->id)
                    ->where('event_date', $request->event_date)
                    ->where('email', $request->email)
                    ->where('payment_method', 'rsvp')
                    ->where('status', 'paid')
                    ->where('is_deleted', false)
                    ->exists();

                if ($duplicate) {
                    throw new \App\Exceptions\BusinessException(__('messages.rsvp_already_registered'));
                }

                // Check for duplicate guest registrations
                if ($event->individual_tickets && count($guests) > 1) {
                    $guestEmails = collect($guests)->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)));
                    if ($guestEmails->count() !== $guestEmails->unique()->count()) {
                        throw new \App\Exceptions\BusinessException(__('messages.duplicate_guest_emails'));
                    }
                    $duplicateGuests = Sale::where('event_id', $event->id)
                        ->where('event_date', $request->event_date)
                        ->whereIn('email', $guestEmails)
                        ->where('payment_method', 'rsvp')
                        ->where('status', 'paid')
                        ->where('is_deleted', false)
                        ->exists();

                    if ($duplicateGuests) {
                        throw new \App\Exceptions\BusinessException(__('messages.rsvp_already_registered'));
                    }
                }

                $sale = new Sale;
                $sale->name = $request->input('name');
                $sale->email = $request->input('email');
                $sale->phone = $request->input('phone') ? strip_tags(trim($request->input('phone'))) : null;
                $sale->event_date = $request->input('event_date');
                $sale->subdomain = $subdomain;
                $sale->event_id = $event->id;
                $sale->user_id = $user ? $user->id : null;
                $sale->secret = strtolower(Str::random(32));
                $sale->payment_method = 'rsvp';
                $sale->payment_amount = 0;
                $sale->status = 'paid';

                // Capture UTM attribution
                $utmParams = $request->session()->get('utm_params', []);
                if (empty($utmParams) && $request->cookie('utm_params')) {
                    $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
                }
                $sale->utm_source = $utmParams['utm_source'] ?? null;
                $sale->utm_medium = $utmParams['utm_medium'] ?? null;
                $sale->utm_campaign = $utmParams['utm_campaign'] ?? null;
                if (($utmParams['utm_source'] ?? null) === 'boost' && ($utmParams['utm_campaign'] ?? null)) {
                    // Verify the campaign exists before assigning it: sales.boost_campaign_id
                    // carries a foreign key, so a crafted utm_campaign that decodes to an unknown
                    // id would fail the INSERT and take the whole checkout down with it.
                    $sale->boost_campaign_id = \App\Models\BoostCampaign::whereKey(
                        UrlUtils::decodeId($utmParams['utm_campaign'])
                    )->value('id');
                }
                if (($utmParams['utm_source'] ?? null) === 'newsletter' && ($utmParams['utm_campaign'] ?? null)) {
                    $sale->newsletter_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }

                if (! $sale->event_date) {
                    $sale->event_date = $event->saleEventDateFromStartsAt();
                }

                // Store event-level custom field values
                $eventCustomValues = $request->input('event_custom_values', []);
                $eventCustomFields = $event->custom_fields ?? [];
                $fallbackIndex = 1;
                foreach ($eventCustomFields as $fieldKey => $fieldConfig) {
                    $index = $fieldConfig['index'] ?? $fallbackIndex;
                    $fallbackIndex++;
                    if ($index >= 1 && $index <= 10) {
                        $value = $eventCustomValues[$fieldKey] ?? null;
                        if (is_array($value)) {
                            $value = implode(', ', array_map('trim', $value));
                        }
                        if ($value !== null) {
                            $value = trim(strip_tags($value));
                        }
                        $sale->{"custom_value{$index}"} = $value;
                    }
                }

                $sale->save();

                // Individual RSVP: create guest sales
                if ($event->individual_tickets && $rsvpQuantity > 1 && count($guests) > 1) {
                    $sale->group_id = $sale->id;
                    $sale->save();

                    for ($g = 1; $g < count($guests); $g++) {
                        $guestData = $guests[$g];
                        $guestSale = new Sale;
                        $guestSale->name = strip_tags(trim($guestData['name'] ?? ''));
                        $guestSale->email = strip_tags(trim($guestData['email'] ?? ''));
                        $guestSale->phone = ! empty($guestData['phone']) ? strip_tags(trim($guestData['phone'])) : null;
                        $guestSale->event_date = $sale->event_date;
                        $guestSale->subdomain = $subdomain;
                        $guestSale->event_id = $event->id;
                        $guestSale->user_id = null;
                        $guestSale->secret = strtolower(Str::random(32));
                        $guestSale->payment_method = 'rsvp';
                        $guestSale->payment_amount = 0;
                        $guestSale->status = 'paid';
                        $guestSale->group_id = $sale->id;

                        // Copy event-level custom values from primary sale
                        for ($cv = 1; $cv <= 10; $cv++) {
                            $guestSale->{"custom_value{$cv}"} = $sale->{"custom_value{$cv}"};
                        }

                        $guestSale->save();
                    }
                }

                // Increment RSVP sold count
                $event->updateRsvpSold($request->event_date, $rsvpQuantity);

                return $sale;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        } catch (\App\Exceptions\BusinessException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        }

        AuditService::log(AuditService::SALE_CHECKOUT, $sale->user_id, 'Sale', $sale->id, null, null, 'rsvp:event_id:'.$event->id);

        // Record RSVP sale in analytics (0 revenue)
        AnalyticsEventsDaily::incrementSale($event->id, 0);

        // Dispatch webhooks
        WebhookService::dispatch('sale.created', $sale);
        WebhookService::dispatch('sale.paid', $sale);
        if ($sale->group_id && $sale->isPrimarySale()) {
            foreach (Sale::where('group_id', $sale->id)->where('id', '!=', $sale->id)->get() as $gs) {
                WebhookService::dispatch('sale.created', $gs);
                WebhookService::dispatch('sale.paid', $gs);
            }
        }

        (new EmailService)->sendSaleConfirmationEmails($sale);

        return $this->redirectToPurchaseLanding($sale, $event, $request->boolean('embed'));
    }

    /**
     * Where a buyer lands after a purchase: the order page when the checkout covered several
     * events, that leg's own ticket otherwise.
     *
     * Every leg keeps its own ticket page and its own scannable QR, so the order page is the only
     * surface that says the other legs exist at all. Dropping a multi-event buyer on one ticket
     * hides the rest of what they just paid for - which is why every post-purchase redirect goes
     * through here rather than building a ticket.view URL by hand.
     */
    private function purchaseLandingUrl($sale, $event, bool $isEmbed = false): string
    {
        $url = $sale->isOrderPrimary()
            ? route('ticket.order', ['order_id' => UrlUtils::encodeId($sale->id), 'secret' => $sale->secret])
            : route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);

        return $isEmbed ? $url.'?embed=true' : $url;
    }

    /**
     * Redirect to the purchase landing page, telling the guest cart what was just bought.
     *
     * The cart lives in localStorage and is rendered only by the guest layout; both landing pages
     * go through x-app-layout, so the widget is not there to empty itself. Left alone, the cart
     * still holds the completed purchase on the buyer's next visit to the schedule, with a live
     * CHECKOUT button that would charge them for it a second time.
     *
     * Flashed rather than cleared unconditionally on the ticket page: a ticket link is permanent
     * and gets opened long after the fact, and re-opening an old ticket must not silently empty a
     * cart the buyer has since refilled.
     */
    private function redirectToPurchaseLanding($sale, $event, bool $isEmbed = false)
    {
        session()->flash('cart_purchased', $sale->orderLegs()->map(fn ($leg) => [
            'subdomain' => $leg->subdomain,
            'event_id' => UrlUtils::encodeId($leg->event_id),
            'event_date' => (string) $leg->event_date,
        ])->values()->all());

        return redirect($this->purchaseLandingUrl($sale, $event, $isEmbed));
    }

    public function success($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;

        // Validate session_id parameter exists
        if (! request()->has('session_id')) {
            abort(404);
        }

        $isDirect = request()->query('direct') === '1';

        try {
            if ($isDirect) {
                // Self-hosted mode: Use platform Stripe keys
                $stripe = new StripeClient(config('services.stripe_platform.secret'));
                $session = $stripe->checkout->sessions->retrieve(request()->session_id);
            } else {
                // Hosted mode: Use Stripe Connect
                $stripe = new StripeClient(config('services.stripe.key'));
                $session = $stripe->checkout->sessions->retrieve(request()->session_id, [], [
                    'stripe_account' => $sale->event->user->stripe_account_id,
                ]);
            }

            // Verify the session belongs to this sale to prevent cross-sale session reuse
            $sessionSaleId = $session->metadata->sale_id ?? null;
            if ($sessionSaleId !== UrlUtils::encodeId($sale->id)) {
                \Log::warning('Stripe session sale_id mismatch in success()', [
                    'url_sale_id' => $sale->id,
                    'session_sale_id' => $sessionSaleId,
                    'session_id' => request()->session_id,
                ]);

                return $this->redirectToPurchaseLanding($sale, $event, request()->boolean('embed'));
            }

            // Store the transaction reference so the webhook can find this sale,
            // but don't set status=paid or overwrite payment_amount here - the webhook
            // handles that with proper locking and amount validation
            if ($sale->status !== 'paid') {
                $sale->transaction_reference = $session->payment_intent;
                $sale->save();
            }

            // Backstop the installment card capture.
            //
            // Which of the two Stripe events reports this purchase is a dashboard setting we do
            // not control, and only payment_intent.succeeded carries the intent the capture needs.
            // On an install subscribed to checkout.session.completed alone, a plan was therefore
            // left with no stored card - which is silent and total: chargeDue() skips it, both
            // reminder sweeps filter it out, and the organizer collects installment 1 and nothing
            // else. This path needs no webhook at all.
            //
            // Deliberately NOT done by expanding payment_intent on the session retrieve above:
            // that value is assigned to transaction_reference a few lines up, and expanding it
            // turns a string into an object for EVERY Stripe checkout, not just these.
            app(InstallmentService::class)->captureFromSession($sale->installmentPlan, $session);
        } catch (\Exception $e) {
            // Log the error but don't fail - webhook will handle payment confirmation
            \Log::warning('Stripe session retrieval failed in success(): '.$e->getMessage());
        }

        return $this->redirectToPurchaseLanding($sale, $event, request()->boolean('embed'));
    }

    public function cancel($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));

        // Verify the secret from the URL to prevent unauthorized cancellations
        $secret = request()->query('secret');
        if (! $secret || ! hash_equals($sale->secret, $secret)) {
            abort(403);
        }

        $expired = DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return false;
            }
            $sale->status = 'expired';
            $sale->save();

            return true;
        });

        if ($expired) {
            AuditService::log(AuditService::SALE_EXPIRED, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'expired'], 'guest_abandon:event_id:'.$sale->event_id);
        }

        $event = $sale->event;
        $cancelRedirectUrl = $event->getGuestUrl($subdomain, $sale->event_date).'?tickets=true';

        return redirect($cancelRedirectUrl);
    }

    /**
     * $subdomain is unused but must be declared: both route groups put a subdomain ahead of the sale
     * id (a path segment on selfhost, a domain parameter when hosted) and the controller dispatcher
     * fills scalar arguments positionally. Without it this method received the SUBDOMAIN as $sale_id
     * and every payment-URL return 404'd - which is why the rail's callbacks have never worked. Every
     * sibling handler here and in GiftCardController already takes the pair.
     */
    public function paymentUrlSuccess($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL (using constant-time comparison to prevent timing attacks)
        // Accept either a per-sale HMAC token or the legacy global secret
        $secret = request()->query('secret');
        $gateway = payment_gateways()->get('payment_url');
        if (! $secret || ! $gateway || ! $gateway->verifySecret($secret, $sale, $user)) {
            abort(403, 'Invalid secret');
        }

        // The rail reports no amount - the buyer just says they paid - so there is nothing to
        // reconcile. The reference is the localised "manual payment" sentinel the sales table reads
        // back to render this as a manual payment rather than a gateway id.
        //
        // Three things this settles that the hand-written version got wrong. An expired or refunded
        // sale is no longer revived: cancel-then-success on the same secret used to mint a paid ticket
        // out of released seats. The sale.paid webhooks are now gated on actually transitioning, so
        // re-opening this URL stops re-firing them. And they carry a refreshed sale, where before the
        // stale outer instance made the payload say status "unpaid" inside a sale.paid delivery.
        app(SaleSettlementService::class)->settle(
            $sale,
            __('messages.manual_payment'),
            null,
            'payment_url',
        );

        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        if (request()->boolean('embed')) {
            $url .= '?embed=true';
        }

        return redirect($url);
    }

    public function paymentUrlCancel($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL (using constant-time comparison to prevent timing attacks)
        // Accept either a per-sale HMAC token or the legacy global secret
        $secret = request()->query('secret');
        $gateway = payment_gateways()->get('payment_url');
        if (! $secret || ! $gateway || ! $gateway->verifySecret($secret, $sale, $user)) {
            abort(403, 'Invalid secret');
        }

        $expired = DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return false;
            }
            $sale->status = 'expired';
            $sale->save();

            return true;
        });

        if ($expired) {
            AuditService::log(AuditService::SALE_EXPIRED, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'expired'], 'payment_url_abandon:event_id:'.$sale->event_id);
        }

        $cancelUrl = $event->getGuestUrl($sale->subdomain, $sale->event_date).'?tickets=true';

        return redirect($cancelUrl);
    }

    /**
     * Generate a per-sale HMAC token for payment URL callbacks.
     * This produces a unique token per sale without requiring schema changes.
     */
    public function scan()
    {
        $user = auth()->user();

        // Event context for the scanner: the operator picks which event they're
        // scanning at (needed for cross-event subscriptions). List the user's
        // events without a tickets-only filter, since a subscription may cover
        // free / ticketless events.
        // Available on every plan: you cannot sell somebody a ticket and then refuse to let the
        // organizer admit them at the door. The Pro feature is the check-in DASHBOARD (live stats,
        // per-ticket breakdown), not scanning itself.
        $events = Event::with(['creatorRole', 'roles'])
            ->where('user_id', $user->id)
            ->whereNotNull('starts_at')
            ->whereNull('appointment_type_id') // appointment bookings are not scannable events
            ->orderBy('starts_at', 'desc')
            ->limit(100)
            ->get();

        // "Today" is each event's own calendar day at its venue, which is what
        // sales.event_date holds. Listed events may sit in different timezones, so query the
        // exact set of venue dates and match per event.
        $salesDatesByEvent = Sale::whereIn('event_id', $events->pluck('id'))
            ->whereIn('event_date', Event::scheduleTodayDates($events))
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->get(['event_id', 'event_date'])
            ->groupBy('event_id')
            ->map(fn ($rows) => $rows->pluck('event_date')->all());

        $hasSalesToday = fn (Event $e) => in_array($e->scheduleToday(), $salesDatesByEvent[$e->id] ?? [], true);
        $occursToday = fn (Event $e) => $e->matchesDate(
            now()->setTimezone($e->scheduleTimezone())->startOfDay(),
            $e->scheduleTimezone()
        );

        // Prefer an event with sales today, then any event occurring today, else most recent.
        $selectedEventId = $events->first($hasSalesToday)?->id
            ?? $events->first($occursToday)?->id
            ?? $events->first()?->id;

        $eventsData = $events->map(fn ($event) => [
            'id' => UrlUtils::encodeId($event->id),
            'name' => $event->name,
            'starts_at' => $event->starts_at ? $event->getShortDateRangeDisplay('D, M j, Y') : null,
            'image_url' => $event->getImageUrl(),
        ]);

        return view('ticket.scan', [
            'events' => $eventsData,
            'selectedEventId' => $selectedEventId ? UrlUtils::encodeId($selectedEventId) : null,
            // The scanner rebuilds its POST target from this rather than from the scanned
            // QR's origin, so a base-path install (/public/...) and the hosted app subdomain
            // both resolve correctly, and a foreign QR can't redirect the POST off-site.
            'scanUrlTemplate' => route('ticket.scanned', [
                'event_id' => '__EVENT_ID__',
                'secret' => '__SECRET__',
            ]),
        ]);
    }

    public function scanned($eventId, $secret)
    {
        $user = auth()->user();
        $event = Event::with('creatorRole')->find(UrlUtils::decodeId($eventId));

        if (! $event) {
            // Both "not valid" branches look identical to the operator; log which one fired
            // so a failing install can be diagnosed without guessing.
            Log::info('Ticket scan rejected: event not found', ['event_id' => $eventId]);

            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        $sale = Sale::with('saleTickets.ticket')
            ->where('event_id', $event->id)
            ->where('is_deleted', false)
            ->where('secret', $secret)
            ->first();

        if (! $sale) {
            Log::info('Ticket scan rejected: no matching sale for event', ['event_id' => $event->id]);

            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        // Passes / subscriptions are valid across one or more events and are
        // redeemed once per event per day, so they bypass the single-date window
        // and per-seat logic below. For a cross-event subscription the operator
        // may be scanning at a covered event other than the one the pass was
        // bought on; `scan_event_id` identifies that event (default: the pass's
        // home event). All validation + the neutral `pass_status` contract live
        // in PassRedemptionService.
        if ($sale->isPass()) {
            $scanningEvent = $event;
            if ($scanEventId = request('scan_event_id')) {
                $resolved = Event::with('creatorRole')->find(UrlUtils::decodeId($scanEventId));
                if ($resolved) {
                    $scanningEvent = $resolved;
                }
            }

            if (! $user->canScanEvent($scanningEvent)) {
                return response()->json(['error' => __('messages.you_are_not_authorized_to_scan_this_ticket')], 200);
            }

            return response()->json(app(PassRedemptionService::class)->redeem($sale, $scanningEvent, now()));
        }

        if (! $user->canScanEvent($event)) {
            return response()->json(['error' => __('messages.you_are_not_authorized_to_scan_this_ticket')], 200);
        }

        if (! $event->starts_at) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        // Build the UTC start moment from $sale->event_date (a schedule-TZ calendar date)
        // and the schedule-TZ time-of-day implied by $event->starts_at.
        $tz = $event->creatorRole?->timezone ?? config('app.timezone');
        $startsAt = strlen($event->starts_at) === 10
            ? Carbon::createFromFormat('Y-m-d', $event->starts_at, 'UTC')->startOfDay()
            : Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC');
        $timeOfDay = $startsAt->copy()->setTimezone($tz)->format('H:i:s');
        $startUtc = Carbon::createFromFormat('Y-m-d H:i:s', $sale->event_date.' '.$timeOfDay, $tz)
            ->setTimezone('UTC');
        $duration = $event->duration > 0 ? $event->duration : 2;
        $endUtc = $startUtc->copy()->addMinutes(Event::durationHoursToMinutes($duration));
        $earliest = $startUtc->copy()->subHours(24);
        $nowUtc = now('UTC');

        if ($nowUtc->lt($earliest)) {
            return response()->json(['error' => __('messages.this_ticket_cannot_be_checked_in_yet')], 200);
        }

        if ($nowUtc->gt($endUtc)) {
            return response()->json(['error' => __('messages.this_ticket_check_in_period_has_ended')], 200);
        }

        // An allowlist, not a list of known-bad statuses. The old branches covered
        // unpaid/cancelled/refunded and let everything else through, so an EXPIRED sale scanned
        // in successfully - and expiry releases the seats for resale (Sale::booted decrements
        // updateSold) while leaving is_deleted false and the secret working, so the original
        // buyer's QR still opened a seat somebody else had since bought.
        if ($sale->status !== 'paid') {
            // amount_mismatch gets its own message on purpose: the money really was captured, it
            // is a site admin who has not reconciled it yet, and telling the door "not paid"
            // would turn an internal backlog into a guest being turned away.
            $error = match ($sale->status) {
                'unpaid' => __('messages.this_ticket_is_not_paid'),
                'cancelled' => __('messages.this_ticket_is_cancelled'),
                'refunded' => __('messages.this_ticket_is_refunded'),
                'expired' => __('messages.this_ticket_is_expired'),
                'amount_mismatch' => __('messages.this_ticket_is_pending_review'),
                default => __('messages.this_ticket_is_not_valid'),
            };

            return response()->json(['error' => $error], 200);
        }

        // Behind on an installment plan. The sale is still `paid` - the seat is taken, the money
        // mostly collected - so this never reaches the allowlist above.
        //
        // Deliberately NOT returned as `error`: the scanner paints any error red and hides the
        // whole details block with it, so the door would see a red X, one sentence, and no
        // attendee name or amount. This returns a distinct payment_status the scanner renders
        // amber, with the name and the balance, and an "admit anyway" override - because turning a
        // paying guest away over one late instalment is the organizer's call to make, not the
        // software's.
        if ($sale->isInstallmentDelinquent()) {
            $plan = $sale->installmentPlan;

            return response()->json([
                'payment_status' => 'overdue',
                'attendee' => $sale->name,
                'event' => $event->name,
                'amount_due' => MoneyUtils::format($plan->amountRemaining(), $plan->currency),
                'amount_paid' => MoneyUtils::format($plan->amount_paid, $plan->currency),
                'amount_total' => MoneyUtils::format($plan->total_amount, $plan->currency),
            ], 200);
        }

        $data = new \stdClass;
        $data->attendee = $sale->name;
        $data->event = $event->name;
        // Venue-local time, matching the pass branch: the operator is standing at the door.
        $data->date = $event->localStartsAt(true, $sale->event_date, false, $tz);
        $data->tickets = [];

        foreach ($sale->saleTickets as $saleTicket) {
            $data->tickets[] = [
                'type' => $saleTicket->ticket->type,
                'seats' => json_decode($saleTicket->seats, true) ?? [],
            ];
        }

        foreach ($sale->saleTickets as $saleTicket) {
            $seats = $saleTicket->seats;
            if ($seats) {
                $seats = json_decode($seats, true);
                if (! is_array($seats)) {
                    continue;
                }
                foreach ($seats as $key => $value) {
                    if (! $value) {
                        $seats[$key] = time();
                    }
                }
                $saleTicket->seats = json_encode($seats);
                $saleTicket->save();
            }
        }

        WebhookService::dispatch('ticket.scanned', $sale);

        return response()->json($data);
    }

    public function qrCode($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        $url = canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $secret,
        ], false));

        return response(QrCodeUtils::png($url))
            ->header('Content-Type', 'image/png')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * The landing page for a checkout that spanned several events.
     *
     * Every leg keeps its own ticket page and its own scannable QR - an event's door needs a code
     * for that event - so this exists to tell the buyer the other legs are there at all. Without
     * it a three-event purchase redirects to one ticket and the other two are only ever seen in
     * their confirmation emails.
     *
     * Authorised by the order primary's own secret, which already opens that leg's ticket page, so
     * this grants nothing new.
     */
    public function viewOrder($orderId, $secret)
    {
        $primary = Sale::where('id', UrlUtils::decodeId($orderId))
            ->where('secret', $secret)
            ->where('is_deleted', false)
            ->firstOrFail();

        abort_unless($primary->isOrderPrimary(), 404);

        $sales = Sale::with('event')
            ->where('order_id', $primary->order_id)
            ->where('is_deleted', false)
            // Guest rows are per-attendee copies of a leg, not separate events; the buyer wants one
            // entry per event they bought.
            ->where(function ($query) {
                $query->whereNull('group_id')->orWhereColumn('group_id', 'id');
            })
            ->orderBy('event_date')
            ->get();

        // Not Event::role(), which is talent-only and returns null on a venue- or curator-hosted
        // event - the order page would then lose the schedule name from its title. Same fallback
        // chain Event::parsedTicketNotesHtml() uses for exactly this reason.
        $event = $primary->event;
        $role = $event ? ($event->getRoleWithEmailSettings() ?? $event->roles->first()) : null;

        return view('ticket.order', compact('primary', 'sales', 'role'));
    }

    public function view($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));

        // Appointment bookings have their own manage page (not the QR ticket view).
        if ($event->appointment_type_id) {
            return redirect()->route('appointments.manage', ['event_id' => $eventId, 'secret' => $secret]);
        }

        $sale = Sale::with(['promoCode', 'saleTickets.ticket'])->where('event_id', $event->id)->where('secret', $secret)->firstOrFail();
        $role = $event->role();

        // Advance booking surface (only for a paid, booking-enabled pass).
        $bookingService = app(PassBookingService::class);
        $passBookable = $bookingService->isBookable($sale);
        $bookedOccurrences = $passBookable ? $bookingService->bookedOccurrences($sale) : [];
        $bookableOccurrences = $passBookable ? $bookingService->bookableOccurrences($sale, now()) : [];
        $passPolicyTicket = $passBookable ? $bookingService->passSaleTicket($sale)?->ticket : null;

        return view('ticket.view', compact('event', 'sale', 'role', 'passBookable', 'bookedOccurrences', 'bookableOccurrences', 'passPolicyTicket'));
    }

    public function handleAction(Request $request, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $user = auth()->user();

        if (! $user->canEditEvent($sale->event)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        // Block status-changing actions on non-primary grouped sales
        if ($sale->group_id && ! $sale->isPrimarySale()) {
            $statusActions = ['mark_paid', 'refund', 'cancel', 'delete'];
            if (in_array($request->action, $statusActions)) {
                return response()->json(['error' => __('messages.error')], 403);
            }
        }

        // Each status-changing action re-fetches the sale under lockForUpdate and re-asserts its
        // precondition INSIDE the transaction. Without this, a concurrent transition (a webhook
        // marking the sale paid, or the ReleaseTickets cron expiring it) between this request's
        // read and its save would let a stale-status overwrite fire the Sale::booted restore hook
        // a second time - double-crediting a redeemed gift card (and misreporting analytics).
        // $previousStatus captures the locked pre-transition status for the audit log.
        $previousStatus = $sale->status;
        $actionPerformed = false;

        switch ($request->action) {
            case 'mark_paid':
                $prev = $this->markSalePaid($sale, __('messages.manual_payment'));
                if ($prev !== null) {
                    $previousStatus = $prev;
                    $actionPerformed = true;
                }
                break;

            case 'refund':
                $prev = $this->refundSale($sale);
                if ($prev !== null) {
                    $previousStatus = $prev;
                    $actionPerformed = true;
                }
                break;

            case 'cancel':
                $prev = $this->cancelSale($sale);
                if ($prev !== null) {
                    $previousStatus = $prev;
                    $actionPerformed = true;
                }
                break;

            case 'delete':
                $prev = $this->deleteSale($sale);
                if ($prev !== null) {
                    $previousStatus = $prev;
                    $actionPerformed = true;
                }
                break;
        }

        // Reflect the committed status change on the outer instance for the audit/webhook/email below.
        if ($actionPerformed) {
            $sale->refresh();
        }

        // Appointment bookings cancelled/refunded from the generic Sales page: tell the guest
        // (every appointment-native cancel path does) and remind the owner to refund real money.
        // Only when the booking was still live: 'delete' reports success even for an already
        // cancelled/refunded/expired sale, which would otherwise re-send the cancellation email.
        if ($actionPerformed
            && in_array($request->action, ['cancel', 'refund', 'delete'], true)
            && ! in_array($previousStatus, ['cancelled', 'refunded', 'expired'], true)
            && $sale->event?->appointment_type_id) {
            $wasPaidMoney = $previousStatus === 'paid' && (float) $sale->payment_amount > 0;
            app(\App\Services\EmailService::class)->sendAppointmentGuestCancellation($sale);
            if ($wasPaidMoney && $request->action !== 'refund') {
                // A refund action means the owner is already handling the money; cancel/delete get the reminder.
                app(\App\Services\EmailService::class)->sendAppointmentOwnerCancellation($sale, true);
            }
        }

        if ($actionPerformed) {
            $auditAction = match ($request->action) {
                'refund' => AuditService::SALE_REFUND,
                default => AuditService::SALE_CHECKIN,
            };
            AuditService::log($auditAction, $user->id, 'Sale', $sale->id,
                ['status' => $previousStatus],
                ['status' => $sale->status],
                $request->action.':event_id:'.$sale->event_id
            );

            $webhookEvent = match ($request->action) {
                'mark_paid' => 'sale.paid',
                'refund' => 'sale.refunded',
                'cancel' => 'sale.cancelled',
                default => null,
            };
            if ($webhookEvent) {
                $this->dispatchSaleWebhookAcrossOrder($webhookEvent, $sale);
            }

            if ($request->action === 'mark_paid') {
                (new EmailService)->sendSaleConfirmationEmails($sale->refresh());
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('message', __('messages.action_completed'));
    }

    public function release()
    {
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');

        if (! $serverSecret || ! $requestSecret || ! hash_equals($serverSecret, $requestSecret)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        \Artisan::call('app:release-tickets');

        return response()->json(['success' => true]);
    }

    public function cancelRsvp(Request $request, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));

        // Verify the secret to prevent unauthorized cancellations
        $secret = $request->input('secret');
        if (! $secret || ! hash_equals($sale->secret, $secret)) {
            abort(403);
        }

        if ($sale->payment_method !== 'rsvp' && $sale->payment_amount != 0) {
            abort(403);
        }

        // Gift-card-paid orders are purchases, not free reservations - self-cancel
        // would be an instant refund-to-card. Cancellation is owner-only for these.
        if ($sale->payment_method !== 'rsvp' && $sale->groupTotalGiftCard() > 0) {
            abort(403);
        }

        $cancelled = DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'paid') {
                return false;
            }
            $sale->status = 'cancelled';
            $sale->save();

            return true;
        });

        $event = $sale->event;

        if ($cancelled) {
            AuditService::log(AuditService::SALE_CANCEL, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'paid'], ['status' => 'cancelled'], 'rsvp_cancel:event_id:'.$sale->event_id);

            WebhookService::dispatch('sale.cancelled', $sale);
            if ($sale->group_id && $sale->isPrimarySale()) {
                foreach (Sale::where('group_id', $sale->id)->where('id', '!=', $sale->id)->get() as $gs) {
                    WebhookService::dispatch('sale.cancelled', $gs);
                }
            }
        }

        return redirect()->route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    /**
     * Reserve a seat for a covered occurrence in advance (pass advance booking).
     * Authenticated by the sale secret in the URL (the holder's private link).
     */
    public function passBook(Request $request, $eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        $back = redirect()->route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]);

        $bookEventId = UrlUtils::decodeId($request->input('book_event_id'));
        $date = $request->input('date');
        // Reject a malformed date here so book()'s Carbon::parse() can't 500 on garbage.
        if (! $bookEventId || ! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            return $back->with('error', __('messages.pass_invalid_date'));
        }

        try {
            $result = app(PassBookingService::class)->book($sale, (int) $bookEventId, $date, now());
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return $back->with('error', __('messages.error'));
        }

        if ($result->ok) {
            $bookedEvent = Event::with('user')->find((int) $bookEventId);
            if ($bookedEvent) {
                (new EmailService)->sendPassBookingConfirmation(
                    $sale, $bookedEvent, $date, $bookedEvent->getRoleWithEmailSettings(), queue: true
                );
            }

            WebhookService::dispatch('ticket.booked', $sale, null, [
                'booked_event_id' => UrlUtils::encodeId((int) $bookEventId),
                'booked_event_date' => $date,
            ]);

            return $back->with('message', __('messages.pass_booking_confirmed'));
        }

        return $back->with('error', $this->passBookingStatusMessage($result->status));
    }

    /**
     * Release an advance reservation, returning the seat to the shared pool.
     */
    public function passCancelBooking(Request $request, $eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->firstOrFail();

        $back = redirect()->route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]);

        $bookEventId = UrlUtils::decodeId($request->input('book_event_id'));
        $date = $request->input('date');
        if (! $bookEventId || ! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            return $back->with('error', __('messages.error'));
        }

        try {
            $result = app(PassBookingService::class)->cancel(
                $sale,
                (int) $bookEventId,
                $date,
                allowForfeit: $request->boolean('forfeit_ack'),
            );
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return $back->with('error', __('messages.error'));
        }

        if ($result->ok) {
            WebhookService::dispatch('ticket.booking_cancelled', $sale, null, [
                'booked_event_id' => UrlUtils::encodeId((int) $bookEventId),
                'booked_event_date' => $date,
                'forfeited' => $result->status === 'forfeited',
            ]);

            // The seat returned to the pool; offer it to the waitlist - but only
            // for an occurrence that hasn't started (a forfeit can land after
            // start, and a "spot opened" email for a running event is noise that
            // burns the guest's one-shot notification). A time-less event has no
            // start instant, so fall back to comparing calendar dates.
            $bookedEvent = Event::find((int) $bookEventId);
            $occurrenceUpcoming = $bookedEvent && ($bookedEvent->starts_at
                ? $bookedEvent->occurrenceStartUtc($date)->isFuture()
                : $date >= $bookedEvent->scheduleToday(now()));
            if ($occurrenceUpcoming) {
                NotifyWaitlist::dispatch((int) $bookEventId, $date);
            }

            return $back->with('message', $result->status === 'forfeited'
                ? __('messages.pass_booking_forfeited')
                : __('messages.pass_booking_cancelled'));
        }

        return $back->with('error', $this->passBookingStatusMessage($result->status));
    }

    /**
     * "Email me my pass link": re-send the holder's private pass link to the
     * email on file. Always responds the same way so a pass's existence can't
     * be probed.
     */
    public function resendPassLink(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'email' => 'required|email',
        ]);

        $generic = back()->with('message', __('messages.pass_link_sent_if_found'));

        $event = Event::find(UrlUtils::decodeId($request->event_id));
        if (! $event) {
            return $generic;
        }

        $sale = Sale::where('event_id', $event->id)
            ->where('email', $request->email)
            ->where('status', 'paid')
            ->whereHas('saleTickets.ticket', fn ($q) => $q->where('is_pass', true))
            ->latest()
            ->first();

        if ($sale) {
            $role = $event->getRoleWithEmailSettings();
            if ($role) {
                try {
                    (new EmailService)->sendTicketEmail($sale, $role, queue: true);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return $generic;
    }

    /**
     * Map a PassBookingService status to a user-facing message, reusing the
     * scanner's neutral pass vocabulary where it applies.
     */
    private function passBookingStatusMessage(string $status): string
    {
        return match ($status) {
            'sold_out' => __('messages.sold_out'),
            'limit_reached' => __('messages.pass_limit_reached'),
            'expired' => __('messages.pass_expired'),
            'not_covered' => __('messages.pass_not_covered'),
            'already_booked' => __('messages.pass_already_booked'),
            'invalid_date' => __('messages.pass_invalid_date'),
            'too_late' => __('messages.pass_cancel_too_late'),
            'confirm_forfeit' => __('messages.pass_forfeit_confirm_notice'),
            default => __('messages.error'),
        };
    }

    /**
     * Resend ticket email
     */
    public function resendEmail($sale_id): JsonResponse
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $user = auth()->user();

        if (! $user->canEditEvent($sale->event)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        try {
            $event = $sale->event;

            $role = $event->getRoleWithEmailSettings();

            if (! $role) {
                return response()->json(['error' => __('messages.error')], 400);
            }

            $emailService = new EmailService;

            $result = $emailService->sendTicketEmail($sale, $role, queue: false);

            if ($result === true) {
                return response()->json(['success' => true, 'message' => __('messages.email_sent_successfully')]);
            } elseif ($result === EmailService::ERROR_NOT_CONFIGURED) {
                return response()->json(['error' => __('messages.email_not_configured')], 422);
            } else {
                return response()->json(['error' => __('messages.failed_to_send_email')], 500);
            }
        } catch (\Exception $e) {
            // Log the full error server-side but return generic message to user
            \Log::error('Resend ticket email failed: '.$e->getMessage(), [
                'sale_id' => $sale->id ?? null,
            ]);

            return response()->json(['error' => __('messages.failed_to_send_email')], 500);
        }
    }

    /**
     * Resend a post-event feedback request email to a single attendee.
     */
    public function resendFeedbackEmail($sale_id): JsonResponse
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $user = auth()->user();

        if (! $sale->event || ! $user->canEditEvent($sale->event)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        // Don't re-ask someone who already submitted feedback (page may be stale)
        if ($sale->feedback()->exists()) {
            return response()->json(['error' => __('messages.feedback_already_responded')], 422);
        }

        // Defensive: an awaiting row should always have a real email, but guard anyway
        if (! $sale->email) {
            return response()->json(['error' => __('messages.email_not_configured')], 422);
        }

        try {
            $event = $sale->event;

            // Resolve the role the same way the feedback pipeline does (by subdomain) so the
            // from-address, branding, and List-Unsubscribe header match the original send.
            // (getRoleWithEmailSettings() is venue-first and picks the wrong schedule for curator events.)
            $role = Role::where('subdomain', $sale->subdomain)->where('is_deleted', false)->first();

            // Feedback is Pro-gated; mirror the bulk-send eligibility (SendFeedbackRequests / sendFeedbackNow).
            // isPro() is always true on selfhosted, so this does not block selfhosted resends.
            if (! $role || ! $role->isPro()) {
                return response()->json(['error' => __('messages.email_not_configured')], 422);
            }
            if (config('app.hosted')) {
                if (! $role->hasEmailSettings()) {
                    return response()->json(['error' => __('messages.email_not_configured')], 422);
                }
            } elseif (in_array(config('mail.default'), ['log', 'array'])) {
                return response()->json(['error' => __('messages.email_not_configured')], 422);
            }

            $originalLocale = app()->getLocale();

            try {
                app()->setLocale($role->language_code ?? $originalLocale);

                $mailable = new FeedbackRequest($sale, $event, $role);
                $sent = app(RoleMailerService::class)->sendForRole($role, $sale->email, $mailable);
            } finally {
                app()->setLocale($originalLocale);
            }

            if (! $sent) {
                return response()->json(['error' => __('messages.email_not_configured')], 422);
            }

            UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $role->id);

            // Refresh the timestamp so the column reflects the latest send and the
            // row re-sorts to the top on reload (matches the bulk-send semantics).
            $sale->feedback_sent_at = now();
            $sale->save();

            return response()->json(['success' => true, 'message' => __('messages.email_sent_successfully')]);
        } catch (\Exception $e) {
            // Never leak raw exception text to the user; report and return a generic message.
            report($e);
            \Log::error('Resend feedback email failed: '.$e->getMessage(), [
                'sale_id' => $sale->id ?? null,
            ]);

            return response()->json(['error' => __('messages.failed_to_send_email')], 500);
        }
    }

    public function importAttendees(Request $request)
    {
        $user = auth()->user();

        $roles = $user->roles()->wherePivot('level', '!=', 'follower')->get();

        if ($roles->isEmpty()) {
            abort(403);
        }

        $selectedRoleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;
        if ($selectedRoleId && ! $roles->pluck('id')->contains($selectedRoleId)) {
            $selectedRoleId = null;
        }
        if (! $selectedRoleId) {
            $selectedRoleId = $roles->first()->id;
        }

        $selectedRole = $selectedRoleId ? $roles->firstWhere('id', $selectedRoleId) : null;

        if ($selectedRole && ! $selectedRole->isPro()) {
            return view('ticket.import_attendees', [
                'roles' => $roles,
                'selectedRoleId' => $selectedRoleId,
                'events' => collect(),
                'event' => null,
                'tickets' => collect(),
                'eventDates' => [],
                'hasEmailSettings' => false,
                'emailSettingsRole' => null,
                'requiresPro' => true,
            ]);
        }

        $events = collect();
        if ($selectedRoleId) {
            $events = Event::whereHas('roles', fn ($q) => $q->where('roles.id', $selectedRoleId))
                ->where('is_draft', false)
                ->orderBy('starts_at', 'desc')
                ->get()
                ->map(fn ($e) => [
                    'id' => UrlUtils::encodeId($e->id),
                    'raw_id' => $e->id,
                    'name' => $e->translatedName(),
                    'starts_at' => $e->getShortDateRangeDisplay('D, M j, Y'),
                    'image_url' => $e->getImageUrl(),
                ]);
        }

        $selectedEventId = $request->event_id ? UrlUtils::decodeId($request->event_id) : null;
        if ($selectedEventId && ! $events->pluck('raw_id')->contains($selectedEventId)) {
            $selectedEventId = null;
        }
        if (! $selectedEventId && $events->isNotEmpty()) {
            $selectedEventId = $events->first()['raw_id'];
        }

        $event = null;
        $tickets = collect();
        $eventDates = [];

        $hasEmailSettings = false;
        $emailSettingsRole = null;

        if ($selectedEventId) {
            $event = Event::with(['tickets', 'creatorRole', 'roles'])->find($selectedEventId);
            if ($event) {
                $emailSettingsRole = $event->getRoleWithEmailSettings();
                $hasEmailSettings = $emailSettingsRole && $emailSettingsRole->hasEmailSettings();
                $tickets = $event->tickets->where('is_addon', false)->values();

                // Offer venue-local occurrence dates: these land in sales.event_date, and the
                // scanner rebuilds its check-in window from that column in the venue's timezone.
                // A UTC date here puts an imported ticket's window a day out.
                $eventTz = $event->scheduleTimezone();

                if ($event->days_of_week) {
                    $end = now()->addYear();
                    $cursor = now()->setTimezone($eventTz)->startOfDay();
                    while ($cursor->lte($end) && count($eventDates) < 60) {
                        if ($event->matchesDate($cursor, $eventTz)) {
                            $eventDates[] = $cursor->format('Y-m-d');
                        }
                        $cursor->addDay();
                    }
                } elseif ($event->starts_at) {
                    $eventDates[] = $event->saleEventDateFromStartsAt();
                }
            }
        }

        return view('ticket.import_attendees', [
            'roles' => $roles,
            'selectedRoleId' => $selectedRoleId,
            'events' => $events,
            'event' => $event,
            'tickets' => $tickets,
            'eventDates' => $eventDates,
            'hasEmailSettings' => $hasEmailSettings,
            'emailSettingsRole' => $emailSettingsRole,
            'requiresPro' => false,
        ]);
    }

    public function importAttendeesStore(ImportAttendeesRequest $request)
    {
        $user = auth()->user();

        $event = Event::with(['tickets', 'roles', 'creatorRole'])
            ->findOrFail(UrlUtils::decodeId($request->event_id));

        if (! $user->canEditEvent($event)) {
            abort(403);
        }

        if (! $event->isPro()) {
            abort(403);
        }

        $subdomain = $event->creatorRole?->subdomain
            ?? $event->roles->first()?->subdomain
            ?? '';

        $fallbackTicketId = UrlUtils::decodeId($request->ticket_id);
        $fallbackTicket = $event->tickets->firstWhere('id', $fallbackTicketId);
        if (! $fallbackTicket || $fallbackTicket->is_addon) {
            return back()->withInput()->with('error', __('messages.ticket_not_found'));
        }

        $eventDate = $request->event_date;
        $defaultStatus = $request->default_status;
        $sendEmails = $request->boolean('send_emails');

        if ($eventDate && ! $event->matchesDate(Carbon::parse($eventDate), $event->scheduleTimezone())) {
            return back()->withInput()->with('error', __('messages.invalid_event_date'));
        }

        $emailSettingsRole = $event->getRoleWithEmailSettings();
        $hasEmailSettings = $emailSettingsRole && $emailSettingsRole->hasEmailSettings();

        $eventCustomFieldIndices = $this->customFieldIndicesFor($event->custom_fields ?? []);
        $ticketCustomFieldIndices = [];
        foreach ($event->tickets as $t) {
            $ticketCustomFieldIndices[$t->id] = $this->customFieldIndicesFor($t->custom_fields ?? []);
        }

        $imported = 0;
        $skipped = [];
        $sentEmails = [];
        $seenEmails = [];

        try {
            DB::transaction(function () use (
                $request, $event, $fallbackTicket, $eventDate, $defaultStatus, $subdomain,
                $eventCustomFieldIndices, $ticketCustomFieldIndices,
                &$imported, &$skipped, &$sentEmails, &$seenEmails
            ) {
                foreach ($request->entries as $i => $entry) {
                    $rowNum = $i + 1;

                    $email = strtolower(trim($entry['email'] ?? ''));
                    if ($email === '') {
                        continue;
                    }
                    if (isset($seenEmails[$email])) {
                        $skipped[] = __('messages.row_error', [
                            'row' => $rowNum,
                            'error' => __('messages.duplicate_email'),
                        ]);

                        continue;
                    }

                    $ticket = $fallbackTicket;
                    if (! empty($entry['ticket_id'])) {
                        $ticketId = UrlUtils::decodeId($entry['ticket_id']);
                        $found = $event->tickets->firstWhere('id', $ticketId);
                        if ($found && ! $found->is_addon) {
                            $ticket = $found;
                        }
                    }

                    $quantity = (int) ($entry['quantity'] ?? 1);
                    if ($quantity < 1) {
                        $quantity = 1;
                    }

                    $lockedTicket = $event->tickets()->lockForUpdate()->find($ticket->id);
                    if ($lockedTicket->quantity > 0) {
                        $soldCount = $lockedTicket->soldCountFor($eventDate);
                        $remaining = $lockedTicket->quantity - $soldCount;
                        if ($quantity > $remaining) {
                            $skipped[] = __('messages.row_error', [
                                'row' => $rowNum,
                                'error' => __('messages.tickets_not_available'),
                            ]);

                            continue;
                        }
                    }

                    $status = ! empty($entry['status']) ? strtolower($entry['status']) : $defaultStatus;
                    if (! in_array($status, ['paid', 'unpaid'], true)) {
                        $status = $defaultStatus;
                    }

                    $amount = isset($entry['amount']) && $entry['amount'] !== ''
                        ? (float) $entry['amount']
                        : (float) $ticket->price * $quantity;

                    $sale = new Sale;
                    $sale->name = trim($entry['name'] ?? '') ?: Str::before($email, '@');
                    $sale->email = $email;
                    $sale->phone = ! empty($entry['phone']) ? strip_tags(trim($entry['phone'])) : null;
                    $sale->event_id = $event->id;
                    $sale->event_date = $eventDate;
                    $sale->subdomain = $subdomain;
                    $sale->secret = strtolower(Str::random(32));
                    $sale->payment_method = 'import';
                    $sale->payment_amount = $amount;
                    $sale->status = $status;

                    $entryCustomValues = $entry['custom_values'] ?? [];
                    foreach ($eventCustomFieldIndices as $idx) {
                        $value = $entryCustomValues[$idx] ?? null;
                        if ($value !== null) {
                            $value = trim(strip_tags((string) $value));
                            $sale->{"custom_value{$idx}"} = $value !== '' ? $value : null;
                        }
                    }

                    $sale->save();

                    $saleTicketData = [
                        'sale_id' => $sale->id,
                        'ticket_id' => $ticket->id,
                        'quantity' => $quantity,
                        'seats' => json_encode(array_fill(1, $quantity, null)),
                    ];

                    $entryTicketCustomValues = $entry['ticket_custom_values'] ?? [];
                    foreach (($ticketCustomFieldIndices[$ticket->id] ?? []) as $idx) {
                        $value = $entryTicketCustomValues[$idx] ?? null;
                        if ($value !== null) {
                            $value = trim(strip_tags((string) $value));
                            $saleTicketData["custom_value{$idx}"] = $value !== '' ? $value : null;
                        }
                    }

                    // SaleTicket::created increments the sold count (passes are forced
                    // to quantity 1); do not call updateSold() again here or imports
                    // double-count inventory.
                    $sale->saleTickets()->create($saleTicketData);

                    // Sale::booted() clears matching TicketWaitlist rows only on `updated`,
                    // not `created` — so bulk-created paid imports must clear them inline.
                    if ($status === 'paid') {
                        TicketWaitlist::where('event_id', $event->id)
                            ->where('event_date', $eventDate)
                            ->where('email', $email)
                            ->whereIn('status', ['waiting', 'notified'])
                            ->update(['status' => 'purchased']);
                    }

                    $seenEmails[$email] = true;
                    $imported++;
                    if ($status === 'paid') {
                        $sentEmails[] = $sale->id;
                    }
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        }

        if ($sendEmails && $hasEmailSettings && ! empty($sentEmails)) {
            $emailService = new EmailService;
            $sales = Sale::whereIn('id', $sentEmails)->get();
            foreach ($sales as $sale) {
                $emailService->sendSaleConfirmationEmails($sale);
            }
        }

        $statusMessage = __('messages.imported_n_attendees', ['count' => $imported]);
        if (! empty($skipped)) {
            $statusMessage .= ' '.__('messages.skipped_n_rows', ['count' => count($skipped)]);
        }

        return redirect()->route('sales')->with('status', $statusMessage);
    }

    protected function customFieldIndicesFor(array $customFields): array
    {
        $indices = [];
        $fallback = 1;
        foreach ($customFields as $fieldConfig) {
            $idx = $fieldConfig['index'] ?? $fallback;
            $fallback++;
            if ($idx >= 1 && $idx <= 10) {
                $indices[] = (int) $idx;
            }
        }

        return array_values(array_unique($indices));
    }
}
