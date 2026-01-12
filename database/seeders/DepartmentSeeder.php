<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea departamentos de prueba para la estructura organizacional.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Producción', 'description' => 'Departamento de producción y manufactura'],
            ['name' => 'Calidad', 'description' => 'Control y aseguramiento de calidad'],
            ['name' => 'Mantenimiento', 'description' => 'Mantenimiento de equipos e instalaciones'],
            ['name' => 'Almacén', 'description' => 'Gestión de inventarios y almacenamiento'],
            ['name' => 'Ingeniería', 'description' => 'Ingeniería de procesos y mejora continua'],
        ];

        $createdCount = 0;

        foreach ($departments as $data) {
            $dept = Department::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($dept->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ DepartmentSeeder completado!");
        $this->command->info("   - Departamentos creados: {$createdCount}");
    }
}
