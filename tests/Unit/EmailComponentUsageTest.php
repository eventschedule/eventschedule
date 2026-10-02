<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Every outgoing email is built on <x-email.layout> (CLAUDE.md), so it gets the shared look, dark
 * mode, RTL, a preheader slot and the client hardening in one place. Before that layout there were
 * 60 hand-rolled shells in four styles, nine footer variants and four button sizes, and only one of
 * them handled dark mode.
 *
 * Out of scope on purpose: the plain-text twins, the partials (included inside a layout), the
 * newsletter (its owner-selectable designs are their own system), support_email (a one-line note to
 * our own inbox) and graphic_email (a <pre> meant to be copied and pasted).
 */
class EmailComponentUsageTest extends TestCase
{
    private const EXEMPT = ['emails/support_email.blade.php', 'emails/graphic_email.blade.php'];

    /** @return array<string, string> relative path => contents */
    private function htmlEmailViews(): array
    {
        $views = [];

        foreach (['emails', 'mail'] as $dir) {
            foreach (File::allFiles(resource_path('views/'.$dir)) as $file) {
                $path = $dir.'/'.str_replace('\\', '/', $file->getRelativePathname());

                if (! str_ends_with($path, '.blade.php')
                    || str_contains($path, 'newsletter')
                    || str_contains($path, '/partials/')
                    || preg_match('/[_-]text\.blade\.php$/', $path)
                    || in_array($path, self::EXEMPT, true)) {
                    continue;
                }

                $views[$path] = $file->getContents();
            }
        }

        return $views;
    }

    public function test_every_html_email_is_built_on_the_shared_layout(): void
    {
        $views = $this->htmlEmailViews();
        $this->assertGreaterThan(50, count($views), 'The sweep found too few views to be looking in the right place.');

        $offenders = array_keys(array_filter($views, fn ($contents) => ! str_contains($contents, '<x-email.layout')));

        $this->assertSame([], $offenders, 'Build every email on <x-email.layout> and the <x-email.*> components.');
    }

    public function test_no_email_view_hides_markup_in_an_html_comment(): void
    {
        // An HTML comment is sent to the recipient and Blade still runs inside it: a QR block
        // "hidden" with <!-- --> kept attaching its image to every ticket email for months.
        // Use {{-- --}}. Outlook's conditional comments are the one legitimate form.
        $offenders = [];

        foreach ($this->htmlEmailViews() as $path => $contents) {
            if (preg_match('/<!--(?!\[if |<!\[endif\]|>)/', $contents)) {
                $offenders[] = $path;
            }
        }

        $this->assertSame([], $offenders, 'Use a Blade comment {{-- --}}, not <!-- -->: an HTML comment is sent, and Blade runs inside it.');
    }
}
