<?php

namespace App\Http\Controllers;

use App\Models\CourseRecord;
use App\Models\Lecturer;
use Illuminate\Http\Request;

class LecturerController extends Controller
{
    /**
     * Display a listing of lecturers.
     */
    public function index(Request $request)
    {
        $stats = [
            'total'    => Lecturer::count(),
            'active'   => Lecturer::where('status', 'Active')->count(),
            'inactive' => Lecturer::where('status', 'Inactive')->count(),
        ];

        $query = Lecturer::query();

        // 1. Search Query
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('lecturer_code', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('email_binus_edu', 'like', "%{$q}%")
                    ->orWhere('email_binus_ac_id', 'like', "%{$q}%");
            });
        }

        // 2. Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('jja')) {
            $query->where('jja', $request->input('jja'));
        }

        if ($request->filled('latest_education')) {
            $query->where('latest_education', $request->input('latest_education'));
        }

        // 3. Dynamic Sorting
        $sortable = ['lecturer_code', 'name', 'email_binus_edu', 'status'];
        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $sortable)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        // 4. Pagination (retaining all active query parameters)
        $perPage = max((int) $request->input('per_page', 10), 1);
        $lecturers = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($lecturers);
        }

        // Dropdown option sets for filter drawer
        $jjaOptions = Lecturer::whereNotNull('jja')->pluck('jja')->unique()->sort()->values();
        $educationOptions = Lecturer::whereNotNull('latest_education')->pluck('latest_education')->unique()->sort()->values();

        return view('lecturers.index', compact('lecturers', 'stats', 'jjaOptions', 'educationOptions'));
    }

    /**
     * Display the specified lecturer profile with Overview and Courses tabs.
     */
    public function show(Request $request, Lecturer $lecturer)
    {
        $activeTab = $request->query('tab', 'overview');

        $courses = null;
        if ($activeTab === 'courses') {
            $courseQuery = CourseRecord::where('lecturer_id', $lecturer->id)
                ->with(['course', 'period', 'classGroup.batch', 'lecturerPerformance'])
                ->withCount('studentGrades');

            if ($request->filled('q')) {
                $q = $request->input('q');
                $courseQuery->where(function ($sub) use ($q) {
                    $sub->where('record_code', 'like', "%{$q}%")
                        ->orWhereHas('course', function ($cq) use ($q) {
                            $cq->where('course_name', 'like', "%{$q}%")
                               ->orWhere('course_code', 'like', "%{$q}%");
                        });
                });
            }

            $courses = $courseQuery->paginate(10)->withQueryString();

            // Transform into view presentation objects
            $courses->getCollection()->transform(function ($record) {
                return (object) [
                    'period'         => $record->period ? "{$record->period->academic_year} - {$record->period->semester}" : '-',
                    'course_id'      => $record->course?->course_code ?? '-',
                    'course_name'    => $record->course?->course_name ?? '-',
                    'class'          => $record->classGroup?->class_code ?? '-',
                    'ikadq'          => $record->lecturerPerformance?->ikadq ?? 0,
                    'students_count' => $record->student_grades_count,
                ];
            });
        }

        if ($request->wantsJson()) {
            return response()->json([
                'lecturer' => $lecturer,
                'courses'  => $courses,
            ]);
        }

        return view('lecturers.profile', compact('lecturer', 'activeTab', 'courses'));
    }

    /**
     * Store a newly created lecturer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'               => 'required|string|max:50|unique:lecturers,nip',
            'lecturer_code'     => 'required|string|max:20|unique:lecturers,lecturer_code',
            'name'              => 'required|string|max:255',
            'email_binus_edu'   => 'nullable|email|max:255|unique:lecturers,email_binus_edu',
            'email_binus_ac_id' => 'nullable|email|max:255|unique:lecturers,email_binus_ac_id',
            'phone_number'      => 'nullable|string|max:30',
            'jja'               => 'nullable|string|max:100',
            'latest_education'  => 'nullable|string|max:50',
            'status'            => 'required|string|in:Active,Inactive,On Leave',
        ]);

        $lecturer = Lecturer::create($validated);

        if ($request->wantsJson()) {
            return response()->json($lecturer, 201);
        }

        return redirect()->route('lecturers.index')->with('success', 'Lecturer created successfully.');
    }

    /**
     * Update the specified lecturer.
     */
    public function update(Request $request, Lecturer $lecturer)
    {
        $validated = $request->validate([
            'lecturer_code'     => 'required|string|max:20|unique:lecturers,lecturer_code,' . $lecturer->id,
            'name'              => 'required|string|max:255',
            'latest_education'  => 'nullable|string|max:50',
            'jja'               => 'nullable|string|max:100',
            'status'            => 'required|string|in:Active,Inactive,On Leave',
            'email_binus_edu'   => 'nullable|email|max:255|unique:lecturers,email_binus_edu,' . $lecturer->id,
            'email_binus_ac_id' => 'nullable|email|max:255|unique:lecturers,email_binus_ac_id,' . $lecturer->id,
            'phone_number'      => 'nullable|string|max:30',
        ]);

        $lecturer->update($validated);

        if ($request->wantsJson()) {
            return response()->json($lecturer);
        }

        return redirect()->route('lecturers.profile', $lecturer->id)->with('success', 'Lecturer updated successfully.');
    }

    /**
     * Export lecturers list as CSV.
     */
    public function export()
    {
        $filename = 'lecturers_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'NIP',
                'Lecturer Code',
                'Name',
                'Email (binus.edu)',
                'Email (binus.ac.id)',
                'Phone Number',
                'JJA',
                'Latest Education',
                'Status',
            ]);

            Lecturer::chunk(200, function ($lecturers) use ($handle) {
                foreach ($lecturers as $lecturer) {
                    fputcsv($handle, [
                        $lecturer->nip,
                        $lecturer->lecturer_code,
                        $lecturer->name,
                        $lecturer->email_binus_edu,
                        $lecturer->email_binus_ac_id,
                        $lecturer->phone_number,
                        $lecturer->jja,
                        $lecturer->latest_education,
                        $lecturer->status,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}