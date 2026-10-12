const openDropdowns = () => document.querySelectorAll('details[data-dropdown][open]');

const setSidebar = (open) => {
    document.querySelector('[data-sidebar]')?.toggleAttribute('data-open', open);
    document.querySelector('[data-sidebar-backdrop]')?.toggleAttribute('hidden', !open);
    document.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', String(open));
};

document.addEventListener('click', (event) => {
    const editToggle = event.target.closest('[data-student-edit-toggle]');

    if (editToggle) {
        const table = document.querySelector(editToggle.getAttribute('aria-controls') ? `#${editToggle.getAttribute('aria-controls')}` : '[data-student-edit-table]');

        if (table) {
            const editing = table.dataset.studentEditMode !== 'on';
            table.dataset.studentEditMode = editing ? 'on' : 'off';
            editToggle.setAttribute('aria-pressed', String(editing));

            const label = editToggle.querySelector('[data-student-edit-label]');
            if (label) label.textContent = editing ? 'Done Editing' : 'Edit List';
        }
    }

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

// Populate the shared Edit Student modal from the selected table row.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-edit-student]');
    if (!button) return;

    const form = document.querySelector('#edit-student-form');
    if (!form) return;

    const template = form.dataset.updateUrlTemplate;
    form.action = template.replace('__STUDENT_ID__', encodeURIComponent(button.dataset.studentId));
    form.querySelector('[name="nim"]').value = button.dataset.nim ?? '';
    form.querySelector('[name="name"]').value = button.dataset.name ?? '';
    form.querySelector('[name="class_id"]').value = button.dataset.classId ?? '';
    form.querySelector('[name="status"]').value = button.dataset.status ?? 'Active';
    form.querySelector('[name="cumulative_gpa"]').value = button.dataset.gpa ?? '0.00';

    document.querySelector('#edit-student-modal')?.showModal();
});


// Lecturer list Edit List toggle and shared edit modal.
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-lecturer-edit-toggle]');
    if (toggle) {
        const table = document.querySelector('#lecturer-table');
        if (table) {
            const editing = table.dataset.lecturerEditMode !== 'on';
            table.dataset.lecturerEditMode = editing ? 'on' : 'off';
            toggle.setAttribute('aria-pressed', String(editing));
            const label = toggle.querySelector('[data-lecturer-edit-label]');
            if (label) label.textContent = editing ? 'Done Editing' : 'Edit List';
        }
    }

    const button = event.target.closest('[data-edit-lecturer]');
    if (!button) return;
    event.preventDefault();
    event.stopPropagation();

    const form = document.querySelector('#edit-lecturer-form');
    if (!form) return;
    form.action = form.dataset.updateUrlTemplate.replace('__LECTURER_ID__', encodeURIComponent(button.dataset.lecturerId));
    const fields = {
        nip: button.dataset.nip,
        lecturer_code: button.dataset.lecturerCode,
        name: button.dataset.name,
        email_binus_edu: button.dataset.emailBinusEdu,
        email_binus_ac_id: button.dataset.emailBinusAcId,
        phone_number: button.dataset.phoneNumber,
        jja: button.dataset.jja,
        latest_education: button.dataset.latestEducation,
        status: button.dataset.status,
    };
    Object.entries(fields).forEach(([name, value]) => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) input.value = value ?? '';
    });
    document.querySelector('#edit-lecturer-modal')?.showModal();
});
