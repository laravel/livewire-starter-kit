<?php

namespace Database\Factories;

use App\Models\StatusWO;
use App\Models\User;
use App\Models\WOStatusLog;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WOStatusLog>
 */
class WOStatusLogFactory extends Factory
{
    protected $model = WOStatusLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'from_status_id' => null,
            'to_status_id' => StatusWO::first()?->id ?? StatusWO::factory(),
            'user_id' => User::factory(),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
