<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'personal_team' => false,
            'owner_user_id' => User::factory(),
        ];
    }

    public function personal(): self
    {
        return $this->state(fn () => [
            'personal_team' => true,
        ]);
    }
}
