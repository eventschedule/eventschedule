<?php

namespace Tests\Feature;

use App\Mail\VendorMapClaimApproved;
use App\Mail\VendorMapClaimDeclined;
use App\Mail\VendorMapClaimsWaiting;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapDraft;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Services\VendorMaps\VendorClaimNotifier;
use App\Services\VendorMaps\VendorMapClaims;
use App\Services\VendorMaps\VendorMapImages;
use App\Services\VendorMaps\VendorMapPng;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The vendor map's mail (VendorClaimNotifier): the organizer's batched notice of claims and
 * "not coming", and the vendor's answer, held for the organizer's Undo.
 */
class VendorMapMailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $dana;

    private Role $market;

    private Event $event;

    private VendorMap $map;

    protected function setUp(): void
    {
        parent::setUp();

        // The strings these assertions read, for as long as the language files do not carry
        // them yet; once they do, the real ones are used.
        foreach ([
            'vm_mail_approved_subject' => 'You\'re at :spot at the ":name" schedule',
            'vm_mail_declined_subject' => 'Not this time at the ":name" schedule',
            'vm_mail_owner_intro' => 'News from the vendor map of the ":name" schedule.',
            'vm_market_spot' => 'Stall :number',
        ] as $key => $line) {
            if (! Lang::has('messages.'.$key, 'en', false)) {
                app('translator')->addLines(['messages.'.$key => $line], 'en');
            }
        }

        Mail::fake();

        $this->dana = $this->createOwner();
        $this->dana->forceFill(['name' => 'Dana Example', 'email' => 'dana@gmail.com'])->save();
        $this->market = $this->createRole($this->dana, 'venue', ['name' => 'Riverside Farmers Market']);
        $this->event = $this->createRecurringEvent($this->market, ['name' => 'Friday market', 'creator_role_id' => $this->market->id]);

        $this->map = VendorMap::create(['role_id' => $this->market->id, 'event_id' => $this->event->id, 'kind' => 'market']);
        $this->map->ensureClaimToken();
    }

    private function spot(string $label): VendorMapSpot
    {
        return VendorMapSpot::create(['vendor_map_id' => $this->map->id, 'label' => $label, 'x' => 10, 'y' => 10, 'w' => 40, 'h' => 30]);
    }

    /** @return array{0: Role, 1: User} */
    private function vendor(string $name, string $email): array
    {
        $user = $this->createOwner();
        $user->forceFill(['email' => $email, 'name' => 'Vendor Person'])->save();

        return [$this->createRole($user, 'talent', ['name' => $name]), $user];
    }

    private function claim(VendorMapSpot $spot, Role $vendor, User $user, array $details = []): VendorMapOccupant
    {
        return VendorMapClaims::request($this->map, $spot, $vendor, $user, $details + [
            'name' => $vendor->name,
            'categories' => ['produce'],
            'sells' => 'Tomatoes and greens',
            'phone' => '+1 202 555 0147',
        ]);
    }

    private function runNotices(): void
    {
        $this->artisan('app:send-vendor-map-notices')->assertSuccessful();
    }

    public function test_the_owner_batch_names_two_claims_and_stamps_only_them(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        [$bee, $beeUser] = $this->vendor('Hill Honey', 'bee@gmail.com');
        $a = $this->claim($this->spot('14'), $avi, $aviUser);
        $b = $this->claim($this->spot('15'), $bee, $beeUser);

        // Told of already, and an answer already given: neither is news.
        [$old, $oldUser] = $this->vendor('Old Bakery', 'old@gmail.com');
        $told = $this->claim($this->spot('16'), $old, $oldUser);
        $told->forceFill(['notified_at' => now()->subHour()])->save();

        // The organizer's own "not coming" mark is not news to the organizer.
        $season = VendorMapOccupant::create(['vendor_map_id' => $this->map->id, 'spot_id' => $this->spot('17')->id, 'placeholder_name' => 'Cheese Stand']);
        $season->forceFill(['status' => 'approved'])->save();
        VendorMapClaims::markAbsent($this->map, $season, [$this->event->nextOccurrenceFrom()], $this->dana);
        $organizerMark = VendorMapOccupant::where('kind', 'absent')->firstOrFail();

        $this->runNotices();

        Mail::assertSent(VendorMapClaimsWaiting::class, 1);
        Mail::assertSent(VendorMapClaimsWaiting::class, function (VendorMapClaimsWaiting $mail) {
            $this->assertTrue($mail->hasTo('dana@gmail.com'));
            $this->assertCount(2, $mail->claims);
            $this->assertSame([], $mail->absences);

            $html = $mail->render();
            $this->assertStringContainsString('Avi&#039;s Vegetables', $html);
            $this->assertStringContainsString('Hill Honey', $html);
            $this->assertStringContainsString('Tomatoes and greens', $html);
            $this->assertStringNotContainsString('Old Bakery', $html);
            // Phones are for the editors' screens, never mail.
            $this->assertStringNotContainsString('555', $html);
            $this->assertStringContainsString('vendor-map', $html);
            $this->assertStringContainsString('the &quot;Riverside Farmers Market&quot; schedule', $html);

            return true;
        });

        $this->assertNotNull($a->fresh()->notified_at);
        $this->assertNotNull($b->fresh()->notified_at);
        $this->assertTrue($told->fresh()->notified_at->lt(now()->subMinutes(30)));
        $this->assertNull($organizerMark->fresh()->notified_at);
    }

    public function test_a_second_run_sends_nothing_and_a_later_claim_waits_for_the_quarter_hour(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $this->claim($this->spot('14'), $avi, $aviUser);

        $this->runNotices();
        $this->runNotices();
        Mail::assertSent(VendorMapClaimsWaiting::class, 1);

        [$bee, $beeUser] = $this->vendor('Hill Honey', 'bee@gmail.com');
        $late = $this->claim($this->spot('15'), $bee, $beeUser);

        $this->travel(5)->minutes();
        $this->runNotices();
        Mail::assertSent(VendorMapClaimsWaiting::class, 1);
        $this->assertNull($late->fresh()->notified_at);

        $this->travel(VendorClaimNotifier::OWNER_EVERY_MINUTES)->minutes();
        $this->runNotices();
        Mail::assertSent(VendorMapClaimsWaiting::class, 2);
        Mail::assertSent(VendorMapClaimsWaiting::class, fn ($mail) => count($mail->claims) === 1 && $mail->claims[0]['name'] === 'Hill Honey');
        $this->assertNotNull($late->fresh()->notified_at);
    }

    public function test_a_vendors_not_coming_is_in_the_owner_mail(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $season = $this->claim($this->spot('14'), $avi, $aviUser);
        VendorMapClaims::approve($season);
        VendorMapOccupant::query()->update(['notified_at' => now()->subDay(), 'vendor_notified_at' => now()->subDay()]);

        VendorMapClaims::markAbsent($this->map, $season->fresh(), [$this->event->nextOccurrenceFrom()], $aviUser);

        $this->runNotices();

        Mail::assertSent(VendorMapClaimsWaiting::class, fn ($mail) => $mail->claims === [] && count($mail->absences) === 1 && $mail->absences[0]['spot'] === '14');
        $this->assertNotNull(VendorMapOccupant::where('kind', 'absent')->first()->notified_at);
    }

    public function test_the_approved_mail_waits_for_undo_then_goes_once_to_the_vendor_pages_owner(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $claim = $this->claim($this->spot('14'), $avi, $aviUser);
        VendorMapClaims::approve($claim);

        $this->runNotices();
        Mail::assertNotSent(VendorMapClaimApproved::class);
        $this->assertNull($claim->fresh()->vendor_notified_at);

        $this->travel(VendorClaimNotifier::HOLD_MINUTES + 1)->minutes();
        $this->runNotices();
        $this->runNotices();

        Mail::assertSent(VendorMapClaimApproved::class, 1);
        Mail::assertSent(VendorMapClaimApproved::class, function (VendorMapClaimApproved $mail) use ($claim) {
            $this->assertTrue($mail->hasTo('avi@gmail.com'));
            $this->assertSame('You\'re at Stall 14 at Riverside Farmers Market', $mail->envelope()->subject);

            $html = $mail->render();
            $away = route('vendor_map.away', [
                'subdomain' => $this->market->subdomain,
                'occupant' => UrlUtils::encodeId($claim->id),
                'hash' => $claim->fresh()->stateHash(),
            ]);
            $this->assertStringContainsString(e($away), $html);
            $this->assertStringContainsString('?s='.rawurlencode($claim->spot->label), $html);
            $this->assertStringNotContainsString('555', $html);

            // The map with the stall marked rides inside the mail, never as a remote picture.
            if (VendorMapPng::stallImage($this->map->fresh(), (int) $claim->spot_id) !== null) {
                // render() swaps each embedded part's cid: for its data, for a browser.
                $this->assertMatchesRegularExpression('#<img src="(cid:|data:image/png;base64,)#', $html);
            }

            return true;
        });
        $this->assertNotNull($claim->fresh()->vendor_notified_at);
    }

    public function test_an_undone_answer_is_never_mailed(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $claim = $this->claim($this->spot('14'), $avi, $aviUser);
        VendorMapClaims::approve($claim);
        $this->assertTrue(VendorMapClaims::undo($claim->fresh()));

        $this->travel(VendorClaimNotifier::HOLD_MINUTES + 1)->minutes();
        $this->runNotices();

        Mail::assertNotSent(VendorMapClaimApproved::class);
    }

    public function test_the_declined_mail_carries_the_organizers_note(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $claim = $this->claim($this->spot('14'), $avi, $aviUser);
        VendorMapClaims::decline($claim, 'We are full this season, ask again in spring.');

        $this->travel(VendorClaimNotifier::HOLD_MINUTES + 1)->minutes();
        $this->runNotices();

        Mail::assertSent(VendorMapClaimDeclined::class, function (VendorMapClaimDeclined $mail) {
            $this->assertTrue($mail->hasTo('avi@gmail.com'));
            $this->assertSame('Not this time at Riverside Farmers Market', $mail->envelope()->subject);
            $this->assertStringContainsString('We are full this season, ask again in spring.', $mail->render());

            return true;
        });
        Mail::assertNotSent(VendorMapClaimApproved::class);
    }

    public function test_the_answer_goes_to_the_vendor_pages_current_owner_not_whoever_asked(): void
    {
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $claim = $this->claim($this->spot('14'), $avi, $aviUser);

        // The page changed hands after asking.
        $newOwner = $this->createOwner();
        $newOwner->forceFill(['email' => 'noa@gmail.com'])->save();
        $avi->users()->updateExistingPivot($aviUser->id, ['level' => 'admin']);
        $avi->users()->attach($newOwner->id, ['level' => 'owner']);

        VendorMapClaims::approve($claim);
        $this->travel(VendorClaimNotifier::HOLD_MINUTES + 1)->minutes();
        $this->runNotices();

        Mail::assertSent(VendorMapClaimApproved::class, 1);
        Mail::assertSent(VendorMapClaimApproved::class, fn ($mail) => $mail->hasTo('noa@gmail.com') && ! $mail->hasTo('avi@gmail.com'));
    }

    public function test_dates_asked_for_together_are_one_mail_in_the_markets_language(): void
    {
        $this->market->forceFill(['language_code' => 'he'])->save();
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');

        $first = $this->event->nextOccurrenceFrom();
        $second = $this->event->nextOccurrenceFrom(Carbon::parse($first)->addDay());
        $claim = $this->claim($this->spot('14'), $avi, $aviUser, ['dates' => [$first, $second]]);
        $rows = VendorMapOccupant::where('vendor_role_id', $avi->id)->get();
        $this->assertCount(2, $rows);
        $rows->each(fn ($row) => VendorMapClaims::approve($row));

        $this->travel(VendorClaimNotifier::HOLD_MINUTES + 1)->minutes();
        $this->runNotices();

        Mail::assertSent(VendorMapClaimApproved::class, 1);
        Mail::assertSent(VendorMapClaimApproved::class, function (VendorMapClaimApproved $mail) use ($first, $second) {
            $this->assertSame([$first, $second], $mail->dates);
            $this->assertSame('he', $mail->locale);
            $this->assertStringContainsString('dir="rtl"', $mail->render());

            return true;
        });
        $this->assertSame(0, VendorMapOccupant::where('vendor_role_id', $avi->id)->whereNull('vendor_notified_at')->count());
    }

    public function test_the_prune_command_deletes_old_drafts_and_their_files(): void
    {
        $disk = config('filesystems.default');
        Storage::fake($disk);

        $oldPath = VendorMapImages::storePrivate('old-photo', 'jpg');
        $newPath = VendorMapImages::storePrivate('new-photo', 'jpg');
        $old = VendorMapDraft::create(['vendor_map_id' => $this->map->id, 'images' => [$oldPath]]);
        $old->forceFill(['created_at' => now()->subHours(25)])->save();
        $fresh = VendorMapDraft::create(['vendor_map_id' => $this->map->id, 'images' => [$newPath]]);

        // A declined vendor's photo goes after a month; an approved one stays.
        [$avi, $aviUser] = $this->vendor('Avi\'s Vegetables', 'avi@gmail.com');
        $declinedPath = VendorMapImages::storePrivate('declined-photo', 'jpg');
        $declined = $this->claim($this->spot('14'), $avi, $aviUser, ['photo_path' => $declinedPath]);
        VendorMapClaims::decline($declined);
        $declined->forceFill(['decided_at' => now()->subDays(31)])->save();

        $this->artisan('app:prune-vendor-map-drafts')->assertSuccessful();

        $this->assertNull(VendorMapDraft::find($old->id));
        Storage::disk($disk)->assertMissing($oldPath);
        $this->assertNotNull(VendorMapDraft::find($fresh->id));
        Storage::disk($disk)->assertExists($newPath);
        Storage::disk($disk)->assertMissing($declinedPath);
        $this->assertNull($declined->fresh()->photo_path);
    }
}
