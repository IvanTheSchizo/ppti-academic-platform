<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a paginated listing of students.
     */
    public function index(Request $request)
    {
        // 1. Calculate Stat Bar Metrics
        $stats = [
            'total' => Student::count(),
            'active' => Student::where('status', 'Active')->count(),
            'graduated' => Student::where('status', 'Graduated')->count(),
            'on_leave' => Student::where('status', 'On Leave')->count(),
        ];

        // 2. Base Query with Relationships
        $query = Student::with([
            'batch',
            'enrollments.classGroup.courseRecord.course',
        ]);

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

            $query->whereHas('batch', function ($q) use ($batchName) {
                $q->where('batch_name', $batchName);
            });
        }

        // 5. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 6. Class Code Filter
        //
        // A student can now have multiple class enrollments,
        // so we search through the student's enrollments.
        if ($request->filled('class')) {
            $classCode = $request->input('class');

            $query->whereHas('enrollments.classGroup', function ($q) use ($classCode) {
                $q->where('class_code', $classCode);
            });
        }

        // 7. GPA Range Filters
        if ($request->filled('gpa_min') && is_numeric($request->input('gpa_min'))) {
            $query->where(
                'cumulative_gpa',
                '>=',
                (float) $request->input('gpa_min')
            );
        }

        if ($request->filled('gpa_max') && is_numeric($request->input('gpa_max'))) {
            $query->where(
                'cumulative_gpa',
                '<=',
                (float) $request->input('gpa_max')
            );
        }

        // 8. Sorting
        $sortColumn = $request->input('sort', 'name');

        $direction = strtolower(
            $request->input('direction', 'asc')
        ) === 'desc'
            ? 'desc'
            : 'asc';

        if ($sortColumn === 'batch') {

            // Students now have batch_id directly.
            $query->join(
                'batches',
                'students.batch_id',
                '=',
                'batches.id'
            )
            ->select('students.*')
            ->orderBy('batches.id', $direction);

        } elseif ($sortColumn === 'class') {

            // A student can have multiple class enrollments.
            // Use the first/lowest class code for sorting instead
            // of duplicating students through a normal JOIN.
            $query->orderBy(
                \App\Models\Enrollment::select('class_groups.class_code')
                    ->join(
                        'class_groups',
                        'enrollments.class_group_id',
                        '=',
                        'class_groups.id'
                    )
                    ->whereColumn(
                        'enrollments.student_id',
                        'students.id'
                    )
                    ->orderBy('class_groups.class_code')
                    ->limit(1),
                $direction
            );

        } elseif ($sortColumn === 'gpa') {

            $query->orderBy('cumulative_gpa', $direction);

        } elseif (in_array($sortColumn, ['nim', 'name', 'status'])) {

            $query->orderBy($sortColumn, $direction);

        } else {

            $query->latest();
        }

        // 9. Pagination
        $perPage = max(
            (int) $request->input('per_page', 10),
            1
        );

        $students = $query
            ->paginate($perPage)
            ->withQueryString();

        // Respond with JSON if requested via API
        if ($request->wantsJson()) {
            return response()->json($students);
        }

        // 10. Dynamic Batch Options
        $batches = Batch::all()
            ->sortBy(function ($batch) {
                return (int) preg_replace(
                    '/\D/',
                    '',
                    $batch->batch_name
                );
            }, SORT_NUMERIC)
            ->values();

        // 11. Dynamic Class Options
        //
        // Classes are now course sections, so they come from
        // ClassGroup independently of Batch.
        $classes = ClassGroup::query()
            ->pluck('class_code')
            ->unique()
            ->sort()
            ->values();

        return view(
            'students.index',
            compact(
                'students',
                'stats',
                'batches',
                'classes'
            )
        );
    }

    /**
     * Export students as CSV.
     */
    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' =>
                'attachment; filename="students_' .
                date('Y-m-d') .
                '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Batch',
                'Student ID',
                'Name',
                'Class',
                'Status',
                'GPA',
            ]);

            Student::with([
                'batch',
                'enrollments.classGroup',
            ])->chunk(200, function ($students) use ($file) {

                foreach ($students as $student) {

                    $classes = $student->enrollments
                        ->map(function ($enrollment) {
                            return $enrollment->classGroup?->class_code;
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->implode(', ');

                    fputcsv($file, [
                        $student->batch?->batch_name ?? '-',
                        $student->nim,
                        $student->name,
                        $classes ?: '-',
                        $student->status,
                        $student->cumulative_gpa,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|unique:students,nim',
            'name' => 'required|string|max:255',
            'batch_id' => 'required|exists:batches,id',
            'status' => 'required|in:Active,Graduated,On Leave',
            'cumulative_gpa' => 'nullable|numeric|between:0.00,4.00',
        ]);

        $student = Student::create($validated);

        return response()->json(
            $student->load('batch'),
            201
        );
    }

    /**
     * Display a single student.
     */
    public function show(Student $student)
    {
        return response()->json(
            $student->load([
                'batch',
                'enrollments.classGroup.courseRecord.course',
            ])
        );
    }

    /**
     * Update a student.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nim' => 'sometimes|unique:students,nim,' . $student->id,
            'name' => 'sometimes|string|max:255',
            'batch_id' => 'sometimes|exists:batches,id',
            'status' => 'sometimes|in:Active,Graduated,On Leave',
            'cumulative_gpa' => 'nullable|numeric|between:0.00,4.00',
        ]);

        $student->update($validated);

        return response()->json(
            $student->load('batch')
        );
    }

    /**
     * Delete a student.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(null, 204);
    }
}