document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        input.toggleAttribute('data-revealed', reveal);
        button.setAttribute('aria-pressed', String(reveal));
        button.setAttribute('aria-label', `${reveal ? 'إخفاء' : 'إظهار'} ${document.querySelector(`label[for="${input.id}"]`).textContent}`);
        button.textContent = reveal ? 'إخفاء' : 'إظهار';
    });
});

document.querySelectorAll('form[data-auth-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.busy === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.busy = 'true';
        form.setAttribute('aria-busy', 'true');
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.querySelector('[data-submit-label]').textContent = 'جارٍ الإرسال…';
        button.querySelector('.auth-spinner').hidden = false;
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-auth-form]').forEach((form) => {
        delete form.dataset.busy;
        form.removeAttribute('aria-busy');
        const button = form.querySelector('[type="submit"]');
        button.disabled = false;
        button.querySelector('[data-submit-label]').textContent = button.dataset.label;
        button.querySelector('.auth-spinner').hidden = true;
    });
});
document.querySelector('[data-error-summary]')?.focus();
