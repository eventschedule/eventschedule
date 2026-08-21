<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ContentDirTest extends TestCase
{
    public function test_hebrew_content_with_an_embedded_latin_phrase_reads_rtl(): void
    {
        $text = 'מדובר בהרכב שמביא לעולם צלילים חדשים בהשפעת Cosmic Girl ו-Virtual Insanity, '
            .'לצד הלהיטים הבלתי נשכחים של שנות השבעים.';

        $this->assertSame('rtl', detect_content_dir($text));
    }

    public function test_english_content_with_a_hebrew_word_reads_ltr(): void
    {
        $this->assertSame('ltr', detect_content_dir('An evening of live music at the מרכז venue.'));
    }

    public function test_leading_latin_does_not_flip_hebrew_content(): void
    {
        // First-strong detection would call this LTR; the majority rule keeps it RTL.
        $this->assertSame('rtl', detect_content_dir('DJ Mike presents: מסיבת ריקודים בתל אביב עם אורחים מיוחדים'));
    }

    public function test_html_tag_names_are_not_counted(): void
    {
        $html = '<p><strong>מופע</strong> של <em>קאובוי</em> מחלל</p>';

        $this->assertSame('rtl', detect_content_dir($html));
    }

    public function test_urls_are_not_counted(): void
    {
        $html = '<p>הופעה <a href="https://example.com/tickets">כרטיסים</a> https://tickets.example.com/very/long/path</p>';

        $this->assertSame('rtl', detect_content_dir($html));
    }

    public function test_returns_null_when_there_is_nothing_to_go_on(): void
    {
        $this->assertNull(detect_content_dir(null));
        $this->assertNull(detect_content_dir(''));
        $this->assertNull(detect_content_dir('   '));
        $this->assertNull(detect_content_dir('12:00 - 18:00 (+972)'));
    }

    public function test_content_dir_falls_back_to_the_schedule_language(): void
    {
        $hebrewSchedule = new class
        {
            public function isContentRtl(): bool
            {
                return true;
            }
        };

        // No content to go on: the schedule's language decides.
        $this->assertSame('rtl', content_dir($hebrewSchedule));
        $this->assertSame('rtl', content_dir($hebrewSchedule, false, ''));
        $this->assertSame('ltr', content_dir(null));

        // With content, the content decides.
        $this->assertSame('ltr', content_dir($hebrewSchedule, false, 'An evening of live music'));
        $this->assertSame('rtl', content_dir(null, false, 'ערב של מוזיקה חיה'));
    }

    public function test_an_exact_tie_falls_back_to_the_first_strong_character(): void
    {
        // Four Hebrew letters against four Latin ones. Matches detectDir() in
        // resources/js/editor-helpers.js, which has always broken ties this way.
        $this->assertSame('rtl', detect_content_dir('שלום Hell'));
        $this->assertSame('ltr', detect_content_dir('Hell שלום'));
    }

    public function test_has_rtl_text_is_presence_not_majority(): void
    {
        $this->assertTrue(has_rtl_text('להקת LadyD'));
        $this->assertTrue(has_rtl_text('אבי אמתי & The Love Machine'));
        $this->assertFalse(has_rtl_text('Jazz Night'));
        $this->assertFalse(has_rtl_text(null));
        $this->assertFalse(has_rtl_text(''));
    }

    public function test_arabic_indic_digits_are_not_strong_rtl(): void
    {
        // U+0660..U+0669 are Script=Arabic but bidi class AN, not strong R. A price written
        // in them must not make an English name read RTL.
        $this->assertFalse(has_rtl_text("Ticket \u{0660}\u{0661}\u{0662}"));
        $this->assertTrue(has_rtl_text("\u{0645}\u{0631}\u{062D}\u{0628}\u{0627}"));
    }

    public function test_a_hebrew_title_with_an_embedded_latin_name_reads_rtl(): void
    {
        // These three rendered backwards on a Hebrew schedule: each spends more letters on
        // the Latin band name than on the Hebrew words around it, so the majority rule in
        // detect_content_dir() called them LTR and the browser moved the trailing colon and
        // the ampersand to the wrong end of the line.
        $this->assertSame('rtl', content_dir_for_language('להקת LadyD', 'he'));
        $this->assertSame('rtl', content_dir_for_language('להקת Rock Bandits חוזרת', 'he'));
        $this->assertSame('rtl', content_dir_for_language('אבי אמתי & The Love Machine', 'he'));

        // Pure Hebrew was never broken; it must stay put.
        $this->assertSame('rtl', content_dir_for_language('ערב 3 הופעות מקור', 'he'));
    }

    public function test_a_latin_only_name_in_a_hebrew_schedule_reads_ltr(): void
    {
        // No RTL letters at all, so there is nothing for the schedule's language to anchor.
        $this->assertSame('ltr', content_dir_for_language('Jazz Night', 'he'));
    }

    public function test_content_dir_for_language_falls_back_to_the_language_when_blank(): void
    {
        $this->assertSame('rtl', content_dir_for_language(null, 'he'));
        $this->assertSame('rtl', content_dir_for_language('', 'he'));
        $this->assertSame('ltr', content_dir_for_language('', 'en'));
    }

    public function test_a_hebrew_event_on_an_english_page_still_reads_rtl(): void
    {
        // The curator case content_dir_for_language() was written for: an aggregated event
        // whose own language differs from the viewing schedule's. A known-LTR language does
        // not get the benefit of the doubt, so the majority rule still overrides it.
        $this->assertSame('rtl', content_dir_for_language('מסיבת ריקודים בתל אביב', 'en'));
        $this->assertSame('rtl', content_dir_for_language('DJ Mike presents: מסיבת ריקודים בתל אביב עם אורחים מיוחדים', 'en'));
        $this->assertSame('ltr', content_dir_for_language('An evening of live music at the מרכז venue.', 'en'));
    }

    public function test_content_dir_keeps_a_hebrew_schedule_rtl_for_latin_heavy_content(): void
    {
        $hebrewSchedule = new class
        {
            public function isContentRtl(): bool
            {
                return true;
            }
        };

        $this->assertSame('rtl', content_dir($hebrewSchedule, false, 'להקת LadyD'));
        // Still LTR when there is no Hebrew in it at all.
        $this->assertSame('ltr', content_dir($hebrewSchedule, false, 'An evening of live music'));
    }

    public function test_malformed_utf8_degrades_instead_of_throwing(): void
    {
        // strip_dir_noise() declares `: string`, but a /u pattern returns null on bad UTF-8.
        // One stray byte from an iCal or scraper import reaches this on every event name, so a
        // TypeError here is a 500 on the whole schedule page.
        $bad = "abc \xC3\x28 שלום";

        $this->assertNull(detect_content_dir($bad));
        $this->assertFalse(has_rtl_text($bad));
        $this->assertSame('rtl', content_dir_for_language($bad, 'he'));
        $this->assertSame('ltr', content_dir_for_language($bad, 'en'));
    }

    public function test_text_with_no_strong_characters_falls_back_to_the_language(): void
    {
        // A blank field keeps the schedule's direction, so a field holding "2026" must too -
        // presence of RTL letters is the wrong question when there is no strong script at all.
        foreach (['2026', '24/7', '12:00 - 18:00', '🎉', ''] as $neutral) {
            $this->assertSame('rtl', content_dir_for_language($neutral, 'he'), $neutral);
            $this->assertSame('ltr', content_dir_for_language($neutral, 'en'), $neutral);
        }

        // Latin present but no RTL: the text does have something to say.
        $this->assertSame('ltr', content_dir_for_language('Jazz Night', 'he'));
    }

    public function test_has_rtl_text_ignores_marks_digits_and_punctuation(): void
    {
        // All of these live inside the Hebrew/Arabic script blocks but are not strong R:
        // niqqud and harakat are NSM, Arabic-Indic digits are AN, and the comma and percent
        // sign are punctuation. One of them alone must not flip a Latin string.
        $this->assertFalse(has_rtl_text("cafe\u{05B4}"));
        $this->assertFalse(has_rtl_text("cafe\u{064B}"));
        $this->assertFalse(has_rtl_text("Hello\u{060C} world"));
        $this->assertFalse(has_rtl_text("50\u{066A} off"));
        $this->assertFalse(has_rtl_text("Rock\u{05F3}n\u{05F3}Roll"));

        // Real letters still count, tatweel (bidi class AL) included.
        $this->assertTrue(has_rtl_text("abc\u{05D0}"));
        $this->assertTrue(has_rtl_text("abc\u{0627}"));
        $this->assertTrue(has_rtl_text("abc\u{0640}"));
    }
}
