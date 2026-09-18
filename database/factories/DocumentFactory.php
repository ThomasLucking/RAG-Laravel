<?php

namespace Database\Factories;

use App\Enums\DocumentOrigin;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'summary' => $this->faker->sentence(),
            'content' => "## {$this->faker->words(2, true)}\n\n{$this->faker->paragraph()}",
            'updated_on' => $this->faker->date(),
            'source_path' => null,
            'origin' => DocumentOrigin::Manual,
        ];
    }

    public function imported(): static
    {
        return $this->state(fn (array $attributes) => [
            'origin' => DocumentOrigin::Imported,
            'source_path' => $attributes['slug'].'.md',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn () => [
            'origin' => DocumentOrigin::Manual,
            'source_path' => null,
        ]);
    }
}
