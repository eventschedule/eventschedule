<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Copies every schedule font (storage/fonts.json) from Google Fonts into public/vendor/fonts, so
 * guest pages and the font previews load them from this install instead of from Google.
 *
 * Hot-linking fonts.googleapis.com sent every visitor's IP address to Google on every schedule
 * page, without asking: the transfer a German court held unlawful in 2022 (LG Muenchen I,
 * 3 O 17493/20), and a call to an outside server that a selfhosted install must never make
 * (CLAUDE.md: never use CDNs). A developer runs this once, and again after adding a font to
 * storage/fonts.json, and commits public/vendor/fonts. Installs never run it.
 *
 * Each font gets public/vendor/fonts/<value>/font.css, which is Google's own CSS for weights 400
 * and 700 with its woff2 files beside it and every url() made relative. Only the subsets the app
 * can use are kept: the ones fonts.json lists, plus latin-ext, which carries the accented letters
 * of Romanian, Estonian and most other Latin-script languages the app is translated into.
 */
class DownloadFonts extends Command
{
    protected $signature = 'fonts:download
        {--font= : Only this font, by its storage/fonts.json value (e.g. Playfair_Display)}
        {--force : Download again even where a copy exists}';

    protected $description = 'Copy the schedule fonts from Google Fonts into public/vendor/fonts';

    /** Google serves woff2 with unicode-range subsets only to a browser it recognises. */
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    public function handle(): int
    {
        $fonts = json_decode((string) file_get_contents(storage_path('fonts.json')), true) ?: [];
        $only = $this->option('font');
        $failed = 0;

        foreach ($fonts as $font) {
            $value = (string) ($font['value'] ?? '');

            if ($value === '' || ($only && $only !== $value)) {
                continue;
            }

            $dir = public_path('vendor/fonts/'.$value);

            if (! $this->option('force') && is_file($dir.'/font.css')) {
                continue;
            }

            try {
                $this->download($value, array_merge($font['subsets'] ?? ['latin'], ['latin-ext']), $dir);
                $this->line("  {$value}");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  {$value}: {$e->getMessage()}");
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<int, string>  $subsets
     */
    private function download(string $value, array $subsets, string $dir): void
    {
        $family = str_replace('_', '+', $value);
        $css = $this->fetchCss("https://fonts.googleapis.com/css2?family={$family}:wght@400;700&display=swap")
            // A family with only one weight refuses the 400;700 request.
            ?? $this->fetchCss("https://fonts.googleapis.com/css2?family={$family}&display=swap");

        if ($css === null) {
            throw new \RuntimeException('Google Fonts returned no stylesheet');
        }

        // One block per subset and weight, each led by a "/* subset */" comment.
        preg_match_all('#/\*\s*([a-z0-9-]+)\s*\*/\s*(@font-face\s*\{[^}]*\})#i', $css, $blocks, PREG_SET_ORDER);

        if (! $blocks) {
            throw new \RuntimeException('no @font-face blocks in the stylesheet');
        }

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $out = [];
        $files = [];

        foreach ($blocks as [, $subset, $block]) {
            if (! in_array($subset, $subsets, true)) {
                continue;
            }

            if (! preg_match('#url\((https://fonts\.gstatic\.com/[^)]+\.woff2)\)#', $block, $url)) {
                continue;
            }

            // A variable font serves 400 and 700 from one file; fetch it once.
            $name = basename(parse_url($url[1], PHP_URL_PATH));

            if (! isset($files[$name])) {
                $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])->timeout(30)->get($url[1]);

                if (! $response->successful()) {
                    throw new \RuntimeException("could not fetch {$url[1]}");
                }

                file_put_contents($dir.'/'.$name, $response->body());
                $files[$name] = true;
            }

            $out[] = "/* {$subset} */\n".str_replace($url[1], $name, $block);
        }

        if (! $out) {
            throw new \RuntimeException('none of the wanted subsets was offered');
        }

        // Anything left from an earlier download that this stylesheet no longer names.
        foreach (glob($dir.'/*.woff2') ?: [] as $stale) {
            if (! isset($files[basename($stale)])) {
                unlink($stale);
            }
        }

        file_put_contents($dir.'/font.css', implode("\n", $out)."\n");
    }

    private function fetchCss(string $url): ?string
    {
        $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])->timeout(30)->get($url);

        return $response->successful() ? $response->body() : null;
    }
}
