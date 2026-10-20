<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Records auditable academic changes for the currently authenticated administrator. */
class AuditLogger
{
    /**
     * Store a change using the existing audit_logs schema. Values are restricted to
     * the supplied snapshots; callers must never pass passwords or credentials.
     */
    public function record(string $action, string $entity, int|string $id, ?array $old, ?array $new): void
    {
        $adminId = Auth::id();
        if (! $adminId) {
            return;
        }

        AuditLog::create([
            'admin_id' => $adminId,
            'action_type' => strtoupper($action),
            'target_entity' => $entity,
            'target_id' => $id,
            'old_value' => $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'new_value' => $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /** Snapshot only explicitly approved model fields. */
    public function snapshot(Model $model, array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = $model->getAttribute($field);
        }
        return $values;
    }
}
