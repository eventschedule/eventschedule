<?php

namespace Tests\Feature;

use App\Mail\InstallmentAuthenticationRequired;
use App\Mail\InstallmentFailed;
use App\Mail\InstallmentFinalNotice;
use App\Mail\InstallmentOnHold;
use App\Mail\InstallmentOrganizerDigest;
use App\Mail\InstallmentReminder;
use App\Models\Role;
use App\Models\SaleInstallmentPlan;
use App\Services\InstallmentService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The installment mails were sent by app:charge-installments and asserted only by class
 * (ChargeInstallmentsTest dispatches them through a faked bus), so nothing rendered them: an
 * undefined variable in one would surface as a failed queued job, never as a red test.
 */
class EmailInstallmentRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Role, 1: SaleInstallmentPlan} */
    private function plan(): array
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Blue Note']);
        $event = $this->createEvent($role, [
            'name' => 'Jazz Night with the Trio',
            'payment_method' => 'stripe',
            'installments_enabled' => true,
            'installment_count' => 4,
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, ['price' => 400, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['name' => 'Sam Lee', 'status' => 'paid', 'payment_amount' => 400], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 400.00, 'USD');
        $plan->update(['amount_paid' => 100, 'card_brand' => 'visa', 'card_last4' => '4242']);
        $plan->installments->firstWhere('sequence', 1)->update(['status' => 'paid', 'paid_at' => now()]);

        return [$role, $plan->fresh('installments')];
    }

    private function assertRendersCleanly(Mailable $mail, string $label): string
    {
        $html = $mail->render();

        $this->assertSame(1, substr_count($html, '<h1'), "$label: exactly one heading");
        $this->assertStringNotContainsString('messages.', $html, "$label: an untranslated key");
        $this->assertLessThan(80 * 1024, strlen($html), "$label: too close to Gmail's 102KB clip");

        return $html;
    }

    public function test_every_buyer_installment_mail_renders(): void
    {
        [$role, $plan] = $this->plan();
        $second = $plan->installments->firstWhere('sequence', 2);
        $second->update(['next_attempt_at' => now()->addDays(3)]);
        $payUrl = route('installment.view', ['plan_id' => UrlUtils::encodeId($plan->id), 'secret' => $plan->secret]);

        foreach ([
            'reminder' => new InstallmentReminder($plan, $second, $role),
            'failed' => new InstallmentFailed($plan, $second->fresh(), $role),
            'final notice' => new InstallmentFinalNotice($plan, $second, $role),
            'on hold' => new InstallmentOnHold($plan, $second, $role),
            'authentication' => new InstallmentAuthenticationRequired($plan, $second, $role),
            'no schedule' => new InstallmentReminder($plan, $second, null),
        ] as $label => $mail) {
            $html = $this->assertRendersCleanly($mail, $label);

            $this->assertStringContainsString('Jazz Night with the Trio', $html, $label);
            $this->assertStringContainsString(e($payUrl), $html, "$label: the pay link");
            // The whole schedule, one row per installment.
            foreach ($plan->installments as $row) {
                $this->assertStringContainsString($row->due_at->translatedFormat('j M Y'), $html, "$label: the payment schedule");
            }
        }
    }

    public function test_the_organizer_digest_renders_both_kinds(): void
    {
        [$role] = $this->plan();
        $rows = [
            ['name' => 'Sam Lee', 'email' => 'sam@example.com', 'event' => 'Jazz Night with the Trio', 'amount' => 100.0, 'currency' => 'USD', 'due_at' => '12 Oct 2026', 'progress' => '1 / 4', 'remaining' => 300.0],
            ['name' => 'Noa Cohen', 'email' => 'noa@example.com', 'event' => 'Jazz Night with the Trio', 'amount' => 100.0, 'currency' => 'USD', 'due_at' => '13 Oct 2026', 'progress' => '1 / 4', 'remaining' => 300.0],
        ];

        foreach (['due', 'overdue'] as $kind) {
            $html = $this->assertRendersCleanly(new InstallmentOrganizerDigest($role, $rows, $kind, 'USD', 200.0), "digest $kind");

            $this->assertStringContainsString('Sam Lee', $html);
            $this->assertStringContainsString('Noa Cohen', $html);
            $this->assertStringContainsString(e(route('sales', ['tab' => 'installments'])), $html);
        }
    }
}
