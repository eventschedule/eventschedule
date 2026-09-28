<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * No colored side-stripe on email cards or callouts (CLAUDE.md). A 4px border on one side of a box
 * reads as AI-generated; every email that had one was swept to a plain card, or to a tinted
 * background with a 1px border for a warning or success state.
 *
 * Catches the literal property and the RTL-flipped form, `border-{{ $isRtl ? 'right' : 'left' }}`,
 * which a grep for "border-left" misses. The newsletter templates are the exception: their quote
 * bar and the bold/compact designs are owner-selectable looks, not our chrome.
 */
class EmailSideStripeTest extends TestCase
{
    private const PATTERN = '/border-(?:left|right|inline-start|inline-end|\{\{[^}]*\}\})\s*:\s*[2-9]px\s+solid/';

    public function test_no_email_view_uses_a_side_stripe(): void
    {
        $offenders = [];

        foreach (['emails', 'mail'] as $dir) {
            foreach (File::allFiles(resource_path('views/'.$dir)) as $file) {
                if (str_contains($file->getRelativePathname(), 'newsletter')) {
                    continue;
                }

                foreach (preg_split('/\R/', $file->getContents()) as $i => $line) {
                    if (preg_match(self::PATTERN, $line)) {
                        $offenders[] = $dir.'/'.$file->getRelativePathname().':'.($i + 1);
                    }
                }
            }
        }

        $this->assertSame([], $offenders, 'Side-stripe accent found. Use a plain card, or a tinted background with a 1px border.');
    }

    /** The pattern itself, so a typo in it cannot turn the test above into a no-op. */
    public function test_the_pattern_matches_both_forms(): void
    {
        $this->assertMatchesRegularExpression(self::PATTERN, 'margin: 0; border-left: 4px solid #4E81FA;');
        $this->assertMatchesRegularExpression(self::PATTERN, "border-{{ \$isRtl ? 'right' : 'left' }}: 4px solid #9ca3af;");
        $this->assertDoesNotMatchRegularExpression(self::PATTERN, 'border: 1px solid #fcd34d;');
        $this->assertDoesNotMatchRegularExpression(self::PATTERN, 'border-top: 1px solid #ddd;');
    }
}
