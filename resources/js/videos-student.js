export function initializeVideoLibrary(root, platform = window) {
    const form = root.querySelector('[data-video-filters]');
    const results = root.querySelector('[data-video-results]');
    const status = root.querySelector('[data-video-status]');
    const error = root.querySelector('[data-video-error]');
    const message = root.querySelector('[data-video-error-message]');
    const retry = root.querySelector('[data-video-retry]');
    const search = form.elements.q;
    const type = form.elements.type;
    let revision = 0;
    let controller;
    let debounce;
    let pendingUrl;
    let pendingHistoryMode = 'push';
    let composing = false;
    const canonical = new URL(root.dataset.canonical, platform.location.href);
    platform.history.replaceState(platform.history.state || null, '', canonical);
    function catalogKey(url) { const value = new URL(url); value.searchParams.delete('video'); return value.href; }
    let lastCatalogKey = catalogKey(canonical);

    function cancel() {
        revision++;
        platform.clearTimeout(debounce);
        controller?.abort();
        results.setAttribute('aria-busy', 'false');
    }

    function formUrl() {
        const url = new URL(form.action);
        if (search.value.trim()) url.searchParams.set('q', search.value.trim());
        if (type.value) url.searchParams.set('type', type.value);
        return url;
    }

    function coverFailed(image) {
        if (!image.matches('.sv-cover img')) return;
        image.hidden = true;
        image.nextElementSibling.hidden = false;
    }
    function inspectCovers() {
        results.querySelectorAll('.sv-cover img').forEach(image => {
            if (image.complete && !image.naturalWidth) coverFailed(image);
        });
    }
    root.addEventListener('error', event => coverFailed(event.target), true);
    inspectCovers();

    async function load(url, historyMode = 'push', focus = false) {
        cancel();
        const current = revision;
        controller = new AbortController();
        const signal = controller.signal;
        pendingUrl = new URL(url);
        pendingHistoryMode = historyMode;
        const endpoint = new URL(root.dataset.endpoint);
        endpoint.search = pendingUrl.search;
        endpoint.searchParams.delete('video');
        error.hidden = true;
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'جارٍ تحديث الفيديوهات…';
        const timeout = platform.setTimeout(() => controllerForRequest.abort(), 15000);
        const controllerForRequest = controller;
        try {
            const response = await platform.fetch(endpoint, {credentials: 'same-origin', headers: {Accept: 'application/json'}, signal});
            if (!response.ok) throw new Error(response.status === 401 ? 'auth' : 'request');
            const data = await response.json();
            if (current !== revision) return;
            const destination = new URL(data.url, platform.location.href);
            if (destination.origin !== canonical.origin || destination.pathname !== canonical.pathname || typeof data.html !== 'string') throw new Error('payload');
            // Only the authenticated server's escaped Blade fragment is accepted here.
            results.innerHTML = data.html;
            search.value = data.state.q;
            type.value = data.state.type;
            lastCatalogKey = catalogKey(destination);
            const selected = new URL(platform.location.href).searchParams.get('video');
            if (selected) destination.searchParams.set('video', selected);
            if (historyMode === 'replace' || destination.href === platform.location.href) platform.history.replaceState(platform.history.state, '', destination);
            else platform.history.pushState(null, '', destination);
            status.textContent = `عُرضت النتائج: ${data.total} فيديو.`;
            inspectCovers();
            if (focus) {
                const summary = results.querySelector('.sv-result-summary');
                summary.setAttribute('tabindex', '-1');
                summary.focus();
                summary.scrollIntoView({block: 'start'});
            }
        } catch (failure) {
            if (current !== revision) return;
            status.textContent = '';
            message.textContent = failure.message === 'auth' ? 'انتهت الجلسة. سجّل الدخول مجددًا ثم أعد المحاولة.' : 'تعذّر تحديث الفيديوهات. تحقق من الاتصال وأعد المحاولة.';
            error.hidden = false;
        } finally {
            platform.clearTimeout(timeout);
            if (current === revision) results.setAttribute('aria-busy', 'false');
        }
    }

    function queueSearch() {
        cancel(); // Invalidate immediately, including the debounce interval.
        error.hidden = true;
        if (composing) return;
        status.textContent = 'بانتظار اكتمال البحث…';
        debounce = platform.setTimeout(() => load(formUrl()), 350);
    }
    search.addEventListener('compositionstart', () => { composing = true; cancel(); });
    search.addEventListener('compositionend', () => { composing = false; queueSearch(); });
    search.addEventListener('input', queueSearch);
    type.addEventListener('change', () => load(formUrl()));
    form.addEventListener('submit', event => { event.preventDefault(); load(formUrl()); });
    root.addEventListener('click', event => {
        const link = event.target.closest('[data-video-reset], [data-video-pagination] a');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        load(new URL(link.href), 'push', link.closest('[data-video-pagination]') !== null);
    });
    retry.addEventListener('click', () => load(pendingUrl, pendingHistoryMode, true));
    platform.addEventListener('popstate', () => {
        const url = new URL(platform.location.href);
        if (catalogKey(url) === lastCatalogKey) return;
        search.value = url.searchParams.get('q') || '';
        type.value = url.searchParams.get('type') || '';
        load(url, 'replace');
    });
    root.addEventListener('video:open', () => {
        cancel();
        const url = new URL(platform.location.href);
        search.value = url.searchParams.get('q') || '';
        type.value = url.searchParams.get('type') || '';
        status.textContent = '';
    });
    platform.addEventListener('pagehide', cancel);
    platform.addEventListener('pageshow', event => { if (event.persisted) load(new URL(platform.location.href), 'replace'); });
}

if (typeof document !== 'undefined') {
    const root = document.querySelector('[data-video-library]');
    if (root) initializeVideoLibrary(root);
}
