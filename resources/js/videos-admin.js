const root = document.querySelector('[data-video-admin]');
if (root) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const pageError = root.querySelector('[data-page-error]');
    const dialog = root.querySelector('[data-preview-dialog]');
    const dialogVideo = dialog?.querySelector('video');
    const localUrls = new Map();
    let busy = 0;
    const notify = message => window.Swal ? window.Swal.fire({ toast: true, position: 'top', icon: 'success', title: message, timer: 1800, showConfirmButton: false }) : null;
    const feedback = { metadata: 'تم حفظ البيانات.', media: 'تم تجهيز الفيديو والغلاف.', visibility: 'تم تحديث ظهور الفيديو.', example: 'تم تحديث المثال العام.', delete: 'تم حذف الفيديو.' };
    const flash = value => { try { sessionStorage.setItem('yarub-video-feedback', value); } catch { /* Feedback never blocks a save. */ } };
    try {
        const saved = sessionStorage.getItem('yarub-video-feedback'); sessionStorage.removeItem('yarub-video-feedback');
        if (feedback[saved]) { const success = root.querySelector('[data-page-success]'); success.textContent = feedback[saved]; success.hidden = false; notify(feedback[saved]); }
    } catch { /* Session storage may be unavailable. */ }
    const showError = (element, message) => { element.textContent = message; element.hidden = false; element.focus(); };
    const failureMessage = status => status === 419 || status === 401 ? 'انتهت الجلسة. سجّل الدخول مجددًا ثم أعد المحاولة.' : status === 413 ? 'حجم الطلب يتجاوز حد الخادم. استخدم ملفًا أصغر أو تواصل مع المسؤول.' : 'تعذّر إكمال الطلب. حاول مرة أخرى.';
    async function confirmAction(title, text, destructive = false) {
        if (window.Swal) {
            return (await window.Swal.fire({ title, text, icon: destructive ? 'warning' : 'question', showCancelButton: true,
                confirmButtonText: destructive ? 'حذف الفيديو' : 'تأكيد', cancelButtonText: 'إلغاء', confirmButtonColor: destructive ? '#991b1b' : '#292a65', focusCancel: true })).isConfirmed;
        }
        return window.confirm(`${title}\n${text}`);
    }
    async function mutate(url, method, data) {
        const response = await fetch(url, { method, credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(data) });
        const result = response.status === 204 ? {} : await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || failureMessage(response.status));
        return result;
    }
    root.querySelectorAll('[data-action]').forEach(button => button.addEventListener('click', async () => {
        if (button.disabled) return;
        const action = button.dataset.action;
        if (action === 'delete' && !await confirmAction('حذف الفيديو؟', `سيُحذف «${button.dataset.title}» وملفاته نهائيًا.`, true)) return;
        if (action === 'example' && button.dataset.exampleId && !await confirmAction('اختيار المثال العام؟', `سيظهر «${button.dataset.title}» لزوار الموقع، وسيحل محل المثال الحالي.`)) return;
        button.disabled = true;
        pageError.hidden = true;
        try {
            if (action === 'delete') await mutate(button.dataset.url, 'DELETE', { confirmation: button.dataset.title });
            if (action === 'visibility') await mutate(button.dataset.url, 'PATCH', { is_visible: button.dataset.visible === '1' });
            if (action === 'example') await mutate(root.dataset.exampleUrl, 'PUT', { video_id: button.dataset.exampleId ? Number(button.dataset.exampleId) : null });
            flash(action);
            if (button.dataset.return) window.location.assign(button.dataset.return);
            else window.location.reload();
        } catch (error) { showError(pageError, error.message); button.disabled = false; }
    }));
    root.querySelectorAll('.video-actions-menu').forEach(menu => menu.addEventListener('toggle', () => {
        if (menu.open) root.querySelectorAll('.video-actions-menu').forEach(other => { if (other !== menu) other.open = false; });
    }));
    document.addEventListener('click', event => root.querySelectorAll('.video-actions-menu').forEach(menu => { if (!menu.contains(event.target)) menu.open = false; }));
    root.querySelectorAll('[data-preview]').forEach(button => button.addEventListener('click', () => {
        root.querySelectorAll('.video-actions-menu').forEach(menu => { menu.open = false; });
        dialog.querySelector('[data-preview-title]').textContent = button.dataset.title;
        dialog.querySelector('[data-playback-error]').hidden = true;
        dialogVideo.poster = button.dataset.poster;
        dialogVideo.src = button.dataset.preview;
        document.documentElement.style.overflow = 'hidden';
        dialog.showModal();
    }));
    dialog?.querySelector('[data-preview-close]').addEventListener('click', () => dialog.close());
    dialog?.addEventListener('click', event => { if (event.target === dialog) { const r = dialog.getBoundingClientRect(); if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close(); } });
    dialog?.addEventListener('close', () => { dialogVideo.pause(); dialogVideo.removeAttribute('src'); dialogVideo.load(); document.documentElement.style.overflow = ''; });
    dialogVideo?.addEventListener('error', () => { dialog.querySelector('[data-playback-error]').hidden = false; });
    root.querySelectorAll('.video-thumb img').forEach(image => image.addEventListener('error', () => { image.hidden = true; }));
    root.querySelector('[data-filter-form]')?.addEventListener('submit', event => {
        const button = event.target.querySelector('button');
        button.disabled = true; button.textContent = 'جارٍ عرض النتائج…';
        event.target.setAttribute('aria-busy', 'true');
    });
    function localFile(name, file) {
        if (localUrls.has(name)) URL.revokeObjectURL(localUrls.get(name));
        localUrls.delete(name);
        if (!file) return null;
        const url = URL.createObjectURL(file); localUrls.set(name, url); return url;
    }
    const formVideo = root.querySelector('[data-form-video]');
    const originalSource = formVideo?.getAttribute('src');
    const originalPoster = formVideo?.getAttribute('poster');
    root.querySelector('input[name="video"]')?.addEventListener('change', event => {
        const url = localFile('video', event.target.files[0]);
        formVideo.pause();
        if (url || originalSource) formVideo.src = url || originalSource;
        else { formVideo.removeAttribute('src'); formVideo.load(); }
        formVideo.hidden = !url && !originalSource;
        root.querySelector('[data-preview-placeholder]').hidden = !formVideo.hidden;
        root.querySelector('[data-form-playback-error]').hidden = true;
    });
    root.querySelector('input[name="cover"]')?.addEventListener('change', event => {
        const url = localFile('cover', event.target.files[0]);
        const slot = root.querySelector('[data-cover-image-slot]');
        let image = slot.querySelector('img');
        if (url || originalPoster) {
            if (!image) { image = document.createElement('img'); image.alt = 'غلاف الفيديو'; image.width = 90; image.height = 160; }
            image.src = url || originalPoster; slot.replaceChildren(image);
        } else slot.replaceChildren();
        root.querySelector('[data-cover-preview]').hidden = !url && !originalPoster;
        root.querySelector('[data-cover-caption]').textContent = url ? 'الغلاف المختار' : 'الغلاف الحالي';
        formVideo.poster = url || originalPoster || '';
        const reset = root.querySelector('[name="reset_cover"]');
        if (reset) { reset.checked = false; reset.disabled = Boolean(url); }
    });
    formVideo?.addEventListener('error', () => { root.querySelector('[data-form-playback-error]').hidden = false; });
    root.querySelectorAll('[data-video-form]').forEach(form => form.addEventListener('submit', event => {
        event.preventDefault();
        if (form.getAttribute('aria-busy') === 'true' || !form.reportValidity()) return;
        const errorBox = form.querySelector('[data-form-error]');
        form.querySelectorAll('[data-error]').forEach(e => { e.hidden = true; e.textContent = ''; });
        form.querySelectorAll('[aria-invalid]').forEach(e => e.removeAttribute('aria-invalid'));
        errorBox.hidden = true;
        const data = new FormData(form);
        const type = data.get('type');
        if (type) {
            const [program, id] = type.split(':'); data.delete('type');
            data.set('qudrat_category_id', program === 'q' ? id : '');
            data.set('arabic_skill_bank_id', program === 'a' ? id : '');
        }
        if (form.dataset.kind === 'media' && !data.get('video')?.size && !data.get('cover')?.size && !data.get('reset_cover')) {
            showError(errorBox, 'اختر فيديو أو غلافًا، أو اطلب إعادة إنشاء الغلاف.'); return;
        }
        const controls = [...form.querySelectorAll('input, select, textarea, button')];
        const enabled = controls.filter(e => !e.disabled);
        enabled.forEach(e => { e.disabled = true; });
        form.setAttribute('aria-busy', 'true'); busy++;
        const state = form.querySelector('[data-upload-state]');
        const message = form.querySelector('[data-upload-message]');
        const progress = form.querySelector('progress');
        progress.setAttribute('aria-label', form.dataset.kind === 'metadata' ? 'حالة حفظ البيانات' : 'تقدّم رفع الملفات');
        state.hidden = false; progress.value = 0;
        message.textContent = form.dataset.kind === 'metadata' ? 'جارٍ حفظ البيانات…' : 'جارٍ رفع الملفات…';
        if (form.dataset.kind === 'metadata') progress.removeAttribute('value');
        const xhr = new XMLHttpRequest();
        let active = true, pollTimer, pollBusy = false;
        const pollAbort = new AbortController();
        const processing = () => {
            message.textContent = 'اكتمل الرفع. جارٍ فحص الفيديو وإعداد الغلاف…'; progress.removeAttribute('value');
            if (!pollTimer && form.dataset.kind !== 'metadata') pollTimer = setInterval(async () => {
                if (pollBusy || !active) return;
                pollBusy = true;
                try {
                    const response = await fetch(`${root.dataset.editBase}/operations/${data.get('request_key')}`, { headers: { Accept: 'application/json' }, signal: pollAbort.signal });
                    if (response.ok) { const result = await response.json(); if (active && result.processing) message.textContent = 'جارٍ فحص الفيديو وإعداد الغلاف…'; }
                } catch { /* The original request remains authoritative. */ }
                finally { pollBusy = false; }
            }, 1800);
        };
        xhr.open('POST', form.action);
        xhr.setRequestHeader('Accept', 'application/json'); xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        const uploadTimeout = Number(root.dataset.uploadTimeout);
        xhr.timeout = form.dataset.kind === 'metadata' ? 150000 : (Number.isFinite(uploadTimeout) && uploadTimeout >= 150000 && uploadTimeout <= 1800000 ? uploadTimeout : 450000);
        xhr.upload.addEventListener('progress', e => {
            if (form.dataset.kind === 'metadata') return;
            if (e.lengthComputable && e.loaded < e.total) { const value = Math.floor(e.loaded / e.total * 100); progress.value = value; message.textContent = `جارٍ الرفع… ${value}٪`; }
            else processing();
        });
        const finish = () => { active = false; clearInterval(pollTimer); pollAbort.abort(); enabled.forEach(e => { e.disabled = false; }); form.removeAttribute('aria-busy'); busy--; };
        const saved = result => {
            state.hidden = true; flash(form.dataset.kind === 'metadata' ? 'metadata' : 'media');
            const back = new URL(root.querySelector('.video-back').href);
            window.location.assign(`${root.dataset.editBase}/${result.video.id}/edit${back.search}`);
        };
        async function recoverProcessing() {
            state.hidden = false; progress.removeAttribute('value'); message.textContent = 'لا تزال المعالجة جارية…';
            enabled.forEach(e => { e.disabled = true; }); form.setAttribute('aria-busy', 'true'); busy++;
            const deadline = Date.now() + 150000;
            try {
                while (Date.now() < deadline) {
                    await new Promise(resolve => setTimeout(resolve, 1800));
                    const response = await fetch(`${root.dataset.editBase}/operations/${data.get('request_key')}`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error(failureMessage(response.status));
                    const result = await response.json();
                    if (!result.processing) {
                        if (result.video.processing_state !== 'ready' || result.processing_error) throw new Error(result.processing_error || 'تعذّرت المعالجة. أعد المحاولة.');
                        saved(result); return;
                    }
                }
                throw new Error('المعالجة لم تكتمل بعد. عد إلى قائمة الفيديوهات لمتابعة الحالة، أو تحقق من الحالة ثم أعد المحاولة.');
            } catch (error) { showError(errorBox, error.message); }
            finally { enabled.forEach(e => { e.disabled = false; }); form.removeAttribute('aria-busy'); busy--; state.hidden = true; }
        }
        xhr.addEventListener('load', () => {
            let result = {}; try { result = JSON.parse(xhr.responseText); } catch { /* An expired proxy response may not be JSON. */ }
            finish();
            if (xhr.status >= 200 && xhr.status < 300 && result.video) {
                if (result.processing && form.dataset.kind !== 'metadata') { recoverProcessing(); return; }
                saved(result);
                return;
            }
            state.hidden = true;
            const errors = result.errors || {};
            Object.entries(errors).forEach(([field, values]) => {
                const mapped = ['qudrat_category_id', 'arabic_skill_bank_id'].includes(field) ? 'type' : field;
                const target = form.querySelector(`[data-error="${mapped}"]`);
                if (target) { target.textContent = values[0]; target.hidden = false; form.querySelector(`[name="${mapped}"]`)?.setAttribute('aria-invalid', 'true'); }
            });
            showError(errorBox, Object.keys(errors).length ? 'راجع الحقول المحددة وأعد المحاولة. بياناتك محفوظة.' : failureMessage(xhr.status));
            form.querySelector('[aria-invalid]')?.focus();
        });
        const connectionFailure = () => { finish(); state.hidden = true; showError(errorBox, 'تعذّر تأكيد الحفظ. أعد المحاولة بالملفات نفسها؛ لن يُنشأ فيديو مكرر.'); };
        xhr.addEventListener('error', connectionFailure); xhr.addEventListener('timeout', connectionFailure);
        xhr.send(data);
    }));
    window.addEventListener('beforeunload', event => { if (busy) event.preventDefault(); });
    window.addEventListener('pagehide', () => { formVideo?.pause(); dialogVideo?.pause(); localUrls.forEach(url => URL.revokeObjectURL(url)); });
}
