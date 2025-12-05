<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $user = User::factory();
        $venue = Role::factory()->ofType('venue');
        $role = Role::factory()->ofType('talent');
        $name = fake()->unique()->sentence(3);

        return [
            'user_id' => $user,
            'role_id' => $role,
            'venue_id' => $venue,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'starts_at' => now()->addWeek(),
            'duration' => 2.5,
            'description' => fake()->sentence(),
            'description_html' => '<p>' . fake()->sentence() . '</p>',
            'tickets_enabled' => false,
            'total_tickets_mode' => 'unlimited',
            'payment_method' => 'free',
        ];
    }
}
