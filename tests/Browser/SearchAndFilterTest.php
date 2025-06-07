<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use Carbon\Carbon;

class SearchAndFilterTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function testBasicSearch(): void
    {
        $this->browse(function (Browser $browser) {
            // Create test data
            $this->createTestData($browser);

            // Test basic search
            $browser->visit('/events')
                    ->type('search', 'Jazz')
                    ->pause(1000)
                    ->assertSee('Jazz Club Central')
                    ->assertSee('Jazz Quartet Pro')
                    ->assertDontSee('Rock Arena Downtown')
                    ->assertDontSee('Pop Star Collective');

            // Clear search
            $browser->clear('search')
                    ->pause(1000)
                    ->assertSee('Jazz Club Central')
                    ->assertSee('Rock Arena Downtown')
                    ->assertSee('Jazz Quartet Pro')
                    ->assertSee('Pop Star Collective');
        });
    }

    public function testCategoryFilter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Filter by category
            $browser->visit('/events')
                    ->select('category', '1') // Music category
                    ->pause(1000)
                    ->assertSee('Music events only')
                    ->click('.reset-filters')
                    ->pause(1000);

            // Filter by another category
            $browser->select('category', '3') // Sports category
                    ->pause(1000)
                    ->assertSee('Sports events only');
        });
    }

    public function testTypeFilter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Filter by type - venues only
            $browser->visit('/events')
                    ->select('type', 'venue')
                    ->pause(1000)
                    ->assertSee('Jazz Club Central')
                    ->assertSee('Rock Arena Downtown')
                    ->assertDontSee('Jazz Quartet Pro')
                    ->assertDontSee('Pop Star Collective');

            // Filter by type - talent only
            $browser->select('type', 'talent')
                    ->pause(1000)
                    ->assertDontSee('Jazz Club Central')
                    ->assertDontSee('Rock Arena Downtown')
                    ->assertSee('Jazz Quartet Pro')
                    ->assertSee('Pop Star Collective');
        });
    }

    public function testLocationFilter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Filter by city
            $browser->visit('/events')
                    ->type('location', 'New York')
                    ->pause(1000)
                    ->assertSee('Jazz Club Central') // NYC venue
                    ->assertDontSee('Rock Arena Downtown'); // LA venue
        });
    }

    public function testDateRangeFilter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            $today = Carbon::today()->format('Y-m-d');
            $tomorrow = Carbon::tomorrow()->format('Y-m-d');

            // Filter by date range
            $browser->visit('/events')
                    ->type('date_from', $today)
                    ->type('date_to', $tomorrow)
                    ->pause(1000)
                    ->assertSee('Events in date range');
        });
    }

    public function testAdvancedSearch(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Open advanced search
            $browser->visit('/events')
                    ->click('#advanced-search-toggle')
                    ->pause(500);

            // Test genre filter
            $browser->select('genre', 'Jazz')
                    ->pause(1000)
                    ->assertSee('Jazz Quartet Pro')
                    ->assertDontSee('Pop Star Collective');

            // Test capacity filter
            $browser->clear('genre')
                    ->type('min_capacity', '100')
                    ->type('max_capacity', '500')
                    ->pause(1000);

            // Test price range filter
            $browser->type('min_price', '20')
                    ->type('max_price', '100')
                    ->pause(1000);
        });
    }

    public function testSortingOptions(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Test sort by name
            $browser->visit('/events')
                    ->select('sort', 'name_asc')
                    ->pause(1000)
                    ->assertSeeIn('.results-container', 'Alphabetical order');

            // Test sort by date
            $browser->select('sort', 'date_desc')
                    ->pause(1000);

            // Test sort by popularity
            $browser->select('sort', 'popularity')
                    ->pause(1000);
        });
    }

    public function testSearchSuggestions(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Test search suggestions
            $browser->visit('/events')
                    ->type('search', 'Ja')
                    ->pause(1000);

            // Check if suggestions appear
            if ($browser->element('.search-suggestions')) {
                $browser->assertSee('Jazz Club Central')
                        ->assertSee('Jazz Quartet Pro');
            }
        });
    }

    public function testSearchHistory(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Perform searches to build history
            $browser->visit('/events')
                    ->type('search', 'Jazz')
                    ->press('Search')
                    ->pause(1000)
                    ->clear('search')
                    ->type('search', 'Rock')
                    ->press('Search')
                    ->pause(1000);

            // Check search history
            $browser->click('#search-history')
                    ->pause(500);

            if ($browser->element('.search-history-list')) {
                $browser->assertSee('Jazz')
                        ->assertSee('Rock');
            }
        });
    }

    public function testSavedSearches(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Create and save a search
            $browser->visit('/events')
                    ->type('search', 'Jazz')
                    ->select('category', '1')
                    ->select('type', 'venue')
                    ->click('#save-search')
                    ->pause(500)
                    ->type('search_name', 'Jazz Venues')
                    ->press('Save Search')
                    ->pause(1000)
                    ->assertSee('Search saved');

            // Load saved search
            $browser->click('#saved-searches')
                    ->pause(500)
                    ->clickLink('Jazz Venues')
                    ->pause(1000)
                    ->assertInputValue('search', 'Jazz')
                    ->assertSelected('category', '1')
                    ->assertSelected('type', 'venue');
        });
    }

    public function testPagination(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createManyTestItems($browser, 25); // Create many items

            $browser->visit('/events')
                    ->assertSee('Showing')
                    ->assertPresent('.pagination');

            // Test pagination
            if ($browser->element('.pagination .next')) {
                $browser->click('.pagination .next')
                        ->pause(1000)
                        ->assertSee('Page 2');
            }
        });
    }

    public function testEmptySearchResults(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/events')
                    ->type('search', 'NonexistentTerm12345')
                    ->pause(1000)
                    ->assertSee('No results found')
                    ->assertSee('Try adjusting your search criteria');
        });
    }

    public function testSearchWithSpecialCharacters(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue with special characters
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Café & Lounge!')
                    ->type('email', 'cafe@example.com')
                    ->type('address1', '123 Special St')
                    ->press('SAVE')
                    ->pause(2000);

            // Search with special characters
            $browser->visit('/events')
                    ->type('search', 'Café')
                    ->pause(1000)
                    ->assertSee('Café & Lounge!');

            // Search with symbols
            $browser->clear('search')
                    ->type('search', 'Lounge!')
                    ->pause(1000)
                    ->assertSee('Café & Lounge!');
        });
    }

    public function testFilterCombinations(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Combine multiple filters
            $browser->visit('/events')
                    ->type('search', 'Jazz')
                    ->select('type', 'venue')
                    ->select('category', '1')
                    ->pause(1000)
                    ->assertSee('Jazz Club Central')
                    ->assertDontSee('Jazz Quartet Pro'); // Should be filtered out as talent
        });
    }

    public function testMobileSearch(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createTestData($browser);

            // Resize to mobile
            $browser->resize(375, 667)
                    ->visit('/events')
                    ->click('#mobile-search-toggle')
                    ->pause(500)
                    ->type('search', 'Jazz')
                    ->pause(1000)
                    ->assertSee('Jazz Club Central');
        });
    }

    protected function createTestData(Browser $browser): void
    {
        $browser->loginAs($this->user);

        // Create test venues
        $browser->visit('/new/venue')
                ->type('name', 'Jazz Club Central')
                ->type('email', 'jazz@example.com')
                ->type('address1', '123 Jazz St')
                ->type('city', 'New York')
                ->type('state', 'NY')
                ->press('SAVE')
                ->pause(2000);

        $browser->visit('/new/venue')
                ->type('name', 'Rock Arena Downtown')
                ->type('email', 'rock@example.com')
                ->type('address1', '456 Rock Ave')
                ->type('city', 'Los Angeles')
                ->type('state', 'CA')
                ->press('SAVE')
                ->pause(2000);

        // Create test talent
        $browser->visit('/new/schedule')
                ->type('name', 'Jazz Quartet Pro')
                ->type('email', 'jazzquartet@example.com')
                ->press('SAVE')
                ->pause(2000);

        $browser->visit('/new/schedule')
                ->type('name', 'Pop Star Collective')
                ->type('email', 'popstar@example.com')
                ->press('SAVE')
                ->pause(2000);
    }

    protected function createManyTestItems(Browser $browser, int $count): void
    {
        $browser->loginAs($this->user);

        for ($i = 1; $i <= $count; $i++) {
            $browser->visit('/new/venue')
                    ->type('name', "Test Venue {$i}")
                    ->type('email', "venue{$i}@example.com")
                    ->type('address1', "123 Test St {$i}")
                    ->press('SAVE')
                    ->pause(1000);
        }
    }
} 