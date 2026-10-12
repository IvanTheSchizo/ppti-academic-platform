<div class="space-y-card">
    <x-ui.card title="Academic Info" icon="school">
        <x-ui.detail-list>
            <x-ui.detail label="Student ID">{{ $student->nim }}</x-ui.detail>
            <x-ui.detail label="Batch">{{ $student->classGroup?->batch?->batch_name ?? '-' }}</x-ui.detail>
            <x-ui.detail label="Class">{{ $student->classGroup?->class_code ?? '-' }}</x-ui.detail>
            <x-ui.detail label="Cumulative GPA">{{ number_format((float) $student->cumulative_gpa, 2) }}</x-ui.detail>
            <x-ui.detail label="Academic Status">{{ $student->status }}</x-ui.detail>
        </x-ui.detail-list>
    </x-ui.card>

    <x-ui.card title="Personal Information" icon="person">
        <x-ui.detail-list>
            <x-ui.detail label="Full Name">{{ $student->name }}</x-ui.detail>
            <x-ui.detail label="Gender">{{ $student->gender ?? '-' }}</x-ui.detail>
            <x-ui.detail label="Email">{{ $student->email ?? '-' }}</x-ui.detail>
            <x-ui.detail label="Date of Birth">{{ $student->date_of_birth?->format('j F Y') ?? '-' }}</x-ui.detail>
            <x-ui.detail label="Phone Number">{{ $student->phone_number ?? '-' }}</x-ui.detail>
        </x-ui.detail-list>
    </x-ui.card>

    <x-ui.card title="Grade Point" icon="assignment">
        <x-slot:actions>
            <span class="text-muted">Average GPA :</span>
            <span class="font-semibold text-text">{{ number_format((float) $student->cumulative_gpa, 2) }}</span>
        </x-slot:actions>

        @if ($semesterGpa->isEmpty())
            <x-ui.empty-state title="No grades yet" description="Grades will appear here once this student has course results." icon="search" />
        @else
            <x-ui.bar-chart :items="$semesterGpa->all()" />
        @endif
    </x-ui.card>
</div>
