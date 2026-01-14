<?php

namespace Tests\Feature;

use App\Livewire\Admin\Shifts\ShiftList;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftListEmployeesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles necesarios
        Role::create(['name' => 'employee', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        // Autenticar como admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
        ]);
        $admin->assignRole('admin');
        $this->actingAs($admin);
    }

    /**
     * Test: Muestra el conteo correcto de empleados activos
     */
    public function test_displays_correct_employee_count_for_shift_with_active_employees(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Prueba']);

        // Crear 3 empleados activos
        for ($i = 1; $i <= 3; $i++) {
            $employee = User::factory()->create([
                'name' => "Empleado {$i}",
                'shift_id' => $shift->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        Livewire::test(ShiftList::class)
            ->assertSee('Turno Prueba')
            ->assertSee('3 empleados');
    }

    /**
     * Test: Muestra "0 empleados" cuando el turno no tiene empleados
     */
    public function test_displays_zero_employees_when_shift_has_no_employees(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Vacío']);

        Livewire::test(ShiftList::class)
            ->assertSee('Turno Vacío')
            ->assertSee('0 empleados');
    }

    /**
     * Test: Muestra "1 empleado" en singular cuando solo hay uno
     */
    public function test_displays_singular_form_for_one_employee(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Singular']);

        $employee = User::factory()->create([
            'name' => 'Juan',
            'shift_id' => $shift->id,
            'active' => true,
        ]);
        $employee->assignRole('employee');

        Livewire::test(ShiftList::class)
            ->assertSee('Turno Singular')
            ->assertSee('1 empleado')
            ->assertDontSee('1 empleados'); // No debe mostrar plural
    }

    /**
     * Test: Solo cuenta empleados activos, no inactivos
     */
    public function test_only_counts_active_employees(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Mixto']);

        // 2 empleados activos
        for ($i = 1; $i <= 2; $i++) {
            $active = User::factory()->create([
                'name' => "Activo {$i}",
                'shift_id' => $shift->id,
                'active' => true,
            ]);
            $active->assignRole('employee');
        }

        // 3 empleados inactivos (no deben contarse)
        for ($i = 1; $i <= 3; $i++) {
            $inactive = User::factory()->create([
                'name' => "Inactivo {$i}",
                'shift_id' => $shift->id,
                'active' => false,
            ]);
            $inactive->assignRole('employee');
        }

        Livewire::test(ShiftList::class)
            ->assertSee('2 empleados') // Solo los activos
            ->assertDontSee('5 empleados'); // No debe contar inactivos
    }

    /**
     * Test: Solo cuenta usuarios con rol 'employee', no otros roles
     */
    public function test_only_counts_users_with_employee_role(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Roles']);

        // 2 usuarios con rol employee
        for ($i = 1; $i <= 2; $i++) {
            $employee = User::factory()->create([
                'name' => "Empleado {$i}",
                'shift_id' => $shift->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        // 1 usuario con rol admin (no debe contarse)
        $admin = User::factory()->create([
            'name' => 'Admin Turno',
            'shift_id' => $shift->id,
            'active' => true,
        ]);
        $admin->assignRole('admin');

        Livewire::test(ShiftList::class)
            ->assertSee('2 empleados') // Solo los employee
            ->assertDontSee('3 empleados'); // No debe contar admin
    }

    /**
     * Test: No cuenta empleados soft deleted
     */
    public function test_does_not_count_soft_deleted_employees(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Deleted']);

        // 2 empleados activos
        $employee1 = User::factory()->create([
            'name' => 'Empleado 1',
            'shift_id' => $shift->id,
            'active' => true,
        ]);
        $employee1->assignRole('employee');

        $employee2 = User::factory()->create([
            'name' => 'Empleado 2',
            'shift_id' => $shift->id,
            'active' => true,
        ]);
        $employee2->assignRole('employee');

        // Soft delete del segundo empleado
        $employee2->delete();

        Livewire::test(ShiftList::class)
            ->assertSee('1 empleado') // Solo el no eliminado
            ->assertDontSee('2 empleados');
    }

    /**
     * Test: Funciona correctamente con búsqueda
     */
    public function test_employee_count_works_with_search(): void
    {
        $shift1 = Shift::factory()->create(['name' => 'Turno Mañana']);
        $shift2 = Shift::factory()->create(['name' => 'Turno Tarde']);

        // Turno Mañana: 5 empleados
        for ($i = 1; $i <= 5; $i++) {
            $employee = User::factory()->create([
                'shift_id' => $shift1->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        // Turno Tarde: 3 empleados
        for ($i = 1; $i <= 3; $i++) {
            $employee = User::factory()->create([
                'shift_id' => $shift2->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        Livewire::test(ShiftList::class)
            ->set('search', 'Mañana')
            ->assertSee('Turno Mañana')
            ->assertSee('5 empleados')
            ->assertDontSee('Turno Tarde');
    }

    /**
     * Test: Funciona correctamente con paginación
     */
    public function test_employee_count_works_with_pagination(): void
    {
        // Crear 15 turnos con diferentes cantidades de empleados
        for ($s = 1; $s <= 15; $s++) {
            $shift = Shift::factory()->create(['name' => "Turno {$s}"]);

            // Cada turno tiene $s empleados
            for ($e = 1; $e <= $s; $e++) {
                $employee = User::factory()->create([
                    'shift_id' => $shift->id,
                    'active' => true,
                ]);
                $employee->assignRole('employee');
            }
        }

        // Primera página (por defecto 10 items)
        Livewire::test(ShiftList::class)
            ->assertSee('Turno 1')
            ->assertSee('1 empleado')
            ->assertSee('Turno 10')
            ->assertSee('10 empleados');

        // Verificar que tiene paginación
        $totalShifts = Shift::count();
        $this->assertGreaterThan(10, $totalShifts, 'Debe haber más de 10 turnos para probar paginación');
    }

    /**
     * Test: Funciona correctamente con ordenamiento
     */
    public function test_employee_count_works_with_sorting(): void
    {
        $shiftA = Shift::factory()->create(['name' => 'Turno A']);
        $shiftZ = Shift::factory()->create(['name' => 'Turno Z']);

        // Turno A: 5 empleados
        for ($i = 1; $i <= 5; $i++) {
            $employee = User::factory()->create([
                'shift_id' => $shiftA->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        // Turno Z: 3 empleados
        for ($i = 1; $i <= 3; $i++) {
            $employee = User::factory()->create([
                'shift_id' => $shiftZ->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        // Verificar que ambos turnos se muestran con sus conteos
        Livewire::test(ShiftList::class)
            ->assertSee('Turno A')
            ->assertSee('5 empleados')
            ->assertSee('Turno Z')
            ->assertSee('3 empleados');

        // Verificar que el ordenamiento cambia correctamente
        $component = Livewire::test(ShiftList::class);

        $initialDirection = $component->get('sortDirection');
        $component->call('sortBy', 'name');
        $newDirection = $component->get('sortDirection');

        // El ordenamiento debe cambiar o establecerse
        $this->assertEquals('name', $component->get('sortField'));
        $this->assertNotNull($newDirection);
        $this->assertContains($newDirection, ['asc', 'desc']);
    }

    /**
     * Test: Usa withCount() para optimización (previene N+1 queries)
     */
    public function test_uses_with_count_to_prevent_n_plus_one_queries(): void
    {
        // Crear 10 turnos con empleados
        for ($s = 1; $s <= 10; $s++) {
            $shift = Shift::factory()->create(['name' => "Turno {$s}"]);

            for ($e = 1; $e <= 3; $e++) {
                $employee = User::factory()->create([
                    'shift_id' => $shift->id,
                    'active' => true,
                ]);
                $employee->assignRole('employee');
            }
        }

        // Contar queries
        DB::enableQueryLog();

        Livewire::test(ShiftList::class);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Debe haber menos de 15 queries totales
        // (1 query principal de shifts con withCount + queries de Livewire/Auth/Roles)
        // Sin withCount() habría ~11+ queries (1 + 10 turnos)
        $this->assertLessThan(15, count($queries),
            'Demasiadas queries ejecutadas. Posible problema de N+1');
    }

    /**
     * Test: La estadística del card muestra el total correcto
     */
    public function test_total_assigned_employees_statistic_is_correct(): void
    {
        // Turno 1: 5 empleados activos
        $shift1 = Shift::factory()->create(['name' => 'Turno 1']);
        for ($i = 1; $i <= 5; $i++) {
            $employee = User::factory()->create([
                'shift_id' => $shift1->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        // Turno 2: 3 empleados activos
        $shift2 = Shift::factory()->create(['name' => 'Turno 2']);
        for ($i = 1; $i <= 3; $i++) {
            $employee = User::factory()->create([
                'shift_id' => $shift2->id,
                'active' => true,
            ]);
            $employee->assignRole('employee');
        }

        // Empleado sin turno (no debe contarse)
        $noShift = User::factory()->create([
            'shift_id' => null,
            'active' => true,
        ]);
        $noShift->assignRole('employee');

        // Empleado inactivo (no debe contarse)
        $inactive = User::factory()->create([
            'shift_id' => $shift1->id,
            'active' => false,
        ]);
        $inactive->assignRole('employee');

        // Verificar que el total es correcto (5 + 3 = 8)
        $totalAssigned = User::role('employee')
            ->whereNotNull('shift_id')
            ->active()
            ->count();

        $this->assertEquals(8, $totalAssigned);

        // Verificar en la vista
        Livewire::test(ShiftList::class)
            ->assertSee('Empleados Asignados')
            ->assertSee('8');
    }

    /**
     * Test: Badge tiene el estilo correcto y muestra el conteo
     */
    public function test_badge_has_correct_styling(): void
    {
        $shift = Shift::factory()->create(['name' => 'Turno Test']);

        $employee = User::factory()->create([
            'shift_id' => $shift->id,
            'active' => true,
        ]);
        $employee->assignRole('employee');

        // Verificar que el turno tiene el conteo correcto
        $shiftWithCount = Shift::withCount('employees')->find($shift->id);
        $this->assertEquals(1, $shiftWithCount->employees_count);

        // Verificar que el badge se renderiza correctamente en la vista
        Livewire::test(ShiftList::class)
            ->assertSee('Turno Test')
            ->assertSee('1 empleado')
            ->assertSee('bg-blue-100'); // Clase CSS del badge
    }

    /**
     * Test: Conteo correcto con múltiples turnos y distribución variada
     */
    public function test_correct_count_with_multiple_shifts_varied_distribution(): void
    {
        // Turno sin empleados
        $shiftEmpty = Shift::factory()->create(['name' => 'Turno Vacío']);

        // Turno con 1 empleado
        $shiftOne = Shift::factory()->create(['name' => 'Turno Uno']);
        $emp1 = User::factory()->create([
            'shift_id' => $shiftOne->id,
            'active' => true,
        ]);
        $emp1->assignRole('employee');

        // Turno con muchos empleados
        $shiftMany = Shift::factory()->create(['name' => 'Turno Muchos']);
        for ($i = 1; $i <= 20; $i++) {
            $emp = User::factory()->create([
                'shift_id' => $shiftMany->id,
                'active' => true,
            ]);
            $emp->assignRole('employee');
        }

        Livewire::test(ShiftList::class)
            ->assertSee('Turno Vacío')
            ->assertSee('0 empleados')
            ->assertSee('Turno Uno')
            ->assertSee('1 empleado')
            ->assertSee('Turno Muchos')
            ->assertSee('20 empleados');
    }
}
