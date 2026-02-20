<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders in order (dependencies first!)
        $this->call([
            // 1. Base: Permisos y Roles
            PermissionSeeder::class,
            RoleSeeder::class,
            
            MaterialsRoleSeeder::class,
            AreaUsersSeeder::class,
            
            // 2. Catálogos base
            StatusWOSeeder::class,
            ProductionStatusSeeder::class,   // Estados para mesas/máquinas
            DepartmentSeeder::class,         // Departamentos
            AreaSeeder::class,               // Áreas (depende de Departamentos)
            
            // 3. Turnos y descansos
            ShiftSeeder::class,              // Turnos de producción
            BreakTimeSeeder::class,          // Descansos por turno
            
            // 4. Estaciones de trabajo (dependen de Áreas y ProductionStatus)
            TableSeeder::class,              // Mesas de trabajo
            Semi_AutomaticSeeder::class,     // Semi-automáticos
            MachineSeeder::class,            // Máquinas
            
            // 5. Precios y partes
            PriceSeeder::class,              // Precios con tiers
            
            // 6. Personal
            EmployeeSeeder::class,           // Empleados por turno
            
            // 7. Estándares (depende de Parts, Tables, Machines)
            StandardSeeder::class,           // Estándares para cálculo de capacidad
            
            // 8. Datos de prueba
            WorkOrderTestSeeder::class,
        ]);

        // Create admin user AFTER roles are created
        $adminUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'account' => 'test',
            'password' => Hash::make('password'),
        ]);
        
        // Assign admin role
        $adminUser->assignRole('admin');
        
        $this->command->info('Admin user created: test@test.com / password');
    }
}
