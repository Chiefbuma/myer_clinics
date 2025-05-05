<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement([User::ROLE_ADMIN, User::ROLE_USER]),
            'branch_id' => Branch::inRandomOrder()->first()?->id ?? Branch::factory(),
        ];
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Create a regular user.
     */
    public function user(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_USER,
        ]);
    }

    /**
     * Assign to a specific branch.
     */
    public function forBranch(Branch $branch): static
    {
        return $this->state(fn(array $attributes) => [
            'branch_id' => $branch->id,
        ]);
    }
}
