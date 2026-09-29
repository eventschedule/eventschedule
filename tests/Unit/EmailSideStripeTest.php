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
    // Any width of 2px or more anywhere in the value, so colour-first shorthand (`#xxx solid 4px`)
    // and wide stripes are caught too; 1px hairlines are allowed.
    private const PATTERN = '/border-(?:left|right|inline-start|inline-end|\{\{[^}]*\}\})(?:-width)?\s*:[^;"]*\b(?:[2-9]|\d{2,})px/';

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
        $this->assertMatchesRegularExpression(self::PATTERN, 'border-left: #48bb78 solid 4px;');
        $this->assertMatchesRegularExpression(self::PATTERN, 'border-left: 12px solid #eee;');
        $this->assertMatchesRegularExpression(self::PATTERN, 'border-left-width: 4px;');
        $this->assertDoesNotMatchRegularExpression(self::PATTERN, 'border-left: 1px solid #eee;');
        $this->assertDoesNotMatchRegularExpression(self::PATTERN, 'border: 1px solid #fcd34d;');
        $this->assertDoesNotMatchRegularExpression(self::PATTERN, 'border-top: 1px solid #ddd;');
    }
}
