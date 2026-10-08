<?php

namespace App\Notifications;

use App\Services\NotificationEmailService;
use App\Utils\RequestSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewRequestsNotification extends Notification
{
    use Queueable;

    protected $role;

    protected $requestCount;

    protected $newRequests;

    protected $alsoNew;

    protected $newCount;

    /**
     * $requestCount is everything waiting on the schedule. $newRequests are the requests this
     * mail spells out (events; RequestNotifier chooses how many fit), $alsoNew those it names in
     * a line each, and $newCount how many no mail has told of in all, these included: the
     * difference is said as "2 more follow in the next email". With none to spell out the mail
     * is the count alone, as it was before 2026-10: an appointment waiting to be confirmed has
     * had its own mail.
     *
     * @param  iterable<\App\Models\Event>|null  $newRequests
     * @param  iterable<\App\Models\Event>|null  $alsoNew
     */
    public function __construct($role, $requestCount, $newRequests = null, int $newCount = 0, $alsoNew = null)
    {
        $this->role = $role;
        $this->requestCount = $requestCount;
        $this->newRequests = collect($newRequests ?? []);
        $this->alsoNew = collect($alsoNew ?? []);
        $this->newCount = max($newCount, $this->newRequests->count() + $this->alsoNew->count());
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $requests = $this->summaries();
        $also = $requests ? $this->lines() : [];
        // Every request the mail names, in full or in a line.
        $shown = count($requests) + count($also);

        // Named after the request, and the event's name FIRST: a list of search results shows
        // the first forty characters of a subject, and "New request for <schedule>" is the same
        // forty on every one of them. One line, and not all of a 255 character name: a subject
        // is a header.
        $first = $requests ? Str::limit(Str::squish($requests[0]['title']), 90) : '';
        $subject = match (true) {
            ! $requests => __('messages.new_requests_notification_subject', ['name' => $this->role->name, 'count' => $this->requestCount]),
            $shown === 1 => __('messages.request_mail_subject', ['name' => $this->role->name, 'event' => $first]),
            default => trans_choice('messages.request_mail_subject_many', $shown - 1, ['name' => $this->role->name, 'event' => $first, 'count' => $shown - 1]),
        };
        // app_url(), never a bare route(): neither route names a host, so route() answers with
        // the host of whatever is running. From the noon digest that is APP_URL, which works.
        // From a guest's submission it is the schedule's own host, where /{slug}/{id} is a guest
        // route: the owner was sent a button that opened "not found", and an unsubscribe link
        // that did the same.
        $actionUrl = app_url(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'requests'], false));
        $unsubscribeUrl = app_url(route('role.unsubscribe', ['subdomain' => $this->role->subdomain], false));

        // The copy for the schedule's shared notification address (routed on demand, so the
        // notifiable is anonymous) unsubscribes by removing that address; nobody there has an
        // account for the generic route to act on.
        $notificationEmailUnsubscribeUrl = null;
        if ($notifiable instanceof AnonymousNotifiable) {
            $notificationEmailUnsubscribeUrl = NotificationEmailService::unsubscribeUrl($this->role);
            $unsubscribeUrl = $notificationEmailUnsubscribeUrl;
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.new_requests', $data = [
                'role' => $this->role,
                'requestCount' => $this->requestCount,
                'requests' => $requests,
                'also' => $also,
                'newCount' => $this->newCount,
                'mailSubject' => $subject,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
                'notificationEmailUnsubscribeUrl' => $notificationEmailUnsubscribeUrl,
            ])
            ->text('emails.new_requests_text', $data)
            ->withSymfonyMessage(function ($message) use ($unsubscribeUrl) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            });
    }

    /**
     * The requests this mail spells out, each as RequestSummary::describe() gives it.
     *
     * Built here, not where the notification is made: toMail() runs in the reader's language,
     * and a summary is dates, "yes" and "no". Several are each told more briefly than one.
     *
     * @return list<array<string, mixed>>
     */
    private function summaries(): array
    {
        $brief = $this->newRequests->count() > 1;

        return $this->newRequests->map(function ($event) use ($brief) {
            try {
                return RequestSummary::describe($event, $this->role, $brief);
            } catch (\Throwable $e) {
                // One request with something odd about it must not hold the mail back: it
                // would never be stamped, and would be the first thing every later mail tried.
                report($e);

                return RequestSummary::bare($event);
            }
        })->values()->all();
    }

    /**
     * The requests this mail only names, each as RequestSummary::line() gives it.
     *
     * @return list<array{title: string, from: ?string, day: ?string}>
     */
    private function lines(): array
    {
        return $this->alsoNew->map(function ($event) {
            try {
                return RequestSummary::line($event, $this->role);
            } catch (\Throwable $e) {
                report($e);

                return ['title' => (string) $event->name, 'from' => null, 'day' => null];
            }
        })->values()->all();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
