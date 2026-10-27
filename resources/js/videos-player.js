export function initializeVideoPlayer(root, platform = window) {
    const dialog = root.querySelector('[data-video-dialog]');
    const get = name => dialog.querySelector(`[data-${name}]`);
    const media = get('player-media'), title = get('player-title'), type = get('player-type');
    const description = get('player-description'), content = get('player-content');
    const status = get('player-status'), error = get('player-error'), errorText = get('player-error-text');
    const close = get('player-close'), play = get('player-play'), retry = get('player-retry');
    const related = get('player-related'), relatedSection = get('player-related-section');
    const shareStatus = get('share-status'), manual = get('share-manual');
    const copyInput = manual.querySelector('input');
    let request, revision = 0, current, currentId, origin, scroll = 0, overflow = '';
    let backdropDown = false;
    const base = url => { const value = new URL(url); value.searchParams.delete('video'); return value; };

    function release() {
        revision++;
        request?.abort();
        media.pause();
        media.removeAttribute('src');
        media.removeAttribute('poster');
        media.load();
        current = null;
        play.hidden = true;
    }
    function dismiss() {
        release(); currentId = null;
        if (!dialog.open) return;
        dialog.close();
        platform.document.documentElement.style.overflow = overflow;
        platform.scrollTo({top: scroll, behavior: 'instant'});
        const fallback = root.querySelector('[data-video-filters] input');
        (origin?.isConnected ? origin : fallback)?.focus({preventScroll: true});
    }
    function closePlayer() {
        const url = base(platform.location.href);
        const owned = platform.history.state?.yarubVideoOverlay;
        dismiss();
        if (owned?.base === url.href) platform.history.back();
        else platform.history.replaceState(null, '', url);
    }
    function reportError(text) {
        status.textContent = '';
        errorText.textContent = text;
        error.hidden = false;
    }
    async function attemptPlay() {
        const source = current;
        try { await media.play(); play.hidden = true; }
        catch { if (current && current === source && dialog.open) { play.hidden = false; status.textContent = 'اضغط تشغيل لبدء المشاهدة.'; } }
    }
    function renderRelated(items) {
        related.replaceChildren(); relatedSection.hidden = items.length === 0;
        for (const item of items) {
            const link = platform.document.createElement('a');
            const url = new URL(platform.location.href); url.searchParams.set('video', item.id);
            link.href = url.href; link.dataset.openVideo = String(item.id); link.dataset.relatedVideo = '';
            const image = platform.document.createElement('img');
            image.src = item.cover; image.alt = ''; image.width = 54; image.height = 96; image.loading = 'lazy';
            image.addEventListener('error', () => { image.style.visibility = 'hidden'; });
            const text = platform.document.createElement('span');
            const heading = platform.document.createElement('strong'); heading.textContent = item.title;
            const label = platform.document.createElement('small'); label.textContent = item.type;
            text.append(heading, label); link.append(image, text); related.append(link);
        }
    }
    async function show(id, autoplay = false) {
        release();
        currentId = id;
        const version = revision;
        if (!dialog.open) {
            scroll = platform.scrollY;
            overflow = platform.document.documentElement.style.overflow;
            platform.document.documentElement.style.overflow = 'hidden';
            dialog.showModal(); close.focus();
        }
        close.focus();
        content.hidden = true; error.hidden = true; manual.hidden = true;
        title.textContent = 'الفيديو'; status.textContent = 'جارٍ تحميل الفيديو…'; shareStatus.textContent = '';
        if (!/^[1-9][0-9]{0,17}$/.test(id)) { reportError('هذا الفيديو غير متاح حاليًا. يمكنك إغلاق المشغّل واختيار فيديو آخر.'); return; }
        request = new AbortController();
        const thisRequest = request;
        const timeout = platform.setTimeout(() => thisRequest.abort(), 15000);
        try {
            const response = await platform.fetch(`${root.dataset.playerEndpoint}/${id}`, {credentials: 'same-origin', headers: {Accept: 'application/json'}, signal: request.signal});
            if (!response.ok) throw new Error([403,404].includes(response.status) ? 'unavailable' : response.status === 401 ? 'auth' : 'network');
            const data = await response.json();
            if (version !== revision || !dialog.open) return;
            current = data;
            title.textContent = data.title; type.textContent = data.type;
            description.textContent = data.description || ''; description.hidden = !data.description;
            content.hidden = false; media.setAttribute('aria-label', data.title);
            media.poster = data.cover; media.src = data.media; media.load();
            renderRelated(data.related);
            status.textContent = 'اضغط تشغيل لبدء المشاهدة.';
            if (autoplay) await attemptPlay();
        } catch (failure) {
            if (version !== revision) return;
            reportError(failure.message === 'unavailable' ? 'هذا الفيديو غير متاح حاليًا. يمكنك إغلاق المشغّل واختيار فيديو آخر.' : failure.message === 'auth' ? 'انتهت الجلسة. سجّل الدخول مجددًا لمتابعة المشاهدة.' : 'تعذّر تحميل الفيديو. تحقق من الاتصال وأعد المحاولة.');
        } finally { platform.clearTimeout(timeout); }
    }
    function navigate(id, trigger, replace = false) {
        root.dispatchEvent(new Event('video:open'));
        const url = new URL(platform.location.href);
        if (!dialog.open) origin = trigger;
        const marker = dialog.open ? platform.history.state : {yarubVideoOverlay: {base: base(url).href}};
        url.searchParams.set('video', id);
        platform.history[replace ? 'replaceState' : 'pushState'](marker, '', url);
        show(id, true);
    }
    root.addEventListener('click', event => {
        const link = event.target.closest('[data-open-video]');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        navigate(link.dataset.openVideo, link, link.hasAttribute('data-related-video'));
    });
    close.addEventListener('click', closePlayer);
    dialog.addEventListener('cancel', event => { event.preventDefault(); closePlayer(); });
    function outside(event) {
        const rect = dialog.getBoundingClientRect();
        return event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom);
    }
    dialog.addEventListener('pointerdown', event => { backdropDown = outside(event); });
    dialog.addEventListener('click', event => { if (backdropDown && outside(event)) closePlayer(); backdropDown = false; });
    retry.addEventListener('click', () => show(currentId));
    play.addEventListener('click', attemptPlay);
    media.addEventListener('waiting', () => { if (current) status.textContent = 'جارٍ تحميل المقطع…'; });
    media.addEventListener('playing', () => { status.textContent = ''; play.hidden = true; });
    media.addEventListener('canplay', () => { if (current) status.textContent = media.paused ? 'اضغط تشغيل لبدء المشاهدة.' : ''; });
    media.addEventListener('error', () => { if (current && media.hasAttribute('src')) { media.pause(); reportError('تعذّر تشغيل الفيديو. أعد المحاولة، أو اختر فيديو آخر.'); } });
    async function copyLink() {
        if (!current) return;
        const record = current, version = revision;
        try {
            await platform.navigator.clipboard.writeText(record.share_url);
            if (version === revision && dialog.open) shareStatus.textContent = 'نُسخ رابط الفيديو.';
        } catch {
            if (version !== revision || !dialog.open) return;
            manual.hidden = false; copyInput.value = record.share_url; copyInput.focus(); copyInput.select(); shareStatus.textContent = 'انسخ الرابط من الحقل المحدد.';
        }
    }
    get('player-copy').addEventListener('click', copyLink);
    get('player-share').addEventListener('click', async () => {
        if (!current) return;
        if (!platform.navigator.share) { await copyLink(); return; }
        const record = current, version = revision;
        try {
            await platform.navigator.share({title: record.title, url: record.share_url});
            if (version === revision && dialog.open) shareStatus.textContent = 'تمت المشاركة.';
        } catch (failure) {
            if (version !== revision || !dialog.open) return;
            if (failure.name === 'AbortError') shareStatus.textContent = 'أُلغيت المشاركة.'; else await copyLink();
        }
    });
    function restore() {
        const id = new URL(platform.location.href).searchParams.get('video');
        if (id !== null) show(id); else dismiss();
    }
    platform.addEventListener('popstate', restore);
    platform.addEventListener('pagehide', () => { release(); });
    platform.addEventListener('pageshow', event => { if (event.persisted) restore(); });
    restore();
    return {show, dismiss, navigate, closePlayer};
}

if (typeof document !== 'undefined') {
    const root = document.querySelector('[data-video-library]');
    if (root) initializeVideoPlayer(root);
}
