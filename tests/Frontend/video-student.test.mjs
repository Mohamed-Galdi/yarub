import test from 'node:test';
import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';

const source = await readFile(new URL('../../resources/js/videos-student.js', import.meta.url), 'utf8');
const {initializeVideoLibrary} = await import(`data:text/javascript;base64,${Buffer.from(source).toString('base64')}`);
const tick = () => new Promise(resolve => setImmediate(resolve));
function element(extra = {}) {
    const handlers = new Map();
    return {hidden: false, textContent: '', value: '', attrs: {}, ...extra,
        addEventListener(name, callback) { handlers.set(name, callback); },
        emit(name, value = {}) { return handlers.get(name)?.(value); },
        setAttribute(name, value) { this.attrs[name] = value; }};
}
function setup() {
    const q = element(), type = element();
    const form = element({elements: {q, type}, action: 'https://local.invalid/student-dashboard/videos'});
    const summary = element({focus() { this.focused = true; }, scrollIntoView() { this.scrolled = true; }});
    const results = element({innerHTML: '', querySelectorAll: () => [], querySelector: () => summary});
    const status = element(), error = element(), message = element(), retry = element();
    const nodes = {'[data-video-filters]': form, '[data-video-results]': results, '[data-video-status]': status, '[data-video-error]': error, '[data-video-error-message]': message, '[data-video-retry]': retry};
    const root = element({dataset: {canonical: form.action, endpoint: `${form.action}/catalog`}, querySelector: selector => nodes[selector]});
    const requests = [], changes = [], timers = new Map();
    let timerId = 0;
    const platform = element({location: {href: form.action}, history: {
        pushState(_, __, url) { platform.location.href = String(url); changes.push(['push', String(url)]); },
        replaceState(_, __, url) { platform.location.href = String(url); changes.push(['replace', String(url)]); },
    }, setTimeout(callback, delay) { timers.set(++timerId, {callback, delay}); return timerId; }, clearTimeout(id) { timers.delete(id); },
    fetch(url, options) { return new Promise((resolve, reject) => requests.push({url, options, resolve, reject})); }});
    initializeVideoLibrary(root, platform);
    const respond = (index, state, total = 2) => requests[index].resolve({ok: true, json: async () => ({html: `results:${state.q}`, state, total, url: `${form.action}?${new URLSearchParams(state)}`})});
    const submit = () => form.emit('submit', {preventDefault() {}});
    const debounce = () => { for (const [id, timer] of timers) if (timer.delay === 350) { timers.delete(id); timer.callback(); } };
    return {q, type, form, results, summary, status, error, message, retry, root, platform, requests, changes, respond, submit, debounce, timers};
}

test('debounced combined filters reset pagination and only the latest response may commit', async () => {
    const s = setup();
    s.q.value = 'قديم'; s.submit();
    s.q.value = ' جديد '; s.q.emit('input');
    assert.equal(s.requests[0].options.signal.aborted, true);
    s.respond(0, {q: 'قديم', type: '', page: 1}); await tick();
    assert.equal(s.results.innerHTML, '');
    s.type.value = 'a:2'; s.debounce();
    assert.equal(s.requests[1].url.searchParams.get('q'), 'جديد');
    assert.equal(s.requests[1].url.searchParams.get('type'), 'a:2');
    assert.equal(s.requests[1].url.searchParams.has('page'), false);
    assert.equal(s.results.attrs['aria-busy'], 'true');
    s.respond(1, {q: 'جديد', type: 'a:2', page: 1}); await tick();
    assert.equal(s.results.innerHTML, 'results:جديد');
    assert.equal(s.q.value, 'جديد');
    assert.equal(s.results.attrs['aria-busy'], 'false');
    assert.equal(s.changes.at(-1)[0], 'push');
});

test('failed updates preserve results and inputs; retry commits the requested URL', async () => {
    const s = setup(); s.results.innerHTML = 'previous results';
    s.q.value = 'عبارة'; s.submit();
    s.requests[0].reject(new Error('network')); await tick();
    assert.equal(s.error.hidden, false);
    assert.equal(s.results.innerHTML, 'previous results');
    assert.equal(s.q.value, 'عبارة');
    assert.equal(s.platform.location.href, s.form.action);
    s.retry.emit('click');
    s.respond(1, {q: 'عبارة', type: '', page: 1}); await tick();
    assert.equal(s.error.hidden, true);
    assert.equal(s.changes.at(-1)[0], 'push');
    assert.equal(new URL(s.platform.location.href).searchParams.get('q'), 'عبارة');
    assert.equal(s.summary.focused, true);
});

test('Back restores fields and results without adding a history entry', async () => {
    const s = setup();
    s.platform.location.href = `${s.form.action}?q=سياق&type=q%3A4&page=2`;
    s.platform.emit('popstate');
    assert.equal(s.q.value, 'سياق'); assert.equal(s.type.value, 'q:4');
    assert.equal(s.requests[0].url.searchParams.get('page'), '2');
    s.respond(0, {q: 'سياق', type: 'q:4', page: 2}); await tick();
    assert.equal(s.changes.at(-1)[0], 'replace');
    assert.equal(s.changes.some(([mode]) => mode === 'push'), false);
});

test('pagination moves focus after success and modified links retain browser behavior', async () => {
    const s = setup(); let prevented = false;
    const link = {href: `${s.form.action}?page=2`, closest: () => ({})};
    const event = {target: {closest: () => link}, button: 0, preventDefault() { prevented = true; }};
    s.root.emit('click', {...event, ctrlKey: true}); assert.equal(s.requests.length, 0);
    s.root.emit('click', event); assert.equal(prevented, true);
    s.respond(0, {q: '', type: '', page: 2}); await tick();
    assert.equal(s.summary.focused, true); assert.equal(s.summary.scrolled, true);
});

test('IME input waits for composition; timeout and session expiry show recoverable errors', async () => {
    const s = setup();
    s.q.emit('compositionstart'); s.q.value = 'نص'; s.q.emit('input'); s.debounce();
    assert.equal(s.requests.length, 0);
    s.q.emit('compositionend'); s.debounce();
    for (const timer of s.timers.values()) if (timer.delay === 15000) timer.callback();
    assert.equal(s.requests[0].options.signal.aborted, true);
    s.requests[0].reject(new Error('aborted')); await tick();
    assert.equal(s.error.hidden, false); assert.equal(s.results.attrs['aria-busy'], 'false');
    s.retry.emit('click'); s.requests[1].resolve({ok: false, status: 401}); await tick();
    assert.match(s.message.textContent, /انتهت الجلسة/);
});

test('an unavailable protected cover keeps its reserved frame and shows a text fallback', () => {
    const s = setup();
    const image = {hidden: false, matches: selector => selector === '.sv-cover img', nextElementSibling: {hidden: true}};
    s.root.emit('error', {target: image});
    assert.equal(image.hidden, true);
    assert.equal(image.nextElementSibling.hidden, false);
});

test('video-only Back and Forward keep the library results and filters intact', () => {
    const s = setup();
    s.platform.location.href += '?video=12'; s.platform.emit('popstate');
    assert.equal(s.requests.length, 0);
    s.platform.location.href = s.form.action; s.platform.emit('popstate');
    assert.equal(s.requests.length, 0);
});
