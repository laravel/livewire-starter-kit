<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_number' => 'PO-' . $this->faker->unique()->numerify('######'),
            'part_id' => Part::factory(),
            'po_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days'),
            'quantity' => $this->faker->numberBetween(100, 50000),
            'unit_price' => $this->faker->randomFloat(4, 0.5, 100),
            'status' => PurchaseOrder::STATUS_PENDING,
            'comments' => $this->faker->optional()->sentence(),
            'pdf_path' => null,
        ];
    }

    /**
     * Indicate that the purchase order is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);
    }

    /**
     * Indicate that the purchase order is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_REJECTED,
        ]);
    }

    /**
     * Indicate that the purchase order is pending price correction.
     */
    public function pendingCorrection(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_PENDING_CORRECTION,
        ]);
    }
}
