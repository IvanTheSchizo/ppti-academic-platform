const openDropdowns = () => document.querySelectorAll('details[data-dropdown][open]');

const setSidebar = (open) => {
    document.querySelector('[data-sidebar]')?.toggleAttribute('data-open', open);
    document.querySelector('[data-sidebar-backdrop]')?.toggleAttribute('hidden', !open);
    document.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', String(open));
};

document.addEventListener('click', (event) => {
    openDropdowns().forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.removeAttribute('open');
        }
    });

    const toggle = event.target.closest('[data-toggle]');
    const target = toggle && document.querySelector(toggle.dataset.toggle);

    if (target) {
        const hidden = target.toggleAttribute('hidden');
        toggle.setAttribute('aria-expanded', String(!hidden));
    }

    if (event.target.closest('[data-sidebar-toggle]')) {
        setSidebar(!document.querySelector('[data-sidebar]')?.hasAttribute('data-open'));
    } else if (event.target.closest('[data-sidebar-backdrop], [data-sidebar] a')) {
        setSidebar(false);
    }
});

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');

    if (opener) {
        document.querySelector(opener.dataset.modalOpen)?.showModal();
        return;
    }

    const accept = event.target.closest('[data-confirm-accept]');

    if (accept) {
        const dialog = accept.closest('dialog');

        dialog.close();
        dialog.dispatchEvent(new CustomEvent('confirmed', { bubbles: true }));
        return;
    }

    if (event.target.closest('[data-modal-close]') ||event.target instanceof HTMLDialogElement) {
        (event.target.closest('dialog') ?? event.target).close();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    setSidebar(false);

    openDropdowns().forEach((dropdown) => {
        dropdown.removeAttribute('open');
        dropdown.querySelector('summary')?.focus();
    });
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        setSidebar(false);
    }
});

document.addEventListener('click', (event) => {
    const reveal = event.target.closest('[data-password-toggle]');

    if (reveal) {
        const input = reveal.parentElement.querySelector('input');
        const show = input.type === 'password';

        input.type = show ? 'text' : 'password';
        reveal.setAttribute('aria-pressed', String(show));
        reveal.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        reveal.querySelector('[data-icon-hidden]')?.classList.toggle('hidden', show);
        reveal.querySelector('[data-icon-shown]')?.classList.toggle('hidden', !show);
    }

    event.target.closest('[data-toast-close]')?.closest('[data-toast]').remove();
});

document.querySelectorAll('[data-flash] [data-toast-auto]').forEach((toast) => {
    setTimeout(() => toast.remove(), 6000);
});

document.addEventListener('click', (event) => {
    if (event.target.matches('input[type="date"]')) {
        event.target.showPicker?.();
    }
});

document.addEventListener('submit', (event) => {
    if (event.target.matches('form[data-autosubmit]')) {
        event.target.querySelectorAll('input[name], select[name]').forEach((field) => {
            field.disabled = field.disabled || field.value === '';
        });
    }
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-autosubmit] input[name], form[data-autosubmit] select[name]').forEach((field) => {
        field.disabled = false;
    });
});

document.addEventListener('change', (event) => {
    if (event.target.matches('select[data-navigate]')) {
        window.location.href = event.target.value;
    }

    event.target.closest('form[data-autosubmit]')?.requestSubmit();
});

const passwordRules = {
    length: (value) => value.length >= 8,
    upper: (value) => /[A-Z]/.test(value),
    lower: (value) => /[a-z]/.test(value),
    symbol: (value) => /[\d\W_]/.test(value),
};

document.querySelectorAll('[data-password-rules]').forEach((list) => {
    const input = document.querySelector(list.dataset.passwordRules);

    const update = () => {
        list.querySelectorAll('[data-rule]').forEach((item) => {
            const met = passwordRules[item.dataset.rule]?.(input.value) ?? false;

            item.classList.toggle('text-success', met);
            item.classList.toggle('text-muted', !met);
            item.querySelector('[data-rule-met]')?.toggleAttribute('hidden', !met);
            item.querySelector('[data-rule-unmet]')?.toggleAttribute('hidden', met);
        });
    };

    input.addEventListener('input', update);
    update();
});

const syncDateRange = (range) => {
    const empty = [...range.querySelectorAll('input[type="date"]')].every((input) => input.value === '');

    range.toggleAttribute('data-empty', empty);
};

document.querySelectorAll('[data-date-range]').forEach(syncDateRange);

document.addEventListener('input', (event) => {
    const range = event.target.closest('[data-date-range]');

    if (range) {
        syncDateRange(range);
    }
});

window.addEventListener('pageshow', () => document.querySelectorAll('[data-date-range]').forEach(syncDateRange));

document.addEventListener('click', (event) => {
    const row = event.target.closest('tr[data-row-href]');

    if (row && !event.target.closest('a, button, input, select, label')) {
        window.location.href = row.dataset.rowHref;
    }
});

// Edit Student modal: batch follows the selected class, semester GPA rows add/remove with a live cumulative GPA.
const recalcCumulative = (box) => {
    const values = [...box.querySelectorAll('[data-semester-gpa-input]')]
        .map((input) => parseFloat(input.value))
        .filter((value) => !Number.isNaN(value));
    const output = box.querySelector('[data-semester-cumulative]');

    output.textContent = values.length ? (values.reduce((sum, value) => sum + value, 0) / values.length).toFixed(2) : '-';
};

document.querySelectorAll('[data-semester-gpa]').forEach(recalcCumulative);

document.addEventListener('click', (event) => {
    const box = event.target.closest('[data-semester-gpa]');

    if (!box) {
        return;
    }

    if (event.target.closest('[data-semester-add]')) {
        const html = box.querySelector('[data-semester-template]').innerHTML.replaceAll('__INDEX__', String(Date.now()));

        box.querySelector('[data-semester-rows]').insertAdjacentHTML('beforeend', html);
        recalcCumulative(box);
    }

    if (event.target.closest('[data-semester-remove]')) {
        event.target.closest('[data-semester-row]').remove();
        recalcCumulative(box);
    }
});

document.addEventListener('input', (event) => {
    const box = event.target.closest('[data-semester-gpa]');

    if (box && event.target.matches('[data-semester-gpa-input]')) {
        recalcCumulative(box);
    }
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-class-select]')) {
        const output = event.target.closest('form').querySelector('[data-batch-output]');

        if (output) {
            output.value = event.target.selectedOptions[0]?.dataset.batch ?? '';
        }
    }
});

// Input Data > Student: picking a batch limits the class dropdown to that batch's classes.
const filterClassesByBatch = (form) => {
    const batch = form.querySelector('[data-batch-select]')?.value;
    const classSelect = form.querySelector('[data-filtered-by-batch]');

    if (!classSelect) {
        return;
    }

    [...classSelect.options].forEach((option) => {
        const hide = Boolean(batch) && Boolean(option.dataset.batchId) && option.dataset.batchId !== batch;

        option.hidden = hide;
        option.disabled = hide;
    });

    if (classSelect.selectedOptions[0]?.disabled) {
        classSelect.value = '';
    }
};

document.querySelectorAll('[data-batch-select]').forEach((select) => filterClassesByBatch(select.closest('form')));

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-batch-select]')) {
        filterClassesByBatch(event.target.closest('form'));
    }
});

// Input Data > Course Record / Grades: search students, add them to a list, and remove them again.
const initStudentPicker = (root) => {
    const body = root.querySelector('[data-picker-body]');
    const empty = root.querySelector('[data-picker-empty]');
    const search = root.querySelector('[data-picker-search]');
    const results = root.querySelector('[data-picker-results]');
    const withGrade = 'withGrade' in root.dataset;
    const gradeOptions = withGrade ? JSON.parse(root.dataset.gradeOptions) : [];
    const jsonHeaders = { Accept: 'application/json' };

    const refreshEmpty = () => empty.toggleAttribute('hidden', body.children.length > 0);

    const cell = (text) => {
        const td = document.createElement('td');

        td.className = 'px-2 py-3';
        td.textContent = text;

        return td;
    };

    const addRow = (student) => {
        if (body.querySelector(`[data-student-id="${student.id}"]`)) {
            return;
        }

        const tr = document.createElement('tr');

        tr.dataset.studentId = student.id;
        [student.nim, student.name, student.batch, student.class].forEach((text) => tr.append(cell(text)));

        if (withGrade) {
            const select = document.createElement('select');

            select.name = `grades[${student.id}]`;
            select.setAttribute('aria-label', `Grade for ${student.name}`);
            select.className = 'h-8 rounded-ui border border-border bg-surface px-2 text-body text-text focus:border-primary focus:outline-2 focus:outline-primary/30';
            select.add(new Option('-', ''));
            gradeOptions.forEach((grade) => select.add(new Option(grade, grade, false, grade === student.grade)));

            const td = document.createElement('td');

            td.className = 'px-2 py-2';
            td.append(select);
            tr.append(td);
        } else {
            const hidden = document.createElement('input');

            hidden.type = 'hidden';
            hidden.name = 'students[]';
            hidden.value = student.id;
            tr.firstElementChild.append(hidden);
        }

        const remove = document.createElement('td');

        remove.className = 'px-2 py-2 text-right';
        remove.append(root.querySelector('template[data-remove-button]').content.cloneNode(true));
        tr.append(remove);

        body.append(tr);
        refreshEmpty();
    };

    const hideResults = () => {
        results.hidden = true;
        results.replaceChildren();
    };

    const showResults = (students) => {
        results.replaceChildren();

        if (students.length === 0) {
            const li = document.createElement('li');

            li.className = 'px-3 py-2 text-muted';
            li.textContent = 'No students found.';
            results.append(li);
        }

        students.forEach((student) => {
            const li = document.createElement('li');
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'block w-full px-3 py-2 text-left transition-colors hover:bg-page focus-visible:bg-page focus-visible:outline-none';
            button.textContent = `${student.nim} — ${student.name} (${student.batch}, ${student.class})`;
            button.addEventListener('click', () => {
                addRow(student);
                search.value = '';
                hideResults();
                search.focus();
            });

            li.append(button);
            results.append(li);
        });

        results.hidden = false;
    };

    let timer;

    search.addEventListener('input', () => {
        clearTimeout(timer);

        if (search.value.trim() === '') {
            hideResults();

            return;
        }

        timer = setTimeout(async () => {
            const response = await fetch(`${root.dataset.searchUrl}?q=${encodeURIComponent(search.value.trim())}`, { headers: jsonHeaders });

            if (response.ok) {
                showResults(await response.json());
            }
        }, 250);
    });

    search.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });

    document.addEventListener('click', (event) => {
        if (!search.parentElement.contains(event.target)) {
            hideResults();
        }
    });

    root.addEventListener('click', async (event) => {
        if (event.target.closest('[data-picker-remove]')) {
            event.target.closest('tr').remove();
            refreshEmpty();
        }

        if (event.target.closest('[data-picker-add-class]')) {
            const classId = document.querySelector(root.dataset.classSelect)?.value;

            if (!classId) {
                return;
            }

            const response = await fetch(`${root.dataset.searchUrl}?class_id=${encodeURIComponent(classId)}`, { headers: jsonHeaders });

            if (response.ok) {
                (await response.json()).forEach(addRow);
            }
        }
    });

    // Grades tab: choosing a course loads the students already enrolled in it.
    const courseSelect = root.dataset.courseSelect ? document.querySelector(root.dataset.courseSelect) : null;

    courseSelect?.addEventListener('change', async () => {
        body.replaceChildren();
        refreshEmpty();

        if (courseSelect.value) {
            const response = await fetch(root.dataset.recordUrl.replace('__ID__', courseSelect.value), { headers: jsonHeaders });

            if (response.ok) {
                (await response.json()).forEach(addRow);
            }
        }
    });

    JSON.parse(root.dataset.initial || '[]').forEach(addRow);
    refreshEmpty();
};

document.querySelectorAll('[data-student-picker]').forEach(initStudentPicker);

// Input Data > Import CSV: upload a file, preview the parsed rows, then import the valid ones.
const initCsvImport = (root) => {
    const dialog = root.closest('dialog');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const input = root.querySelector('[data-import-file]');
    const dropzone = root.querySelector('[data-import-dropzone]');
    const filename = root.querySelector('[data-import-filename]');
    const error = root.querySelector('[data-import-error]');
    const steps = { upload: root.querySelector('[data-import-step="upload"]'), preview: root.querySelector('[data-import-step="preview"]') };
    const footer = (name) => dialog.querySelector(`[data-import-${name}]`);
    let token = null;

    const setBusy = (button, busy) => {
        button.disabled = busy;
        button.classList.toggle('opacity-50', busy);
    };

    const showError = (message) => {
        error.hidden = !message;
        error.querySelector('div').textContent = message ?? '';
    };

    const showStep = (name) => {
        steps.upload.hidden = name !== 'upload';
        steps.preview.hidden = name !== 'preview';
        footer('upload').hidden = name !== 'upload';
        footer('back').hidden = name !== 'preview';
        footer('commit').hidden = name !== 'preview';
    };

    const reset = () => {
        input.value = '';
        token = null;
        filename.hidden = true;
        setBusy(footer('upload'), true);
        showError(null);
        showStep('upload');
    };

    const chooseFile = (file) => {
        filename.textContent = file ? file.name : '';
        filename.hidden = !file;
        setBusy(footer('upload'), !file);
        showError(null);
    };

    input.addEventListener('change', () => chooseFile(input.files[0]));

    ['dragenter', 'dragover'].forEach((type) => dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        dropzone.dataset.dragging = '';
    }));

    ['dragleave', 'drop'].forEach((type) => dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        delete dropzone.dataset.dragging;
    }));

    dropzone.addEventListener('drop', (event) => {
        if (event.dataTransfer.files.length) {
            input.files = event.dataTransfer.files;
            chooseFile(input.files[0]);
        }
    });

    const cell = (tag, text, className = 'px-3 py-2') => {
        const element = document.createElement(tag);

        element.className = className;
        element.textContent = text;

        return element;
    };

    const renderPreview = (data) => {
        const head = root.querySelector('[data-import-head]');
        const body = root.querySelector('[data-import-body]');
        const headRow = document.createElement('tr');

        ['Row', ...data.columns, 'Status'].forEach((name) => headRow.append(cell('th', name.replaceAll('_', ' '), 'px-3 py-2 font-semibold whitespace-nowrap')));
        head.replaceChildren(headRow);

        body.replaceChildren(...data.rows.map((row) => {
            const tr = document.createElement('tr');

            tr.append(cell('td', row.line, 'px-3 py-2 text-muted'));
            row.cells.forEach((value) => tr.append(cell('td', value || '-', 'px-3 py-2 whitespace-nowrap')));
            const status = row.errors.length
                ? cell('td', row.errors.join(' '), 'px-3 py-2 text-danger')
                : cell('td', row.notices?.length ? `Ready — ${row.notices.join(' ')}` : 'Ready', 'px-3 py-2 text-success min-w-64');
            tr.append(status);

            return tr;
        }));

        const shown = data.rows.length < data.total ? ` Showing the first ${data.rows.length}.` : '';

        const planned = data.plannedActions?.length ?? 0;
        const setupSummary = planned
            ? ` Missing batch/class setup: ${planned} new batch or class record(s) will be created automatically if you confirm; see row statuses for details.`
            : '';

        root.querySelector('[data-import-summary]').textContent =
            `${data.total} row${data.total === 1 ? '' : 's'} read: ${data.valid} ready to import, ${data.invalid} with errors (these are skipped).${shown}${setupSummary} ${data.notes.join(' ')}`;

        footer('commit').textContent = `Import ${data.valid} row${data.valid === 1 ? '' : 's'}`;
        setBusy(footer('commit'), data.valid === 0);
    };

    const post = async (url, body) => {
        const response = await fetch(url, { method: 'POST', body, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message ?? Object.values(data.errors ?? {}).flat()[0] ?? 'Something went wrong. Please try again.');
        }

        return data;
    };

    footer('upload').addEventListener('click', async () => {
        if (!input.files[0]) {
            return;
        }

        const form = new FormData();

        form.append('file', input.files[0]);
        setBusy(footer('upload'), true);
        showError(null);

        try {
            const data = await post(root.dataset.previewUrl, form);

            token = data.token;
            renderPreview(data);
            showStep('preview');
        } catch (exception) {
            showError(exception.message);
        } finally {
            setBusy(footer('upload'), !input.files[0]);
        }
    });

    footer('back').addEventListener('click', () => {
        showError(null);
        showStep('upload');
    });

    footer('commit').addEventListener('click', async () => {
        setBusy(footer('commit'), true);
        showError(null);

        try {
            const form = new FormData();

            form.append('token', token);
            await post(root.dataset.commitUrl, form);
            window.location.reload();
        } catch (exception) {
            showError(exception.message);
            setBusy(footer('commit'), false);
        }
    });

    dialog.addEventListener('close', reset);
    reset();
};

document.querySelectorAll('[data-csv-import]').forEach(initCsvImport);

// Course record detail: "Edit" toggles the remove icons, "Add Students" jumps to the search that enrols a student on click.
document.addEventListener('click', (event) => {
    const editToggle = event.target.closest('[data-edit-toggle]');

    if (editToggle) {
        const target = document.querySelector(editToggle.dataset.editToggle);
        const editing = target.dataset.editMode !== 'on';

        target.dataset.editMode = editing ? 'on' : 'off';
        editToggle.setAttribute('aria-pressed', String(editing));
        editToggle.querySelector('[data-edit-label]').textContent = editing ? 'Done' : 'Edit';
    }

    const addToggle = event.target.closest('[data-add-students-toggle]');

    if (addToggle) {
        document.querySelector('[data-record-add-search]')?.focus();
    }

    const remove = event.target.closest('[data-remove-student]');

    if (remove) {
        document.getElementById('remove-student-form').action = remove.dataset.removeStudent;
    }
});

document.querySelectorAll('[data-record-add]').forEach((root) => {
    const search = root.querySelector('[data-record-add-search]');
    const results = root.querySelector('[data-record-add-results]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let timer;

    const hide = () => {
        results.hidden = true;
        results.replaceChildren();
    };

    const add = async (student) => {
        const form = new FormData();

        form.append('student_id', student.id);

        const response = await fetch(root.dataset.addUrl, { method: 'POST', body: form, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });

        if (response.ok) {
            window.location.reload();
        }
    };

    const show = (students) => {
        results.replaceChildren();

        if (students.length === 0) {
            const li = document.createElement('li');

            li.className = 'px-3 py-2 text-muted';
            li.textContent = 'No students found.';
            results.append(li);
        }

        students.forEach((student) => {
            const li = document.createElement('li');
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'block w-full px-3 py-2 text-left transition-colors hover:bg-page focus-visible:bg-page focus-visible:outline-none';
            button.textContent = `${student.nim} — ${student.name} (${student.batch}, ${student.class})`;
            button.addEventListener('click', () => add(student));
            li.append(button);
            results.append(li);
        });

        results.hidden = false;
    };

    search.addEventListener('input', () => {
        clearTimeout(timer);

        if (search.value.trim() === '') {
            hide();

            return;
        }

        timer = setTimeout(async () => {
            const params = new URLSearchParams({ q: search.value.trim(), exclude_record: root.dataset.excludeRecord });
            const response = await fetch(`${root.dataset.searchUrl}?${params}`, { headers: { Accept: 'application/json' } });

            if (response.ok) {
                show(await response.json());
            }
        }, 250);
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            hide();
        }
    });
});
