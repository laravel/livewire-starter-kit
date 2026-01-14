<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use App\Models\User;
use Livewire\Component;

class ShiftShow extends Component
{
    public Shift $shift;

    /**
     * Estadisticas globales de empleados
     */
    public array $globalStats = [];

    /**
     * Estadisticas de empleados del turno
     */
    public array $shiftStats = [];

    public function mount(Shift $shift): void
    {
        // Cargar shift con relaciones necesarias
        $this->shift = $shift->load(['BreakTimes', 'allEmployees']);

        // Calcular estadisticas
        $this->calculateStats();
    }

    /**
     * Calcula todas las estadisticas de empleados
     *
     * NOTA: Si no hay empleados en el sistema o en el turno,
     * los valores se establecen como null para mostrar "N/A" en la vista.
     */
    protected function calculateStats(): void
    {
        // Verificar si hay empleados en el sistema
        $totalEmployees = User::employees()->count();

        if ($totalEmployees === 0) {
            // No hay empleados en el sistema - mostrar N/A
            $this->globalStats = [
                'total_active' => null,   // Se mostrara como N/A en la vista
                'total_inactive' => null,
                'total_all' => null,
            ];
        } else {
            // Estadisticas globales del sistema
            $this->globalStats = [
                'total_active' => User::employees()->active()->count(),
                'total_inactive' => User::employees()->inactive()->count(),
                'total_all' => $totalEmployees,
            ];
        }

        // Verificar si hay empleados en este turno
        $shiftTotalEmployees = $this->shift->allEmployees()->count();

        if ($shiftTotalEmployees === 0) {
            // No hay empleados en este turno - mostrar N/A
            $this->shiftStats = [
                'total' => null,    // Se mostrara como N/A en la vista
                'active' => null,
                'inactive' => null,
            ];
        } else {
            // Estadisticas del turno actual
            $this->shiftStats = [
                'total' => $shiftTotalEmployees,
                'active' => $this->shift->employees()->count(),
                'inactive' => $this->shift->allEmployees()
                                          ->where('active', false)
                                          ->count(),
            ];
        }
    }

    /**
     * Refrescar estadisticas (util si se implementa actualizacion en tiempo real)
     */
    public function refreshStats(): void
    {
        $this->calculateStats();
    }

    public function render()
    {
        return view('livewire.admin.shifts.shift-show');
    }
}
