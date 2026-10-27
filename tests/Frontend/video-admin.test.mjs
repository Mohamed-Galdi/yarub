import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/videos-admin.js', import.meta.url), 'utf8');

function editor(kind = 'upload', uploadTimeout) {
    const requests = [], navigation = [], stored = new Map();
    const entries = new Map([['title', 'عنوان محفوظ'], ['description', 'وصف محفوظ'], ['type', 'a:42'], ['request_key', 'same-logical-request'], ['video', {size: 12}]]);
    const element = () => ({hidden: true, disabled: false, textContent: '', attrs: {}, setAttribute(k, v) { this.attrs[k] = v; }, removeAttribute(k) { delete this.attrs[k]; }, focus() { this.focused = true; }});
    const error = element(), fieldError = element(), state = element(), message = element(), progress = element(), type = element();
    const controls = [type, element()];
    const form = {
        dataset: {kind}, action: 'http://local/admin-dashboard/videos', attrs: {},
        reportValidity: () => true, getAttribute(k) { return this.attrs[k]; }, setAttribute(k, v) { this.attrs[k] = v; }, removeAttribute(k) { delete this.attrs[k]; },
        addEventListener(name, callback) { this[name] = callback; },
        querySelectorAll(selector) { return selector === '[data-error]' ? [fieldError] : selector === '[aria-invalid]' ? controls.filter(c => c.attrs['aria-invalid']) : controls; },
        querySelector(selector) {
            return ({'[data-form-error]': error, '[data-upload-state]': state, '[data-upload-message]': message, progress,
                '[data-error="type"]': fieldError, '[name="type"]': type, '[aria-invalid]': controls.find(c => c.attrs['aria-invalid'])})[selector] || null;
        },
    };
    const root = {
        dataset: {editBase: 'http://local/admin-dashboard/videos', uploadTimeout},
        querySelector(selector) { return selector === '[data-page-error]' ? element() : selector === '.video-back' ? {href: 'http://local/admin-dashboard/videos?q=local'} : null; },
        querySelectorAll(selector) { return selector === '[data-video-form]' ? [form] : []; },
    };
    class Data {
        constructor() { this.values = new Map(entries); }
        get(k) { return this.values.get(k); } set(k, v) { this.values.set(k, v); } delete(k) { this.values.delete(k); }
    }
    class Request {
        constructor() { this.events = {}; this.upload = {addEventListener: (event, fn) => { this.events[`upload-${event}`] = fn; }}; requests.push(this); }
        open(method, url) { this.method = method; this.url = url; }
        setRequestHeader() {}
        addEventListener(event, fn) { this.events[event] = fn; }
        send(data) { this.data = data; }
        respond(status, result) { this.status = status; this.responseText = JSON.stringify(result); this.events.load(); }
    }
    vm.runInNewContext(source, {
        document: {querySelector: selector => selector === '[data-video-admin]' ? root : {content: 'local-csrf'}, addEventListener() {}},
        window: {addEventListener() {}, location: {assign: url => navigation.push(url), reload() {}}},
        sessionStorage: {getItem: k => stored.get(k), removeItem: k => stored.delete(k), setItem: (k, v) => stored.set(k, v)},
        FormData: Data, XMLHttpRequest: Request, URL, AbortController, setInterval: () => 1, clearInterval() {},
    });
    return {requests, entries, form, controls, error, fieldError, state, message, progress, navigation, submit: () => form.submit({preventDefault() {}})};
}

test('real upload percentage switches to a separate indeterminate processing state', () => {
    const e = editor(); e.submit();
    e.requests[0].events['upload-progress']({lengthComputable: true, loaded: 40, total: 100});
    assert.equal(e.progress.value, 40); assert.match(e.message.textContent, /40٪/);
    e.requests[0].events['upload-progress']({lengthComputable: true, loaded: 100, total: 100});
    assert.match(e.message.textContent, /جارٍ فحص الفيديو/);
    assert.equal(e.progress.attrs.value, undefined);
    assert.equal(e.requests[0].data.get('arabic_skill_bank_id'), '42');
    assert.equal(e.requests[0].data.get('qudrat_category_id'), '');
});

test('media deadline includes transfer and processing; server-size rejection preserves retry inputs', () => {
    const e = editor(); e.submit();
    assert.equal(e.requests[0].timeout, 450000);
    e.requests[0].respond(413, {});
    assert.match(e.error.textContent, /حد الخادم/);
    assert.equal(e.entries.get('title'), 'عنوان محفوظ');
    assert.ok(e.controls.every(c => !c.disabled));
    const configured = editor('media', 700000); configured.submit();
    assert.equal(configured.requests[0].timeout, 700000);
    const invalid = editor('upload', -1); invalid.submit();
    assert.equal(invalid.requests[0].timeout, 450000);
});

test('duplicate submits are blocked and a connection retry keeps the same request key', () => {
    const e = editor(); e.submit(); e.submit();
    assert.equal(e.requests.length, 1); assert.ok(e.controls.every(c => c.disabled));
    e.requests[0].events.error();
    assert.ok(e.controls.every(c => !c.disabled)); assert.equal(e.entries.get('title'), 'عنوان محفوظ');
    e.submit(); assert.equal(e.requests.length, 2);
    assert.equal(e.requests[0].data.get('request_key'), e.requests[1].data.get('request_key'));
});

test('category validation maps to the type control without losing inputs', () => {
    const e = editor(); e.submit();
    e.requests[0].respond(422, {errors: {arabic_skill_bank_id: ['اختر نوعًا متاحًا.']}});
    assert.equal(e.fieldError.textContent, 'اختر نوعًا متاحًا.'); assert.equal(e.fieldError.hidden, false);
    assert.equal(e.controls[0].attrs['aria-invalid'], 'true'); assert.ok(e.controls[0].focused);
    assert.equal(e.entries.get('description'), 'وصف محفوظ'); assert.equal(e.entries.get('video').size, 12);
});

test('metadata saves finish even when an independent media operation is processing', () => {
    const e = editor('metadata'); e.submit();
    e.requests[0].respond(202, {video: {id: 7}, processing: true});
    assert.deepEqual(e.navigation, ['http://local/admin-dashboard/videos/7/edit?q=local']);
    assert.equal(e.form.attrs['aria-busy'], undefined);
});
