import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/arabic-skills-student.js', import.meta.url), 'utf8').replace(/^import .*;$/gm, '');

function harness({hint = false, stalled = false} = {}) {
    let submit, timer, delay, cleared = false, focused = null, busy = false;
    const status = {textContent: ''};
    const paragraph = {textContent: ''};
    const input = {disabled: false, value: 'a'};
    const trigger = {disabled: false, focus: () => { focused = 'trigger'; }};
    const aside = {hidden: true, querySelector: () => paragraph, focus: () => { focused = 'hint'; }, getBoundingClientRect: () => ({top: 10, bottom: 100})};
    const root = {
        querySelector: selector => selector === '[data-arabic-status]' ? status : selector === '[data-arabic-hint-content]' ? aside : null,
        querySelectorAll: () => [input, trigger], setAttribute: () => { busy = true; }, removeAttribute: () => { busy = false; },
    };
    const document = {querySelector: () => root, addEventListener: (name, handler) => { if (name === 'submit') submit = handler; }};
    const window = {innerHeight: 844, setTimeout: (callback, ms) => { timer = callback; delay = ms; return 1; }, clearTimeout: () => { cleared = true; }};
    const fetch = async (url, options) => {
        if (stalled) return new Promise((resolve, reject) => options.signal.addEventListener('abort', () => reject(new Error('aborted')), {once: true}));
        return {ok: true, json: async () => ({hint: 'تلميح حقيقي'})};
    };
    vm.runInNewContext(source, {window, document, fetch, AbortController, Alpine: {start() {}}, FormData: class {}});
    const form = {action: '/local-test', dataset: {arabicAction: 'answer'}, matches: selector => selector.includes(',') || hint};

    return {run: () => submit({target: form, submitter: trigger, preventDefault() {}}), expire: () => timer(), input, trigger, status, aside, paragraph,
        state: () => ({delay, cleared, focused, busy})};
}

test('stalled requests abort and restore the selected answer and initiating keyboard control', async () => {
    const h = harness({stalled: true});
    const request = h.run();
    assert.equal(h.state().delay, 20000);
    assert.equal(h.input.disabled, true);
    h.expire();
    await request;
    assert.equal(h.input.disabled, false);
    assert.equal(h.trigger.disabled, false);
    assert.equal(h.input.value, 'a');
    assert.equal(h.state().focused, 'trigger');
    assert.equal(h.state().busy, false);
    assert.equal(h.state().cleared, true);
    assert.match(h.status.textContent, /أعد المحاولة/);
});

test('genuine hints receive focus after reveal without submitting an answer', async () => {
    const h = harness({hint: true});
    await h.run();
    assert.equal(h.aside.hidden, false);
    assert.equal(h.paragraph.textContent, 'تلميح حقيقي');
    assert.equal(h.state().focused, 'hint');
    assert.equal(h.state().busy, false);
    assert.equal(h.input.value, 'a');
    assert.equal(h.state().cleared, true);
});
