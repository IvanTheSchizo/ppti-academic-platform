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

        // 6. Presentation Transformation matching table.blade.php
        $auditLogs->getCollection()->transform(function ($log) {
            return (object) [
                'id'            => $log->id,
                'timestamp'     => $log->created_at ? $log->created_at->format('Y-m-d H:i') : '-',
                'admin'         => $log->admin?->username ?? 'System',
                'action'        => $log->action_type,
                'target_entity' => $log->target_entity,
                'target_id'     => $log->target_id,
                'old_value'     => $this->valueLines($log->old_value),
                'new_value'     => $this->valueLines($log->new_value),
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