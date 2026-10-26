import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Livewire = Livewire;
window.Alpine = Alpine;

document.addEventListener('click', (event) => {
    const row = event.target.closest('[data-student-row]');
    if (!row || event.button !== 0 || event.target.closest('a, button, input, select, form, [popover], [data-no-row]') || window.getSelection()?.toString()) return;
    if (event.ctrlKey || event.metaKey || event.shiftKey) window.open(row.dataset.href, '_blank', 'noopener');
    else window.location.assign(row.dataset.href);
});

document.addEventListener('toggle', (event) => {
    const menu = event.target;
    if (!menu.matches?.('[data-users-menu]') || event.newState !== 'open') return;
    const trigger = document.getElementById(menu.dataset.trigger);
    const rect = trigger.getBoundingClientRect();
    const bounds = menu.getBoundingClientRect();
    menu.style.left = `${Math.max(8, Math.min(rect.right - bounds.width, window.innerWidth - bounds.width - 8))}px`;
    menu.style.top = `${rect.bottom + bounds.height + 8 > window.innerHeight ? Math.max(8, rect.top - bounds.height - 4) : rect.bottom + 4}px`;
}, true);

for (const eventName of ['resize', 'scroll']) {
    window.addEventListener(eventName, () => document.querySelectorAll('[data-users-menu]:popover-open').forEach(menu => menu.hidePopover()), { passive: true });
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-student-account-form]');
    if (!form) return;
    event.preventDefault();
    if (form.dataset.pending === 'true') return;
    const restore = form.dataset.action === 'restore';
    const invoker = document.getElementById(form.closest('[data-users-menu]').dataset.trigger);
    form.closest('[popover]')?.hidePopover();
    if (!window.Swal) {
        window.alert('تعذّر فتح التأكيد. أعد تحميل الصفحة ثم حاول مجددًا.');
        return;
    }
    const confirmation = await Swal.fire({
        title: restore ? 'إزالة حظر الطالب؟' : 'حظر الطالب؟',
        text: restore ? `سيُتاح الحساب للطالب ${form.dataset.name} من جديد.` : `سيُحظر حساب الطالب ${form.dataset.name}. يمكنك إزالة الحظر لاحقًا.`,
        icon: 'question', showCancelButton: true,
        confirmButtonText: restore ? 'نعم، إزالة الحظر' : 'نعم، حظر الطالب', cancelButtonText: 'إلغاء',
        confirmButtonColor: restore ? '#292a65' : '#b91c1c', returnFocus: false,
    });
    if (!confirmation.isConfirmed) { invoker?.focus(); return; }
    form.dataset.pending = 'true';
    try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Account action failed');
        Livewire.dispatch('student-account-updated');
        await Swal.fire({ title: restore ? 'تمت إزالة الحظر' : 'تم حظر الطالب', icon: 'success', confirmButtonText: 'حسنًا', confirmButtonColor: '#292a65', returnFocus: false });
        if (document.querySelector('[data-student-detail]')) window.location.reload();
    } catch {
        await Swal.fire({ title: 'تعذّر تحديث الحساب', text: 'تحقق من الاتصال ثم أعد المحاولة من إجراءات الطالب.', icon: 'error', confirmButtonText: 'حسنًا', returnFocus: false });
    } finally {
        delete form.dataset.pending;
        (document.getElementById(invoker?.id) ?? document.getElementById('users-title'))?.focus();
    }
});

document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (!document.querySelector('[data-users-list]')) return;
            preventDefault();
            if (window.Swal) Swal.fire({ title: status === 419 ? 'انتهت صلاحية الصفحة' : 'تعذّر تحديث القائمة', text: 'أعد تحميل الصفحة للمحاولة مجددًا.', icon: 'error', showCancelButton: true, confirmButtonText: 'إعادة التحميل', cancelButtonText: 'إغلاق' }).then(result => { if (result.isConfirmed) window.location.reload(); });
        });
    });
});

Livewire.start();
