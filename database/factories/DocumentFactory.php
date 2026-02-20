<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'title' => $this->faker->sentence(6),
            'body' => $this->faker->paragraphs(4, true),
            'embedding' => null,
            'source_url' => null,
        ];
    }
}
