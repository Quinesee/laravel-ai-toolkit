<?php

namespace Database\Factories;

use App\Models\TicketTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTagFactory extends Factory
{
    protected $model = TicketTag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
