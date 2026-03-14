<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketCheckoutRequest;
use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\TicketWaitlist;
use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\WebhookService;
use App\Utils\InvoiceNinja;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class TicketController extends Controller
{
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
            $query->where('event_date', '>=', now()->subDay()->startOfDay())
                ->whereHas('event', function ($eq) {
                    $eq->where('starts_at', '>=', now()->subDay()->startOfDay());
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

            return view('ticket.sales', compact('sales', 'count', 'waitlistCount', 'waitlistEntries', 'hasPro', 'groupCounts', 'sortBy', 'sortDir'));
        }
    }

    private function salesQuery(?string $filter, bool $primaryOnly = true, bool $includePast = false)
    {
        $user = auth()->user();

        $query = Sale::with('event', 'saleTickets.ticket', 'promoCode', 'feedback')
            ->where('is_deleted', false)
            ->whereHas('event', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        if (! $includePast) {
            $query->where('event_date', '>=', now()->subDay()->startOfDay())
                ->whereHas('event', function ($eq) {
                    $eq->where('starts_at', '>=', now()->subDay()->startOfDay());
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
            ->whereHas('sale', fn ($q) => $q->where('is_deleted', false))
            ->selectRaw('COUNT(*) as feedback_count, AVG(rating) as avg_rating')
            ->first();

        $feedbackCount = $stats->feedback_count;
        $averageRating = $stats->avg_rating ? round($stats->avg_rating, 1) : null;

        $totalEligibleSales = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->where(function ($q) {
                $q->whereNotNull('feedback_sent_at')
                    ->orWhereHas('feedback');
            })
            ->whereHas('event', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        $responseRate = $totalEligibleSales > 0 ? round(($feedbackCount / $totalEligibleSales) * 100) : 0;

        return compact('feedbacks', 'feedbackCount', 'averageRating', 'responseRate', 'sortBy', 'sortDir');
    }

    public function exportSales()
    {
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
            $headers = ['Name', 'Email', 'Phone', 'Event', 'Event Date', 'Tickets', 'Add-ons', 'Quantity', 'Amount', 'Currency', 'Promo Code', 'Discount', 'Transaction Reference', 'Payment Method', 'Status', 'Date', 'Group ID'];
            $headers = array_merge($headers, $customFieldNames);
            fputcsv($handle, $headers);

            // Second pass: write data rows
            foreach ($sales as $sale) {
                $tickets = $sale->saleTickets->filter(fn($st) => $st->ticket && !$st->ticket->is_addon)->map(function ($st) {
                    return ($st->ticket->type ?: '').' x'.$st->quantity;
                })->implode(', ');

                $addons = $sale->saleTickets->filter(fn($st) => $st->ticket && $st->ticket->is_addon)->map(function ($st) {
                    return ($st->ticket->type ?: '').' x'.$st->quantity;
                })->implode(', ');

                $row = [
                    $sale->name,
                    $sale->email,
                    $sale->phone,
                    $sale->event->name,
                    $sale->event_date,
                    $tickets,
                    $addons,
                    $sale->quantity(),
                    number_format($sale->payment_amount, 2, '.', ''),
                    $sale->event->ticket_currency_code,
                    $sale->promoCode ? $sale->promoCode->code : '',
                    $sale->discount_amount ? number_format($sale->discount_amount, 2, '.', '') : '',
                    $sale->transaction_reference,
                    $sale->payment_method,
                    $sale->status,
                    $sale->created_at->format('Y-m-d H:i'),
                    $sale->group_id ? UrlUtils::encodeId($sale->group_id) : '',
                ];

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

                $row = array_merge($row, $customValues);
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function checkout(TicketCheckoutRequest $request, $subdomain)
    {
        if ($request->filled('website')) {
            return back();
        }

        $event = Event::findOrFail(UrlUtils::decodeId($request->event_id));

        $role = Role::subdomain($subdomain)->firstOrFail();
        if (! $event->roles()->wherePivot('role_id', $role->id)->exists()) {
            abort(403);
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        // Verify event can sell tickets (checks past dates, tickets_enabled, and Pro plan)
        if (! $event->canSellTickets($request->event_date)) {
            return back()->withInput()->with('error', __('messages.tickets_not_available'));
        }

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
            $sale = DB::transaction(function () use ($request, $event, $user, $subdomain, $isPaymentLink) {
                // Check ticket availability with row locking (skip for payment link mode)
                if (! $isPaymentLink) {
                    // Resolve event_date for one-time events (hidden field may be empty)
                    $eventDate = $request->event_date;
                    if (! $eventDate && $event->starts_at) {
                        $eventDate = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
                    }

                    foreach ($request->tickets as $ticketId => $quantity) {
                        if ($quantity > 0) {
                            // Lock the ticket row to prevent concurrent modifications
                            $ticketModel = $event->tickets()->lockForUpdate()->find(UrlUtils::decodeId($ticketId));

                            if (! $ticketModel) {
                                throw new \Exception(__('messages.ticket_not_found'));
                            }

                            if ($ticketModel->isSalesEnded()) {
                                throw new \Exception(__('messages.tickets_not_available'));
                            }

                            if ($ticketModel->quantity > 0) {
                                // Handle combined mode logic
                                if ($event->total_tickets_mode === 'combined' && $event->hasSameTicketQuantities()) {
                                    // Lock all tickets for combined mode
                                    $lockedTickets = $event->tickets()->lockForUpdate()->get();
                                    $totalSold = $lockedTickets->sum(function ($ticket) use ($eventDate) {
                                        $ticketSold = $ticket->sold ? json_decode($ticket->sold, true) : [];

                                        return $ticketSold[$eventDate] ?? 0;
                                    });
                                    // In combined mode, the total quantity is the same as individual quantity
                                    $totalQuantity = $event->getSameTicketQuantity();
                                    $remainingTickets = $totalQuantity - $totalSold;

                                    // Check if the total requested quantity exceeds remaining tickets
                                    $totalRequested = array_sum($request->tickets);
                                    if ($totalRequested > $remainingTickets) {
                                        throw new \Exception(__('messages.tickets_not_available'));
                                    }
                                } else {
                                    $sold = json_decode($ticketModel->sold, true);
                                    $soldCount = $sold[$eventDate] ?? 0;
                                    $remainingTickets = $ticketModel->quantity - $soldCount;

                                    if ($quantity > $remainingTickets) {
                                        throw new \Exception(__('messages.tickets_not_available'));
                                    }
                                }
                            }
                        }
                    }

                    // Check add-on availability
                    $addonSelections = $request->input('addons', []);
                    foreach ($addonSelections as $addonId => $addonQty) {
                        $addonQty = (int) $addonQty;
                        if ($addonQty > 0) {
                            $addonModel = $event->addons()->lockForUpdate()->find(UrlUtils::decodeId($addonId));

                            if (! $addonModel) {
                                throw new \Exception(__('messages.ticket_not_found'));
                            }

                            if ($addonModel->quantity > 0) {
                                $sold = $addonModel->sold ? json_decode($addonModel->sold, true) : [];
                                $soldCount = $sold[$eventDate] ?? 0;
                                $remaining = $addonModel->quantity - $soldCount;

                                if ($addonQty > $remaining) {
                                    throw new \Exception(__('messages.tickets_not_available'));
                                }
                            }
                        }
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
                    $sale->boost_campaign_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }
                if (($utmParams['utm_source'] ?? null) === 'newsletter' && ($utmParams['utm_campaign'] ?? null)) {
                    $sale->newsletter_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }

                if (! $sale->event_date) {
                    $sale->event_date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
                }

                // Store event-level custom field values using stable indices
                // Fallback to iteration order for backward compatibility with fields without index
                $eventCustomValues = $request->input('event_custom_values', []);
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

                $sale->save();

                if ($isPaymentLink) {
                    // Payment link mode: quantities selected on IN, SaleTickets created by webhook
                    $sale->payment_amount = 0;
                } else {
                    // Check if individual tickets mode is active
                    $guests = $request->input('guests', []);
                    $isIndividualTickets = $event->individual_tickets && count($guests) > 1;

                    if ($isIndividualTickets) {
                        // Validate no duplicate emails among guests
                        $guestEmails = collect($guests)->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)));
                        if ($guestEmails->count() !== $guestEmails->unique()->count()) {
                            throw new \Exception(__('messages.duplicate_guest_emails'));
                        }

                        // Build flat list of ticket assignments for guests
                        $ticketAssignments = [];
                        $subtotal = 0;
                        foreach ($request->tickets as $ticketId => $quantity) {
                            if ($quantity > 0) {
                                $decodedId = UrlUtils::decodeId($ticketId);
                                $ticketModel = $event->tickets()->findOrFail($decodedId);
                                $subtotal += $ticketModel->price * $quantity;
                                for ($i = 0; $i < $quantity; $i++) {
                                    $ticketAssignments[] = $decodedId;
                                }
                            }
                        }

                        // Validate guest count matches ticket count
                        if (count($ticketAssignments) !== count($guests)) {
                            throw new \Exception(__('messages.error'));
                        }

                        // Primary sale gets the first ticket (qty=1)
                        $primarySaleTicketData = [
                            'ticket_id' => $ticketAssignments[0],
                            'quantity' => 1,
                            'seats' => json_encode([1 => null]),
                        ];

                        // Store per-guest ticket custom fields for primary guest
                        if ($event->individual_ticket_fields) {
                            $guestTicketCustomValues = $request->input('guest_ticket_custom_values', []);
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
                        $sale->payment_amount = $subtotal;
                    } else {
                        // Standard flow: create SaleTickets with full quantities
                        $ticketCustomValues = $request->input('ticket_custom_values', []);

                        foreach ($request->tickets as $ticketId => $quantity) {
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

                    // Create SaleTickets for add-ons (attach to primary sale only)
                    $addonSelections = $request->input('addons', []);
                    $hasAddons = false;
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
                        $addonTotal = $sale->saleTickets->filter(fn($st) => $st->ticket->is_addon)
                            ->sum(fn($st) => $st->ticket->price * $st->quantity);
                        $subtotal += $addonTotal;
                        $sale->payment_amount = $subtotal;
                    }

                    // Apply promo code if provided
                    if ($request->promo_code) {
                        $promoCode = PromoCode::where('event_id', $event->id)
                            ->whereRaw('LOWER(code) = ?', [strtolower($request->promo_code)])
                            ->lockForUpdate()
                            ->first();

                        if ($promoCode && $promoCode->isValid()) {
                            if ($isIndividualTickets) {
                                // Calculate discount from all ticket selections
                                $sale->load('saleTickets.ticket');
                                $allSaleTickets = collect();
                                foreach ($request->tickets as $ticketId => $quantity) {
                                    if ($quantity > 0) {
                                        $decodedId = UrlUtils::decodeId($ticketId);
                                        $ticketModel = $event->tickets()->find($decodedId);
                                        $fakeSaleTicket = new \App\Models\SaleTicket(['ticket_id' => $decodedId, 'quantity' => $quantity]);
                                        $fakeSaleTicket->setRelation('ticket', $ticketModel);
                                        $allSaleTickets->push($fakeSaleTicket);
                                    }
                                }
                                $discountAmount = $promoCode->calculateDiscount($allSaleTickets);
                            } else {
                                $discountAmount = $promoCode->calculateDiscount($sale->saleTickets);
                            }
                            if ($discountAmount > 0) {
                                $sale->promo_code_id = $promoCode->id;
                                $sale->discount_amount = $discountAmount;
                                $sale->payment_amount = max(0, $subtotal - $discountAmount);
                                $promoCode->increment('times_used');
                            }
                        }
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
                            $guestSale->payment_amount = 0;
                            $guestSale->status = $sale->status ?? 'unpaid';
                            $guestSale->group_id = $sale->id;

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
                                    $guestTicketCustomValues = $request->input('guest_ticket_custom_values', []);
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

                $sale->save();

                return $sale;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $total = $sale->payment_amount;

        // Send emails
        if ($event->individual_tickets && $sale->group_id) {
            $groupedSales = Sale::where('group_id', $sale->id)->get();
            foreach ($groupedSales as $groupSale) {
                $this->sendTicketPurchaseEmail($groupSale, $event);
            }
        } else {
            $this->sendTicketPurchaseEmail($sale, $event);
        }
        $this->sendNewSaleNotification($sale, $event);

        AuditService::log(AuditService::SALE_CHECKOUT, $sale->user_id, 'Sale', $sale->id, null, null, 'event_id:'.$event->id);

        // Dispatch sale.created webhook (outside transaction)
        WebhookService::dispatch('sale.created', $sale);
        if ($sale->group_id && $sale->isPrimarySale()) {
            foreach (Sale::where('group_id', $sale->id)->where('id', '!=', $sale->id)->get() as $gs) {
                WebhookService::dispatch('sale.created', $gs);
            }
        }

        $isEmbed = $request->boolean('embed');

        if ($total == 0 && ! $isPaymentLink) {
            $sale->status = 'paid';
            $sale->save();

            // Record free ticket sale in analytics (0 revenue)
            $analyticsCount = ($sale->group_id && $sale->isPrimarySale())
                ? Sale::where('group_id', $sale->id)->count()
                : 1;
            for ($i = 0; $i < $analyticsCount; $i++) {
                AnalyticsEventsDaily::incrementSale($event->id, 0);
            }
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($event->id, $sale->discount_amount);
            }

            WebhookService::dispatch('sale.paid', $sale);
            if ($sale->group_id && $sale->isPrimarySale()) {
                foreach (Sale::where('group_id', $sale->id)->where('id', '!=', $sale->id)->get() as $gs) {
                    WebhookService::dispatch('sale.paid', $gs);
                }
            }

            $ticketViewUrl = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
            if ($isEmbed) {
                $ticketViewUrl .= '?embed=true';
            }

            return redirect($ticketViewUrl);
        } else {
            switch ($event->payment_method) {
                case 'stripe':
                    return $this->stripeCheckout($subdomain, $sale, $event, $isEmbed);
                case 'invoiceninja':
                    return $this->invoiceninjaCheckout($subdomain, $sale, $event, $isEmbed);
                case 'payment_url':
                    return $this->paymentUrlCheckout($subdomain, $sale, $event, $isEmbed);
                default:
                    return $this->cashCheckout($subdomain, $sale, $event, $isEmbed);
            }
        }
    }

    public function rsvp(Request $request, $subdomain)
    {
        if ($request->filled('website')) {
            return back();
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
        if ($event->is_private && ! $isMemberOrAdmin) {
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
                    throw new \Exception(__('messages.rsvp_full'));
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
                    throw new \Exception(__('messages.rsvp_already_registered'));
                }

                // Check for duplicate guest registrations
                if ($event->individual_tickets && count($guests) > 1) {
                    $guestEmails = collect($guests)->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)));
                    if ($guestEmails->count() !== $guestEmails->unique()->count()) {
                        throw new \Exception(__('messages.duplicate_guest_emails'));
                    }
                    $duplicateGuests = Sale::where('event_id', $event->id)
                        ->where('event_date', $request->event_date)
                        ->whereIn('email', $guestEmails)
                        ->where('payment_method', 'rsvp')
                        ->where('status', 'paid')
                        ->where('is_deleted', false)
                        ->exists();

                    if ($duplicateGuests) {
                        throw new \Exception(__('messages.rsvp_already_registered'));
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
                    $sale->boost_campaign_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }
                if (($utmParams['utm_source'] ?? null) === 'newsletter' && ($utmParams['utm_campaign'] ?? null)) {
                    $sale->newsletter_id = UrlUtils::decodeId($utmParams['utm_campaign']);
                }

                if (! $sale->event_date) {
                    $sale->event_date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d');
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
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Send confirmation email and admin notification
        if ($event->individual_tickets && $sale->group_id) {
            $groupedSales = Sale::where('group_id', $sale->id)->get();
            foreach ($groupedSales as $groupSale) {
                $this->sendTicketPurchaseEmail($groupSale, $event);
            }
        } else {
            $this->sendTicketPurchaseEmail($sale, $event);
        }
        $this->sendNewSaleNotification($sale, $event);

        AuditService::log(AuditService::SALE_CHECKOUT, $sale->user_id, 'Sale', $sale->id, null, null, 'rsvp:event_id:'.$event->id);

        // Record in analytics (0 revenue) - count each attendee
        $rsvpAnalyticsCount = ($sale->group_id && $sale->isPrimarySale())
            ? Sale::where('group_id', $sale->id)->count()
            : 1;
        for ($i = 0; $i < $rsvpAnalyticsCount; $i++) {
            AnalyticsEventsDaily::incrementSale($event->id, 0);
        }

        // Dispatch webhooks
        WebhookService::dispatch('sale.created', $sale);
        WebhookService::dispatch('sale.paid', $sale);
        if ($sale->group_id && $sale->isPrimarySale()) {
            foreach (Sale::where('group_id', $sale->id)->where('id', '!=', $sale->id)->get() as $gs) {
                WebhookService::dispatch('sale.created', $gs);
                WebhookService::dispatch('sale.paid', $gs);
            }
        }

        $ticketViewUrl = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        if ($request->boolean('embed')) {
            $ticketViewUrl .= '?embed=true';
        }

        return redirect($ticketViewUrl);
    }

    private function stripeCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        $promoCode = $sale->promo_code_id ? $sale->promoCode : null;
        $discount = $sale->discount_amount ?? 0;

        // For grouped sales, aggregate SaleTickets across all sales in the group
        if ($sale->group_id && $sale->isPrimarySale()) {
            $allGroupSales = Sale::where('group_id', $sale->group_id)->with('saleTickets.ticket')->get();
            $aggregatedTickets = [];
            foreach ($allGroupSales as $gs) {
                foreach ($gs->saleTickets as $st) {
                    $tid = $st->ticket_id;
                    if (isset($aggregatedTickets[$tid])) {
                        $aggregatedTickets[$tid]['quantity'] += $st->quantity;
                    } else {
                        $aggregatedTickets[$tid] = [
                            'ticket_id' => $tid,
                            'quantity' => $st->quantity,
                            'ticket' => $st->ticket,
                        ];
                    }
                }
            }
            $stripeSaleTickets = collect($aggregatedTickets)->map(function ($item) {
                $st = new \App\Models\SaleTicket(['ticket_id' => $item['ticket_id'], 'quantity' => $item['quantity']]);
                $st->setRelation('ticket', $item['ticket']);

                return $st;
            });
        } else {
            $stripeSaleTickets = $sale->saleTickets;
        }

        // Calculate discount ratio for eligible tickets (exclude add-ons)
        $eligibleSubtotal = 0;
        if ($promoCode && $discount > 0) {
            foreach ($stripeSaleTickets as $saleTicket) {
                if ($saleTicket->ticket->is_addon) {
                    continue;
                }
                if ($promoCode->appliesToTicket($saleTicket->ticket_id)) {
                    $eligibleSubtotal += $saleTicket->ticket->price * $saleTicket->quantity;
                }
            }
        }
        $discountRatio = ($eligibleSubtotal > 0 && $discount > 0)
            ? ($eligibleSubtotal - $discount) / $eligibleSubtotal
            : 1;

        $lineItems = [];
        $totalCents = 0;
        $lastEligibleIndex = null;

        foreach ($stripeSaleTickets as $index => $saleTicket) {
            $unitPrice = $saleTicket->ticket->price;
            if ($promoCode && $discount > 0 && ! $saleTicket->ticket->is_addon && $promoCode->appliesToTicket($saleTicket->ticket_id)) {
                $unitPrice = $unitPrice * $discountRatio;
                $lastEligibleIndex = count($lineItems);
            }
            $unitAmountCents = (int) round($unitPrice * MoneyUtils::getSmallestUnitMultiplier($event->ticket_currency_code));
            $totalCents += $unitAmountCents * $saleTicket->quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => $event->ticket_currency_code,
                    'product_data' => [
                        'name' => $saleTicket->ticket->type ?: ($saleTicket->ticket->is_addon ? __('messages.add_on') : __('messages.tickets')),
                        ...$saleTicket->ticket->description ? ['description' => $saleTicket->ticket->description] : [],
                    ],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => $saleTicket->quantity,
            ];
        }

        // Fix rounding difference to match payment_amount exactly
        $expectedCents = (int) round($sale->payment_amount * MoneyUtils::getSmallestUnitMultiplier($event->ticket_currency_code));
        if ($totalCents !== $expectedCents && $lastEligibleIndex !== null) {
            $diff = $expectedCents - $totalCents;
            $lastItem = &$lineItems[$lastEligibleIndex];
            if ($lastItem['quantity'] > 1) {
                // Split: (quantity-1) at original price + 1 at adjusted price
                $originalUnit = $lastItem['price_data']['unit_amount'];
                $lastItem['quantity'] -= 1;
                $totalCents -= $originalUnit; // remove one unit from total
                $adjustedUnit = $originalUnit + $diff;
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $lastItem['price_data']['currency'],
                        'product_data' => $lastItem['price_data']['product_data'],
                        'unit_amount' => $adjustedUnit,
                    ],
                    'quantity' => 1,
                ];
            } else {
                $lastItem['price_data']['unit_amount'] += $diff;
            }
            unset($lastItem);
        }

        $data = [
            'sale_id' => UrlUtils::encodeId($sale->id),
            'subdomain' => $subdomain,
            'date' => $sale->event_date,
        ];

        // Determine if using Stripe Connect (hosted mode) or direct payments (self-hosted)
        $useConnect = config('app.hosted') && $event->user->stripe_account_id;

        if ($useConnect) {
            // Hosted mode: Use Stripe Connect with event creator's account
            $stripe = new StripeClient(config('services.stripe.key'));

            $session = $stripe->checkout->sessions->create(
                [
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'customer_email' => $sale->email,
                    'metadata' => [
                        'customer_name' => $sale->name,
                        'sale_id' => UrlUtils::encodeId($sale->id),
                    ],
                    'payment_intent_data' => [
                        'metadata' => [
                            'sale_id' => UrlUtils::encodeId($sale->id),
                        ],
                    ],
                    'success_url' => custom_domain_url(route('checkout.success', $data).(str_contains(route('checkout.success', $data), '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}'.($isEmbed ? '&embed=true' : '')),
                    'cancel_url' => custom_domain_url(route('checkout.cancel', $data).(str_contains(route('checkout.cancel', $data), '?') ? '&' : '?').'secret='.$sale->secret),
                ],
                [
                    'stripe_account' => $event->user->stripe_account_id,
                ],
            );
        } else {
            // Self-hosted mode: Use direct Stripe payments with platform keys
            $stripe = new StripeClient(config('services.stripe_platform.secret'));

            $session = $stripe->checkout->sessions->create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => $sale->email,
                'metadata' => [
                    'customer_name' => $sale->name,
                    'sale_id' => UrlUtils::encodeId($sale->id),
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'sale_id' => UrlUtils::encodeId($sale->id),
                    ],
                ],
                'success_url' => custom_domain_url(route('checkout.success', $data).(str_contains(route('checkout.success', $data), '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}&direct=1'.($isEmbed ? '&embed=true' : '')),
                'cancel_url' => custom_domain_url(route('checkout.cancel', $data).(str_contains(route('checkout.cancel', $data), '?') ? '&' : '?').'secret='.$sale->secret),
            ]);
        }

        return redirect($session->url);
    }

    private function invoiceninjaCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        $user = $event->user;

        if ($user->invoiceninja_mode === 'payment_link') {
            return $this->invoiceninjaPaymentLinkCheckout($subdomain, $sale, $event, $isEmbed);
        }

        return $this->invoiceninjaInvoiceCheckout($subdomain, $sale, $event, $isEmbed);
    }

    private function invoiceninjaInvoiceCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        try {
            $user = $event->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
            $company = null;

            $foundClient = false;
            $clientMachesEmail = false;
            $requirePassword = false;
            $sendEmail = false;

            $client = $invoiceNinja->findClient($sale->email, $event->ticket_currency_code);

            if ($client) {
                $foundClient = true;
                if (auth()->user() && auth()->user()->email_verified_at) {
                    foreach ($client['contacts'] as $contact) {
                        if ($contact['email'] == auth()->user()->email) {
                            $clientMachesEmail = true;
                        }
                    }
                }
                if (! $clientMachesEmail) {
                    $company = $invoiceNinja->getCompany();
                    $requirePassword = $company['settings']['enable_client_portal_password'];
                }
            } else {
                $client = $invoiceNinja->createClient($sale->name, $sale->email, $event->ticket_currency_code);
            }

            if ($foundClient && ! $clientMachesEmail && ! $requirePassword) {
                $sendEmail = true;
            }

            // For grouped sales, aggregate SaleTickets across all sales in the group
            if ($sale->group_id && $sale->isPrimarySale()) {
                $allGroupSales = Sale::where('group_id', $sale->group_id)->with('saleTickets.ticket')->get();
                $aggregatedTickets = [];
                foreach ($allGroupSales as $gs) {
                    foreach ($gs->saleTickets as $st) {
                        $tid = $st->ticket_id;
                        if (isset($aggregatedTickets[$tid])) {
                            $aggregatedTickets[$tid]['quantity'] += $st->quantity;
                        } else {
                            $aggregatedTickets[$tid] = [
                                'ticket_id' => $tid,
                                'quantity' => $st->quantity,
                                'ticket' => $st->ticket,
                            ];
                        }
                    }
                }
                $invoiceSaleTickets = collect($aggregatedTickets)->map(function ($item) {
                    $st = new \App\Models\SaleTicket(['ticket_id' => $item['ticket_id'], 'quantity' => $item['quantity']]);
                    $st->setRelation('ticket', $item['ticket']);

                    return $st;
                });
            } else {
                $invoiceSaleTickets = $sale->saleTickets;
            }

            $lineItems = [];
            foreach ($invoiceSaleTickets as $saleTicket) {
                $lineItems[] = [
                    'product_key' => $saleTicket->ticket->type ?: ($saleTicket->ticket->is_addon ? __('messages.add_on') : __('messages.tickets')),
                    'notes' => $saleTicket->ticket->description ?: ($saleTicket->ticket->type ?: ($saleTicket->ticket->is_addon ? __('messages.add_on') : __('messages.tickets'))),
                    'quantity' => $saleTicket->quantity,
                    'cost' => $saleTicket->ticket->price,
                ];
            }

            if ($sale->discount_amount > 0 && $sale->promoCode) {
                $lineItems[] = [
                    'product_key' => __('messages.discount'),
                    'notes' => $sale->promoCode->code,
                    'quantity' => 1,
                    'cost' => -$sale->discount_amount,
                ];
            }

            $qrCodeUrl = route('ticket.qr_code', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
            $invoice = $invoiceNinja->createInvoice($client['id'], $lineItems, $qrCodeUrl, $sendEmail);

            $sale->transaction_reference = $invoice['id'];
            $sale->payment_amount = $invoice['amount'];
            $sale->save();

            if ($sendEmail) {
                $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
                if ($isEmbed) {
                    $url .= '?embed=true';
                }

                return redirect($url);
            } else {
                return redirect($invoice['invitations'][0]['link']);
            }
        } catch (\Exception $e) {
            \Log::error('Invoice Ninja invoice checkout failed', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error'));
        }
    }

    private function invoiceninjaPaymentLinkCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        try {
            $user = $event->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);

            // Lazy-create the shared subscription for this event
            if (! $event->invoiceninja_subscription_id) {
                // Create an IN product for each ticket on the event
                foreach ($event->tickets as $ticket) {
                    if (! $ticket->invoiceninja_product_id) {
                        $productKey = $ticket->type ?: __('messages.tickets');
                        $product = $invoiceNinja->createProduct(
                            $productKey,
                            $ticket->description ?: $productKey,
                            $ticket->price
                        );
                        $ticket->invoiceninja_product_id = $product['id'];
                        $ticket->save();
                    }
                }

                // Create IN products for add-ons
                foreach ($event->addons as $addon) {
                    if (! $addon->invoiceninja_product_id) {
                        $productKey = $addon->type ?: __('messages.add_on');
                        $product = $invoiceNinja->createProduct(
                            $productKey,
                            $addon->description ?: $productKey,
                            $addon->price
                        );
                        $addon->invoiceninja_product_id = $product['id'];
                        $addon->save();
                    }
                }

                $optionalProductIds = $event->tickets->pluck('invoiceninja_product_id')
                    ->merge($event->addons->pluck('invoiceninja_product_id'))
                    ->filter()->values()->toArray();

                $encodedEventId = UrlUtils::encodeId($event->id);
                $subscriptionName = 'ES-'.($event->name ?: $encodedEventId).'-'.time();

                $webhookConfig = [
                    'post_purchase_url' => route('invoiceninja.event_purchase_webhook', ['event' => $encodedEventId]),
                    'post_purchase_rest_method' => 'post',
                    'post_purchase_headers' => ['X-Webhook-Secret' => $user->invoiceninja_webhook_secret],
                ];

                $promoCode = $event->promoCodes()->where('is_active', true)->first();

                $subscription = $invoiceNinja->createSubscription(
                    $subscriptionName,
                    $optionalProductIds,
                    $webhookConfig,
                    'auth.login-or-register,cart',
                    $promoCode?->code,
                    $promoCode ? (float) $promoCode->value : 0,
                    $promoCode ? ($promoCode->type !== 'percentage') : true
                );

                $event->invoiceninja_subscription_id = $subscription['id'];
                $event->invoiceninja_subscription_url = $subscription['purchase_page'];
                $event->save();
            }

            $sale->transaction_reference = 'sub:'.$event->invoiceninja_subscription_id;
            $sale->save();

            $purchaseUrl = $event->invoiceninja_subscription_url.'/v3';

            return redirect($purchaseUrl);
        } catch (\Exception $e) {
            \Log::warning('Invoice Ninja payment link checkout failed, falling back to invoice mode', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return $this->invoiceninjaInvoiceCheckout($subdomain, $sale, $event, $isEmbed);
        }
    }

    private function paymentUrlCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        $user = $event->user;

        return redirect($user->payment_url);
    }

    private function cashCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        if ($isEmbed) {
            $url .= '?embed=true';
        }

        return redirect($url);
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

                $mismatchUrl = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
                if (request()->boolean('embed')) {
                    $mismatchUrl .= '?embed=true';
                }

                return redirect($mismatchUrl);
            }

            // Store the transaction reference so the webhook can find this sale,
            // but don't set status=paid or overwrite payment_amount here - the webhook
            // handles that with proper locking and amount validation
            if ($sale->status !== 'paid') {
                $sale->transaction_reference = $session->payment_intent;
                $sale->save();
            }
        } catch (\Exception $e) {
            // Log the error but don't fail - webhook will handle payment confirmation
            \Log::warning('Stripe session retrieval failed in success(): '.$e->getMessage());
        }

        $successUrl = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        if (request()->boolean('embed')) {
            $successUrl .= '?embed=true';
        }

        return redirect($successUrl);
    }

    public function cancel($subdomain, $sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));

        // Verify the secret from the URL to prevent unauthorized cancellations
        $secret = request()->query('secret');
        if (! $secret || ! hash_equals($sale->secret, $secret)) {
            abort(403);
        }

        $cancelled = DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return false;
            }
            $sale->status = 'cancelled';
            $sale->save();

            return true;
        });

        $event = $sale->event;
        $cancelRedirectUrl = $event->getGuestUrl($subdomain, $sale->event_date).'?tickets=true';

        return redirect($cancelRedirectUrl);
    }

    public function paymentUrlSuccess($sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL (using constant-time comparison to prevent timing attacks)
        // Accept either a per-sale HMAC token or the legacy global secret
        $secret = request()->query('secret');
        if (! $secret || ! $user->payment_secret || ! $this->verifyPaymentUrlSecret($secret, $sale, $user)) {
            abort(403, 'Invalid secret');
        }

        // Use lockForUpdate to prevent race conditions from concurrent requests
        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status === 'paid') {
                return;
            }

            $sale->status = 'paid';
            $sale->transaction_reference = __('messages.manual_payment');
            $sale->save();

            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
            if ($sale->group_id && $sale->isPrimarySale()) {
                $guestCount = Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->count();
                for ($i = 0; $i < $guestCount; $i++) {
                    AnalyticsEventsDaily::incrementSale($sale->event_id, 0);
                }
            }
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }
        });

        WebhookService::dispatch('sale.paid', $sale);
        if ($sale->group_id && $sale->isPrimarySale()) {
            foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
                WebhookService::dispatch('sale.paid', $gs);
            }
        }

        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
        if (request()->boolean('embed')) {
            $url .= '?embed=true';
        }

        return redirect($url);
    }

    public function paymentUrlCancel($sale_id)
    {
        $sale = Sale::findOrFail(UrlUtils::decodeId($sale_id));
        $event = $sale->event;
        $user = $event->user;

        // Verify the secret from the URL (using constant-time comparison to prevent timing attacks)
        // Accept either a per-sale HMAC token or the legacy global secret
        $secret = request()->query('secret');
        if (! $secret || ! $user->payment_secret || ! $this->verifyPaymentUrlSecret($secret, $sale, $user)) {
            abort(403, 'Invalid secret');
        }

        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            if ($sale->status !== 'unpaid') {
                return;
            }
            $sale->status = 'cancelled';
            $sale->save();
        });

        $cancelUrl = $event->getGuestUrl($sale->subdomain, $sale->event_date).'?tickets=true';

        return redirect($cancelUrl);
    }

    /**
     * Generate a per-sale HMAC token for payment URL callbacks.
     * This produces a unique token per sale without requiring schema changes.
     */
    public static function generatePaymentUrlHmac(Sale $sale, $paymentSecret): string
    {
        return hash_hmac('sha256', (string) $sale->id, $paymentSecret);
    }

    /**
     * Verify a payment URL secret: accepts either a per-sale HMAC token
     * or the legacy global payment_secret for backward compatibility.
     */
    private function verifyPaymentUrlSecret(string $secret, Sale $sale, $user): bool
    {
        // Check per-sale HMAC token first (preferred)
        $expectedHmac = self::generatePaymentUrlHmac($sale, $user->payment_secret);
        if (hash_equals($expectedHmac, $secret)) {
            return true;
        }

        // Fall back to legacy global secret for backward compatibility
        return hash_equals($user->payment_secret, $secret);
    }

    public function scan()
    {
        return view('ticket.scan');
    }

    public function scanned($eventId, $secret)
    {
        $user = auth()->user();
        $event = Event::find(UrlUtils::decodeId($eventId));

        if (! $event) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        $sale = Sale::with('saleTickets.ticket')
            ->where('event_id', $event->id)
            ->where('is_deleted', false)
            ->where('secret', $secret)
            ->first();

        if (! $sale) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid')], 200);
        }

        if (! $user->canScanEvent($event)) {
            return response()->json(['error' => __('messages.you_are_not_authorized_to_scan_this_ticket')], 200);
        }

        if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
            return response()->json(['error' => __('messages.this_ticket_is_not_valid_for_today')], 200);
        }

        if ($sale->status == 'unpaid') {
            return response()->json(['error' => __('messages.this_ticket_is_not_paid')], 200);
        } elseif ($sale->status == 'cancelled') {
            return response()->json(['error' => __('messages.this_ticket_is_cancelled')], 200);
        } elseif ($sale->status == 'refunded') {
            return response()->json(['error' => __('messages.this_ticket_is_refunded')], 200);
        }

        $data = new \stdClass;
        $data->attendee = $sale->name;
        $data->event = $event->name;
        $data->date = $event->localStartsAt(true, $sale->event_date);
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

        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $secret]);

        $qrCode = QrCode::create($url)
            ->setSize(200)
            ->setMargin(10);

        $writer = new PngWriter;
        $result = $writer->write($qrCode);

        header('Content-Type: '.$result->getMimeType());
        header('X-Content-Type-Options: nosniff');

        echo $result->getString();

        exit;
    }

    public function view($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::with('promoCode')->where('event_id', $event->id)->where('secret', $secret)->firstOrFail();
        $role = $event->role();

        return view('ticket.view', compact('event', 'sale', 'role'));
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

        $previousStatus = $sale->status;
        $actionPerformed = false;

        switch ($request->action) {
            case 'mark_paid':
                if ($sale->status === 'unpaid') {
                    DB::transaction(function () use ($sale) {
                        $sale->status = 'paid';
                        $sale->transaction_reference = __('messages.manual_payment');
                        $sale->save();
                    });
                    $actionPerformed = true;

                    AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
                    if ($sale->discount_amount > 0) {
                        AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
                    }
                    if ($sale->group_id && $sale->isPrimarySale()) {
                        $guestCount = Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->count();
                        for ($i = 0; $i < $guestCount; $i++) {
                            AnalyticsEventsDaily::incrementSale($sale->event_id, 0);
                        }
                    }
                }
                break;

            case 'refund':
                if ($sale->status === 'paid') {
                    DB::transaction(function () use ($sale) {
                        $sale->status = 'refunded';
                        $sale->save();

                        // Skip analytics decrement for RSVP sales - handled by Sale::booted hook
                        if ($sale->payment_method !== 'rsvp') {
                            AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);
                            if ($sale->group_id && $sale->isPrimarySale()) {
                                $guestCount = Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->count();
                                for ($i = 0; $i < $guestCount; $i++) {
                                    AnalyticsEventsDaily::decrementSale($sale->event_id, 0);
                                }
                            }

                            if ($sale->discount_amount > 0) {
                                AnalyticsEventsDaily::decrementPromoSale($sale->event_id, $sale->discount_amount);
                            }
                        }
                    });
                    $actionPerformed = true;
                }
                break;

            case 'cancel':
                if (in_array($sale->status, ['unpaid', 'paid'])) {
                    $wasPaid = $sale->status === 'paid';

                    DB::transaction(function () use ($sale) {
                        $sale->status = 'cancelled';
                        $sale->save();
                    });
                    $actionPerformed = true;

                    // Skip analytics decrement for RSVP sales - handled by Sale::booted hook
                    if ($wasPaid && $sale->payment_method !== 'rsvp') {
                        AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);
                        if ($sale->group_id && $sale->isPrimarySale()) {
                            $guestCount = Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->count();
                            for ($i = 0; $i < $guestCount; $i++) {
                                AnalyticsEventsDaily::decrementSale($sale->event_id, 0);
                            }
                        }

                        if ($sale->discount_amount > 0) {
                            AnalyticsEventsDaily::decrementPromoSale($sale->event_id, $sale->discount_amount);
                        }
                    }
                }
                break;

            case 'delete':
                DB::transaction(function () use ($sale) {
                    // If the sale was paid, cancel first to release ticket inventory
                    // (triggers Sale::booted hook) and decrement analytics
                    if ($sale->status === 'paid') {
                        $sale->status = 'cancelled';
                        $sale->save();

                        // Skip analytics decrement for RSVP sales - handled by Sale::booted hook
                        if ($sale->payment_method !== 'rsvp') {
                            AnalyticsEventsDaily::decrementSale($sale->event_id, $sale->payment_amount);
                            if ($sale->group_id && $sale->isPrimarySale()) {
                                $guestCount = Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->count();
                                for ($i = 0; $i < $guestCount; $i++) {
                                    AnalyticsEventsDaily::decrementSale($sale->event_id, 0);
                                }
                            }

                            if ($sale->discount_amount > 0) {
                                AnalyticsEventsDaily::decrementPromoSale($sale->event_id, $sale->discount_amount);
                            }
                        }
                    }

                    $sale->is_deleted = true;
                    $sale->save();

                    // Cascade is_deleted to grouped guest sales
                    if ($sale->group_id && $sale->isPrimarySale()) {
                        Sale::where('group_id', $sale->group_id)
                            ->where('id', '!=', $sale->id)
                            ->update(['is_deleted' => true]);
                    }
                });
                $actionPerformed = true;
                break;
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
                WebhookService::dispatch($webhookEvent, $sale);
                if ($sale->group_id && $sale->isPrimarySale()) {
                    foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
                        WebhookService::dispatch($webhookEvent, $gs);
                    }
                }
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
     * Send ticket purchase email
     */
    private function sendTicketPurchaseEmail(Sale $sale, Event $event): void
    {
        try {
            $role = $event->getRoleWithEmailSettings();

            if (! $role) {
                \Log::warning('No schedule found for ticket email', [
                    'sale_id' => $sale->id,
                    'event_id' => $event->id,
                ]);

                return;
            }

            $emailService = new EmailService;
            $emailService->sendTicketEmail($sale, $role);
        } catch (\Exception $e) {
            // Log error but don't fail the sale creation
            \Log::error('Failed to send ticket purchase email: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
            ]);
        }
    }

    /**
     * Send new sale notification to opted-in editors
     */
    private function sendNewSaleNotification(Sale $sale, Event $event): void
    {
        try {
            $role = $event->getRoleWithEmailSettings();

            if (! $role) {
                return;
            }

            $emailService = new EmailService;
            $emailService->sendNewSaleNotification($sale, $event, $role);
        } catch (\Exception $e) {
            \Log::error('Failed to send sale notification: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
            ]);
        }
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
}
