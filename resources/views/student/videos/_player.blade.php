<dialog class="vp-dialog" data-video-dialog aria-labelledby="vp-title" dir="rtl" lang="ar">
    <div class="vp-shell">
        <header class="vp-header"><p>مكتبة الفيديوهات</p><button type="button" class="sv-button sv-secondary" data-player-close autofocus>إغلاق</button></header>
        <p class="vp-status" role="status" data-player-status></p>
        <div class="vp-error" role="alert" data-player-error hidden><p data-player-error-text></p><button type="button" class="sv-button sv-secondary" data-player-retry>إعادة المحاولة</button></div>
        <div class="vp-layout" data-player-content hidden>
            <div class="vp-screen"><video controls playsinline preload="metadata" data-player-media aria-label="مشغّل الفيديو"></video><button class="sv-button vp-play" type="button" data-player-play hidden>تشغيل الفيديو</button></div>
            <div class="vp-details"><h2 id="vp-title" data-player-title>الفيديو</h2><p class="sv-type" data-player-type></p><p class="vp-description" data-player-description></p>
                <div class="vp-share"><button class="sv-button" type="button" data-player-share>مشاركة الفيديو</button><button class="sv-button sv-secondary" type="button" data-player-copy>نسخ الرابط</button><p role="status" data-share-status></p><div data-share-manual hidden><label for="vp-share-url">رابط الفيديو — حدّده وانسخه</label><input id="vp-share-url" type="url" dir="ltr" readonly></div></div>
                <section class="vp-related" data-player-related-section aria-labelledby="vp-related-heading" hidden><h3 id="vp-related-heading">شاهد أيضًا</h3><div data-player-related></div></section>
            </div>
        </div>
    </div>
</dialog>
