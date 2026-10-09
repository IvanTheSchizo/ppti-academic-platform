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
