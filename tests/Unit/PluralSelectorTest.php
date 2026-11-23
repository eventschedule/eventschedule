<?php

namespace Tests\Unit;

use App\Support\PluralSelector;
use PHPUnit\Framework\TestCase;

/**
 * Exact forms beside a language's own (App\Support\PluralSelector): Russian and Romanian count
 * by their last digits, which no list of ranges can say.
 */
class PluralSelectorTest extends TestCase
{
    public function test_russian_follows_the_last_digits_after_an_exact_zero(): void
    {
        $line = '{0} Мест не найдено.|Найдено :count место.|Найдено :count места.|Найдено :count мест.';
        $selector = new PluralSelector;

        $this->assertSame('Мест не найдено.', $selector->choose($line, 0, 'ru'));
        $this->assertSame('Найдено :count место.', $selector->choose($line, 1, 'ru'));
        $this->assertSame('Найдено :count место.', $selector->choose($line, 21, 'ru'));
        $this->assertSame('Найдено :count места.', $selector->choose($line, 22, 'ru'));
        $this->assertSame('Найдено :count мест.', $selector->choose($line, 11, 'ru'));
        $this->assertSame('Найдено :count мест.', $selector->choose($line, 25, 'ru'));
    }

    public function test_romanian_takes_de_only_where_the_last_two_digits_say_so(): void
    {
        $line = '{1} Ai un bilet|Ai :count bilete|Ai :count bilete|Ai :count de bilete';
        $selector = new PluralSelector;

        $this->assertSame('Ai un bilet', $selector->choose($line, 1, 'ro'));
        $this->assertSame('Ai :count bilete', $selector->choose($line, 19, 'ro'));
        $this->assertSame('Ai :count de bilete', $selector->choose($line, 20, 'ro'));
        $this->assertSame('Ai :count bilete', $selector->choose($line, 101, 'ro'));
    }

    public function test_strings_of_one_kind_read_as_laravel_reads_them(): void
    {
        $selector = new PluralSelector;

        $this->assertSame('one', $selector->choose('{1} one|[2,*] many', 1, 'en'));
        $this->assertSame('many', $selector->choose('{1} one|[2,*] many', 7, 'en'));
        $this->assertSame('apple', $selector->choose('apple|apples', 1, 'en'));
        $this->assertSame('apples', $selector->choose('apple|apples', 3, 'en'));
    }
}
