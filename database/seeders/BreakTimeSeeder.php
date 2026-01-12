<?php

namespace Database\Seeders;

use App\Models\BreakTime;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class BreakTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea tiempos de descanso para cada turno.
     */
    public function run(): void
    {
        $shifts = Shift::active()->get();

        if ($shifts->isEmpty()) {
            $this->command->warn('No hay turnos activos. Ejecute ShiftSeeder primero.');
            return;
        }

        $createdCount = 0;

        foreach ($shifts as $shift) {
            // Verificar si ya tiene descansos
            if ($shift->breakTimes()->exists()) {
                continue;
            }

            // Calcular descansos basados en el horario del turno
            $startHour = (int) substr($shift->start_time, 0, 2);
            
            // Descanso de comida (30 min) - a mitad del turno
            $lunchStart = ($startHour + 4) % 24;
            BreakTime::create([
                'shift_id' => $shift->id,
                'name' => 'Comida',
                'start_break_time' => sprintf('%02d:00:00', $lunchStart),
                'end_break_time' => sprintf('%02d:30:00', $lunchStart),
                'comments' => 'Descanso para comida',
                'active' => true,
            ]);
            $createdCount++;

            // Descanso corto (15 min) - 2 horas después del inicio
            $breakStart = ($startHour + 2) % 24;
            BreakTime::create([
                'shift_id' => $shift->id,
                'name' => 'Descanso 1',
                'start_break_time' => sprintf('%02d:00:00', $breakStart),
                'end_break_time' => sprintf('%02d:15:00', $breakStart),
                'comments' => 'Primer descanso corto',
                'active' => true,
            ]);
            $createdCount++;

            // Descanso corto (15 min) - 6 horas después del inicio
            $break2Start = ($startHour + 6) % 24;
            BreakTime::create([
                'shift_id' => $shift->id,
                'name' => 'Descanso 2',
                'start_break_time' => sprintf('%02d:00:00', $break2Start),
                'end_break_time' => sprintf('%02d:15:00', $break2Start),
                'comments' => 'Segundo descanso corto',
                'active' => true,
            ]);
            $createdCount++;
        }

        $this->command->info("✅ BreakTimeSeeder completado!");
        $this->command->info("   - Descansos creados: {$createdCount}");
        
        foreach ($shifts as $shift) {
            $count = $shift->breakTimes()->count();
            $this->command->info("   - {$shift->name}: {$count} descansos");
        }
    }
}
