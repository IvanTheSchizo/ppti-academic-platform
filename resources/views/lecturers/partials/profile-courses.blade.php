<div class="space-y-6">
    {{-- Search Filter Khusus Tab Courses (Opsional) --}}
    <form method="GET" action="{{ route('lecturers.profile', $lecturer->id) }}" class="flex gap-4">
        <input type="hidden" name="tab" value="courses">
        <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search courses" class="flex-1" placeholder="Search..." />
    </form>

    {{-- Tabel Daftar Mata Kuliah yang Diampu --}}
    <x-ui.table :headings="['Period', 'Course ID', 'Course Name', 'Class', 'IKADQ', 'Students']">
        {{-- Contoh Dummy Data / Looping Data dari Controller --}}
        @forelse ($courses ?? [] as $course)
            <tr>
                <td>{{ $course->period }}</td>
                <td>{{ $course->course_id }}</td>
                <td>{{ $course->course_name }}</td>
                <td>{{ $course->class }}</td>
                <td>{{ number_format($course->ikadq, 2) }}</td>
                <td>{{ $course->students_count }}</td>
            </tr>
        @empty
            <tr>
                <td>2025 - Odd</td>
                <td>COMP6047</td>
                <td>Algorithm and Programming</td>
                <td>L4BC</td>
                <td>3.80</td>
                <td>18</td>
            </tr>
            <tr>
                <td>2025 - Odd</td>
                <td>COMP6048</td>
                <td>Data Structures</td>
                <td>L4B1</td>
                <td>3.50</td>
                <td>18</td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$courses ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10)" />
        </x-slot:footer>
    </x-ui.table>
</div>