<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\FeedbackNotification;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\Role;
use App\Models\Sale;
use App\Services\OneSignalService;
use App\Services\WebhookService;
use App\Utils\CsvUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function show($eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->where('status', 'paid')->where('is_deleted', false)->firstOrFail();

        $role = Role::where('subdomain', $sale->subdomain)->where('is_deleted', false)->first();
        if (! $role) {
            $event->loadMissing('roles');
            $role = $event->role() ?? $event->roles->first();
        }

        if (! $role || ! $role->isPro()) {
            abort(404);
        }

        if ($event->is_draft) {
            abort(404);
        }

        if (! $event->isFeedbackEnabled($role)) {
            abort(404);
        }

        // $sale->event_date is a venue-local calendar date, so the occurrence must be resolved
        // in the venue's timezone. Without it the end instant lands a day early for any evening
        // event west of UTC and the feedback page opens ~24h before the show.
        $endDateTime = $event->getEndDateTime($sale->event_date, true, $event->scheduleTimezone());
        if ($endDateTime->isFuture()) {
            abort(404);
        }

        $fonts = array_unique(array_filter([$role->font_family]));

        $existingFeedback = EventFeedback::where('sale_id', $sale->id)->first();

        if ($existingFeedback) {
            return view('feedback.thank-you', compact('event', 'sale', 'role', 'existingFeedback', 'fonts'));
        }

        return view('feedback.show', compact('event', 'sale', 'role', 'fonts'));
    }

    public function store(Request $request, $eventId, $secret)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($eventId));
        $sale = Sale::where('event_id', $event->id)->where('secret', $secret)->where('status', 'paid')->where('is_deleted', false)->firstOrFail();

        $role = Role::where('subdomain', $sale->subdomain)->where('is_deleted', false)->first();
        if (! $role) {
            $event->loadMissing('roles');
            $role = $event->role() ?? $event->roles->first();
        }

        if (! $role || ! $role->isPro()) {
            abort(404);
        }

        if ($event->is_draft) {
            abort(404);
        }

        if (! $event->isFeedbackEnabled($role)) {
            abort(404);
        }

        // $sale->event_date is a venue-local calendar date, so the occurrence must be resolved
        // in the venue's timezone. Without it the end instant lands a day early for any evening
        // event west of UTC and the feedback page opens ~24h before the show.
        $endDateTime = $event->getEndDateTime($sale->event_date, true, $event->scheduleTimezone());
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

        try {
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
        } catch (\Exception $e) {
            report($e);
        }

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
                    CsvUtils::sanitizeCell($feedback->event?->name ?? ''),
                    $feedback->event_date ?? '',
                    CsvUtils::sanitizeCell($feedback->sale?->name ?? ''),
                    CsvUtils::sanitizeCell($feedback->sale?->email ?? ''),
                    $feedback->rating,
                    CsvUtils::sanitizeCell($feedback->comment ?? ''),
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

        $editors = $role->getEditorsWantingNotification('new_feedback');

        // Push is an independent channel - dispatch regardless of email config.
        foreach ($editors as $editor) {
            OneSignalService::pushToUser($editor, [
                'title_key' => 'messages.push_new_feedback_title',
                'body_key' => 'messages.push_new_feedback_body',
                'body_params' => ['event' => $event->name],
                'url' => route('sales').'?tab=feedback',
                'options' => ['icon' => $role->profile_image_url],
            ], $role);
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
