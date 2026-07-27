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
}
