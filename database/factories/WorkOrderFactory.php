<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\StatusWO;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wo_number' => WorkOrder::generateWONumber(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'status_id' => StatusWO::first()?->id ?? StatusWO::factory(),
            'sent_pieces' => 0,
            'scheduled_send_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'actual_send_date' => null,
            'opened_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'eq' => $this->faker->optional()->word(),
            'pr' => $this->faker->optional()->word(),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the work order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => StatusWO::where('name', 'In Progress')->first()?->id ?? $attributes['status_id'],
            'sent_pieces' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Indicate that the work order is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $po = PurchaseOrder::find($attributes['purchase_order_id']);
            return [
                'status_id' => StatusWO::where('name', 'Completed')->first()?->id ?? $attributes['status_id'],
                'sent_pieces' => $po?->quantity ?? 100,
                'actual_send_date' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }
}
