<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = trim(fake()->unique()->company());
        $type = fake()->randomElement(['venue', 'talent', 'vendor']);

        return [
            'user_id' => User::factory(),
            'subdomain' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'type' => $type,
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'description' => fake()->sentence(),
            'timezone' => 'America/New_York',
            'language_code' => 'en',
        ];
    }

    public function ofType(string $type): static
    {
        return $this->state(fn () => ['type' => $type]);
    }
}
