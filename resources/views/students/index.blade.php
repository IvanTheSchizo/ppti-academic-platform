@extends('layouts.app')

@section('title', 'Student List')

@php
    // Placeholder data: replace this block with a paginator from the controller.
    $all = collect([
        ['PPTI23', '2201581234', 'Ayu Wijaya', 'Active', '1A', 3.72],
        ['PPTI23', '2201581235', 'Budi Santoso', 'Active', '1A', 3.45],
        ['PPTI22', '2201581236', 'Citra Dewi', 'Graduated', '1B', 3.90],
        ['PPTI24', '2201581237', 'Dimas Prasetyo', 'Active', '1A', 3.10],
        ['PPTI24', '2201581238', 'Eka Putri', 'Active', '1B', 3.55],
        ['PPTI23', '2201581239', 'Fajar Nugraha', 'Active', '1B', 3.82],
        ['PPTI22', '2201581240', 'Gita Savitri', 'Graduated', '1A', 3.95],
        ['PPTI23', '2201581241', 'Hendra Gunawan', 'On Leave', '1B', 3.40],
        ['PPTI24', '2201581242', 'Indah Permatasari', 'Active', '1A', 3.65],
        ['PPTI22', '2201581243', 'Joko Wibowo', 'Graduated', '1B', 3.78],
    ])->map(fn ($r) => (object) ['batch' => $r[0], 'nim' => $r[1], 'name' => $r[2], 'status' => $r[3], 'class' => $r[4], 'gpa' => $r[5]]);

    $all = $all
        ->when(request()->query('q'), fn ($c, $q) => $c->filter(fn ($s) => str_contains(strtolower("$s->name $s->nim"), strtolower($q))))
        ->when(request()->query('batch'), fn ($c, $v) => $c->where('batch', $v))
        ->when(request()->query('status'), fn ($c, $v) => $c->where('status', $v))
        ->when(request()->query('class'), fn ($c, $v) => $c->where('class', $v))
        ->when(is_numeric(request()->query('gpa_min')), fn ($c) => $c->where('gpa', '>=', (float) request()->query('gpa_min')))
        ->when(is_numeric(request()->query('gpa_max')), fn ($c) => $c->where('gpa', '<=', (float) request()->query('gpa_max')));

    if (in_array(request()->query('sort'), ['batch', 'nim', 'name', 'status', 'gpa'])) {
        $all = $all->sortBy(request()->query('sort'), SORT_REGULAR, request()->query('direction') === 'desc');
    }

    $perPage = max((int) request()->query('per_page', 10), 1);
    $page = max((int) request()->query('page', 1), 1);
    $students = new \Illuminate\Pagination\LengthAwarePaginator($all->forPage($page, $perPage)->values(), $all->count(), $perPage, $page, ['path' => url()->current(), 'query' => request()->query()]);

    $statusColors = ['Active' => 'success', 'Graduated' => 'primary', 'On Leave' => 'danger'];
    $filtersOpen = collect(['batch', 'status', 'class', 'gpa_min', 'gpa_max'])->contains(fn ($key) => filled(request()->query($key)));
@endphp

@section('content')
    <div class="space-y-6">
        <x-ui.page-header title="Student List" divider>
            <x-slot:actions>
                <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="flex flex-wrap items-center gap-x-10 gap-y-2">
            <x-ui.stat label="Total" value="1067" />
            <x-ui.stat label="Active" value="167" color="success" />
            <x-ui.stat label="Graduated" value="567" color="primary" />
            <x-ui.stat label="On Leave" value="67" color="danger" />
        </div>

        <form method="GET" action="{{ route('students.index') }}" data-autosubmit class="space-y-6">
            <div class="flex gap-4">
                <x-form.search-input name="q" :value="request()->query('q')" aria-label="Search students" class="flex-1" />

                <x-ui.button type="button" variant="secondary" data-toggle="#student-filters" aria-controls="student-filters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
                    <x-ui.icon name="filter_alt" /> Filter
                </x-ui.button>
            </div>

            <div id="student-filters" @unless ($filtersOpen) hidden @endunless class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <x-form.label for="batch">Batch</x-form.label>
                    <x-form.select name="batch">
                        <option value="">All</option>
                        @foreach (['PPTI22', 'PPTI23', 'PPTI24'] as $option)
                            <option value="{{ $option }}" @selected(request()->query('batch') === $option)>{{ $option }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <div>
                    <x-form.label for="status">Status</x-form.label>
                    <x-form.select name="status">
                        <option value="">All</option>
                        @foreach (['Active', 'Graduated', 'On Leave'] as $option)
                            <option value="{{ $option }}" @selected(request()->query('status') === $option)>{{ $option }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <div>
                    <x-form.label for="class">Class</x-form.label>
                    <x-form.select name="class">
                        <option value="">All</option>
                        @foreach (['1A', '1B'] as $option)
                            <option value="{{ $option }}" @selected(request()->query('class') === $option)>{{ $option }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <div>
                    <x-form.label>GPA Range</x-form.label>
                    <x-form.range-input name="gpa" :min="request()->query('gpa_min')" :max="request()->query('gpa_max')" />
                </div>
            </div>
        </form>

        <x-ui.table :headings="[
            'Batch',
            ['label' => 'Student ID', 'key' => 'nim'],
            'Name',
            'Status',
            'GPA',
        ]">
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->batch }}</td>
                    <td>{{ $student->nim }}</td>
                    <td>{{ $student->name }}</td>
                    <td><x-ui.badge :color="$statusColors[$student->status] ?? 'muted'">{{ $student->status }}</x-ui.badge></td>
                    <td>{{ number_format($student->gpa, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-ui.empty-state title="No students found" description="Try changing the search or filters." icon="search" />
                    </td>
                </tr>
            @endforelse

            <x-slot:footer>
                <x-ui.pagination :paginator="$students" />
            </x-slot:footer>
        </x-ui.table>
    </div>
@endsection
