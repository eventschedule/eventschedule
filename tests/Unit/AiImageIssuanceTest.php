<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Utils\AiImageIssuance;
use App\Utils\ImageUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * AiImageIssuance: a posted AI image name is stored only when this app issued it to the same
 * schedule, for the same slot, in the shape ImageUtils::saveImageData() writes.
 *
 * The save that accepts a name also deletes the image it replaces, and later saves delete the name
 * itself, so a name that belongs to another schedule is a way to delete that schedule's file.
 */
class AiImageIssuanceTest extends TestCase
{
    private function role(int $id): Role
    {
        $role = new Role;
        $role->id = $id;

        return $role;
    }

    private function issuedName(string $slot, string $extension = 'png'): string
    {
        return $slot.'_'.strtolower(str_repeat('a1b2c3d4', 4)).'.'.$extension;
    }

    public function test_every_extension_save_image_data_can_write_is_accepted(): void
    {
        $role = $this->role(7);

        // Every format detectImageFormat() returns, plus an unknown one, which falls back to jpg.
        foreach (['jpeg', 'jpg', 'png', 'gif', 'webp', 'bmp', 'tiff'] as $format) {
            $name = 'flyer_'.str_pad($format, 32, '0').'.'.ImageUtils::getImageExtension($format);
            AiImageIssuance::record($name, 7, 3);

            $this->assertSame($name, AiImageIssuance::accept('flyer', $name, $role), $format);
        }
    }

    public function test_every_extension_an_agenda_scan_keeps_is_accepted(): void
    {
        $role = $this->role(7);

        // EventController::parseEventParts() keeps the uploaded photo's own extension.
        foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $extension) {
            $name = $this->issuedName('agenda', $extension);
            AiImageIssuance::record($name, 7, 3);

            $this->assertSame($name, AiImageIssuance::accept('agenda', $name, $role), $extension);
        }
    }

    public function test_an_issued_name_is_used_up_only_once_its_save_has_stored_it(): void
    {
        $role = $this->role(7);
        $name = $this->issuedName('profile');

        AiImageIssuance::record($name, 7, 3);

        $this->assertSame($name, AiImageIssuance::accept('profile', $name, $role));
        $this->assertSame($name, AiImageIssuance::accept('profile', $name, $role), 'a save that failed before storing it can be posted again');

        AiImageIssuance::consume($name);

        $this->assertNull(AiImageIssuance::accept('profile', $name, $role), 'a second save must not reuse the record');
    }

    /** A schedule save hands it every style image it stored at once (RoleController::update()). */
    public function test_consume_uses_up_every_name_it_is_given_and_no_other(): void
    {
        $role = $this->role(7);
        $profile = $this->issuedName('profile');
        $header = $this->issuedName('header');
        $background = $this->issuedName('background');

        foreach ([$profile, $header, $background] as $name) {
            AiImageIssuance::record($name, 7, 3);
        }

        AiImageIssuance::consume($profile, $header);

        $this->assertNull(AiImageIssuance::accept('profile', $profile, $role));
        $this->assertNull(AiImageIssuance::accept('header', $header, $role));
        $this->assertSame($background, AiImageIssuance::accept('background', $background, $role));
    }

    public function test_a_name_issued_to_another_schedule_is_refused_and_stays_issued(): void
    {
        $name = $this->issuedName('header');

        AiImageIssuance::record($name, 8, 3);

        $this->assertNull(AiImageIssuance::accept('header', $name, $this->role(7)));
        $this->assertSame($name, AiImageIssuance::accept('header', $name, $this->role(8)), 'a refusal must not use up the owner\'s record');
    }

    public function test_a_name_never_issued_is_refused(): void
    {
        $this->assertNull(AiImageIssuance::accept('background', $this->issuedName('background'), $this->role(7)));
    }

    public function test_a_name_issued_for_another_slot_is_refused(): void
    {
        $name = $this->issuedName('header');

        AiImageIssuance::record($name, 7, 3);

        $this->assertNull(AiImageIssuance::accept('profile', $name, $this->role(7)));
        $this->assertNull(AiImageIssuance::accept('flyer', $name, $this->role(7)));
    }

    public function test_a_record_lasts_a_day(): void
    {
        $role = $this->role(7);
        $early = $this->issuedName('profile', 'jpg');
        $late = $this->issuedName('profile', 'webp');

        AiImageIssuance::record($early, 7, 3);
        AiImageIssuance::record($late, 7, 3);

        $this->travel(23)->hours();
        $this->assertSame($early, AiImageIssuance::accept('profile', $early, $role));

        $this->travel(2)->hours();
        $this->assertNull(AiImageIssuance::accept('profile', $late, $role));
    }

    /** @return array<string, array{0: mixed}> */
    public static function malformed(): array
    {
        $random = str_repeat('a1b2c3d4', 4);

        return [
            'a trailing newline' => ["profile_{$random}.png\n"],
            'a leading path' => ["../profile_{$random}.png"],
            'a path after the name' => ["profile_{$random}.png/../../x.png"],
            'a directory' => ["public/profile_{$random}.png"],
            'upper case' => ['profile_'.strtoupper($random).'.png'],
            'a short random part' => ['profile_abc123.png'],
            'a long random part' => ["profile_{$random}0.png"],
            'an extension no generator writes' => ["profile_{$random}.svg"],
            'an upper-case extension' => ["profile_{$random}.JPEG"],
            'a double extension' => ["profile_{$random}.png.php"],
            'a quote' => ["profile_{$random}.png'"],
            'an array' => [["profile_{$random}.png"]],
            'null' => [null],
        ];
    }

    #[DataProvider('malformed')]
    public function test_a_malformed_value_is_refused_even_when_recorded(mixed $value): void
    {
        // Recorded as if issued, so only the shape check can refuse it.
        if (is_string($value)) {
            AiImageIssuance::record($value, 7, 3);
        }

        $this->assertNull(AiImageIssuance::accept('profile', $value, $this->role(7)));
    }
}
