<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\GeminiUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * European Portuguese ('pt-PT') as a language of its own, beside 'pt' (Brazilian Portuguese).
 *
 * Its strings live in resources/lang/pt-PT as overlays: each file starts from the pt file and
 * replaces what Portugal says differently. So a key added to pt and forgotten here reaches pt-PT
 * as pt has it, and the Brazilian words below are what would give it away.
 */
class EuropeanPortugueseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Words a reader in Portugal would take as Brazilian, as they would appear in a UI string.
     * Each is a whole word, so "ingressar" or "arquivar" do not match.
     */
    private const BRAZILIAN = [
        'ingresso' => '/\bingressos?\b/iu',
        'você' => '/\bvocês?\b/iu',
        'usuário' => '/\busuári[oa]s?\b/iu',
        'celular' => '/\bcelular(es)?\b/iu',
        'tela' => '/\btelas?\b/iu',
        'equipe' => '/\bequipes?\b/iu',
        'senha' => '/\bsenhas?\b/iu',
        'cadastro' => '/\bcadastr/iu',
        'compartilhar' => '/\bcompartilh/iu',
        'aplicativo' => '/\baplicativos?\b/iu',
        'salvar' => '/\bsalvar\b/iu',
        'registro' => '/\bregistr(o|os|ar|ado|ada)\b/iu',
        'contato' => '/\bcontat(o|os|ar)\b/iu',
        'gerenciar' => '/\bgerenc/iu',
        'Google Agenda' => '/\bGoogle Agenda\b/u',
        'está + gerúndio' => '/\b(está|estão|estamos|estou) (sendo|\w+ndo)\b/iu',
        'em um' => '/\bem (um|uma|uns|umas)\b/iu',
        'tem certeza' => '/\b(tem|ter|tenha) certeza\b/iu',
        'Carregando' => '/\b(Carregando|Salvando|Enviando|Processando)\b/u',
    ];

    private const FILES = ['messages', 'marketing', 'accessibility'];

    public function test_pt_pt_is_a_supported_language_next_to_pt(): void
    {
        $codes = array_keys(config('app.supported_languages'));

        $this->assertContains('pt-PT', $codes);
        $this->assertContains('pt', $codes);
        $this->assertTrue(is_valid_language_code('pt-PT'));
        // Exact, because what it accepts is used as it stands (a lang directory, a column); a
        // loose spelling is corrected by normalize_language_code() at the door instead.
        $this->assertFalse(is_valid_language_code('pt_PT'), 'one spelling, the BCP 47 one');
        $this->assertFalse(is_valid_language_code('pt-pt'));

        // Listed right after pt, which is where a Portuguese speaker looks for it.
        $this->assertSame(array_search('pt', $codes) + 1, array_search('pt-PT', $codes));
    }

    public function test_every_pt_file_has_a_pt_pt_counterpart_with_the_same_keys(): void
    {
        foreach (glob(lang_path('pt/*.php')) as $ptFile) {
            $name = basename($ptFile);
            $path = lang_path('pt-PT/'.$name);

            $this->assertFileExists($path);

            $pt = require $ptFile;
            $ptPt = require $path;

            $this->assertIsArray($ptPt);
            $this->assertSame([], array_keys(array_diff_key($pt, $ptPt)), "pt-PT/{$name} misses keys");
            $this->assertSame([], array_keys(array_diff_key($ptPt, $pt)), "pt-PT/{$name} has keys pt has not");
        }
    }

    public function test_the_translator_serves_european_portuguese_and_leaves_pt_alone(): void
    {
        app()->setLocale('pt-PT');
        $this->assertSame('Bilhetes', __('messages.tickets'));
        $this->assertSame('Guardar', __('messages.save'));
        $this->assertSame('Palavra-passe', __('messages.password'));
        $this->assertSame('Tem a certeza?', __('messages.are_you_sure'));
        $this->assertSame('A carregar', __('messages.loading'));
        $this->assertSame('Português (Portugal)', __('messages.portuguese_portugal'));

        app()->setLocale('pt');
        $this->assertSame('Ingressos', __('messages.tickets'));
        $this->assertSame('Salvar', __('messages.save'));
        $this->assertSame('Português (Brasil)', __('messages.portuguese'));
    }

    public function test_no_pt_pt_string_carries_a_brazilian_word(): void
    {
        $found = [];

        foreach (self::FILES as $file) {
            foreach (require lang_path("pt-PT/{$file}.php") as $key => $value) {
                foreach (self::BRAZILIAN as $word => $pattern) {
                    if (preg_match($pattern, (string) $value)) {
                        $found[] = "{$file}.{$key} ({$word}): {$value}";
                    }
                }
            }
        }

        $this->assertSame([], $found, "A pt-PT string reads as Brazilian Portuguese. Add the European form to resources/lang/pt-PT:\n".implode("\n", $found));
    }

    public function test_the_overlay_only_repeats_what_differs(): void
    {
        foreach (self::FILES as $file) {
            $source = file_get_contents(lang_path("pt-PT/{$file}.php"));
            $this->assertStringContainsString("require __DIR__.'/../pt/{$file}.php'", $source);
        }

        // A value equal to pt's is dead weight that hides the next change to pt.
        $pt = require lang_path('pt/messages.php');
        preg_match_all("/^    '([a-z0-9_.]+)' =>/m", file_get_contents(lang_path('pt-PT/messages.php')), $m);
        $ptPt = require lang_path('pt-PT/messages.php');
        $same = array_filter($m[1], fn ($key) => $ptPt[$key] === $pt[$key]);

        $this->assertSame([], array_values($same));
    }

    public function test_a_guest_page_renders_in_european_portuguese_and_keeps_the_language(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['language_code' => 'en', 'translation_language_code' => 'pt-PT']);
        $url = route('role.view_guest', ['subdomain' => $role->subdomain]);

        $html = $this->get($url.'?lang=pt-PT')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="pt-PT"', $html);
        $this->assertStringContainsString("languageCode: 'pt-PT'", $html, 'the calendar asks for its months with &lang=pt-PT');
        $this->assertStringContainsString(e(__('messages.share', [], 'pt-PT')), $html);
        $this->assertStringNotContainsString('Compartilhar', $html);

        // The switcher: "PT" for the European side, named in full, and a way back to English.
        $this->assertStringContainsString('aria-label="Português (Portugal)">PT</span>', $html);
        $this->assertStringContainsString('data-lang-switch="en"', $html);

        // The next page of the visit, with no ?lang=, stays in European Portuguese.
        $this->get($url)->assertOk()->assertSee('<html lang="pt-PT"', false);
    }

    public function test_a_hand_typed_spelling_of_the_code_is_read_as_pt_pt(): void
    {
        foreach (['pt-PT', 'pt-pt', 'pt_PT', 'pt_pt', 'PT-PT', ' pt-pt '] as $typed) {
            $this->assertSame('pt-PT', normalize_language_code($typed), $typed);
        }
        $this->assertSame('pt', normalize_language_code('PT'));
        $this->assertSame('en', normalize_language_code('en'));
        $this->assertNull(normalize_language_code('pt-BR'));
        $this->assertNull(normalize_language_code('xx'));
        $this->assertNull(normalize_language_code(['pt-PT']));

        $role = $this->createRole($this->createOwner(), 'venue', ['language_code' => 'en', 'translation_language_code' => 'pt-PT']);
        $url = route('role.view_guest', ['subdomain' => $role->subdomain]);

        foreach (['pt-pt', 'pt_PT'] as $typed) {
            session()->forget('translate');

            $html = $this->get($url.'?lang='.$typed)->assertOk()->getContent();

            $this->assertStringContainsString('<html lang="pt-PT"', $html, $typed);
            $this->assertStringContainsString("languageCode: 'pt-PT'", $html, $typed);
            $this->assertStringContainsString('aria-label="Português (Portugal)">PT</span>', $html, $typed.': the switcher marks it as the language shown');
        }

        // An embed too, where nothing else carries the language.
        $this->get($url.'?embed=true&lang=pt_pt')->assertOk()->assertSee('<html lang="pt-PT"', false);
    }

    public function test_an_embed_renders_in_european_portuguese(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['language_code' => 'en', 'translation_language_code' => 'pt-PT']);

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]).'?embed=true&lang=pt-PT')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<html lang="pt-PT"', $html);
        $this->assertStringContainsString("languageCode: 'pt-PT'", $html);
    }

    public function test_a_schedule_written_in_european_portuguese_speaks_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['language_code' => 'pt-PT', 'translation_language_code' => 'pt-PT']);

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="pt-PT"', $html);
        $this->assertStringContainsString('content="pt_PT"', $html, 'og:locale');
        $this->assertStringNotContainsString('id="gp-language-switcher"', $html, 'one language, no switcher');
    }

    public function test_the_ai_prompt_names_european_portuguese_and_leaves_pt_unchanged(): void
    {
        $european = GeminiUtils::buildTranslatePrompt('Weekly jazz night', 'en', 'pt-PT', [], ['kind' => 'name']);

        $this->assertStringContainsString('from English into European Portuguese (Portugal).', $european);
        $this->assertStringContainsString('not Brazilian Portuguese', $european);
        $this->assertStringContainsString('"bilhete" (not "ingresso")', $european);

        $brazilian = GeminiUtils::buildTranslatePrompt('Weekly jazz night', 'en', 'pt', [], ['kind' => 'name']);

        $this->assertStringContainsString('from English into Portuguese. The text is a NAME', $brazilian);
        $this->assertStringNotContainsString('European', $brazilian);
        $this->assertStringNotContainsString('Brazilian', $brazilian);

        // A pt-PT source is named as such too.
        $this->assertStringContainsString('from European Portuguese (Portugal) into English.', GeminiUtils::buildTranslatePrompt('Noite de jazz', 'pt-PT', 'en'));

        $this->assertSame('', GeminiUtils::languageVariantInstruction('pt'));
        $this->assertSame('', GeminiUtils::languageVariantInstruction('en'));
    }

    public function test_a_schedule_can_be_saved_with_european_portuguese_as_either_language(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'en', 'translation_language_code' => 'en']);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'language_code' => 'en',
            'translation_enabled' => '1',
            'translation_language_code' => 'pt-PT',
        ])->assertSessionHasNoErrors();

        $this->assertSame('pt-PT', $role->fresh()->translation_language_code);
        $this->assertTrue($role->fresh()->offersTranslation());

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'language_code' => 'pt-PT',
            'translation_enabled' => '1',
            'translation_language_code' => 'en',
        ])->assertSessionHasNoErrors();

        $fresh = Role::find($role->id);
        $this->assertSame('pt-PT', $fresh->language_code);
        $this->assertSame('en', $fresh->translation_language_code);
    }

    public function test_the_language_pickers_name_both_portugueses(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSeeInOrder(['value="pt"', 'Portuguese (Brazil)', 'value="pt-PT"', 'Portuguese (Portugal)'], false);
    }

    public function test_the_switcher_badge_spells_the_region_only_beside_the_other_portuguese(): void
    {
        $this->assertSame('PT', language_badge('pt-PT', 'en'));
        $this->assertSame('PT', language_badge('pt', 'en'));
        $this->assertSame('EN', language_badge('en', 'pt-PT'));
        $this->assertSame('PT-PT', language_badge('pt-PT', 'pt'));
        $this->assertSame('PT-BR', language_badge('pt', 'pt-PT'), 'PT alone reads as Portugal');
    }
}
