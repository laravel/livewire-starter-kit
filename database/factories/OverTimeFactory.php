<?php

namespace Database\Factories;

use App\Models\OverTime;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OverTime>
 */
class OverTimeFactory extends Factory
{
    protected $model = OverTime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shift = Shift::inRandomOrder()->first() ?? Shift::factory()->create();

        // Generar overtime realista:
        // - Empieza después del turno normal (shift->end_time)
        // - Dura entre 2-4 horas
        // - Puede ser en la noche (22:00 - 02:00) o madrugada

        $shiftEndTime = \Carbon\Carbon::parse($shift->end_time);

        // Start time: 30 min después del turno normal
        $startTime = $shiftEndTime->copy()->addMinutes(30);

        // End time: 2-4 horas después
        $duration = $this->faker->numberBetween(2, 4);
        $endTime = $startTime->copy()->addHours($duration);

        return [
            'name' => $this->faker->randomElement([
                'Overtime Producción Urgente',
                'Tiempo Extra - Pedido Especial',
                'Overtime Fin de Semana',
                'Producción Extra Cliente Prioritario',
                'Overtime Recuperación',
            ]),
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'break_minutes' => $this->faker->randomElement([0, 15, 30]), // 0, 15 o 30 min
            'employees_qty' => $this->faker->numberBetween(5, 20),
            'date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'shift_id' => $shift->id,
            'comments' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Overtime nocturno (22:00 a 02:00)
     */
    public function nightShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Nocturno',
            'start_time' => '22:00',
            'end_time' => '02:00', // Cruza medianoche
            'break_minutes' => 30,
            'employees_qty' => $this->faker->numberBetween(5, 15),
        ]);
    }

    /**
     * Overtime de fin de semana
     */
    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Fin de Semana',
            'date' => $this->faker->dateTimeBetween('now', '+30 days')
                                  ->modify('next saturday')
                                  ->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'employees_qty' => $this->faker->numberBetween(10, 25),
        ]);
    }

    /**
     * Overtime corto (2 horas)
     */
    public function short(): static
    {
        $start = \Carbon\Carbon::parse('17:00');
        $end = $start->copy()->addHours(2);

        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Corto',
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'break_minutes' => 0,
            'employees_qty' => $this->faker->numberBetween(5, 10),
        ]);
    }

    /**
     * Overtime largo (6+ horas)
     */
    public function long(): static
    {
        $start = \Carbon\Carbon::parse('17:00');
        $end = $start->copy()->addHours(6);

        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Extendido',
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'break_minutes' => 60,
            'employees_qty' => $this->faker->numberBetween(15, 30),
        ]);
    }
}
