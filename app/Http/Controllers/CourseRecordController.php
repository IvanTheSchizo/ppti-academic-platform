<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\Period;
use Illuminate\Http\Request;

class CourseRecordController extends Controller
{
    /**
     * Display a listing of course records with search, filter, and sort.
     */
    public function index(Request $request)
    {
       $query = CourseRecord::query()
            ->select('course_records.*')
            ->withCount('studentGrades')
            ->with([
                'course',
                'lecturer',
                'period',
                'classGroup',
            ]);

        // 1. Search Query (Record Code, Course, Lecturer, or Class)
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('record_code', 'like', "%{$q}%")
                    ->orWhereHas('course', function ($cq) use ($q) {
                        $cq->where('course_code', 'like', "%{$q}%")
                           ->orWhere('course_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('lecturer', function ($lq) use ($q) {
                        $lq->where('lecturer_code', 'like', "%{$q}%")
                           ->orWhere('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('classGroup', function ($cgq) use ($q) {
                        $cgq->where('class_code', 'like', "%{$q}%");
                    });
            });
        }

        // 2. Period Filter
        if ($request->filled('period')) {
            $periodVal = $request->input('period');
            $query->whereHas('period', function ($pq) use ($periodVal) {
                $pq->where('period_name', $periodVal)
                   ->orWhereRaw("CONCAT(academic_year, ' - ', semester) = ?", [$periodVal]);
            });
        }

        // 3. Class Group Filter
        if ($request->filled('class')) {
            $classVal = $request->input('class');
            $query->whereHas('classGroup', function ($cgq) use ($classVal) {
                $cgq->where('class_code', $classVal);
            });
        }

        // 4. Lecturer Filter
        if ($request->filled('lecturer_code')) {
            $lecturerCode = $request->input('lecturer_code');
            $query->whereHas('lecturer', function ($lq) use ($lecturerCode) {
                $lq->where('lecturer_code', $lecturerCode);
            });
        }

        // 5. Student Count Range Filter ('high' >= 20, 'low' < 20)
        if ($request->filled('range')) {
            if ($request->input('range') === 'high') {
                $query->has('studentGrades', '>=', 20);
            } elseif ($request->input('range') === 'low') {
                $query->has('studentGrades', '<', 20);
            }
        }

        // 6. Dynamic Sorting
        $sort = $request->input('sort', 'record_code');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'period') {
            $query->join('periods', 'course_records.period_id', '=', 'periods.id')
                  ->orderBy('periods.period_name', $direction);
        } elseif ($sort === 'course_id') {
            $query->join('courses', 'course_records.course_id', '=', 'courses.id')
                  ->orderBy('courses.course_code', $direction);
        } elseif ($sort === 'class') {
            $query->join('class_groups', 'course_records.class_id', '=', 'class_groups.id')
                  ->orderBy('class_groups.class_code', $direction);
        } elseif ($sort === 'lecturer_code') {
            $query->join('lecturers', 'course_records.lecturer_id', '=', 'lecturers.id')
                  ->orderBy('lecturers.lecturer_code', $direction);
        } elseif ($sort === 'students') {
            $query->orderBy('student_grades_count', $direction);
        } else {
            $query->orderBy('course_records.record_code', $direction);
        }

        // 7. Paginate & Preserve Query String
        $perPage = max((int) $request->input('per_page', 10), 1);
        $courseRecords = $query->paginate($perPage)->withQueryString();

        // Transform for presentation
        $courseRecords->getCollection()->transform(function ($record) {
            $periodDisplay = $record->period
                ? ($record->period->period_name ?? "{$record->period->academic_year} - {$record->period->semester}")
                : '-';

            return (object) [
                'id'             => $record->id,
                'record_code'    => $record->record_code,
                'period'         => $periodDisplay,
                'course_id'      => $record->course?->course_code ?? '-',
                'class'          => $record->classGroup?->class_code ?? '-',
                'lecturer_code'  => $record->lecturer?->lecturer_code ?? '-',
                'students_count' => $record->student_grades_count,
            ];
        });

        if ($request->wantsJson()) {
            return response()->json($courseRecords);
        }

        // 8. Dynamic Filter Options directly from DB
        $periodOptions = Period::all()->map(function ($p) {
            return $p->period_name ?? "{$p->academic_year} - {$p->semester}";
        })->unique()->sort()->values();

        $classOptions = ClassGroup::pluck('class_code')->unique()->sort()->values();
        $lecturerOptions = Lecturer::pluck('lecturer_code')->unique()->sort()->values();

        return view('course-records.index', compact(
            'courseRecords',
            'periodOptions',
            'classOptions',
            'lecturerOptions'
        ));
    }

    /**
     * Export course records to CSV.
     */
    public function export()
    {
        $filename = 'course_records_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Record Code',
                'Period',
                'Course Code',
                'Course Name',
                'Class',
                'Lecturer Code',
                'Lecturer Name',
            ]);

            CourseRecord::with(['course', 'lecturer', 'period', 'classGroup'])
                ->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $record) {
                        fputcsv($handle, [
                            $record->record_code,
                            $record->period?->period_name ?? "{$record->period?->academic_year} {$record->period?->semester}",
                            $record->course?->course_code ?? '-',
                            $record->course?->course_name ?? '-',
                            $record->classGroup?->class_code ?? '-',
                            $record->lecturer?->lecturer_code ?? '-',
                            $record->lecturer?->name ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}