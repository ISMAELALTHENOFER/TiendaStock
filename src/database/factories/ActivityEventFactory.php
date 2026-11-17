<?php

namespace Database\Factories;

use App\Models\ActivityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ActivityEvent> */
class ActivityEventFactory extends Factory
{
    protected $model = ActivityEvent::class;

    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'type' => 'product.created',
            'title' => 'Product created',
            'description' => 'A product was created',
            'occurred_at' => now(),
        ];
    }
}
