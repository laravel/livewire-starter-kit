<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ShiftEmployeeTestSeeder extends Seeder
{
    /**
     * Seed de prueba para validar la funcionalidad de conteo de empleados por turno
     *
     * Escenarios creados:
     * - Turno Mañana: 5 empleados activos
     * - Turno Tarde: 3 empleados activos
     * - Turno Noche: 1 empleado activo
     * - Turno Vacío: 0 empleados
     * - Turno Mixto: 2 activos + 1 inactivo (solo cuenta los activos)
     * - Turno Admin: 1 empleado + 1 admin (solo cuenta el empleado)
     */
    public function run(): void
    {
        // Verificar/crear roles
        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee', 'guard_name' => 'web']
        );
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );

        // Crear área de prueba
        $area = Area::firstOrCreate(
            ['name' => 'Producción Test'],
            ['active' => true]
        );

        // ==================================================
        // ESCENARIO 1: Turno con 5 empleados activos
        // ==================================================
        $turnoManana = Shift::firstOrCreate(
            ['name' => 'Turno Mañana'],
            [
                'start_time' => '07:00',
                'end_time' => '15:00',
                'active' => true,
                'comments' => 'Turno de prueba con 5 empleados',
            ]
        );

        $this->createEmployee('Juan', 'Pérez García', $turnoManana->id, $area->id, true);
        $this->createEmployee('María', 'López Martínez', $turnoManana->id, $area->id, true);
        $this->createEmployee('Carlos', 'García Rodríguez', $turnoManana->id, $area->id, true);
        $this->createEmployee('Ana', 'Martínez Sánchez', $turnoManana->id, $area->id, true);
        $this->createEmployee('Pedro', 'Rodríguez López', $turnoManana->id, $area->id, true);

        $this->command->info("✓ Turno Mañana: 5 empleados activos");

        // ==================================================
        // ESCENARIO 2: Turno con 3 empleados activos
        // ==================================================
        $turnoTarde = Shift::firstOrCreate(
            ['name' => 'Turno Tarde'],
            [
                'start_time' => '15:00',
                'end_time' => '23:00',
                'active' => true,
                'comments' => 'Turno de prueba con 3 empleados',
            ]
        );

        $this->createEmployee('Laura', 'Fernández Gómez', $turnoTarde->id, $area->id, true);
        $this->createEmployee('Diego', 'Sánchez Ruiz', $turnoTarde->id, $area->id, true);
        $this->createEmployee('Carmen', 'González Díaz', $turnoTarde->id, $area->id, true);

        $this->command->info("✓ Turno Tarde: 3 empleados activos");

        // ==================================================
        // ESCENARIO 3: Turno con 1 empleado (test singular)
        // ==================================================
        $turnoNoche = Shift::firstOrCreate(
            ['name' => 'Turno Noche'],
            [
                'start_time' => '23:00',
                'end_time' => '07:00',
                'active' => true,
                'comments' => 'Turno de prueba con 1 empleado',
            ]
        );

        $this->createEmployee('Roberto', 'Morales Castro', $turnoNoche->id, $area->id, true);

        $this->command->info("✓ Turno Noche: 1 empleado activo");

        // ==================================================
        // ESCENARIO 4: Turno sin empleados
        // ==================================================
        Shift::firstOrCreate(
            ['name' => 'Turno Vacío'],
            [
                'start_time' => '08:00',
                'end_time' => '16:00',
                'active' => true,
                'comments' => 'Turno de prueba sin empleados asignados',
            ]
        );

        $this->command->info("✓ Turno Vacío: 0 empleados");

        // ==================================================
        // ESCENARIO 5: Turno con empleados activos e inactivos
        // ==================================================
        $turnoMixto = Shift::firstOrCreate(
            ['name' => 'Turno Mixto'],
            [
                'start_time' => '09:00',
                'end_time' => '17:00',
                'active' => true,
                'comments' => 'Turno con empleados activos e inactivos',
            ]
        );

        $this->createEmployee('Activo', 'Uno', $turnoMixto->id, $area->id, true);
        $this->createEmployee('Activo', 'Dos', $turnoMixto->id, $area->id, true);
        $this->createEmployee('Inactivo', 'Tres', $turnoMixto->id, $area->id, false);

        $this->command->info("✓ Turno Mixto: 2 activos + 1 inactivo (solo cuenta 2)");

        // ==================================================
        // ESCENARIO 6: Turno con employee y admin (solo cuenta employee)
        // ==================================================
        $turnoAdmin = Shift::firstOrCreate(
            ['name' => 'Turno Admin'],
            [
                'start_time' => '10:00',
                'end_time' => '18:00',
                'active' => true,
                'comments' => 'Turno con employee y admin',
            ]
        );

        $this->createEmployee('Empleado', 'Normal', $turnoAdmin->id, $area->id, true);
        $this->createAdmin('Admin', 'Usuario', $turnoAdmin->id);

        $this->command->info("✓ Turno Admin: 1 empleado + 1 admin (solo cuenta 1)");

        // ==================================================
        // ESCENARIO 7: Empleado sin turno (para validar estadística)
        // ==================================================
        $this->createEmployee('Sin', 'Turno', null, $area->id, true);

        $this->command->info("✓ Empleado sin turno creado (no debe contarse en estadísticas)");

        // ==================================================
        // RESUMEN
        // ==================================================
        $totalShifts = Shift::count();
        $totalEmployees = User::role('employee')->count();
        $totalAssigned = User::role('employee')->whereNotNull('shift_id')->active()->count();

        $this->command->newLine();
        $this->command->info("========================================");
        $this->command->info("RESUMEN DE DATOS DE PRUEBA");
        $this->command->info("========================================");
        $this->command->info("Total de turnos creados: {$totalShifts}");
        $this->command->info("Total de empleados: {$totalEmployees}");
        $this->command->info("Empleados asignados a turno (activos): {$totalAssigned}");
        $this->command->newLine();
        $this->command->info("Desglose por turno:");
        $this->command->info("- Turno Mañana: 5 empleados");
        $this->command->info("- Turno Tarde: 3 empleados");
        $this->command->info("- Turno Noche: 1 empleado");
        $this->command->info("- Turno Vacío: 0 empleados");
        $this->command->info("- Turno Mixto: 2 empleados (ignora inactivos)");
        $this->command->info("- Turno Admin: 1 empleado (ignora admin)");
        $this->command->newLine();
        $this->command->info("Total esperado: 12 empleados asignados");
        $this->command->info("========================================");
    }

    /**
     * Crear un empleado de prueba
     */
    private function createEmployee(
        string $name,
        string $lastName,
        ?int $shiftId,
        int $areaId,
        bool $active
    ): User {
        $email = strtolower($name . '.' . $lastName . '@test.com');
        $email = str_replace(' ', '', $email);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'last_name' => $lastName,
                'password' => Hash::make('password'),
                'shift_id' => $shiftId,
                'area_id' => $areaId,
                'active' => $active,
                'position' => 'Operario',
                'employee_number' => 'EMP' . rand(1000, 9999),
            ]
        );

        if (!$user->hasRole('employee')) {
            $user->assignRole('employee');
        }

        return $user;
    }

    /**
     * Crear un admin de prueba
     */
    private function createAdmin(
        string $name,
        string $lastName,
        ?int $shiftId
    ): User {
        $email = strtolower($name . '.' . $lastName . '@test.com');
        $email = str_replace(' ', '', $email);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'last_name' => $lastName,
                'password' => Hash::make('password'),
                'shift_id' => $shiftId,
                'active' => true,
            ]
        );

        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        return $user;
    }
}
