import './bootstrap';
import 'flowbite/dist/flowbite.min.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

function initializePractice() {
    const form = document.querySelector('[data-qudrat-answer]');
    const check = form?.querySelector('[data-qudrat-check]');
    if (!check) return;

    const status = form.querySelector('[data-qudrat-save]');
    const hint = form.querySelector('[data-qudrat-hint]');
    check.disabled = !form.querySelector('input[name="option"]:checked');
    let queue = Promise.resolve();
    let revision = 0;
    let submitting = false;

    form.addEventListener('change', event => {
        if (!event.target.matches('input[name="option"]') || submitting) return;
        check.disabled = false;
        const option = event.target.value;
        const current = ++revision;
        status.textContent = 'جارٍ حفظ الاختيار…';
        queue = queue.then(async () => {
            try {
                const response = await fetch(form.dataset.selectionUrl, {
                    method: 'PATCH', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value},
                    body: JSON.stringify({option}),
                });
                const data = await response.json();
                if (!response.ok || !data.saved) throw new Error('save');
                if (current === revision) status.textContent = 'حُفظ الاختيار';
            } catch {
                if (current === revision) status.textContent = 'لم يُحفظ الاختيار. اضغط «تحقق من الإجابة» لإرساله، أو أعد المحاولة.';
            }
        });
    });

    form.addEventListener('submit', async event => {
        // Native forms remain the fallback, including the separate next-question form.
        const wantsHint = event.submitter === hint && hint !== null;
        if (event.submitter !== check && !wantsHint) return;
        event.preventDefault();
        if (submitting) return;
        if (!wantsHint && !form.querySelector('input[name="option"]:checked')) {
            status.textContent = 'اختر إجابة أولًا.';
            return;
        }
        const body = new FormData(form);
        const action = wantsHint ? hint.formAction : form.action;
        submitting = true;
        check.disabled = true;
        if (hint) hint.disabled = true;
        form.querySelectorAll('input[name="option"]').forEach(input => { input.disabled = true; });
        form.setAttribute('aria-busy', 'true');
        await queue;
        status.textContent = wantsHint ? 'جارٍ عرض التلميح…' : 'جارٍ التحقق من الإجابة…';
        try {
            const response = await fetch(action, {
                method: 'POST', credentials: 'same-origin',
                headers: {'Accept': 'text/html'}, body,
            });
            if (!response.ok) throw new Error('submit');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const replacement = page.querySelector('[data-qudrat-practice]');
            const targetId = wantsHint ? 'qudrat-hint' : 'qudrat-feedback';
            if (!replacement?.querySelector(`#${targetId}`)) throw new Error('content');
            document.querySelector('[data-qudrat-practice]').replaceWith(replacement);
            initializePractice();
            const target = document.getElementById(targetId);
            target.focus({preventScroll: true});
            const bounds = target.getBoundingClientRect();
            if (!target.classList.contains('session-feedback-toast') && (bounds.top < 24 || bounds.bottom > window.innerHeight - 24)) {
                target.scrollIntoView({block: 'start', behavior: 'instant'});
            }
        } catch {
            status.textContent = 'تعذّر تحميل النتيجة. أعد المحاولة، أو أعد تحميل الصفحة لمتابعة تقدّمك المحفوظ.';
            submitting = false;
            check.disabled = !form.querySelector('input[name="option"]:checked');
            if (hint) hint.disabled = false;
            form.querySelectorAll('input[name="option"]').forEach(input => { input.disabled = false; });
            form.removeAttribute('aria-busy');
        }
    });
}

initializePractice();
