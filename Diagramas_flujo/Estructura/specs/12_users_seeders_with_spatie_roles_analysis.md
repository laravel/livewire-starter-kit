# Spec 12: User & Employee Seeders with Spatie Permission Roles

**Fecha**: 2025-12-29
**Modulo**: User Management & Authentication
**Fase**: FASE 3 - Data Seeding & Testing Infrastructure
**Prioridad**: ALTA
**Estado**: PENDING IMPLEMENTATION

---

## Resumen Ejecutivo

Este documento define la estrategia completa para crear usuarios ficticios (Users) y empleados (Employees) con roles asignados usando Spatie Permission. El objetivo es poblar la base de datos con datos de prueba realistas que permitan testing completo del sistema, específicamente:

- **50 usuarios** con rol "Production" para el **primer turno** (First Shift)
- **50 usuarios** con rol "Production" para el **segundo turno** (Second Shift)
- **Al menos 1 usuario** por cada rol existente en el sistema (Admin, HR, Maintenance, Shipping, Warehouse, Materials)

**Contexto del requerimiento**:
El sistema necesita datos de prueba para validar:
- Sistema de roles y permisos (Spatie Permission)
- Relación Users ↔ Roles
- Relación Employees ↔ Shifts
- Relación Employees ↔ Areas
- Capacidad de producción por turno
- Dashboards y reportes por rol

---

## 1. Estado Actual del Sistema

### 1.1 Spatie Permission - Configuración Actual

**Archivo**: `config/permission.php`

**Configuración**:
```php
'models' => [
    'permission' => Spatie\Permission\Models\Permission::class,
    'role' => Spatie\Permission\Models\Role::class,
],

'table_names' => [
    'roles' => 'roles',
    'permissions' => 'permissions',
    'model_has_permissions' => 'model_has_permissions',
    'model_has_roles' => 'model_has_roles',
    'role_has_permissions' => 'role_has_permissions',
],

'teams' => false, // No se usa feature de teams
```

**Estado**: Configuración estándar de Spatie Permission, sin modificaciones custom.

### 1.2 Roles Existentes en el Sistema

**Archivo**: `database/seeders/RoleSeeder.php`

**Roles definidos**:
```php
$roles = [
    'Admin',        // Administrador del sistema
    'HR',           // Recursos Humanos
    'Maintenance',  // Mantenimiento
    'Production',   // Producción (OPERATIVO)
    'Shipping',     // Envíos
    'Warehouse',    // Almacén
    'Materials'     // Materiales
];
```

**Permisos por rol**:

1. **Admin**:
   - Todos los permisos del sistema (`Permission::all()`)
   - Control total del sistema

2. **HR**:
   - view-dashboard
   - view-users, create-users, edit-users, delete-users
   - view-roles
   - view-departments, view-areas
   - view-reports, create-reports, export-reports

3. **Production, Maintenance, Shipping, Warehouse, Materials**:
   - view-dashboard
   - view-users
   - view-reports

**Análisis**:
- Existe un rol "Production" que es el requerido para los 100 usuarios (50 por turno)
- Cada rol tiene permisos asignados mediante `syncPermissions()`
- Los roles operativos (Production, etc.) tienen permisos básicos de solo lectura

### 1.3 Modelo User - Configuración Actual

**Archivo**: `app/Models/User.php`

**Traits utilizados**:
```php
use HasFactory, Notifiable, HasRoles; // ← Spatie Permission
```

**Fillable**:
```php
protected $fillable = [
    'name',
    'last_name',
    'account',    // Número de cuenta/username
    'email',
    'password',
];
```

**Análisis**:
- El modelo User YA está configurado con `HasRoles` de Spatie
- Tiene campos `name` y `last_name` (similar a Employee)
- Tiene campo `account` para username único
- **NO tiene** relación directa con Employee (son entidades separadas)
- **NO tiene** relación directa con Shift (Users no están asignados a turnos)

### 1.4 Modelo Employee - Configuración Actual

**Archivo**: `app/Models/Employee.php`

**Estado**: Modelo VACÍO (pendiente de implementación según Spec 11)

**Estructura de tabla** (`employees`):
```php
- name, last_name, email, password
- number (employee number - unique)
- position
- birth_date, entry_date
- active (1: activo, 0: inactivo)
- area_id (FK a areas)
- shift_id (FK a shifts) ← IMPORTANTE
- comments
```

**Análisis**:
- Employees SÍ están relacionados con Shifts (shift_id FK)
- Employees tienen email y password (pueden autenticarse)
- **NO tienen roles** de Spatie (son entidad separada de Users)

### 1.5 Shifts Existentes en el Sistema

**Archivo**: `database/seeders/ShiftSeeder.php`

**Turnos definidos**:
```php
[
    'name' => 'First Shift (Morning)',
    'start_time' => '06:00:00',
    'end_time' => '14:00:00',
],
[
    'name' => 'Second Shift (Afternoon)',
    'start_time' => '14:00:00',
    'end_time' => '22:00:00',
],
[
    'name' => 'Third Shift (Night)',
    'start_time' => '22:00:00',
    'end_time' => '06:00:00',
],
[
    'name' => 'Day Shift (Standard)',
    'start_time' => '08:00:00',
    'end_time' => '16:00:00',
],
```

**Análisis**:
- Existen 4 turnos definidos
- Los turnos requeridos son: **First Shift** (ID=1) y **Second Shift** (ID=2)
- Los turnos están activos por defecto

### 1.6 Areas Existentes

**Análisis**: Se requiere verificar si existen áreas creadas, ya que Employees necesitan area_id.

**Acción necesaria**: Verificar existencia de AreaSeeder o crear áreas por defecto.

---

## 2. Análisis del Requerimiento

### 2.1 Interpretación del Requerimiento

**Requerimiento original**:
> "50 usuarios con rol de producción para el primer turno"
> "50 usuarios con rol de producción para el segundo turno"
> "Al menos 1 empleado por cada rol existente en el sistema"

**Ambigüedad detectada**:

El requerimiento mezcla dos conceptos:
1. **Users** con roles de Spatie Permission
2. **Employees** con asignación de turnos (shifts)

**Pregunta crítica**: ¿Se requieren Users o Employees?

### 2.2 Dos Interpretaciones Posibles

#### INTERPRETACIÓN A: Crear USERS con roles

**Descripción**:
- Crear 100 Users con rol "Production"
- 50 Users asociados conceptualmente al "First Shift"
- 50 Users asociados conceptualmente al "Second Shift"
- Crear Users adicionales con roles Admin, HR, Maintenance, etc.

**Problema**:
- El modelo User NO tiene relación directa con Shift
- No hay campo `shift_id` en la tabla `users`
- Los Users no están diseñados para estar en turnos

**Ventaja**:
- Cumple literalmente con "usuarios con rol"
- Usa Spatie Permission correctamente

#### INTERPRETACIÓN B: Crear EMPLOYEES con Users asociados

**Descripción**:
- Crear 100 Employees asignados a turnos
- 50 Employees en First Shift
- 50 Employees en Second Shift
- Crear Users correspondientes con rol "Production"
- Crear Users adicionales con otros roles

**Ventaja**:
- Tiene sentido de negocio (empleados en turnos)
- Usa correctamente la relación Employee ↔ Shift
- Permite testing completo del módulo de producción

**Problema**:
- Requiere clarificar la relación User ↔ Employee

---

## 3. Diseño de Solución Propuesta

### 3.1 Solución Recomendada: INTERPRETACIÓN B (Employees + Users)

**Justificación**:

1. **Contexto de negocio**: El sistema es de producción, los turnos son para empleados
2. **Arquitectura**: Employee tiene `shift_id`, User no
3. **Spec 11**: Ya existe análisis completo del módulo Employee
4. **Testing realista**: Necesitamos empleados en turnos para probar capacidad de producción

**Estrategia propuesta**:

```
┌─────────────────────────────────────────────────────┐
│           ESTRATEGIA DE SEEDING                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│  1. USERS CON ROLES (Administrativos)               │
│     - 1 Admin                                        │
│     - 1 HR                                           │
│     - 1 Maintenance                                  │
│     - 1 Shipping                                     │
│     - 1 Warehouse                                    │
│     - 1 Materials                                    │
│     - 100 Production (para login de empleados)      │
│                                                      │
│  2. EMPLOYEES CON TURNOS                             │
│     - 50 Employees en First Shift                    │
│     - 50 Employees en Second Shift                   │
│     - Todos con position "Production Operator"       │
│     - Todos activos (active = 1)                     │
│     - Distribuidos en diferentes Areas               │
│                                                      │
│  3. RELACIÓN USER ↔ EMPLOYEE (OPCIONAL)             │
│     - Cada Employee puede tener un User asociado     │
│     - El User tiene rol "Production"                 │
│     - Permite login de empleados al sistema          │
│                                                      │
└─────────────────────────────────────────────────────┘
```

### 3.2 Estructura de Datos a Generar

#### USERS (Total: 106)

**Usuarios Administrativos (6)**:
```php
[
    ['name' => 'Admin', 'last_name' => 'System', 'account' => 'admin', 'role' => 'Admin'],
    ['name' => 'HR', 'last_name' => 'Manager', 'account' => 'hr001', 'role' => 'HR'],
    ['name' => 'Maintenance', 'last_name' => 'Chief', 'account' => 'maint001', 'role' => 'Maintenance'],
    ['name' => 'Shipping', 'last_name' => 'Supervisor', 'account' => 'ship001', 'role' => 'Shipping'],
    ['name' => 'Warehouse', 'last_name' => 'Manager', 'account' => 'wh001', 'role' => 'Warehouse'],
    ['name' => 'Materials', 'last_name' => 'Coordinator', 'account' => 'mat001', 'role' => 'Materials'],
]
```

**Usuarios de Producción (100)**:
```php
// 50 para First Shift
[
    'name' => 'Production User 1',
    'last_name' => 'Shift 1',
    'account' => 'prod_s1_001',
    'email' => 'prod.s1.001@flexcon.test',
    'role' => 'Production',
]

// 50 para Second Shift
[
    'name' => 'Production User 51',
    'last_name' => 'Shift 2',
    'account' => 'prod_s2_001',
    'email' => 'prod.s2.001@flexcon.test',
    'role' => 'Production',
]
```

#### EMPLOYEES (Total: 100)

**Employees First Shift (50)**:
```php
[
    'name' => 'Employee First',
    'last_name' => 'Shift 01',
    'email' => 'emp.s1.001@flexcon.test',
    'number' => 'EMP-S1-001',
    'position' => 'Production Operator',
    'shift_id' => 1, // First Shift
    'area_id' => [distribuido entre áreas disponibles],
    'active' => 1,
]
```

**Employees Second Shift (50)**:
```php
[
    'name' => 'Employee Second',
    'last_name' => 'Shift 01',
    'email' => 'emp.s2.001@flexcon.test',
    'number' => 'EMP-S2-001',
    'position' => 'Production Operator',
    'shift_id' => 2, // Second Shift
    'area_id' => [distribuido entre áreas disponibles],
    'active' => 1,
]
```

---

## 4. Implementación Técnica

### 4.1 Prerequisitos

**Seeders que DEBEN ejecutarse ANTES**:

```php
// En DatabaseSeeder.php - ORDEN CRÍTICO
$this->call([
    PermissionSeeder::class,    // 1. Crear permisos
    RoleSeeder::class,          // 2. Crear roles y asignar permisos
    DepartmentSeeder::class,    // 3. Crear departamentos (REQUERIDO para Areas)
    AreaSeeder::class,          // 4. Crear áreas (REQUERIDO para Employees)
    ShiftSeeder::class,         // 5. Crear turnos (REQUERIDO para Employees)
    UserSeeder::class,          // 6. Crear users con roles ← NUEVO
    EmployeeSeeder::class,      // 7. Crear employees ← ACTUALIZAR
]);
```

**Acción requerida**: Verificar que exista `AreaSeeder` o crearlo.

### 4.2 Verificar/Crear AreaSeeder

**Archivo**: `database/seeders/AreaSeeder.php` (CREAR si no existe)

```php
<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que existan departamentos
        if (Department::count() === 0) {
            $this->command->warn('No hay departamentos. Ejecuta DepartmentSeeder primero.');
            return;
        }

        $productionDept = Department::firstOrCreate(
            ['name' => 'Production'],
            ['description' => 'Production Department', 'comments' => 'Main production area']
        );

        $areas = [
            [
                'name' => 'Assembly Line A',
                'description' => 'Main assembly line for connectors',
                'department_id' => $productionDept->id,
            ],
            [
                'name' => 'Assembly Line B',
                'description' => 'Secondary assembly line',
                'department_id' => $productionDept->id,
            ],
            [
                'name' => 'Quality Control',
                'description' => 'Quality inspection area',
                'department_id' => $productionDept->id,
            ],
            [
                'name' => 'Packaging',
                'description' => 'Final packaging and labeling',
                'department_id' => $productionDept->id,
            ],
        ];

        foreach ($areas as $areaData) {
            Area::firstOrCreate(
                ['name' => $areaData['name']],
                $areaData
            );
        }

        $this->command->info('Areas created: ' . Area::count());
    }
}
```

### 4.3 UserSeeder - Implementación Completa

**Archivo**: `database/seeders/UserSeeder.php` (CREAR)

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder crea:
     * - 1 usuario por cada rol administrativo (Admin, HR, Maintenance, etc.)
     * - 100 usuarios con rol Production (50 para cada turno conceptualmente)
     */
    public function run(): void
    {
        // Verificar que los roles existan
        if (Role::count() === 0) {
            $this->command->warn('No hay roles creados. Ejecuta RoleSeeder primero.');
            return;
        }

        $this->command->info('Creando usuarios con roles...');

        // ================================================
        // PASO 1: Crear usuarios administrativos
        // ================================================
        $this->createAdminUsers();

        // ================================================
        // PASO 2: Crear usuarios de producción
        // ================================================
        $this->createProductionUsers();

        $this->command->info('Users creados exitosamente.');
        $this->command->info('Total users: ' . User::count());
    }

    /**
     * Crear usuarios administrativos (1 por cada rol no-Production)
     */
    private function createAdminUsers(): void
    {
        $adminUsers = [
            [
                'name' => 'System',
                'last_name' => 'Administrator',
                'account' => 'admin',
                'email' => 'admin@flexcon.test',
                'password' => 'password',
                'role' => 'Admin',
            ],
            [
                'name' => 'HR',
                'last_name' => 'Manager',
                'account' => 'hr001',
                'email' => 'hr@flexcon.test',
                'password' => 'password',
                'role' => 'HR',
            ],
            [
                'name' => 'Maintenance',
                'last_name' => 'Chief',
                'account' => 'maint001',
                'email' => 'maintenance@flexcon.test',
                'password' => 'password',
                'role' => 'Maintenance',
            ],
            [
                'name' => 'Shipping',
                'last_name' => 'Supervisor',
                'account' => 'ship001',
                'email' => 'shipping@flexcon.test',
                'password' => 'password',
                'role' => 'Shipping',
            ],
            [
                'name' => 'Warehouse',
                'last_name' => 'Manager',
                'account' => 'wh001',
                'email' => 'warehouse@flexcon.test',
                'password' => 'password',
                'role' => 'Warehouse',
            ],
            [
                'name' => 'Materials',
                'last_name' => 'Coordinator',
                'account' => 'mat001',
                'email' => 'materials@flexcon.test',
                'password' => 'password',
                'role' => 'Materials',
            ],
        ];

        foreach ($adminUsers as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'last_name' => $userData['last_name'],
                    'account' => $userData['account'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );

            // Asignar rol usando Spatie Permission
            $user->assignRole($role);

            $this->command->info("✓ Created {$role} user: {$user->email}");
        }
    }

    /**
     * Crear usuarios de producción (100 usuarios con rol Production)
     * 50 asociados conceptualmente al primer turno
     * 50 asociados conceptualmente al segundo turno
     */
    private function createProductionUsers(): void
    {
        $productionRole = Role::where('name', 'Production')->first();

        if (!$productionRole) {
            $this->command->error('Rol "Production" no encontrado.');
            return;
        }

        $this->command->info('Creando 100 usuarios con rol Production...');

        // ================================================
        // PRIMER TURNO: 50 usuarios
        // ================================================
        for ($i = 1; $i <= 50; $i++) {
            $paddedNumber = str_pad($i, 3, '0', STR_PAD_LEFT);

            $user = User::firstOrCreate(
                ['email' => "prod.s1.{$paddedNumber}@flexcon.test"],
                [
                    'name' => "Production User S1",
                    'last_name' => "#{$paddedNumber}",
                    'account' => "prod_s1_{$paddedNumber}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole($productionRole);
        }

        $this->command->info('✓ Created 50 Production users for First Shift');

        // ================================================
        // SEGUNDO TURNO: 50 usuarios
        // ================================================
        for ($i = 1; $i <= 50; $i++) {
            $paddedNumber = str_pad($i, 3, '0', STR_PAD_LEFT);

            $user = User::firstOrCreate(
                ['email' => "prod.s2.{$paddedNumber}@flexcon.test"],
                [
                    'name' => "Production User S2",
                    'last_name' => "#{$paddedNumber}",
                    'account' => "prod_s2_{$paddedNumber}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole($productionRole);
        }

        $this->command->info('✓ Created 50 Production users for Second Shift');
    }
}
```

### 4.4 EmployeeSeeder - Actualización Completa

**Archivo**: `database/seeders/EmployeeSeeder.php` (ACTUALIZAR)

```php
<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Area;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder crea:
     * - 50 empleados en First Shift (Morning)
     * - 50 empleados en Second Shift (Afternoon)
     * - Distribuidos equitativamente entre las áreas disponibles
     */
    public function run(): void
    {
        // Verificar prerequisitos
        if (Area::count() === 0) {
            $this->command->warn('No hay áreas creadas. Ejecuta AreaSeeder primero.');
            return;
        }

        if (Shift::count() === 0) {
            $this->command->warn('No hay turnos creados. Ejecuta ShiftSeeder primero.');
            return;
        }

        $this->command->info('Creando empleados para turnos de producción...');

        // Obtener turnos
        $firstShift = Shift::where('name', 'like', '%First Shift%')->first();
        $secondShift = Shift::where('name', 'like', '%Second Shift%')->first();

        if (!$firstShift || !$secondShift) {
            $this->command->error('No se encontraron los turnos First Shift y Second Shift.');
            return;
        }

        // Obtener áreas disponibles
        $areas = Area::all();

        if ($areas->isEmpty()) {
            $this->command->error('No hay áreas disponibles.');
            return;
        }

        // ================================================
        // PRIMER TURNO: 50 empleados
        // ================================================
        $this->command->info("Creando 50 empleados para {$firstShift->name}...");

        for ($i = 1; $i <= 50; $i++) {
            $paddedNumber = str_pad($i, 3, '0', STR_PAD_LEFT);

            // Distribuir equitativamente entre áreas
            $areaIndex = ($i - 1) % $areas->count();
            $area = $areas[$areaIndex];

            Employee::firstOrCreate(
                ['number' => "EMP-S1-{$paddedNumber}"],
                [
                    'name' => "Employee First Shift",
                    'last_name' => "#{$paddedNumber}",
                    'email' => "emp.s1.{$paddedNumber}@flexcon.test",
                    'password' => Hash::make('password'),
                    'number' => "EMP-S1-{$paddedNumber}",
                    'position' => 'Production Operator',
                    'birth_date' => now()->subYears(rand(20, 50))->subDays(rand(1, 365)),
                    'entry_date' => now()->subYears(rand(0, 5))->subDays(rand(1, 365)),
                    'active' => 1,
                    'comments' => "First shift production employee",
                    'area_id' => $area->id,
                    'shift_id' => $firstShift->id,
                ]
            );
        }

        $this->command->info("✓ Created 50 employees for {$firstShift->name}");

        // ================================================
        // SEGUNDO TURNO: 50 empleados
        // ================================================
        $this->command->info("Creando 50 empleados para {$secondShift->name}...");

        for ($i = 1; $i <= 50; $i++) {
            $paddedNumber = str_pad($i, 3, '0', STR_PAD_LEFT);

            // Distribuir equitativamente entre áreas
            $areaIndex = ($i - 1) % $areas->count();
            $area = $areas[$areaIndex];

            Employee::firstOrCreate(
                ['number' => "EMP-S2-{$paddedNumber}"],
                [
                    'name' => "Employee Second Shift",
                    'last_name' => "#{$paddedNumber}",
                    'email' => "emp.s2.{$paddedNumber}@flexcon.test",
                    'password' => Hash::make('password'),
                    'number' => "EMP-S2-{$paddedNumber}",
                    'position' => 'Production Operator',
                    'birth_date' => now()->subYears(rand(20, 50))->subDays(rand(1, 365)),
                    'entry_date' => now()->subYears(rand(0, 5))->subDays(rand(1, 365)),
                    'active' => 1,
                    'comments' => "Second shift production employee",
                    'area_id' => $area->id,
                    'shift_id' => $secondShift->id,
                ]
            );
        }

        $this->command->info("✓ Created 50 employees for {$secondShift->name}");

        // ================================================
        // RESUMEN
        // ================================================
        $this->command->info('');
        $this->command->info('=== RESUMEN DE EMPLEADOS CREADOS ===');
        $this->command->info('Total empleados: ' . Employee::count());
        $this->command->info('Empleados activos: ' . Employee::where('active', 1)->count());
        $this->command->info('Empleados First Shift: ' . Employee::where('shift_id', $firstShift->id)->count());
        $this->command->info('Empleados Second Shift: ' . Employee::where('shift_id', $secondShift->id)->count());

        foreach ($areas as $area) {
            $count = Employee::where('area_id', $area->id)->count();
            $this->command->info("Empleados en {$area->name}: {$count}");
        }
    }
}
```

### 4.5 Actualizar DatabaseSeeder

**Archivo**: `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de prueba inicial (mantener si existe)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'account' => 'test',
            'password' => Hash::make('password'),
        ]);

        // ================================================
        // ORDEN CRÍTICO DE SEEDERS
        // ================================================
        $this->call([
            // 1. Sistema de permisos
            PermissionSeeder::class,
            RoleSeeder::class,

            // 2. Estructuras organizacionales
            DepartmentSeeder::class,  // REQUERIDO antes de Areas
            AreaSeeder::class,        // REQUERIDO antes de Employees

            // 3. Configuración de producción
            StatusWOSeeder::class,
            ShiftSeeder::class,       // REQUERIDO antes de Employees

            // 4. Usuarios y empleados
            UserSeeder::class,        // Crear users con roles ← NUEVO
            EmployeeSeeder::class,    // Crear employees en turnos ← ACTUALIZADO

            // 5. Infraestructura de producción
            TableSeeder::class,
            Semi_AutomaticSeeder::class,
            MachineSeeder::class,

            // 6. Datos de prueba
            WorkOrderTestSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('=== SEEDING COMPLETADO ===');
        $this->command->info('Verifica los datos con:');
        $this->command->info('  php artisan tinker');
        $this->command->info('  >>> User::with("roles")->get()');
        $this->command->info('  >>> Employee::with(["shift", "area"])->get()');
    }
}
```

### 4.6 Crear DepartmentSeeder (Si no existe)

**Archivo**: `database/seeders/DepartmentSeeder.php` (CREAR si no existe)

```php
<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Production',
                'description' => 'Main production department',
                'comments' => 'Handles all manufacturing operations',
            ],
            [
                'name' => 'Quality Control',
                'description' => 'Quality assurance and testing',
                'comments' => 'Ensures product quality standards',
            ],
            [
                'name' => 'Warehouse',
                'description' => 'Storage and inventory management',
                'comments' => 'Manages raw materials and finished goods',
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Equipment maintenance and repair',
                'comments' => 'Keeps production equipment operational',
            ],
        ];

        foreach ($departments as $deptData) {
            Department::firstOrCreate(
                ['name' => $deptData['name']],
                $deptData
            );
        }

        $this->command->info('Departments created: ' . Department::count());
    }
}
```

---

## 5. Nomenclatura y Convenciones

### 5.1 Convenciones de Naming

**Users**:
```
Account pattern: [role]_[shift]_[number]
- Admins: admin, hr001, maint001, etc.
- Production: prod_s1_001, prod_s1_002, ..., prod_s2_050

Email pattern: [role].[shift].[number]@flexcon.test
- Admins: admin@flexcon.test, hr@flexcon.test
- Production: prod.s1.001@flexcon.test, prod.s2.050@flexcon.test

Name pattern:
- Admins: Descriptive names (System Administrator, HR Manager)
- Production: "Production User S1 #001", "Production User S2 #050"
```

**Employees**:
```
Number pattern: EMP-[SHIFT]-[NUMBER]
- First Shift: EMP-S1-001, EMP-S1-002, ..., EMP-S1-050
- Second Shift: EMP-S2-001, EMP-S2-002, ..., EMP-S2-050

Email pattern: emp.[shift].[number]@flexcon.test
- emp.s1.001@flexcon.test
- emp.s2.050@flexcon.test

Name pattern: "Employee [Shift Name] #[NUMBER]"
- "Employee First Shift #001"
- "Employee Second Shift #050"
```

### 5.2 Passwords por Defecto

**Todos los usuarios**:
```php
password: 'password' // Hasheado automáticamente
```

**Nota de seguridad**: En producción, estos seeders NO deben ejecutarse. Solo para desarrollo y testing.

### 5.3 Estados por Defecto

**Users**:
```php
email_verified_at: now() // Todos verificados
```

**Employees**:
```php
active: 1 // Todos activos
```

---

## 6. Mejores Prácticas para Datos Ficticios

### 6.1 Datos Realistas

**Fechas**:
```php
birth_date: now()->subYears(rand(20, 50))->subDays(rand(1, 365))
// Empleados entre 20 y 50 años

entry_date: now()->subYears(rand(0, 5))->subDays(rand(1, 365))
// Antigüedad entre 0 y 5 años
```

**Distribución de Áreas**:
```php
// Distribuir equitativamente
$areaIndex = ($i - 1) % $areas->count();
$area = $areas[$areaIndex];

// Si hay 4 áreas y 50 empleados:
// - Área 1: empleados 1, 5, 9, 13... (12-13 empleados)
// - Área 2: empleados 2, 6, 10, 14... (12-13 empleados)
// - etc.
```

### 6.2 Idempotencia

**Uso de `firstOrCreate()`**:
```php
User::firstOrCreate(
    ['email' => $userData['email']], // Condición de búsqueda
    [...] // Datos a crear si no existe
);
```

**Ventajas**:
- Ejecutar seeder múltiples veces no crea duplicados
- Safe para development workflow
- Permite refresh parcial de datos

### 6.3 Verificación de Prerequisitos

**Siempre verificar dependencias**:
```php
if (Role::count() === 0) {
    $this->command->warn('No hay roles. Ejecuta RoleSeeder primero.');
    return;
}
```

**Beneficios**:
- Errores claros y descriptivos
- Previene foreign key violations
- Facilita debugging

### 6.4 Logging y Feedback

**Información útil durante seeding**:
```php
$this->command->info('Creando usuarios...');
$this->command->info('✓ Created Admin user: admin@flexcon.test');
$this->command->info('Total users: ' . User::count());
```

**Uso de colores (opcional)**:
```php
$this->command->error('Error: No roles found');
$this->command->warn('Warning: Skipping...');
$this->command->info('Success: Created');
```

---

## 7. Testing y Validación

### 7.1 Comandos de Verificación

**Resetear y sembrar base de datos**:
```bash
php artisan migrate:fresh --seed
```

**Solo ejecutar seeders específicos**:
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=EmployeeSeeder
```

### 7.2 Validación con Tinker

**Verificar Users con Roles**:
```php
php artisan tinker

// Ver todos los users con sus roles
>>> User::with('roles')->get()

// Contar users por rol
>>> use Spatie\Permission\Models\Role;
>>> Role::all()->map(function($role) {
    return [
        'role' => $role->name,
        'users' => $role->users->count()
    ];
})

// Ver users de Production
>>> User::role('Production')->count()
>>> // Debe retornar: 100

// Ver users administrativos
>>> User::role('Admin')->first()
>>> User::role('HR')->first()
```

**Verificar Employees con Shifts**:
```php
// Ver employees con shift y área
>>> Employee::with(['shift', 'area'])->get()

// Contar por shift
>>> Employee::byShift(1)->count() // First Shift
>>> // Debe retornar: 50

>>> Employee::byShift(2)->count() // Second Shift
>>> // Debe retornar: 50

// Ver distribución por área
>>> use App\Models\Area;
>>> Area::all()->map(function($area) {
    return [
        'area' => $area->name,
        'employees' => $area->employees->count()
    ];
})
```

**Verificar Relaciones**:
```php
// Shift → Employees
>>> $shift = Shift::find(1);
>>> $shift->employees()->count()
>>> // Debe retornar: 50

// Area → Employees
>>> $area = Area::first();
>>> $area->employees()->count()
>>> // Debe retornar: 12-13 (dependiendo de cuántas áreas hay)
```

### 7.3 Tests Automatizados

**Archivo**: `tests/Feature/SeedersTest.php` (CREAR)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders
        $this->seed();
    }

    /** @test */
    public function all_roles_have_at_least_one_user()
    {
        $roles = ['Admin', 'HR', 'Maintenance', 'Production', 'Shipping', 'Warehouse', 'Materials'];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $this->assertNotNull($role, "Role {$roleName} should exist");

            $usersCount = $role->users()->count();
            $this->assertGreaterThanOrEqual(1, $usersCount, "Role {$roleName} should have at least 1 user");
        }
    }

    /** @test */
    public function production_role_has_100_users()
    {
        $productionUsers = User::role('Production')->count();
        $this->assertEquals(100, $productionUsers);
    }

    /** @test */
    public function first_shift_has_50_employees()
    {
        $firstShift = \App\Models\Shift::where('name', 'like', '%First Shift%')->first();
        $this->assertNotNull($firstShift);

        $employeesCount = Employee::where('shift_id', $firstShift->id)->count();
        $this->assertEquals(50, $employeesCount);
    }

    /** @test */
    public function second_shift_has_50_employees()
    {
        $secondShift = \App\Models\Shift::where('name', 'like', '%Second Shift%')->first();
        $this->assertNotNull($secondShift);

        $employeesCount = Employee::where('shift_id', $secondShift->id)->count();
        $this->assertEquals(50, $employeesCount);
    }

    /** @test */
    public function all_employees_are_active()
    {
        $activeEmployees = Employee::where('active', 1)->count();
        $totalEmployees = Employee::count();

        $this->assertEquals($totalEmployees, $activeEmployees);
    }

    /** @test */
    public function all_employees_have_valid_area()
    {
        $employeesWithoutArea = Employee::whereNull('area_id')->count();
        $this->assertEquals(0, $employeesWithoutArea);
    }

    /** @test */
    public function admin_user_has_all_permissions()
    {
        $admin = User::role('Admin')->first();
        $this->assertNotNull($admin);

        // Admin debe tener todos los permisos
        $this->assertTrue($admin->hasPermissionTo('view-dashboard'));
        $this->assertTrue($admin->hasPermissionTo('create-users'));
        $this->assertTrue($admin->hasPermissionTo('delete-users'));
    }
}
```

**Ejecutar tests**:
```bash
php artisan test --filter SeedersTest
```

---

## 8. Troubleshooting

### 8.1 Errores Comunes

**Error: Foreign key constraint fails (area_id)**
```
Causa: AreaSeeder no se ejecutó antes de EmployeeSeeder
Solución: Verificar orden en DatabaseSeeder
```

**Error: Foreign key constraint fails (shift_id)**
```
Causa: ShiftSeeder no se ejecutó antes de EmployeeSeeder
Solución: Verificar orden en DatabaseSeeder
```

**Error: Role "Production" not found**
```
Causa: RoleSeeder no se ejecutó antes de UserSeeder
Solución: Ejecutar php artisan db:seed --class=RoleSeeder primero
```

**Error: Duplicate entry for email**
```
Causa: Seeder ejecutado múltiples veces sin usar firstOrCreate
Solución: Cambiar create() por firstOrCreate() en seeders
```

### 8.2 Reset Completo

**Resetear todo desde cero**:
```bash
# Eliminar base de datos
php artisan db:wipe

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# O todo junto
php artisan migrate:fresh --seed
```

**Resetear solo seeders (mantener estructura)**:
```bash
# Limpiar tablas específicas
php artisan tinker
>>> DB::table('model_has_roles')->truncate();
>>> User::truncate();
>>> Employee::truncate();

# Ejecutar seeders nuevamente
php artisan db:seed
```

---

## 9. Plan de Implementación

### FASE 1: Prerequisitos (1-2 horas)

- [ ] **Paso 1.1**: Verificar existencia de DepartmentSeeder
  - Si no existe, crear DepartmentSeeder
  - Ejecutar y validar

- [ ] **Paso 1.2**: Verificar existencia de AreaSeeder
  - Si no existe, crear AreaSeeder
  - Ejecutar y validar que se creen al menos 4 áreas

- [ ] **Paso 1.3**: Completar modelo Employee (si no está hecho)
  - Referirse a Spec 11
  - Agregar fillable, casts, relaciones

### FASE 2: Crear UserSeeder (2-3 horas)

- [ ] **Paso 2.1**: Crear archivo UserSeeder.php
  - Copiar template del spec
  - Implementar createAdminUsers()
  - Implementar createProductionUsers()

- [ ] **Paso 2.2**: Testing local
  - Ejecutar `php artisan db:seed --class=UserSeeder`
  - Verificar con tinker: `User::with('roles')->get()`
  - Confirmar 106 usuarios (6 admin + 100 production)

- [ ] **Paso 2.3**: Validar asignación de roles
  - Verificar cada rol administrativo tiene 1 usuario
  - Verificar rol Production tiene 100 usuarios

### FASE 3: Actualizar EmployeeSeeder (2-3 horas)

- [ ] **Paso 3.1**: Actualizar EmployeeSeeder.php
  - Copiar template del spec
  - Implementar creación de 50 employees para First Shift
  - Implementar creación de 50 employees para Second Shift

- [ ] **Paso 3.2**: Testing local
  - Ejecutar `php artisan db:seed --class=EmployeeSeeder`
  - Verificar con tinker: `Employee::with(['shift', 'area'])->get()`
  - Confirmar 100 empleados (50 por turno)

- [ ] **Paso 3.3**: Validar distribución
  - Verificar distribución equitativa por áreas
  - Verificar todos tienen shift_id y area_id

### FASE 4: Integración en DatabaseSeeder (1 hora)

- [ ] **Paso 4.1**: Actualizar DatabaseSeeder.php
  - Agregar DepartmentSeeder (si se creó)
  - Agregar AreaSeeder (si se creó)
  - Agregar UserSeeder
  - Verificar orden correcto de ejecución

- [ ] **Paso 4.2**: Ejecutar seeding completo
  - `php artisan migrate:fresh --seed`
  - Verificar que no hay errores
  - Verificar output de cada seeder

### FASE 5: Validación Completa (1-2 horas)

- [ ] **Paso 5.1**: Verificación manual con Tinker
  - Ejecutar todos los comandos de la sección 7.2
  - Verificar conteos correctos
  - Verificar relaciones funcionan

- [ ] **Paso 5.2**: Crear tests automatizados
  - Crear SeedersTest.php
  - Implementar tests de la sección 7.3
  - Ejecutar `php artisan test --filter SeedersTest`

- [ ] **Paso 5.3**: Documentar credenciales
  - Crear lista de credenciales de prueba
  - Compartir con el equipo

### FASE 6: Cleanup y Documentación (1 hora)

- [ ] **Paso 6.1**: Revisar código
  - Asegurar idempotencia (firstOrCreate)
  - Asegurar verificación de prerequisitos
  - Agregar comentarios donde sea necesario

- [ ] **Paso 6.2**: Actualizar README (opcional)
  - Documentar cómo ejecutar seeders
  - Documentar credenciales de prueba
  - Documentar comandos de validación

---

## 10. Credenciales de Prueba

### 10.1 Usuarios Administrativos

| Rol | Email | Account | Password |
|-----|-------|---------|----------|
| Admin | admin@flexcon.test | admin | password |
| HR | hr@flexcon.test | hr001 | password |
| Maintenance | maintenance@flexcon.test | maint001 | password |
| Shipping | shipping@flexcon.test | ship001 | password |
| Warehouse | warehouse@flexcon.test | wh001 | password |
| Materials | materials@flexcon.test | mat001 | password |

### 10.2 Usuarios de Producción

**First Shift (50 usuarios)**:
```
Email: prod.s1.001@flexcon.test hasta prod.s1.050@flexcon.test
Account: prod_s1_001 hasta prod_s1_050
Password: password (todos)
```

**Second Shift (50 usuarios)**:
```
Email: prod.s2.001@flexcon.test hasta prod.s2.050@flexcon.test
Account: prod_s2_001 hasta prod_s2_050
Password: password (todos)
```

### 10.3 Empleados

**First Shift (50 empleados)**:
```
Email: emp.s1.001@flexcon.test hasta emp.s1.050@flexcon.test
Number: EMP-S1-001 hasta EMP-S1-050
Password: password (todos)
```

**Second Shift (50 empleados)**:
```
Email: emp.s2.001@flexcon.test hasta emp.s2.050@flexcon.test
Number: EMP-S2-001 hasta EMP-S2-050
Password: password (todos)
```

---

## 11. Diagrama de Relaciones

```
┌──────────────────────────────────────────────────────────┐
│                    ESTRUCTURA DE DATOS                    │
└──────────────────────────────────────────────────────────┘

┌─────────────────────┐
│       Roles         │
│    (Spatie)         │
├─────────────────────┤
│ - Admin (1 user)    │──────┐
│ - HR (1 user)       │      │
│ - Maintenance (1)   │      │
│ - Production (100)  │      │  assignRole()
│ - Shipping (1)      │      │
│ - Warehouse (1)     │      │
│ - Materials (1)     │      │
└─────────────────────┘      │
                              ▼
                    ┌─────────────────────┐
                    │       Users         │
                    │   (106 total)       │
                    ├─────────────────────┤
                    │ - 6 administrativos │
                    │ - 100 production    │
                    │   * 50 para S1      │
                    │   * 50 para S2      │
                    └─────────────────────┘


┌─────────────────────┐         ┌─────────────────────┐
│      Shifts         │1      N │     Employees       │
├─────────────────────┤◄────────┤─────────────────────┤
│ - First Shift       │         │ - 50 en First Shift │
│ - Second Shift      │         │ - 50 en Second Shift│
│ - Third Shift       │         │                     │
│ - Day Shift         │         │ shift_id (FK)       │
└─────────────────────┘         │ area_id (FK)        │
                                └─────────────────────┘
                                          ▲
                                          │
                                          │
                                ┌─────────┴──────────┐
                                │       Areas        │1
                                ├────────────────────┤
                                │ - Assembly Line A  │
                                │ - Assembly Line B  │
                                │ - Quality Control  │
                                │ - Packaging        │
                                └────────────────────┘
                                          ▲
                                          │
                                          │
                                ┌─────────┴──────────┐
                                │    Departments     │1
                                ├────────────────────┤
                                │ - Production       │
                                │ - Quality Control  │
                                │ - Warehouse        │
                                │ - Maintenance      │
                                └────────────────────┘
```

---

## 12. Consideraciones Finales

### 12.1 Diferencia entre Users y Employees

**IMPORTANTE**: En este sistema existen DOS entidades separadas:

1. **Users**:
   - Para autenticación y autorización
   - Tienen roles de Spatie Permission
   - NO tienen relación con Shifts
   - Se usan para acceso al sistema admin

2. **Employees**:
   - Para gestión de recursos humanos
   - Asignados a Shifts y Areas
   - Tienen email y password (pueden autenticarse)
   - Se usan para tracking de producción

**Relación User ↔ Employee**:
- Actualmente NO existe relación directa
- Cada entidad existe independientemente
- En el futuro podría agregarse un campo `user_id` en Employee

### 12.2 Escalabilidad

**Si se necesitan más usuarios**:

```php
// En UserSeeder, cambiar loops:
for ($i = 1; $i <= 100; $i++) { // cambiar a 200, 500, etc.
```

**Si se necesitan más turnos**:

```php
// Agregar Third Shift en EmployeeSeeder:
$thirdShift = Shift::where('name', 'like', '%Third Shift%')->first();

for ($i = 1; $i <= 50; $i++) {
    // Crear empleados para tercer turno
}
```

### 12.3 Seguridad en Producción

**CRÍTICO**: Estos seeders son SOLO para desarrollo/testing.

**En producción**:
- NO ejecutar `php artisan db:seed`
- NO usar passwords genéricos
- Crear usuarios reales uno por uno o importar desde LDAP/AD
- Asignar roles manualmente o mediante proceso de onboarding

### 12.4 Mantenimiento

**Agregar nuevos roles**:
1. Actualizar `RoleSeeder.php`
2. Agregar bloque en `UserSeeder::createAdminUsers()`
3. Ejecutar `php artisan db:seed --class=RoleSeeder`
4. Ejecutar `php artisan db:seed --class=UserSeeder`

**Modificar distribución de empleados**:
1. Actualizar `EmployeeSeeder.php`
2. Modificar loops o lógica de asignación
3. Ejecutar `php artisan migrate:fresh --seed` (CUIDADO: borra todo)

---

## 13. Resumen de Archivos a Crear/Modificar

### Archivos NUEVOS a crear:

**Seeders**:
- `database/seeders/UserSeeder.php` (CREAR)
- `database/seeders/DepartmentSeeder.php` (CREAR si no existe)
- `database/seeders/AreaSeeder.php` (CREAR si no existe)

**Tests**:
- `tests/Feature/SeedersTest.php` (CREAR)

### Archivos EXISTENTES a modificar:

**Seeders**:
- `database/seeders/EmployeeSeeder.php` (ACTUALIZAR - completar implementación)
- `database/seeders/DatabaseSeeder.php` (ACTUALIZAR - agregar nuevos seeders)

**Modelos** (si no están completos):
- `app/Models/Employee.php` (según Spec 11)

---

## 14. Checklist de Implementación

### Prerequisitos
- [ ] Verificar que RoleSeeder existe y funciona
- [ ] Verificar que PermissionSeeder existe y funciona
- [ ] Verificar que ShiftSeeder existe y funciona
- [ ] Crear/verificar DepartmentSeeder
- [ ] Crear/verificar AreaSeeder

### UserSeeder
- [ ] Crear archivo UserSeeder.php
- [ ] Implementar createAdminUsers()
- [ ] Implementar createProductionUsers()
- [ ] Asegurar uso de firstOrCreate
- [ ] Asegurar verificación de prerequisitos
- [ ] Testing: ejecutar seeder
- [ ] Validar: 106 usuarios creados
- [ ] Validar: roles asignados correctamente

### EmployeeSeeder
- [ ] Actualizar EmployeeSeeder.php
- [ ] Implementar creación First Shift (50)
- [ ] Implementar creación Second Shift (50)
- [ ] Implementar distribución por áreas
- [ ] Asegurar uso de firstOrCreate
- [ ] Testing: ejecutar seeder
- [ ] Validar: 100 empleados creados
- [ ] Validar: 50 por turno
- [ ] Validar: distribución equitativa por área

### DatabaseSeeder
- [ ] Agregar DepartmentSeeder al call array
- [ ] Agregar AreaSeeder al call array
- [ ] Agregar UserSeeder al call array
- [ ] Verificar orden correcto de ejecución
- [ ] Testing: migrate:fresh --seed
- [ ] Validar: sin errores

### Tests
- [ ] Crear SeedersTest.php
- [ ] Implementar test de roles con usuarios
- [ ] Implementar test de empleados por turno
- [ ] Implementar test de distribución
- [ ] Ejecutar: php artisan test --filter SeedersTest
- [ ] Todos los tests pasan

### Documentación
- [ ] Documentar credenciales de prueba
- [ ] Documentar comandos de verificación
- [ ] Actualizar README (opcional)
- [ ] Compartir con equipo

---

## 15. Conclusiones

Este spec define la estrategia completa para crear usuarios ficticios con roles de Spatie Permission y empleados asignados a turnos. La implementación está diseñada para:

1. **Cumplir el requerimiento**: 100 usuarios Production (50 por turno) + 1 usuario por cada rol administrativo
2. **Ser realista**: Datos ficticios que simulan un ambiente de producción real
3. **Ser mantenible**: Código limpio, idempotente, con verificaciones
4. **Ser testeable**: Tests automatizados que validan la correcta creación de datos
5. **Ser escalable**: Fácil agregar más usuarios, turnos o roles

**Próximos pasos**:
1. Ejecutar FASE 1 (prerequisitos)
2. Ejecutar FASE 2 (UserSeeder)
3. Ejecutar FASE 3 (EmployeeSeeder)
4. Ejecutar FASE 4 (integración)
5. Ejecutar FASE 5 (validación)

**Coordinación con equipo**:
- Validar que la interpretación del requerimiento es correcta
- Confirmar si se necesita relación User ↔ Employee
- Definir si Employees deben poder autenticarse (guards separados)

---

**Fecha de creación**: 2025-12-29
**Autor**: Claude (Agent Architect)
**Versión**: 1.0
**Estado**: Pendiente de aprobación e implementación
**Spec relacionado**: Spec 11 (Employee CRUD Architecture)
