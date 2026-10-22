import './bootstrap';
import 'flowbite/dist/flowbite.min.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const form = document.querySelector('[data-qudrat-answer]');
if (form) {
    const check = form.querySelector('[data-qudrat-check]');
    const status = form.querySelector('[data-qudrat-save]');
    if (check) {
        check.disabled = !form.querySelector('input[name="option"]:checked');
        let queue = Promise.resolve();
        form.addEventListener('change', event => {
            if (!event.target.matches('input[name="option"]')) return;
            check.disabled = false;
            const option = event.target.value;
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
                    status.textContent = 'حُفظ الاختيار';
                } catch {
                    status.textContent = 'لم يُحفظ الاختيار. اضغط «تحقق» لإرساله، أو أعد المحاولة.';
                }
            });
        });
        form.addEventListener('submit', event => {
            if (event.submitter === check && !form.querySelector('input[name="option"]:checked')) {
                event.preventDefault();
                status.textContent = 'اختر إجابة أولًا.';
            }
        });
    }
}
