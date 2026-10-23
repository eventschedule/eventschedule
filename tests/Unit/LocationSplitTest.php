<?php

namespace Tests\Unit;

use App\Utils\IcsImportUtils;
use Tests\TestCase;

/**
 * A location as feeds write it, in its parts. Regional sources often name a postal code and a
 * town; read whole, the town went into the street line and nothing could find the place by it.
 * Anything that is not clearly a code and a town is read exactly as before.
 */
class LocationSplitTest extends TestCase
{
    private function parts(string $location): array
    {
        return array_intersect_key(IcsImportUtils::location($location), array_flip(['venue_name', 'event_address', 'event_postal_code', 'event_city']));
    }

    private function expect(string $name, string $address, string $postal = '', string $city = ''): array
    {
        return ['venue_name' => $name, 'event_address' => $address, 'event_postal_code' => $postal, 'event_city' => $city];
    }

    public function test_a_postal_code_and_town_are_their_own_parts(): void
    {
        $this->assertSame($this->expect('Mill Hall', 'Brook Lane 7', '1234', 'Lindenau'), $this->parts('Mill Hall, Brook Lane 7, 1234 Lindenau'));
        $this->assertSame($this->expect('Mill Hall', '', '1234', 'Lindenau'), $this->parts('Mill Hall, 1234 Lindenau'));
        $this->assertSame($this->expect('', 'Brook Lane 7', '12345', 'Neustadt am See'), $this->parts('Brook Lane 7, 12345 Neustadt am See'));
        $this->assertSame($this->expect('Mill Hall', 'Brook Lane 7, Austria', '1234', 'Lindenau'), $this->parts('Mill Hall, Brook Lane 7, 1234 Lindenau, Austria'));
        $this->assertSame($this->expect('Mill Hall', 'Brook Lane 7', '1234', 'Lindenau-Ost'), $this->parts('Mill Hall, Brook Lane 7, 1234 Lindenau-Ost'));
    }

    /** Town first, then the place: the venue's name is no longer lost behind the number. */
    public function test_a_town_written_first_and_set_off_by_a_bar(): void
    {
        $this->assertSame($this->expect('School', 'Main Square 8', '1234', 'Lindenau'), $this->parts('1234 Lindenau | School, Main Square 8'));
        $this->assertSame($this->expect('Market', '', '1234', 'Lindenau'), $this->parts('1234 Lindenau | Market'));
        // A street on its own is no venue's name.
        $this->assertSame($this->expect('', 'Main Street', '1234', 'Lindenau'), $this->parts('Main Street, 1234 Lindenau'));
    }

    public function test_anything_else_reads_as_it_always_did(): void
    {
        $this->assertSame($this->expect('Mill Hall', 'Lindenau'), $this->parts('Mill Hall, Lindenau'));
        $this->assertSame($this->expect('Mill Hall', ''), $this->parts('Mill Hall'));
        $this->assertSame($this->expect('', '1234 Brook Lane 131a'), $this->parts('1234 Brook Lane 131a'));
        $this->assertSame($this->expect('', 'Brook Lane; 1234 Lindenau - Old Castle'), $this->parts('Brook Lane / 1234 Lindenau - Old Castle'));
        $this->assertSame($this->expect('The Blue Room', '1 Main St, Springfield, IL 62701, USA'), $this->parts('The Blue Room, 1 Main St, Springfield, IL 62701, USA'));
        // A five-digit street number is not a postal code, nor is one with numbers after it.
        $this->assertSame($this->expect('The Blue Room', '12345 Main Street, Springfield'), $this->parts('The Blue Room, 12345 Main Street, Springfield'));
        $this->assertSame($this->expect('The Blue Room', '12345 Elm Ave'), $this->parts('The Blue Room, 12345 Elm Ave'));
        $this->assertSame($this->expect('The Blue Room', '12345 Springfield, CA 90210'), $this->parts('The Blue Room, 12345 Springfield, CA 90210'));
    }

    public function test_the_location_as_written_is_kept(): void
    {
        $this->assertSame('Brook Lane 7, 1234 Lindenau', IcsImportUtils::location('  Brook Lane 7,   1234 Lindenau ')['event_location']);
        $this->assertSame('', IcsImportUtils::location('')['event_location']);
    }
}
