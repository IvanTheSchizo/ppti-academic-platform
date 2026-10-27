<x-ui.modal id="remove-student-modal" title="Remove Students" size="xl">
    <form method="POST" action="{{ route('course-records.students.bulk-destroy', $record->id) }}" class="space-y-4" id="remove-student-form">
        @csrf
        @method('DELETE')
        
        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-form.search-input id="modal-search-remove" placeholder="Search enrolled students..." class="w-full max-w-sm" />
        </div>
        
        <div class="max-h-96 overflow-y-auto rounded-ui border border-border shadow-sm">
            <table class="w-full border-collapse text-left text-body">
                <thead class="sticky top-0 z-10 bg-surface shadow-sm outline outline-1 outline-border">
                    <tr class="text-caption uppercase tracking-wide text-muted">
                        <th class="w-10 px-3 py-3 font-normal text-center">
                            <input type="checkbox" id="modal-select-all-remove" class="rounded border-border text-danger focus:ring-danger">
                        </th>
                        <th class="px-3 py-3 font-normal">NIM</th>
                        <th class="px-3 py-3 font-normal">Name</th>
                        <th class="px-3 py-3 font-normal">Batch</th>
                        <th class="px-3 py-3 font-normal">Class</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($allStudents as $student)
                        @if(in_array($student->id, $enrolledStudentIds))
                            <tr class="student-remove-row hover:bg-surface-hover transition-colors" 
                                data-search="{{ strtolower($student->nim . ' ' . $student->name) }}">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" name="students[]" value="{{ $student->id }}" 
                                        class="student-remove-cb rounded border-border text-danger focus:ring-danger">
                                </td>
                                <td class="px-3 py-3 font-medium text-text">{{ $student->nim }}</td>
                                <td class="px-3 py-3">{{ $student->name }}</td>
                                <td class="px-3 py-3">{{ $student->classGroup?->batch?->batch_name ?? '-' }}</td>
                                <td class="px-3 py-3 font-medium text-text">{{ $student->classGroup?->class_code ?? '-' }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-muted">No students enrolled yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-border pt-4">
            <span class="text-sm font-medium text-muted"><span id="selected-count-remove">0</span> selected</span>
            <div class="flex gap-3">
                <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                <x-ui.button type="submit" variant="danger" id="btn-submit-remove">Remove 0 students</x-ui.button>
            </div>
        </div>
    </form>
</x-ui.modal>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('remove-student-form');
    if (!form) return;

    const searchInput = document.getElementById('modal-search-remove');
    const selectAllCb = document.getElementById('modal-select-all-remove');
    const checkboxes = document.querySelectorAll('.student-remove-cb');
    const rows = document.querySelectorAll('.student-remove-row');
    const countDisplay = document.getElementById('selected-count-remove');
    const submitBtn = document.getElementById('btn-submit-remove');

    const updateCount = () => {
        const count = document.querySelectorAll('.student-remove-cb:checked').length;
        countDisplay.textContent = count;
        submitBtn.textContent = `Remove ${count} student${count !== 1 ? 's' : ''}`;
        
        // Force fully lock/unlock the button
        if (count === 0) {
            submitBtn.setAttribute('disabled', 'disabled');
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.5';
        } else {
            submitBtn.removeAttribute('disabled');
            submitBtn.style.pointerEvents = 'auto';
            submitBtn.style.opacity = '1';
        }

        if (selectAllCb) {
            const visibleCheckboxes = [...checkboxes].filter(cb => cb.closest('tr').style.display !== 'none');
            selectAllCb.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
        }
    };

    searchInput?.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.dataset.search.includes(query) ? '' : 'none';
        });
        updateCount();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    selectAllCb?.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') cb.checked = isChecked;
        });
        updateCount();
    });

    updateCount();
});
</script>