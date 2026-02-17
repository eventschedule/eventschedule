<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class AvailabilityTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * Test availability page loads, dates can be toggled, and changes persist.
     */
    public function test_availability(): void
    {
        $this->browse(function (Browser $browser) {
            $userName = 'Test User';
            $userEmail = 'test@gmail.com';
            $userPassword = 'password';

            $this->setupTestAccount($browser, $userName, $userEmail, $userPassword);
            $this->createTestTalent($browser);

            $targetDate = date('Y-m-15');

            // Visit the availability page
            $browser->visit('/talent/availability')
                ->waitFor('#saveButton', 10);

            // Verify page loaded: calendar days and save button are visible
            $browser->assertVisible('.day-element')
                ->assertVisible('#saveButton');

            // Save button should start disabled
            $isDisabled = $browser->script("return document.getElementById('saveButton').disabled;");
            $this->assertTrue($isDisabled[0], 'Save button should be disabled initially');

            // Click the 15th to mark it unavailable
            $browser->click('.day-element[data-date="'.$targetDate.'"]')
                ->pause(500);

            // Verify .day-x overlay appeared on the clicked date
            $hasDayX = $browser->script(
                "return document.querySelector('.day-element[data-date=\"".$targetDate."\"] .day-x') !== null;"
            );
            $this->assertTrue($hasDayX[0], 'Day-x overlay should appear after clicking');

            // Save button should now be enabled
            $isDisabled = $browser->script("return document.getElementById('saveButton').disabled;");
            $this->assertFalse($isDisabled[0], 'Save button should be enabled after toggling a date');

            // Save
            $browser->click('#saveButton')
                ->waitForText('Successfully updated availability', 5);

            // Verify database has the date
            $role = Role::where('subdomain', 'talent')->first();
            $roleUser = RoleUser::where('role_id', $role->id)->first();
            $dates = json_decode($roleUser->dates_unavailable, true);
            $this->assertContains($targetDate, $dates);

            // Verify persistence after reload
            $browser->visit('/talent/availability')
                ->waitFor('#saveButton', 10)
                ->pause(500);

            $hasDayX = $browser->script(
                "return document.querySelector('.day-element[data-date=\"".$targetDate."\"] .day-x') !== null;"
            );
            $this->assertTrue($hasDayX[0], 'Day-x overlay should persist after reload');

            // Toggle back to available
            $browser->click('.day-element[data-date="'.$targetDate.'"]')
                ->pause(500);

            $hasDayX = $browser->script(
                "return document.querySelector('.day-element[data-date=\"".$targetDate."\"] .day-x') !== null;"
            );
            $this->assertFalse($hasDayX[0], 'Day-x overlay should be removed after toggling back');

            // Save again
            $browser->click('#saveButton')
                ->waitForText('Successfully updated availability', 5);

            // Verify database no longer has the date
            $roleUser->refresh();
            $dates = json_decode($roleUser->dates_unavailable, true);
            $this->assertNotContains($targetDate, $dates ?: []);
        });
    }
}
