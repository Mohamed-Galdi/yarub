import './bootstrap';
import 'flowbite';

document.querySelectorAll('[data-learning-demo]').forEach(demo => {
    const form = demo.querySelector('form');
    const submit = demo.querySelector('[data-demo-submit]');
    const reset = demo.querySelector('[data-demo-reset]');
    const feedback = demo.querySelector('[data-demo-feedback]');
    submit.hidden = false;
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const response = new FormData(form);
        const matching = demo.dataset.demoType === 'matching';
        const points = matching ? Number(response.get('subject') === 'subject') + Number(response.get('object') === 'object') : Number(response.get('answer') === 'correct') * 2;
        const title = points === 2 ? 'إجابة صحيحة.' : points === 1 ? 'إجابة صحيحة جزئيًا.' : 'لنراجع الفكرة معًا.';
        const explanation = matching ? 'الطالبُ هو من قام بالقراءة، فهو الفاعل. وكتابًا هو ما وقع عليه الفعل، فهو المفعول به.' : 'القلم أداة للكتابة، والمفتاح أداة للفتح؛ العلاقة في الزوجين أداة ووظيفتها.';
        feedback.replaceChildren();
        const heading = document.createElement('strong');
        heading.textContent = title + (matching ? ` ${points} من نقطتين.` : '');
        const text = document.createElement('p');
        text.textContent = explanation;
        feedback.append(heading, text);
        feedback.dataset.result = points === 2 ? 'correct' : 'review';
        feedback.hidden = false;
        form.querySelectorAll('input, select, button').forEach(control => { control.disabled = true; });
        reset.hidden = false;
        feedback.focus({preventScroll: true});
        feedback.scrollIntoView({block: 'nearest', behavior: 'instant'});
    });
    reset.addEventListener('click', () => {
        form.reset();
        form.querySelectorAll('input, select, button').forEach(control => { control.disabled = false; });
        feedback.hidden = true;
        reset.hidden = true;
        form.querySelector('input, select').focus();
    });
});
