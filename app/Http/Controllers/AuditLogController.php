<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('admin');

        // 1. Search (Admin name/username, Target Entity, Target ID, Old/New values)
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('target_entity', 'like', "%{$q}%")
                    ->orWhere('target_id', 'like', "%{$q}%")
                    ->orWhere('action_type', 'like', "%{$q}%")
                    ->orWhere('old_value', 'like', "%{$q}%")
                    ->orWhere('new_value', 'like', "%{$q}%")
                    ->orWhereHas('admin', function ($aq) use ($q) {
                        $aq->where('username', 'like', "%{$q}%");
                    });
            });
        }

        // 2. Action Type Filter
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // 3. Target Entity Filter
        if ($request->filled('target_entity')) {
            $query->where('target_entity', $request->input('target_entity'));
        }

        // 4. Dynamic Sorting
        $sort = $request->input('sort', 'timestamp');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'timestamp') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy('id', $direction);
        }

        // 5. Pagination
        $perPage = max((int) $request->input('per_page', 10), 1);
        $auditLogs = $query->paginate($perPage)->withQueryString();

        // 6. Presentation Transformation matching table.blade.php.
        // For updates, display only fields whose values actually changed.
        // For creates/deletes, retain the available full snapshot.
        $auditLogs->getCollection()->transform(function ($log) {
            $oldValue = $log->old_value;
            $newValue = $log->new_value;

            if (strtoupper((string) $log->action_type) === 'UPDATE') {
                [$oldValue, $newValue] = $this->changedValues($log->old_value, $log->new_value);
            }

            return (object) [
                'id'            => $log->id,
                'timestamp'     => $log->created_at ? $log->created_at->format('Y-m-d H:i') : '-',
                'admin'         => $log->admin?->username ?? 'System',
                'action'        => $log->action_type,
                'target_entity' => $log->target_entity,
                'target_id'     => $log->target_id,
                'old_value'     => $this->valueLines($oldValue),
                'new_value'     => $this->valueLines($newValue),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json($auditLogs);
        }

        // Distinct filter options directly from database
        $actionOptions = AuditLog::pluck('action_type')->unique()->sort()->values();
        $adminOptions = Admin::orderBy('username')->get(['id', 'username']);

        return view('audit-logs.index', compact('auditLogs', 'actionOptions', 'adminOptions'));
    }

    public function export()
    {
        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Timestamp',
                'Admin',
                'Action',
                'Target Entity',
                'Target ID',
                'Old Value',
                'New Value',
            ]);

            AuditLog::with('admin')
                ->latest('id')
                ->chunk(200, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                            $log->admin?->username ?? 'System',
                            $log->action_type,
                            $log->target_entity,
                            $log->target_id,
                            $log->old_value,
                            $log->new_value,
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * Return JSON snapshots containing only changed top-level attributes.
     * JSON encoding preserves the existing table's valueLines presentation.
     * Non-JSON legacy values fall back to the original snapshots.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function changedValues(?string $oldValue, ?string $newValue): array
    {
        $old = $this->decodeAuditSnapshot($oldValue);
        $new = $this->decodeAuditSnapshot($newValue);

        if (! is_array($old) || ! is_array($new)) {
            return [$oldValue, $newValue];
        }

        $changedOld = [];
        $changedNew = [];
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($keys as $key) {
            $oldHasKey = array_key_exists($key, $old);
            $newHasKey = array_key_exists($key, $new);
            $oldItem = $old[$key] ?? null;
            $newItem = $new[$key] ?? null;

            if ($oldHasKey && $newHasKey && is_array($oldItem) && is_array($newItem)) {
                $this->collectChangedFields((string) $key, $oldItem, $newItem, $changedOld, $changedNew);
                continue;
            }

            if ($oldHasKey !== $newHasKey || ($oldHasKey && $newHasKey && $oldItem !== $newItem)) {
                if ($oldHasKey) {
                    $changedOld[$key] = $oldItem;
                }
                if ($newHasKey) {
                    $changedNew[$key] = $newItem;
                }
            }
        }

        return [
            json_encode($changedOld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            json_encode($changedNew, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
        ];
    }

    /** Parse both JSON snapshots and the legacy "student: {...}\nsemester_gpas: [...]" format. */
    private function decodeAuditSnapshot(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $result = [];
        foreach (preg_split('/\r?\n/', $value) as $line) {
            if (! preg_match('/^\s*([a-zA-Z0-9_ -]+):\s*(.*?)\s*$/', $line, $matches)) {
                continue;
            }

            $key = trim($matches[1]);
            $raw = $matches[2];
            $item = json_decode($raw, true);
            $result[$key] = json_last_error() === JSON_ERROR_NONE ? $item : $raw;
        }

        return $result !== [] ? $result : null;
    }

    /** Flatten nested snapshots so the table shows paths such as student.status only. */
    private function collectChangedFields(string $prefix, array $old, array $new, array &$changedOld, array &$changedNew): void
    {
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        foreach ($keys as $key) {
            $oldHasKey = array_key_exists($key, $old);
            $newHasKey = array_key_exists($key, $new);
            $path = $prefix . '.' . $key;
            $oldItem = $old[$key] ?? null;
            $newItem = $new[$key] ?? null;

            if ($oldHasKey && $newHasKey && is_array($oldItem) && is_array($newItem)) {
                $this->collectChangedFields($path, $oldItem, $newItem, $changedOld, $changedNew);
            } elseif ($oldHasKey !== $newHasKey || ($oldHasKey && $newHasKey && $oldItem !== $newItem)) {
                if ($oldHasKey) {
                    $changedOld[$path] = $oldItem;
                }
                if ($newHasKey) {
                    $changedNew[$path] = $newItem;
                }
            }
        }
    }

    /**
     * Turn a stored JSON (or plain) value into display lines such as "status: active".
     *
     * @return list<string>
     */
    private function valueLines(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [$value];
        }

        $lines = [];

        foreach ($decoded as $key => $item) {
            $lines[] = $key . ': ' . (is_scalar($item) || $item === null ? (string) $item : json_encode($item));
        }

        return $lines;
    }
}