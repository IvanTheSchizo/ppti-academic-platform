<x-ui.modal id="add-student-modal" title="Add Students" size="lg">
    <form method="POST" action="{{ route('course-records.students.store', $record->id) }}" class="space-y-4" id="add-student-form">
        @csrf
        
        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-form.search-input id="modal-search" placeholder="Search by name or NIM" class="w-full max-w-sm" />
            
            <x-ui.button type="button" variant="secondary" class="text-sm" id="btn-add-all-class" data-target-class="{{ $record->classGroup?->class_code }}">
                <x-ui.icon name="groups" /> Add all from class {{ $record->classGroup?->class_code ?? 'assigned' }}
            </x-ui.button>
        </div>
        
        <div class="max-h-96 overflow-y-auto rounded-ui border border-border shadow-sm">
            <table class="w-full border-collapse text-left text-body" id="modal-student-table">
                <thead class="sticky top-0 z-10 bg-surface shadow-sm outline outline-1 outline-border">
                    <tr class="text-caption uppercase tracking-wide text-muted">
                        <th class="w-10 px-3 py-3 font-normal text-center">
                            <input type="checkbox" id="modal-select-all" class="rounded border-border text-primary focus:ring-primary">
                        </th>
                        <th class="px-3 py-3 font-normal">NIM</th>
                        <th class="px-3 py-3 font-normal">Name</th>
                        <th class="px-3 py-3 font-normal">Batch</th>
                        <th class="px-3 py-3 font-normal">Class</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($allStudents as $student)
                        @php
                            $isEnrolled = in_array($student->id, $enrolledStudentIds);
                            $isDifferentClass = $record->class_id !== $student->class_id;
                            $searchString = strtolower($student->nim . ' ' . $student->name);
                        @endphp
                        <tr class="student-row {{ $isEnrolled ? 'bg-page opacity-50' : 'hover:bg-surface-hover transition-colors' }}" 
                            data-search="{{ $searchString }}" 
                            data-class="{{ $student->classGroup?->class_code }}">
                            
                            <td class="px-3 py-3 text-center">
                                {{-- CHANGED: name="student_ids[]" to name="students[]" to match backend --}}
                                <input type="checkbox" name="students[]" value="{{ $student->id }}" 
                                    class="student-cb rounded border-border text-primary focus:ring-primary"
                                    @disabled($isEnrolled)>
                            </td>
                            <td class="px-3 py-3 font-medium text-text">{{ $student->nim }}</td>
                            <td class="px-3 py-3">{{ $student->name }}</td>
                            <td class="px-3 py-3">{{ $student->classGroup?->batch?->batch_name ?? '-' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-text">{{ $student->classGroup?->class_code ?? '-' }}</span>
                                    @if($isEnrolled)
                                        <span class="mt-0.5 text-xs font-medium text-success">Already added</span>
                                    @elseif($isDifferentClass)
                                        <span class="mt-0.5 text-xs text-muted">Different class</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-border pt-4">
            <span class="text-sm font-medium text-muted"><span id="selected-count">0</span> selected</span>
            <div class="flex gap-3">
                <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                <x-ui.button type="submit" id="btn-submit-students">Add 0 students</x-ui.button>
            </div>
        </div>
    </form>
</x-ui.modal>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalForm = document.getElementById('add-student-form');
    if (!modalForm) return;

    const searchInput = document.getElementById('modal-search');
    const selectAllCb = document.getElementById('modal-select-all');
    const addAllClassBtn = document.getElementById('btn-add-all-class');
    const checkboxes = document.querySelectorAll('.student-cb:not([disabled])');
    const rows = document.querySelectorAll('.student-row');
    const countDisplay = document.getElementById('selected-count');
    const submitBtn = document.getElementById('btn-submit-students');

    const updateCount = () => {
        const count = document.querySelectorAll('.student-cb:checked').length;
        countDisplay.textContent = count;
        submitBtn.textContent = `Add ${count} student${count !== 1 ? 's' : ''}`;
        submitBtn.disabled = count === 0;

        if (selectAllCb) {
            const visibleCheckboxes = [...checkboxes].filter(cb => cb.closest('tr').style.display !== 'none');
            selectAllCb.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
        }
    };

    // 1. Search Filtering
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.dataset.search.includes(query) ? '' : 'none';
        });
        updateCount();
    });

    // 2. Individual Checkbox Change
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    // 3. Select All Checkbox
    if (selectAllCb) {
        selectAllCb.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            checkboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
            updateCount();
        });
    }

    // 4. Add All From Class Button
    if (addAllClassBtn) {
        addAllClassBtn.addEventListener('click', () => {
            const targetClass = addAllClassBtn.dataset.targetClass;
            rows.forEach(row => {
                if (row.dataset.class === targetClass) {
                    const cb = row.querySelector('.student-cb:not([disabled])');
                    if (cb) cb.checked = true;
                }
            });
            updateCount();
        });
    }

    // Initialize state
    updateCount();
});
</script>