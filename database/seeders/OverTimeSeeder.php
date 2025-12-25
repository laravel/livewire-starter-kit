<?php

namespace Database\Seeders;

use App\Models\OverTime;
use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OverTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan turnos
        if (Shift::count() === 0) {
            $this->command->warn('⚠️  No hay turnos creados. Ejecute ShiftSeeder primero.');
            return;
        }

        $this->command->info('🕐 Creando overtimes de ejemplo...');

        $shift1 = Shift::where('name', 'like', '%Turno 1%')->first()
                      ?? Shift::first();
        $shift2 = Shift::where('name', 'like', '%Turno 2%')->first()
                      ?? Shift::skip(1)->first()
                      ?? $shift1;

        // Overtime 1: Producción urgente - Turno 1
        OverTime::create([
            'name' => 'Overtime Producción Urgente - Cliente ABC',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->addDays(2),
            'start_time' => '17:00',
            'end_time' => '21:00', // 4 horas
            'break_minutes' => 15,
            'employees_qty' => 12,
            'comments' => 'Pedido urgente cliente ABC - Deadline viernes',
        ]);

        // Overtime 2: Fin de semana
        OverTime::create([
            'name' => 'Overtime Fin de Semana',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->next('Saturday'),
            'start_time' => '08:00',
            'end_time' => '17:00', // 9 horas
            'break_minutes' => 60,
            'employees_qty' => 20,
            'comments' => 'Producción extra para cumplir cuota mensual',
        ]);

        // Overtime 3: Nocturno (cruza medianoche)
        OverTime::create([
            'name' => 'Overtime Nocturno - Turno 2',
            'shift_id' => $shift2->id,
            'date' => Carbon::today()->addDays(5),
            'start_time' => '22:00',
            'end_time' => '02:00', // Cruza medianoche - 4 horas
            'break_minutes' => 30,
            'employees_qty' => 8,
            'comments' => 'Producción nocturna - máquina X disponible',
        ]);

        // Overtime 4: Corto - 2 horas
        OverTime::create([
            'name' => 'Overtime Corto - Completar Lote',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->addDays(3),
            'start_time' => '17:00',
            'end_time' => '19:00', // 2 horas
            'break_minutes' => 0,
            'employees_qty' => 6,
            'comments' => 'Completar lote L-12345',
        ]);

        // Overtime 5: Extendido - 6 horas
        OverTime::create([
            'name' => 'Overtime Extendido - Pedido Especial',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->addWeeks(1),
            'start_time' => '17:00',
            'end_time' => '23:00', // 6 horas
            'break_minutes' => 45,
            'employees_qty' => 15,
            'comments' => 'Cliente prioritario - pago de overtime 2x',
        ]);

        // Generar 10 overtimes aleatorios adicionales
        OverTime::factory()->count(10)->create();

        $this->command->info('✅ Overtimes creados exitosamente');
        $this->command->info('   - 5 overtimes específicos');
        $this->command->info('   - 10 overtimes aleatorios');
        $this->command->table(
            ['Total de Overtimes'],
            [[OverTime::count()]]
        );
    }
}
