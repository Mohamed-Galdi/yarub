import './bootstrap';
import '../css/arabic-skills.css';
import 'flowbite/dist/flowbite.min.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

function initialize() {
    const root = document.querySelector('[data-arabic-root]');
    if (!root) return;
    const form = root.querySelector('[data-arabic-action="answer"]');
    if (!form) return;
    const check = form.querySelector('[data-arabic-check]');
    const update = () => {
        form.querySelectorAll('[data-arabic-match]').forEach(select => {
            select.closest('.arabic-matching-item').querySelector('output').textContent = select.selectedOptions[0]?.dataset.choiceText || '';
        });
        if (check) check.disabled = !form.checkValidity();
    };
    form.addEventListener('change', update);
    update();
}

function bringIntoView(element) {
    if (!element) return;
    element.focus({preventScroll: true});
    const rect = element.getBoundingClientRect();
    if (rect.top < 0 || rect.bottom > window.innerHeight - 32) element.scrollIntoView({block: 'nearest', behavior: 'instant'});
}

let busy = false;
document.addEventListener('submit', async event => {
    const form = event.target;
    if (!form.matches('[data-arabic-action], [data-arabic-hint]')) return;
    event.preventDefault();
    if (busy) return;
    const root = document.querySelector('[data-arabic-root]');
    const status = root.querySelector('[data-arabic-status]');
    const hint = form.matches('[data-arabic-hint]');
    const trigger = event.submitter;
    const body = new FormData(form);
    const controls = [...root.querySelectorAll('button, input, select')].filter(control => !control.disabled);
    busy = true;
    controls.forEach(control => { control.disabled = true; });
    root.setAttribute('aria-busy', 'true');
    status.textContent = hint ? 'جارٍ عرض التلميح…' : (form.dataset.arabicAction === 'next' ? 'جارٍ الانتقال…' : 'جارٍ حفظ الإجابة…');
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 20000);
    try {
        const response = await fetch(form.action, {method: 'POST', credentials: 'same-origin', headers: {'Accept': hint ? 'application/json' : 'text/html'}, body, signal: controller.signal});
        if (!response.ok) throw new Error('request');
        if (hint) {
            const data = await response.json();
            const aside = root.querySelector('[data-arabic-hint-content]');
            aside.querySelector('p').textContent = data.hint;
            aside.hidden = false;
            status.textContent = '';
            bringIntoView(aside);
        } else {
            const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
            const replacement = documentResult.querySelector('[data-arabic-root]');
            if (!replacement) throw new Error('page');
            root.replaceWith(replacement);
            window.history.replaceState({}, '', response.url);
            initialize();
            bringIntoView(replacement.querySelector('[role="alert"]') || (form.dataset.arabicAction === 'answer' ? replacement.querySelector('[data-arabic-feedback-area]') : (replacement.querySelector('[data-arabic-prompt]') || replacement.querySelector('[data-arabic-heading]'))));
        }
    } catch {
        status.textContent = 'تعذّر إكمال الطلب. أعد المحاولة أو حدّث الصفحة لاستعادة آخر إجابة محفوظة.';
        controls.forEach(control => { control.disabled = false; });
        trigger?.focus({preventScroll: true});
    } finally {
        window.clearTimeout(timeout);
        busy = false;
        controls.forEach(control => { control.disabled = false; });
        root.removeAttribute('aria-busy');
    }
});

initialize();
