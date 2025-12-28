<?php

namespace App\Livewire\Admin\Kits;

use App\Models\Kit;
use App\Models\KitIncident;
use Livewire\Component;
use Livewire\Attributes\Layout;

class KitShow extends Component
{
    public Kit $kit;
    
    // Incident form
    public bool $showIncidentForm = false;
    public string $incident_type = '';
    public string $incident_description = '';
    public string $fca_44_reference = '';

    public function mount(Kit $kit): void
    {
        $this->kit = $kit->load(['workOrder.purchaseOrder.part', 'preparedBy', 'releasedBy', 'incidents.resolvedBy']);
    }

    public function validateKit(): void
    {
        $this->kit->update(['validated' => true]);
        session()->flash('message', 'Kit validado correctamente.');
    }

    public function invalidateKit(): void
    {
        $this->kit->update(['validated' => false]);
        session()->flash('message', 'Validación del kit removida.');
    }

    public function markAsReady(): void
    {
        if ($this->kit->canBeReady()) {
            $this->kit->update([
                'status' => Kit::STATUS_READY,
                'prepared_by' => auth()->id(),
            ]);
            session()->flash('message', 'Kit marcado como listo.');
        }
    }

    public function release(): void
    {
        if ($this->kit->canBeReleased()) {
            $this->kit->update([
                'status' => Kit::STATUS_RELEASED,
                'released_by' => auth()->id(),
            ]);
            session()->flash('message', 'Kit liberado correctamente.');
        } else {
            session()->flash('error', 'El kit debe estar validado antes de ser liberado.');
        }
    }

    public function startAssembly(): void
    {
        if ($this->kit->canStartAssembly()) {
            $this->kit->update(['status' => Kit::STATUS_IN_ASSEMBLY]);
            session()->flash('message', 'Kit en ensamble.');
        }
    }

    public function openIncidentForm(): void
    {
        $this->showIncidentForm = true;
        $this->reset(['incident_type', 'incident_description', 'fca_44_reference']);
    }

    public function closeIncidentForm(): void
    {
        $this->showIncidentForm = false;
    }

    public function saveIncident(): void
    {
        $this->validateOnly('incident_type', [
            'incident_type' => 'required|in:' . implode(',', array_keys(KitIncident::getIncidentTypes())),
        ]);
        $this->validateOnly('incident_description', [
            'incident_description' => 'required|string|max:1000',
        ]);

        KitIncident::create([
            'kit_id' => $this->kit->id,
            'incident_type' => $this->incident_type,
            'description' => $this->incident_description,
            'fca_44_reference' => $this->fca_44_reference ?: null,
        ]);

        $this->kit->refresh();
        $this->showIncidentForm = false;
        session()->flash('message', 'Incidencia registrada correctamente.');
    }

    public function resolveIncident(int $incidentId): void
    {
        $incident = KitIncident::find($incidentId);
        if ($incident && !$incident->resolved) {
            $incident->resolve(auth()->id());
            $this->kit->refresh();
            session()->flash('message', 'Incidencia resuelta.');
        }
    }

    public function render()
    {
        return view('livewire.admin.kits.kit-show', [
            'incidentTypes' => KitIncident::getIncidentTypes(),
        ]);
    }
}
