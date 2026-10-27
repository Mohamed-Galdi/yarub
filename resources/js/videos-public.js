import 'flowbite';

const example = document.querySelector('[data-public-video]');
if (example) {
    const video = example.querySelector('video');
    const status = example.querySelector('[data-public-status]');
    const error = example.querySelector('[data-public-error]');
    video.addEventListener('waiting', () => { status.textContent = 'جارٍ تحميل المثال…'; });
    video.addEventListener('playing', () => { status.textContent = ''; });
    video.addEventListener('canplay', () => { status.textContent = ''; });
    video.addEventListener('error', () => { video.pause(); status.textContent = ''; error.hidden = false; });
    window.addEventListener('pagehide', () => { video.pause(); video.removeAttribute('src'); video.load(); });
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
}
