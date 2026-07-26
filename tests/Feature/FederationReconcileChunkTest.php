<?php

namespace Tests\Feature;

use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Services\FederationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Reconcile has to survive a manifest larger than one request.
 *
 * The first implementation deleted "everything not in this request", so the final
 * chunk wiped every listing the earlier chunks carried - silent data loss for any
 * instance with more than MANIFEST_CHUNK_SIZE events.
 */
class FederationReconcileChunkTest extends TestCase
{
    use RefreshDatabase;

    private const RECONCILE = '/api/federation/reconcile';

    private string $secret = 'a-secret-long-enough-to-pass-validation-0123456789';

    private function makeInstance(): FederatedInstance
    {
        return FederatedInstance::create([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'secret' => $this->secret,
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);
    }

    private function makeListing(FederatedInstance $instance, string $externalId): FederatedEvent
    {
        return FederatedEvent::create([
            'federated_instance_id' => $instance->id,
            'external_id' => $externalId,
            'url' => 'https://operator.test/e/'.$externalId,
            'name' => 'Listing '.$externalId,
            'next_occurrence_at' => now()->addWeek(),
        ]);
    }

    private function signed(array $payload)
    {
        $body = json_encode($payload);

        return $this->call('POST', self::RECONCILE, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_'.str_replace('-', '_', strtoupper(FederationService::SIGNATURE_HEADER)) => 'sha256='.hash_hmac('sha256', $body, $this->secret),
        ], $body);
    }

    public function test_a_chunked_manifest_keeps_listings_from_earlier_chunks(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, 'first-chunk');
        $this->makeListing($instance, 'second-chunk');
        $this->makeListing($instance, 'gone-at-source');

        $runToken = (string) Str::uuid();

        // Chunk 1 of 2: names one listing, not final.
        $this->signed([
            'instance_id' => $instance->instance_id,
            'external_ids' => ['first-chunk'],
            'run_token' => $runToken,
            'is_final' => false,
        ])->assertOk()->assertJson(['removed' => 0]);

        // Chunk 2 of 2: names the other, and closes the pass.
        $this->signed([
            'instance_id' => $instance->instance_id,
            'external_ids' => ['second-chunk'],
            'run_token' => $runToken,
            'is_final' => true,
        ])->assertOk()->assertJson(['removed' => 1]);

        // Both named listings survive; only the one absent from every chunk goes.
        $this->assertDatabaseHas('federated_events', ['external_id' => 'first-chunk']);
        $this->assertDatabaseHas('federated_events', ['external_id' => 'second-chunk']);
        $this->assertDatabaseMissing('federated_events', ['external_id' => 'gone-at-source']);
    }

    public function test_a_stale_token_from_a_previous_pass_does_not_protect_a_row(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, 'kept');
        $this->makeListing($instance, 'dropped');

        // First pass covers both.
        $first = (string) Str::uuid();
        $this->signed([
            'instance_id' => $instance->instance_id,
            'external_ids' => ['kept', 'dropped'],
            'run_token' => $first,
            'is_final' => true,
        ])->assertOk();

        $this->assertDatabaseCount('federated_events', 2);

        // Second pass no longer mentions one of them.
        $second = (string) Str::uuid();
        $this->signed([
            'instance_id' => $instance->instance_id,
            'external_ids' => ['kept'],
            'run_token' => $second,
            'is_final' => true,
        ])->assertOk()->assertJson(['removed' => 1]);

        $this->assertDatabaseHas('federated_events', ['external_id' => 'kept']);
        $this->assertDatabaseMissing('federated_events', ['external_id' => 'dropped']);
    }

    public function test_a_blocked_row_still_survives_a_sweep(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, 'blocked')->block();

        $this->signed([
            'instance_id' => $instance->instance_id,
            'external_ids' => [],
            'run_token' => (string) Str::uuid(),
            'is_final' => true,
        ])->assertOk();

        $this->assertDatabaseHas('federated_events', ['external_id' => 'blocked']);
    }
}
