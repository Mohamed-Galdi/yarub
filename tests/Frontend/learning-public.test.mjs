import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/learning-public.js', import.meta.url), 'utf8').replace(/^import .*;$/gm, '');

function demo(type, answers) {
    const handlers = {};
    let focused = '', resetCalled = false;
    const controls = [{disabled: false}];
    const submit = {hidden: true};
    const reset = {hidden: true, addEventListener: (event, fn) => { handlers.reset = fn; }};
    const feedback = {hidden: true, dataset: {}, children: [], replaceChildren() { this.children = []; }, append(...items) { this.children.push(...items); }, focus() { focused = 'feedback'; }, scrollIntoView() {}};
    const form = {reportValidity: () => true, addEventListener: (event, fn) => { handlers.submit = fn; }, querySelectorAll: () => controls, reset: () => { resetCalled = true; }, querySelector: () => ({focus() { focused = 'input'; }})};
    const root = {dataset: {demoType: type}, querySelector: selector => ({form, '[data-demo-submit]': submit, '[data-demo-reset]': reset, '[data-demo-feedback]': feedback})[selector]};
    vm.runInNewContext(source, {document: {querySelectorAll: () => [root], createElement: () => ({textContent: ''})}, FormData: class { get(key) { return answers[key]; } }});
    return {submit: () => handlers.submit({preventDefault() {}}), reset: () => handlers.reset(), feedback, controls, resetButton: reset, state: () => ({focused, resetCalled})};
}

test('matching demo grades both mappings independently and allows repeated choices', () => {
    for (const [answers, points] of [[{subject: 'subject', object: 'object'}, 2], [{subject: 'subject', object: 'subject'}, 1], [{subject: 'object', object: 'subject'}, 0]]) {
        const d = demo('matching', answers);
        d.submit();
        assert.match(d.feedback.children[0].textContent, new RegExp(`${points} من نقطتين`));
        assert.equal(d.feedback.hidden, false);
        assert.equal(d.controls[0].disabled, true);
        assert.equal(d.state().focused, 'feedback');
    }
});

test('choice demo explains the relation after both correct and incorrect answers', () => {
    for (const [answer, result] of [['correct', 'correct'], ['opposite', 'review']]) {
        const d = demo('choice', {answer});
        d.submit();
        assert.equal(d.feedback.dataset.result, result);
        assert.match(d.feedback.children[1].textContent, /أداة ووظيفتها/);
    }
});

test('retry clears feedback, restores controls and returns keyboard focus', () => {
    const d = demo('matching', {subject: 'subject', object: 'object'});
    d.submit();
    d.reset();
    assert.equal(d.feedback.hidden, true);
    assert.equal(d.controls[0].disabled, false);
    assert.equal(d.resetButton.hidden, true);
    assert.deepEqual(d.state(), {focused: 'input', resetCalled: true});
});
