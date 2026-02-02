<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Kit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AuditTrailService
{
    /**
     * Record a creation event.
     */
    public function recordCreate(Model $model, User $user): AuditTrail
    {
        return $this->createAuditEntry($model, $user, 'create', null, $model->getAttributes());
    }

    /**
     * Record an update event.
     */
    public function recordUpdate(Model $model, User $user, array $oldValues, array $newValues): AuditTrail
    {
        return $this->createAuditEntry($model, $user, 'update', $oldValues, $newValues);
    }

    /**
     * Record a deletion attempt.
     */
    public function recordDelete(Model $model, User $user): AuditTrail
    {
        return $this->createAuditEntry($model, $user, 'delete', $model->getAttributes(), null);
    }

    /**
     * Record a status change.
     */
    public function recordStatusChange(Model $model, User $user, string $oldStatus, string $newStatus): AuditTrail
    {
        return $this->createAuditEntry(
            $model,
            $user,
            'status_change',
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );
    }

    /**
     * Record an approval cycle action.
     */
    public function recordApprovalCycle(Kit $kit, User $user, string $action, array $data): AuditTrail
    {
        return $this->createAuditEntry($kit, $user, $action, null, $data);
    }

    /**
     * Get the audit trail for a specific entity.
     */
    public function getAuditTrail(Model $model): Collection
    {
        return AuditTrail::forEntity($model)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Export audit report for a date range.
     */
    public function exportAuditReport(Carbon $startDate, Carbon $endDate, ?string $entityType = null): Collection
    {
        $query = AuditTrail::query()
            ->with(['user', 'auditable'])
            ->forDateRange($startDate, $endDate);

        if ($entityType) {
            $query->where('auditable_type', $entityType);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create an audit trail entry.
     */
    protected function createAuditEntry(
        Model $model,
        User $user,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): AuditTrail {
        return AuditTrail::create([
            'user_id' => $user->id,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
