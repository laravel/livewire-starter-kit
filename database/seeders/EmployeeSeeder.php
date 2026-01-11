<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use App\Models\Area;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea empleados de prueba distribuidos entre los turnos existentes.
     * Usa firstOrCreate para ser idempotente (no crear duplicados).
     */
    public function run(): void
    {
        $shifts = Shift::active()->get();
        
        if ($shifts->isEmpty()) {
            $this->command->warn('No hay turnos activos. Ejecute ShiftSeeder primero.');
            return;
        }

        $areas = Area::all();
        $defaultAreaId = $areas->first()?->id;

        // Lista de empleados de prueba
        $employees = [
            ['name' => 'Juan Carlos', 'last_name' => 'García López', 'position' => 'Operador de Línea'],
            ['name' => 'María Elena', 'last_name' => 'Rodríguez Pérez', 'position' => 'Operador de Línea'],
            ['name' => 'Roberto', 'last_name' => 'Martínez Sánchez', 'position' => 'Técnico de Calidad'],
            ['name' => 'Ana Lucía', 'last_name' => 'Hernández Díaz', 'position' => 'Operador de Máquina'],
            ['name' => 'Carlos Alberto', 'last_name' => 'López Ramírez', 'position' => 'Operador de Línea'],
            ['name' => 'Patricia', 'last_name' => 'González Torres', 'position' => 'Operador de Línea'],
            ['name' => 'Miguel Ángel', 'last_name' => 'Flores Morales', 'position' => 'Técnico de Mantenimiento'],
            ['name' => 'Laura', 'last_name' => 'Jiménez Castro', 'position' => 'Operador de Máquina'],
            ['name' => 'Fernando', 'last_name' => 'Ruiz Vargas', 'position' => 'Operador de Línea'],
            ['name' => 'Gabriela', 'last_name' => 'Moreno Reyes', 'position' => 'Inspector de Calidad'],
            ['name' => 'José Luis', 'last_name' => 'Ortiz Mendoza', 'position' => 'Operador de Línea'],
            ['name' => 'Sandra', 'last_name' => 'Castillo Aguilar', 'position' => 'Operador de Máquina'],
            ['name' => 'Ricardo', 'last_name' => 'Vega Navarro', 'position' => 'Operador de Línea'],
            ['name' => 'Verónica', 'last_name' => 'Ramos Guerrero', 'position' => 'Técnico de Calidad'],
            ['name' => 'Alejandro', 'last_name' => 'Medina Cruz', 'position' => 'Operador de Línea'],
            ['name' => 'Claudia', 'last_name' => 'Herrera Delgado', 'position' => 'Operador de Máquina'],
            ['name' => 'Eduardo', 'last_name' => 'Peña Rojas', 'position' => 'Operador de Línea'],
            ['name' => 'Mónica', 'last_name' => 'Silva Campos', 'position' => 'Inspector de Calidad'],
        ];

        $createdCount = 0;
        $existingCount = 0;

        foreach ($employees as $index => $data) {
            // Distribuir empleados entre turnos de forma balanceada
            $shift = $shifts[$index % $shifts->count()];
            
            // Distribuir entre áreas si hay disponibles
            $areaId = $areas->isNotEmpty() 
                ? $areas[$index % $areas->count()]->id 
                : $defaultAreaId;

            // Generar email único basado en nombre
            $emailBase = $this->generateEmailBase($data['name'], $data['last_name']);
            $email = $emailBase . '@flexcon.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'last_name' => $data['last_name'],
                    'account' => $emailBase,
                    'position' => $data['position'],
                    'shift_id' => $shift->id,
                    'area_id' => $areaId,
                    'active' => true,
                    'password' => bcrypt('password'),
                    'entry_date' => now()->subMonths(rand(1, 24)),
                ]
            );

            // Asignar rol 'employee' si no lo tiene
            if (!$user->hasRole('employee')) {
                $user->assignRole('employee');
            }

            if ($user->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $existingCount++;
            }
        }

        $this->command->info("✅ EmployeeSeeder completado!");
        $this->command->info("   - Empleados creados: {$createdCount}");
        $this->command->info("   - Empleados existentes: {$existingCount}");
        
        // Mostrar distribución por turno
        foreach ($shifts as $shift) {
            $count = User::where('shift_id', $shift->id)->role('employee')->count();
            $this->command->info("   - {$shift->name}: {$count} empleados");
        }
    }

    /**
     * Genera un email base único a partir del nombre y apellido.
     */
    private function generateEmailBase(string $name, string $lastName): string
    {
        // Tomar primer nombre y primer apellido
        $firstName = strtolower(explode(' ', trim($name))[0]);
        $firstLastName = strtolower(explode(' ', trim($lastName))[0]);
        
        // Remover acentos
        $firstName = $this->removeAccents($firstName);
        $firstLastName = $this->removeAccents($firstLastName);
        
        return $firstName . '.' . $firstLastName;
    }

    /**
     * Remueve acentos de una cadena.
     */
    private function removeAccents(string $string): string
    {
        $unwanted = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
        ];
        
        return strtr($string, $unwanted);
    }
}
