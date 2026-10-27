import test from 'node:test';
import assert from 'node:assert/strict';
import {initializeVideoPlayer} from '../../resources/js/videos-player.js';

const tick = () => new Promise(resolve => setImmediate(resolve));
function element(extra = {}) {
    const listeners = new Map();
    return {hidden: false, value: '', textContent: '', attrs: {}, style: {}, dataset: {}, isConnected: true, children: [], ...extra,
        addEventListener(name, fn) { listeners.set(name, fn); }, emit(name, event = {}) { return listeners.get(name)?.(event); },
        dispatchEvent(event) { return this.emit(event.type, event); },
        setAttribute(name, value) { this.attrs[name] = value; }, removeAttribute(name) { delete this.attrs[name]; if (name === 'src') this.src = ''; },
        hasAttribute(name) { return Object.hasOwn(this.attrs, name); }, append(...items) { this.children.push(...items); }, replaceChildren(...items) { this.children = items; },
        focus() { this.focused = true; }, select() { this.selected = true; }};
}
function setup(initial = '') {
    const nodes = {};
    for (const name of ['player-media','player-title','player-type','player-description','player-content','player-status','player-error','player-error-text','player-close','player-play','player-retry','player-related','player-related-section','share-status','share-manual','player-copy','player-share']) nodes[name] = element();
    const media = nodes['player-media'];
    media.pause = () => { media.paused = true; media.pauseCount = (media.pauseCount || 0) + 1; };
    media.play = async () => { if (media.rejectPlay) throw new Error('browser denied'); media.paused = false; };
    media.load = () => { media.loads = (media.loads || 0) + 1; };
    const input = element(); nodes['share-manual'].querySelector = () => input;
    const dialog = element({open: false, querySelector: s => nodes[s.match(/data-(.*)]/)[1]], showModal() { this.open = true; }, close() { this.open = false; }});
    const fallback = element();
    const root = element({dataset: {playerEndpoint: 'https://local.invalid/student-dashboard/videos'}, querySelector: s => s === '[data-video-dialog]' ? dialog : fallback});
    const calls = [], history = [], timers = new Map(); let timerId = 0;
    const platform = element({location: {href: `${root.dataset.playerEndpoint}?q=معنى&type=q%3A1&page=2${initial}`}, scrollY: 800,
        document: {documentElement: {style: {overflow: ''}}, createElement: () => element()}, navigator: {clipboard: {writeText: async value => { platform.copied = value; }}},
        setTimeout(fn) { timers.set(++timerId, fn); return timerId; }, clearTimeout(id) { timers.delete(id); }, scrollTo(value) { platform.scrollY = value.top; },
        fetch(url, options) { return new Promise((resolve, reject) => calls.push({url, options, resolve, reject})); }});
    platform.history = {state: null,
        pushState(state, _, url) { this.state = state; platform.location.href = String(url); history.push('push'); },
        replaceState(state, _, url) { this.state = state; platform.location.href = String(url); history.push('replace'); },
        back() { history.push('back'); }};
    const api = initializeVideoPlayer(root, platform);
    const respond = (index, id = 1, related = []) => calls[index].resolve({ok: true, json: async () => ({id, title: `عنوان ${id}`, type: 'نوع', description: '<script>plain</script>', cover: '/cover', media: `/media/${id}`, share_url: `${root.dataset.playerEndpoint}?video=${id}`, related})});
    return {api, nodes, media, dialog, fallback, root, platform, calls, history, respond, input};
}

test('card entry pushes one overlay entry; related selection replaces it; close stops media and goes Back', async () => {
    const s = setup(), origin = element();
    s.api.navigate('1', origin); s.respond(0); await tick();
    assert.equal(s.dialog.open, true); assert.equal(s.history.at(-1), 'push');
    assert.equal(s.platform.document.documentElement.style.overflow, 'hidden');
    assert.equal(s.media.paused, false); assert.equal(s.media.src, '/media/1');
    assert.equal(new URL(s.platform.location.href).searchParams.get('q'), 'معنى');
    s.api.navigate('2', element(), true); assert.equal(s.media.src, ''); assert.equal(s.media.paused, true);
    s.respond(1, 2); await tick(); assert.equal(s.history.at(-1), 'replace');
    s.api.closePlayer();
    assert.equal(s.history.at(-1), 'back'); assert.equal(s.dialog.open, false);
    assert.equal(s.media.src, ''); assert.equal(s.media.paused, true);
    assert.equal(s.platform.scrollY, 800); assert.equal(origin.focused, true);
    assert.equal(s.platform.document.documentElement.style.overflow, '');
});

test('direct links never autoplay and closing replaces URL without leaving the library', async () => {
    const s = setup('&video=4'); s.respond(0, 4); await tick();
    assert.equal(s.media.paused, true);
    assert.equal(s.nodes['player-description'].textContent, '<script>plain</script>');
    s.api.closePlayer(); assert.deepEqual(s.history, ['replace']);
    assert.equal(new URL(s.platform.location.href).searchParams.has('video'), false);
    assert.equal(new URL(s.platform.location.href).searchParams.get('page'), '2');
    assert.equal(s.fallback.focused, true);
});

test('Back releases bytes and Forward refetches eligibility instead of reusing old metadata', async () => {
    const s = setup('&video=4'); s.respond(0, 4); await tick();
    s.platform.location.href = s.platform.location.href.replace('&video=4', ''); s.platform.emit('popstate');
    assert.equal(s.dialog.open, false); assert.equal(s.media.src, '');
    s.platform.location.href += '&video=4'; s.platform.emit('popstate');
    s.calls[1].resolve({ok: false, status: 404}); await tick();
    assert.equal(s.nodes['player-content'].hidden, true);
    assert.match(s.nodes['player-error-text'].textContent, /غير متاح/);
    assert.equal(s.media.src, '');
});

test('changing or closing while metadata is in flight cannot resurrect stale playback', async () => {
    const s = setup();
    s.api.show('1', true); s.api.show('2');
    assert.equal(s.calls[0].options.signal.aborted, true);
    s.respond(1, 2); s.respond(0, 1); await tick();
    assert.equal(s.nodes['player-title'].textContent, 'عنوان 2'); assert.equal(s.media.src, '/media/2');
    s.api.show('3'); s.api.dismiss(); s.respond(2, 3); await tick();
    assert.equal(s.dialog.open, false); assert.equal(s.media.src, '');
});

test('blocked autoplay leaves an explicit play button and sharing copies a clean protected URL', async () => {
    const s = setup(); s.media.rejectPlay = true;
    s.api.navigate('1', element()); s.respond(0); await tick();
    assert.equal(s.nodes['player-play'].hidden, false);
    s.media.rejectPlay = false; await s.nodes['player-play'].emit('click');
    assert.equal(s.media.paused, false);
    await s.nodes['player-share'].emit('click');
    assert.equal(s.platform.copied, 'https://local.invalid/student-dashboard/videos?video=1');
    s.platform.navigator.clipboard.writeText = async () => { throw new Error('denied'); };
    await s.nodes['player-copy'].emit('click');
    assert.equal(s.nodes['share-manual'].hidden, false); assert.equal(s.input.selected, true);
    assert.equal(s.input.value, s.platform.copied);
});

test('malformed IDs never fetch metadata and Escape uses the same safe close behavior', () => {
    const s = setup('&video=invalid');
    assert.equal(s.calls.length, 0); assert.equal(s.dialog.open, true);
    let prevented = false;
    s.dialog.emit('cancel', {preventDefault() { prevented = true; }});
    assert.equal(prevented, true); assert.equal(s.dialog.open, false); assert.equal(s.history.at(-1), 'replace');
});

test('native sharing receives a clean URL and cancellation does not copy without consent', async () => {
    const s = setup('&video=1'); s.respond(0); await tick();
    let shared;
    s.platform.navigator.share = async value => { shared = value; };
    await s.nodes['player-share'].emit('click');
    assert.equal(shared.url, 'https://local.invalid/student-dashboard/videos?video=1');
    assert.equal(s.nodes['player-related-section'].hidden, true);
    s.platform.navigator.share = async () => { const error = new Error('cancelled'); error.name = 'AbortError'; throw error; };
    await s.nodes['player-share'].emit('click');
    assert.equal(s.platform.copied, undefined);
    assert.equal(s.nodes['share-status'].textContent, 'أُلغيت المشاركة.');
});

test('late clipboard rejection after closing cannot reopen fallback or access released metadata', async () => {
    const s = setup('&video=1'); s.respond(0); await tick();
    let rejectCopy;
    s.platform.navigator.clipboard.writeText = () => new Promise((_, reject) => { rejectCopy = reject; });
    const copying = s.nodes['player-copy'].emit('click');
    s.api.dismiss(); rejectCopy(new Error('permission denied'));
    await assert.doesNotReject(copying);
    assert.equal(s.nodes['share-manual'].hidden, true);
});

test('late native share failure cannot copy a newly selected video', async () => {
    const s = setup('&video=1'); s.respond(0); await tick();
    let rejectShare;
    s.platform.navigator.share = () => new Promise((_, reject) => { rejectShare = reject; });
    const sharing = s.nodes['player-share'].emit('click');
    s.api.show('2'); s.respond(1, 2); await tick();
    rejectShare(new Error('device share failed')); await sharing;
    assert.equal(s.platform.copied, undefined);
    assert.equal(s.nodes['share-status'].textContent, '');
});
