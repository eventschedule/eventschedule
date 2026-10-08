<?php

namespace Tests\Unit;

use App\Utils\PosterWall;
use PHPUnit\Framework\TestCase;

/**
 * The rows of /browse's wall of posters.
 *
 * PosterWall only decides where each row ends; the page draws the rows with flexbox (a poster is
 * as wide as its shape, over a box of that shape). So what is held here is what the stylesheet
 * leans on without being able to check: every tile is in exactly one row, a label never closes a
 * row, a phone gets rows it can read, and only a last row too thin to stretch is left short.
 */
class PosterWallTest extends TestCase
{
    private const SQUARE = ['ratio' => 1.0];

    private const POSTER = ['ratio' => 0.75];

    private const STORY = ['ratio' => 0.56];

    private const BANNER = ['ratio' => 2.0];

    private const LABEL = ['label' => true];

    private const BLANK = ['ratio' => 0.75, 'stretch' => [0.62, 1.1]];

    public function test_every_tile_is_in_exactly_one_row(): void
    {
        $tiles = $this->mixedWall(40);

        foreach (array_keys(PosterWall::PROFILES) as $profile) {
            $ends = PosterWall::rows($tiles, $profile)['ends'];

            $this->assertSame($ends, array_values(array_unique($ends)), "$profile: a row end repeats");
            $sorted = $ends;
            sort($sorted);
            $this->assertSame($sorted, $ends, "$profile: the rows are out of order");
            $this->assertSame(count($tiles) - 1, end($ends), "$profile: the last row must end on the last tile");
        }
    }

    public function test_a_row_never_ends_on_a_label_away_from_the_phone(): void
    {
        // A label closing a row would stand at the far end of the row ABOVE the posters it names.
        $tiles = $this->mixedWall(60);

        foreach (['t', 'l', 'd', 'x'] as $profile) {
            foreach (PosterWall::rows($tiles, $profile)['ends'] as $end) {
                $this->assertArrayNotHasKey('label', $tiles[$end], "$profile: row ends on the label at $end");
            }
        }
    }

    public function test_a_label_is_a_row_of_its_own_on_a_phone(): void
    {
        $tiles = [self::LABEL, self::SQUARE, self::SQUARE, self::LABEL, self::BANNER];

        // The label, the pair, the label, the banner.
        $this->assertSame([0, 2, 3, 4], PosterWall::rows($tiles, 'm')['ends']);
    }

    public function test_two_squares_share_a_phone_row_and_a_banner_takes_one_to_itself(): void
    {
        // Until squares could pair, 24 flyers of unknown shape were 24 full-width rows.
        $this->assertSame([1], PosterWall::rows([self::SQUARE, self::SQUARE], 'm')['ends']);
        $this->assertSame([0, 1], PosterWall::rows([self::SQUARE, self::BANNER], 'm')['ends']);
        $this->assertSame([1], PosterWall::rows([self::STORY, self::STORY], 'm')['ends']);
    }

    public function test_a_phone_row_never_holds_three(): void
    {
        // Three stories "fit" by the sums and are each a third of a phone wide: unreadable. And
        // the odd one out ends the wall, it does not open it at full width.
        $rows = PosterWall::rows(array_fill(0, 9, self::STORY), 'm')['ends'];

        $this->assertSame([1, 3, 5, 7, 8], $rows);
    }

    public function test_a_row_of_banners_holds_fewer_than_a_row_of_squares_but_not_half(): void
    {
        // The measure sits between equal height (a banner twice the size of a square) and equal
        // area (rows that run from squat to towering). Five squares to a desktop row, so pure
        // equal height would take two and a half banners and pure equal area three and a half.
        $squares = PosterWall::rows(array_fill(0, 20, self::SQUARE), 'd')['ends'];
        $banners = PosterWall::rows(array_fill(0, 20, self::BANNER), 'd')['ends'];

        $this->assertSame(4, $squares[0], 'Five squares to a row');
        $this->assertSame(2, $banners[0], 'Three banners to a row');
        $this->assertSame([17, 19], array_slice($banners, -2), 'The two left over end the wall, short');
    }

    public function test_only_a_last_row_too_thin_to_stretch_is_left_short(): void
    {
        // Ten squares at five to a row: both rows full, nothing left over.
        $this->assertSame(0.0, PosterWall::rows(array_fill(0, 10, self::SQUARE), 'd')['pad']);

        // One poster on a desktop cannot be stretched across the window.
        $alone = PosterWall::rows([self::POSTER], 'd');
        $this->assertGreaterThan(2.0, $alone['pad']);
        $this->assertLessThan(0.3, $alone['share'][0], 'It keeps the size it would have in a full row');
    }

    public function test_the_blank_poster_takes_the_shape_that_brings_its_row_flush(): void
    {
        // Four squares and the blank: the blank is as wide as the row has left, inside its range.
        $rows = PosterWall::rows([self::SQUARE, self::SQUARE, self::SQUARE, self::SQUARE, self::BLANK], 'd');

        $this->assertSame([4], $rows['ends']);
        $this->assertSame(0.0, $rows['pad']);
        $this->assertGreaterThanOrEqual(0.62, $rows['ratio'][4]);
        $this->assertLessThanOrEqual(1.1, $rows['ratio'][4]);
        $this->assertEqualsWithDelta(1.0, array_sum($rows['share']), 0.001, 'The shares of a full row add up to it');

        // It never leaves its range to do so.
        $wide = PosterWall::rows([self::SQUARE, self::BLANK], 'x');
        $this->assertSame(1.1, $wide['ratio'][1]);
    }

    public function test_the_blank_poster_alone_on_a_phone_takes_the_whole_row(): void
    {
        // Left at its own shape it stood at half width beside nothing.
        $rows = PosterWall::rows([self::SQUARE, self::SQUARE, self::BLANK], 'm');

        $this->assertSame([1, 2], $rows['ends']);
        $this->assertSame(0.0, $rows['pad']);
        $this->assertGreaterThan(1.5, $rows['ratio'][2]);
    }

    public function test_a_label_left_as_the_last_tile_does_not_break_the_wall(): void
    {
        // BrowseWall always ends on the blank poster, so this cannot be reached through it. It
        // used to make every profile fall back to one tile per row.
        $this->assertSame([3], PosterWall::rows([self::SQUARE, self::SQUARE, self::SQUARE, self::LABEL], 'd')['ends']);
    }

    public function test_nothing_and_a_great_deal_both_come_back(): void
    {
        $this->assertSame(['ends' => [], 'pad' => 0.0, 'share' => [], 'ratio' => []], PosterWall::rows([], 'd'));

        $layout = PosterWall::layout($this->mixedWall(97));

        $this->assertSame(array_keys(PosterWall::PROFILES), array_keys($layout));

        foreach ($layout as $profile => $rows) {
            $this->assertSame(96, end($rows['ends']), "$profile lost a tile");
        }
    }

    public function test_a_shape_nobody_recorded_or_nobody_could_use_is_brought_into_range(): void
    {
        $this->assertSame(1.0, PosterWall::clampRatio(null));
        $this->assertSame(1.0, PosterWall::clampRatio(0.0));
        $this->assertSame(0.5, PosterWall::clampRatio(0.2));
        $this->assertSame(2.4, PosterWall::clampRatio(9.0));
        $this->assertSame(0.563, PosterWall::clampRatio(1080 / 1920));
    }

    /** A wall like the live one: labels now and then, every shape, the blank poster last. */
    private function mixedWall(int $count): array
    {
        $shapes = [self::STORY, self::POSTER, self::SQUARE, ['ratio' => 1.78], self::BANNER];
        $tiles = [];

        for ($i = 0; $i < $count - 1; $i++) {
            $tiles[] = $i % 11 === 0 ? self::LABEL : $shapes[$i % 5];
        }

        $tiles[] = self::BLANK;

        return $tiles;
    }
}
