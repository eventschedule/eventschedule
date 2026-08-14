<?php

namespace App\Jobs;

use App\Models\Role;
use App\Services\RoleMailerService;
use App\Services\UsageTrackingService;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendQueuedEmail implements ShouldQueue
{
    use Queueable;

    /**
     * $mailable is stored whole, and every mailable in app/Mail uses SerializesModels, so
     * TicketPurchase's Sale/Event/Role become model identifiers in the payload. Sales are
     * hard rows - Sale has no SoftDeletes, and sales.event_id / ticket_id / user_id are all
     * ON DELETE CASCADE - so deleting an event, a schedule or an account removes the sale
     * out from under a queued email. Without this the payload cannot be deserialized and the
     * job fails inside CallQueuedHandler before handle() runs, which no try/catch in this
     * class can reach.
     *
     * Dropping it is the right answer rather than a shortcut: the mailable dereferences the
     * model to render (TicketPurchase::content() reads $this->sale->secret), so a confirmation
     * whose sale is gone has nothing left to confirm and can never be delivered.
     */
    public $deleteWhenMissingModels = true;

    public int $tries = 3;

    public int $backoff = 60;

    protected Mailable $mailable;

    protected string $recipient;

    protected ?int $roleId;

    protected ?string $locale;

    /**
     * Create a new job instance.
     */
    public function __construct(Mailable $mailable, string $recipient, ?int $roleId = null, ?string $locale = null)
    {
        // What makes the drop above safe: "missing" must mean deleted, not merely uncommitted.
        // Dispatched inside an open transaction, a worker could otherwise pick this up before
        // the sale row is visible and discard a perfectly good email. config/queue.php sets
        // after_commit on the database connection, but not on redis or sqs, so pin it here and
        // the guarantee no longer depends on which connection an install happens to use.
        // Set via the trait method rather than a property: Illuminate\Bus\Queueable already
        // declares $afterCommit, and redeclaring a trait property with a different default is
        // a fatal error on the PHP 8.2 this package still supports.
        $this->afterCommit();

        $this->mailable = $mailable;
        $this->recipient = $recipient;
        $this->roleId = $roleId;
        $this->locale = $locale;
    }

    /**
     * Execute the job. Role-mailer failures are caught and recorded inside
     * RoleMailerService: when a schedule's custom SMTP is failing the message
     * is intentionally not sent (sendForRole returns false) rather than
     * falling back to the platform mailer, so we only track usage when the
     * message was actually sent. A bare exception escaping this method
     * therefore indicates the platform mailer itself failed.
     */
    public function handle(): void
    {
        $originalLocale = app()->getLocale();

        try {
            if ($this->locale) {
                app()->setLocale($this->locale);
            }

            $role = $this->roleId ? Role::find($this->roleId) : null;

            if ($role) {
                if (app(RoleMailerService::class)->sendForRole($role, $this->recipient, $this->mailable)) {
                    UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $role->id);
                }

                return;
            }

            Mail::to($this->recipient)->send($this->mailable);
            UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $this->roleId ?? 0);
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
