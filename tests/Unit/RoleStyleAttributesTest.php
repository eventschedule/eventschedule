<?php

namespace Tests\Unit;

use App\Models\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The schedule style values every guest page prints into CSS - in the layout's <style> block, in
 * style="" attributes, and in Vue :style expressions that the runtime compiler evaluates - are
 * sanitized where they are READ, by Role's accessors, so every reader gets a safe value: the
 * schedule's own page, another schedule's event page through $otherRole, a row a backup restored
 * as it came, and any view written later.
 *
 * Nothing is rewritten on save. The raw column is what getAttributes() and every dirty check see.
 */
class RoleStyleAttributesTest extends TestCase
{
    private function role(array $attributes): Role
    {
        $role = new Role;
        $role->setRawAttributes($attributes);

        return $role;
    }

    public function test_every_gradient_preset_reads_back_exactly_as_stored(): void
    {
        $presets = json_decode(file_get_contents(base_path('storage/gradients.json')), true);
        $this->assertNotEmpty($presets);

        foreach ($presets as $preset) {
            // Stored the way ColorUtils::randomGradient() and the edit form's options store it.
            $stored = implode(', ', $preset['colors']);

            $this->assertSame($stored, $this->role(['background_colors' => $stored])->background_colors, $preset['name']);
        }
    }

    /** @return array<string, array{0: ?string, 1: ?string}> */
    public static function gradients(): array
    {
        return [
            'two colours' => ['#aabbcc, #112233', '#aabbcc, #112233'],
            'no spaces' => ['#aabbcc,#112233', '#aabbcc, #112233'],
            'no hashes, as one preset has' => ['43ADD0, 998EE0', '43ADD0, 998EE0'],
            'short and alpha forms' => ['#abc, #aabbccdd', '#abc, #aabbccdd'],
            'a declaration break-out' => ['red; } body{display:none} x{', null],
            'a break-out after a real colour' => ['#123456, red; } body{display:none} x{', '#123456'],
            'a line break' => ["#ffffff, #000000\n} body{display:none} x{", '#ffffff'],
            'a colour split by a line break' => ["#fff\n,#000", '#fff, #000'],
            'a function' => ['#fff, url(//evil.test/x)', '#fff'],
            'named colours' => ['red, blue', null],
            'what an empty custom pair stores' => [', ', null],
            'empty' => ['', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('gradients')]
    public function test_background_colors(?string $stored, ?string $expected): void
    {
        $role = $this->role(['background_colors' => $stored]);

        $this->assertSame($expected, $role->background_colors);
        $this->assertSame($stored, $role->getAttributes()['background_colors'], 'the column itself is left alone');
    }

    /** @return array<string, array{0: mixed, 1: ?string}> */
    public static function colors(): array
    {
        return [
            'six digits' => ['#4E81FA', '#4E81FA'],
            'three digits' => ['#fff', '#fff'],
            'eight digits' => ['#11223344', '#11223344'],
            'no hash' => ['4E81FA', null],
            'four digits' => ['#abcd', null],
            'a named colour' => ['red', null],
            'a declaration break-out' => ['#fff; } body{display:none} x{', null],
            'a trailing line break' => ["#ffffff\n", null],
            'a Vue expression' => ["'+alert(1)+'", null],
            'empty, which a cleared accent stores' => ['', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('colors')]
    public function test_accent_color_and_background_color(mixed $stored, ?string $expected): void
    {
        $role = $this->role(['accent_color' => $stored, 'background_color' => $stored]);

        $this->assertSame($expected, $role->accent_color);
        $this->assertSame($expected, $role->background_color);
    }

    public function test_background_rotation_is_a_number(): void
    {
        $this->assertSame(150, $this->role(['background_rotation' => 150])->background_rotation);
        $this->assertSame(45, $this->role(['background_rotation' => '45'])->background_rotation);
        $this->assertSame(12, $this->role(['background_rotation' => '12deg, red); } body{display:none'])->background_rotation);
        $this->assertNull($this->role(['background_rotation' => null])->background_rotation);
    }

    public function test_every_bundled_background_is_a_valid_preset_name(): void
    {
        $names = array_unique(array_map(
            fn ($file) => pathinfo($file, PATHINFO_FILENAME),
            glob(public_path('images/backgrounds/*.webp'))
        ));
        $this->assertNotEmpty($names);

        foreach ($names as $name) {
            $this->assertSame($name, $this->role(['background_image' => $name])->background_image);
        }
    }

    /** @return array<string, array{0: ?string, 1: ?string}> */
    public static function backgroundImages(): array
    {
        return [
            'a preset' => ['Abstract_Sunrise', 'Abstract_Sunrise'],
            'none' => ['none', 'none'],
            'a path' => ['../../../.env', null],
            'a url break-out' => ['x.webp"); } body{display:none} x{', null],
            'a line break' => ["Calm\n", null],
            'empty' => ['', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('backgroundImages')]
    public function test_background_image(?string $stored, ?string $expected): void
    {
        $this->assertSame($expected, $this->role(['background_image' => $stored])->background_image);
    }

    public function test_every_offered_font_reads_back_exactly(): void
    {
        $fonts = json_decode(file_get_contents(base_path('storage/fonts.json')), true);
        $this->assertNotEmpty($fonts);

        foreach ($fonts as $font) {
            $this->assertSame($font['value'], $this->role(['font_family' => $font['value']])->font_family);
        }
    }

    /** @return array<string, array{0: ?string, 1: ?string}> */
    public static function fonts(): array
    {
        return [
            'a font' => ['Playfair_Display', 'Playfair_Display'],
            'a font with a space' => ['Open Sans', 'Open Sans'],
            'a quote' => ["Roboto'; background:url(//evil.test/x)", null],
            'a semicolon' => ['Roboto; color: red', null],
            'a line break' => ["Roboto\n", null],
            'too long' => [str_repeat('a', 101), null],
            'empty' => ['', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('fonts')]
    public function test_font_family(?string $stored, ?string $expected): void
    {
        $this->assertSame($expected, $this->role(['font_family' => $stored])->font_family);
    }

    /** @return array<string, array{0: ?string, 1: ?string}> */
    public static function sponsorBackgrounds(): array
    {
        return [
            'transparent' => ['transparent', 'transparent'],
            'six digits' => ['#102030', '#102030'],
            'three digits' => ['#fff', null],
            'a declaration break-out' => ['#102030; } body{display:none}', null],
            'javascript' => ['javascript:alert(1)', null],
            'empty' => ['', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('sponsorBackgrounds')]
    public function test_sponsor_background_color(?string $stored, ?string $expected): void
    {
        $this->assertSame($expected, $this->role(['sponsor_background_color' => $stored])->sponsor_background_color);
    }

    public function test_the_array_form_is_sanitized_too(): void
    {
        $role = $this->role([
            'accent_color' => "'+alert(1)+'",
            'font_family' => "Roboto'",
            'background_colors' => '#123456, red; }',
        ]);

        $array = $role->toArray();

        $this->assertNull($array['accent_color']);
        $this->assertNull($array['font_family']);
        $this->assertSame('#123456', $array['background_colors']);
    }
}
