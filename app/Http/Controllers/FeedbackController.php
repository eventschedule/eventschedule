<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\FeedbackNotification;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\Sale;
use App\Services\WebhookService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function show($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->where('status', 'paid')->where('is_deleted', false)->firstOrFail();

        $event->loadMissing('roles');
        $role = $event->role() ?? $event->roles->first();

        if (! $role || ! $role->isPro()) {
            abort(404);
        }

        if (! $event->isFeedbackEnabled()) {
            abort(404);
        }

        $endDateTime = $event->getEndDateTime($sale->event_date);
        if ($endDateTime->isFuture()) {
            abort(404);
        }

        $existingFeedback = EventFeedback::where('sale_id', $sale->id)->first();

        if ($existingFeedback) {
            return view('feedback.thank-you', compact('event', 'sale', 'role', 'existingFeedback'));
        }

        return view('feedback.show', compact('event', 'sale', 'role'));
    }

    public function store(Request $request, $eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->where('status', 'paid')->where('is_deleted', false)->firstOrFail();

        $event->loadMissing('roles');
        $role = $event->role() ?? $event->roles->first();

        if (! $role || ! $role->isPro()) {
            abort(404);
        }

        if (! $event->isFeedbackEnabled()) {
            abort(404);
        }

        $endDateTime = $event->getEndDateTime($sale->event_date);
        if ($endDateTime->isFuture()) {
            abort(404);
        }

        $existingFeedback = EventFeedback::where('sale_id', $sale->id)->first();

        if ($existingFeedback) {
            return redirect()->route('feedback.show', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $comment = $validated['comment'] ?? null;

        try {
            $feedback = EventFeedback::create([
                'event_id' => $event->id,
                'sale_id' => $sale->id,
                'event_date' => $sale->event_date,
                'rating' => $validated['rating'],
                'comment' => $comment,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return redirect()->route('feedback.show', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]);
        }

        // Dispatch webhook
        WebhookService::dispatch('feedback.submitted', $sale, [
            'event' => 'feedback.submitted',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'event_id' => UrlUtils::encodeId($event->id),
                'event_name' => $event->name,
                'event_date' => $sale->event_date,
                'attendee_name' => $sale->name,
                'attendee_email' => $sale->email,
                'rating' => $feedback->rating,
                'comment' => $feedback->comment,
            ],
        ]);

        // Notify opted-in editors
        $this->notifyEditors($feedback, $sale, $event, $role);

        return redirect()->route('feedback.show', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]);
    }

    public function export(Request $request)
    {
        $user = auth()->user();

        $hasPro = $user->roles()->get()->contains(fn ($role) => $role->isPro());

        if (! $hasPro) {
            abort(403);
        }

        $query = EventFeedback::whereHas('event', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereHas('sale', fn ($q) => $q->where('is_deleted', false))
            ->with(['event', 'sale'])
            ->orderBy('created_at', 'desc');

        $filename = 'feedback-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM
            fwrite($file, "\xEF\xBB\xBF");

            // Sanitize values to prevent CSV formula injection in spreadsheet apps
            $sanitize = function ($value) {
                if ($value && preg_match('/^[\=\+\-\@\t\r]/', $value)) {
                    return "'".$value;
                }

                return $value;
            };

            fputcsv($file, [
                __('messages.event'),
                __('messages.date'),
                __('messages.attendee'),
                __('messages.email'),
                __('messages.rating'),
                __('messages.comment'),
                __('messages.submitted'),
            ]);

            foreach ($query->lazy() as $feedback) {
                fputcsv($file, [
                    $sanitize($feedback->event?->name ?? ''),
                    $feedback->event_date ?? '',
                    $sanitize($feedback->sale?->name ?? ''),
                    $feedback->sale?->email ?? '',
                    $feedback->rating,
                    $sanitize($feedback->comment ?? ''),
                    $feedback->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function notifyEditors(EventFeedback $feedback, Sale $sale, Event $event, $role): void
    {
        if (is_demo_role($role)) {
            return;
        }

        // Check if email sending is possible
        if (config('app.hosted')) {
            if (! $role->hasEmailSettings()) {
                return;
            }
        } else {
            $mailer = config('mail.default');
            if (in_array($mailer, ['log', 'array'])) {
                return;
            }
        }

        $editors = $role->getEditorsWantingNotification('new_feedback');

        foreach ($editors as $editor) {
            try {
                $mailable = new FeedbackNotification($feedback, $sale, $event, $role, $editor);

                SendQueuedEmail::dispatch(
                    $mailable,
                    $editor->email,
                    $role->id,
                    $editor->language_code ?? app()->getLocale()
                );
            } catch (\Exception $e) {
                report($e);
                Log::error('Failed to send feedback notification: '.$e->getMessage(), [
                    'feedback_id' => $feedback->id,
                    'editor_id' => $editor->id,
                ]);
            }
        }
    }
}
