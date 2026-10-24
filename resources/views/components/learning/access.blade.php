@props(['destination', 'label'])
<div class="learning-access">
    @if(auth()->check() && auth()->user()->role !== 'student')
    <p>أنت تتصفح بحساب مشرف. لبدء التدريب وحفظ تقدّمك، استخدم حساب طالب.</p>
    @else
    <a class="learning-button" href="{{ route($destination) }}">{{ auth()->check() && auth()->user()->role === 'student' ? 'انتقل إلى '.$label : 'سجّل الدخول وابدأ التدريب' }}<span aria-hidden="true">←</span></a>
    @guest<p>كل ما تحتاجه هو تسجيل الدخول إلى حسابك كطالب. <a href="{{ route('register') }}">ليس لديك حساب؟ أنشئ حساب طالب</a></p>@else<p>التدريب متاح من لوحة الطالب، ولا يحتاج إلى شراء دورة أو اشتراك في الشروحات.</p>@endguest
    @endif
</div>
