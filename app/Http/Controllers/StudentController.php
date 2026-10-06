<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a paginated listing of students (View or JSON).
     */
    public function index(Request $request)
    {
            // 1. Calculate Stat Bar Metrics
        $stats = [
            'total'     => Student::count(),
            'active'    => Student::where('status', 'Active')->count(),
            'graduated' => Student::where('status', 'Graduated')->count(),
            'on_leave'  => Student::where('status', 'On Leave')->count(),
        ];

        // 2. Base Query with Relationships Eager Loaded
        $query = Student::with(['classGroup.batch']);

        // 3. Search (Name or NIM)
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('nim', 'like', "%{$q}%");
            });
        }

        // 4. Batch Filter
        if ($request->filled('batch')) {
            $batchName = $request->input('batch');
            $query->whereHas('classGroup.batch', function ($q) use ($batchName) {
                $q->where('batch_name', $batchName);
            });
        }

        // 5. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 6. Class Code Filter
        if ($request->filled('class')) {
            $classCode = $request->input('class');
            $query->whereHas('classGroup', function ($q) use ($classCode) {
                $q->where('class_code', $classCode);
            });
        }

        // 7. GPA Range Filters
        if ($request->filled('gpa_min') && is_numeric($request->input('gpa_min'))) {
            $query->where('cumulative_gpa', '>=', (float) $request->input('gpa_min'));
        }
        if ($request->filled('gpa_max') && is_numeric($request->input('gpa_max'))) {
            $query->where('cumulative_gpa', '<=', (float) $request->input('gpa_max'));
        }

// 8. Sorting
        $sortColumn = $request->input('sort', 'name');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortColumn === 'batch') {
            // Join class_groups and batches so we can sort students by batch number
            $query->join('class_groups', 'students.class_id', '=', 'class_groups.id')
                  ->join('batches', 'class_groups.batch_id', '=', 'batches.id')
                  ->select('students.*')
                  ->orderBy('batches.id', $direction);
        } elseif ($sortColumn === 'class') {
            $query->join('class_groups', 'students.class_id', '=', 'class_groups.id')
                  ->select('students.*')
                  ->orderBy('class_groups.class_code', $direction);
        } elseif ($sortColumn === 'gpa') {
            $query->orderBy('cumulative_gpa', $direction);
        } elseif (in_array($sortColumn, ['nim', 'name', 'status'])) {
            $query->orderBy($sortColumn, $direction);
        } else {
            $query->latest();
        }

        // 9. Paginate and Retain Active Query String
        $perPage = max((int) $request->input('per_page', 10), 1);
        $students = $query->paginate($perPage)->withQueryString();

        // Respond with JSON if requested via API
        if ($request->wantsJson()) {
            return response()->json($students);
        }

        // Dynamic Options for Form Dropdowns (1 -> 25)
        $batches = Batch::all()->sortBy(function ($batch) {
            return (int) preg_replace('/\D/', '', $batch->batch_name);
        }, SORT_NUMERIC)->values();

        $classes = ClassGroup::pluck('class_code')->unique()->sort()->values();

        return view('students.index', compact('students', 'stats', 'batches', 'classes'));
    }

    public function export()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Batch', 'Student ID', 'Name', 'Class', 'Status', 'GPA']);

            Student::with(['classGroup.batch'])->chunk(200, function ($students) use ($file) {
                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->classGroup?->batch?->batch_name ?? '-',
                        $student->nim,
                        $student->name,
                        $student->classGroup?->class_code ?? '-',
                        $student->status,
                        $student->cumulative_gpa,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim'            => 'required|unique:students,nim',
            'name'           => 'required|string|max:255',
            'class_id'       => 'required|exists:class_groups,id',
            'status'         => 'required|in:Active,Graduated,On Leave',
            'cumulative_gpa' => 'nullable|numeric|between:0.00,4.00',
        ]);

        $student = Student::create($validated);
        return response()->json($student, 201);
    }

    public function show(Student $student)
    {
        return response()->json($student->load('classGroup.batch'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nim'            => 'sometimes|unique:students,nim,' . $student->id,
            'name'           => 'sometimes|string|max:255',
            'class_id'       => 'sometimes|exists:class_groups,id',
            'status'         => 'sometimes|in:Active,Graduated,On Leave',
            'cumulative_gpa' => 'nullable|numeric|between:0.00,4.00',
        ]);

        $student->update($validated);
        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, 204);
    }
}