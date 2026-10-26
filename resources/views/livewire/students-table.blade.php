<section data-users-list dir="rtl" aria-labelledby="users-title" x-data>
    <header class="users-heading">
        <div><h1 id="users-title" tabindex="-1">المستخدمون</h1><p>حسابات الطلاب وتقدّمهم في التدريب.</p></div>
        <a class="users-text-link" href="{{ route('admin.students', array_replace($state, ['status' => 'blocked', 'page' => 1])) }}">المحظورون <span class="users-count">{{ $blockedCount }}</span></a>
    </header>

    <div class="users-surface">
        <div class="users-toolbar">
            <div class="users-search">
                <label for="users-search">البحث عن طالب</label>
                <div class="users-search-control"><x-icons.search class="users-search-icon" /><input id="users-search" type="search" wire:model.live.debounce.350ms="search" placeholder="الاسم أو البريد الإلكتروني" autocomplete="off" maxlength="255"></div>
            </div>
            <div class="users-filter"><label for="users-status">حالة الحساب</label><select id="users-status" wire:model.live="status"><option value="active">الفعالون</option><option value="blocked">المحظورون</option><option value="all">جميع الحسابات</option></select></div>
            <div class="users-filter"><label for="users-program">المشاركة في التدريب</label><select id="users-program" wire:model.live="participation"><option value="all">جميع الطلاب</option><option value="qudrat">القدرات</option><option value="arabic_skills">مهارات اللغة العربية</option><option value="neither">لم يبدأ أي برنامج</option></select></div>
            <button class="users-button users-export" type="button" wire:click="exportToXLS" wire:loading.attr="disabled" @disabled(!$students || $students->total() === 0) title="تصدير جميع النتائج المطابقة للبحث والفلاتر، بجميع الصفحات"><x-icons.download class="users-icon" /><span wire:loading.remove wire:target="exportToXLS">تصدير النتائج</span><span wire:loading wire:target="exportToXLS">جارٍ التصدير…</span></button>
        </div>

        <div class="users-context">
            <p aria-live="polite">@if($students)<strong>{{ number_format($students->total()) }}</strong> طالب@endif <span wire:loading.delay role="status"> · جارٍ تحديث النتائج…</span></p>
            @if($search !== '' || $status !== 'active' || $participation !== 'all' || $sort !== 'joined' || $direction !== 'desc')<button class="users-text-link" type="button" wire:click="resetFilters">مسح الفلاتر</button>@endif
            <span class="users-export-note">التصدير يشمل كل النتائج المطابقة، بجميع الصفحات.</span>
        </div>

        <div class="users-mobile-sort"><label for="users-sort">الترتيب حسب</label><select id="users-sort" wire:model.live="sort"><option value="joined">تاريخ الانضمام</option><option value="last_practice">آخر تدريب</option><option value="qudrat_coverage">تغطية القدرات</option><option value="arabic_skills_coverage">تغطية مهارات اللغة العربية</option></select><button class="users-button" wire:click="sortBy('{{ $sort }}')" aria-label="عكس اتجاه الترتيب">{{ $direction === 'desc' ? 'تنازلي' : 'تصاعدي' }}</button></div>

        @if($loadError)
            <div class="users-empty" role="alert"><h2>تعذّر تحميل الطلاب</h2><p>أعد المحاولة لتحميل القائمة.</p><button class="users-button" wire:click="$refresh">إعادة المحاولة</button></div>
        @elseif($students->isEmpty())
            <div class="users-empty"><x-icons.students class="users-empty-icon" /><h2>{{ $search !== '' || $participation !== 'all' ? 'لا توجد نتائج مطابقة' : ($status === 'blocked' ? 'لا يوجد طلاب محظورون' : 'لا يوجد طلاب لعرضهم') }}</h2><p>يمكنك تغيير البحث أو حالة الحساب أو المشاركة في التدريب.</p><button class="users-button" wire:click="resetFilters">عرض الطلاب الفعالين</button></div>
        @else
            <div class="users-table-region" wire:loading.class="users-updating" wire:target="search,status,participation,sort,direction,perPage,gotoPage,nextPage,previousPage,refreshRows">
                <table class="users-table">
                    <caption class="users-sr-only">الطلاب وتغطية الأسئلة المتاحة في القدرات ومهارات اللغة العربية. آخر تدريب هو نشاط تعلّم محفوظ، وليس تسجيل دخول.</caption>
                    <thead><tr>
                        <th scope="col">الطالب</th>
                        @foreach(['qudrat_coverage' => 'القدرات', 'arabic_skills_coverage' => 'مهارات اللغة العربية', 'last_practice' => 'آخر تدريب', 'joined' => 'الانضمام'] as $column => $label)
                            <th scope="col" aria-sort="{{ $sort === $column ? ($direction === 'desc' ? 'descending' : 'ascending') : 'none' }}"><button class="users-sort" wire:click="sortBy('{{ $column }}')">{{ $label }}<svg class="users-sort-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="{{ $sort === $column && $direction === 'asc' ? 'm4 10 4-4 4 4' : 'm4 6 4 4 4-4' }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></button></th>
                        @endforeach
                        <th scope="col">الحالة / الإجراءات</th>
                    </tr></thead>
                    <tbody>
                    @foreach($students as $student)
                        @php
                            $detailUrl = route('admin.view_student', ['id' => $student['id'], 'list' => $state]);
                            $initial = mb_substr(trim($student['name']), 0, 1) ?: 'ط';
                            $avatar = $student['avatar'] && !str_starts_with($student['avatar'], 'data:') ? asset($student['avatar']) : null;
                        @endphp
                        <tr wire:key="student-{{ $student['id'] }}" data-student-row data-href="{{ $detailUrl }}">
                            <td class="users-profile-cell"><div class="users-profile"><span class="users-avatar" aria-hidden="true" x-data="{ failed: false }"><span>{{ $initial }}</span>@if($avatar)<img src="{{ $avatar }}" alt="" width="36" height="36" loading="lazy" x-cloak x-init="if ($el.complete && $el.naturalWidth === 0) failed = true" x-show="!failed" x-on:error="failed = true">@endif</span><div class="users-identity"><a class="users-name" href="{{ $detailUrl }}">{{ $student['name'] }}</a><span class="users-email" dir="ltr" data-no-row>{{ $student['email'] }}</span></div></div></td>
                            @foreach(['qudrat' => 'القدرات', 'arabic_skills' => 'مهارات اللغة العربية'] as $program => $label)
                                @php($metric = $student['programs'][$program])
                                <td class="users-coverage" data-label="{{ $label }}"><div class="users-coverage-content">
                                    @if(!$metric['participated'])<span class="users-muted">لم يبدأ</span>
                                    @elseif($metric['coverage'] === null)<span class="users-muted">غير متاح</span>
                                    @else<div class="users-coverage-numbers"><bdi dir="ltr">{{ $metric['answered_questions'] }} <span>/ {{ $metric['available_questions'] }}</span></bdi><span class="users-percent">{{ round($metric['coverage']) }}٪</span></div><progress value="{{ $metric['answered_questions'] }}" max="{{ max(1, $metric['available_questions']) }}" aria-label="تغطية {{ $label }} للطالب {{ $student['name'] }}" title="الأسئلة المختلفة التي أُجيبت من الأسئلة المتاحة حاليًا. قد تتغير التغطية عند إضافة المحتوى أو إخفائه.">{{ $metric['coverage'] }}٪</progress>@endif
                                </div></td>
                            @endforeach
                            <td class="users-date" data-label="آخر تدريب">@if($student['last_practice_at'])<time datetime="{{ \Carbon\Carbon::parse($student['last_practice_at'])->toIso8601String() }}" title="{{ \Carbon\Carbon::parse($student['last_practice_at'])->format('Y-m-d H:i:s') }} UTC">{{ \Carbon\Carbon::parse($student['last_practice_at'])->diffForHumans() }}</time>@else<span class="users-muted">لم يبدأ</span>@endif</td>
                            <td class="users-date" data-label="الانضمام"><time datetime="{{ $student['joined_at'] }}" title="{{ \Carbon\Carbon::parse($student['joined_at'])->format('Y-m-d H:i:s') }} UTC"><bdi dir="ltr">{{ \Carbon\Carbon::parse($student['joined_at'])->format('d-m-Y') }}</bdi></time></td>
                            <td class="users-account"><span class="users-status {{ $student['blocked'] ? 'is-blocked' : '' }}">{{ $student['blocked'] ? 'محظور' : 'فعال' }}</span><button id="student-action-trigger-{{ $student['id'] }}" type="button" class="users-menu-trigger" popovertarget="student-actions-{{ $student['id'] }}" aria-label="إجراءات الطالب {{ $student['name'] }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg></button>
                                <nav id="student-actions-{{ $student['id'] }}" popover class="users-action-menu" aria-label="إجراءات الطالب {{ $student['name'] }}" data-users-menu data-trigger="student-action-trigger-{{ $student['id'] }}">
                                    <a href="{{ $detailUrl }}">عرض الطالب</a>
                                    <form method="POST" action="{{ route($student['blocked'] ? 'admin.students.restore' : 'admin.students.delete', ['student_id' => $student['id']]) }}" data-student-account-form data-name="{{ $student['name'] }}" data-action="{{ $student['blocked'] ? 'restore' : 'block' }}">
                                        @csrf @method('DELETE')
                                        @foreach($state as $key => $value)<input type="hidden" name="list[{{ $key }}]" value="{{ $value }}">@endforeach
                                        <button type="submit" class="{{ $student['blocked'] ? '' : 'users-danger' }}">{{ $student['blocked'] ? 'إزالة الحظر' : 'حظر الطالب' }}</button>
                                    </form>
                                </nav>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <footer class="users-footer"><div><label for="users-per-page">في الصفحة</label><select id="users-per-page" wire:model.live="perPage">@foreach([10,20,50,100] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach</select><span><bdi>{{ $students->firstItem() }}–{{ $students->lastItem() }}</bdi> من {{ $students->total() }}</span></div>{{ $students->onEachSide(1)->links('admin.students.pagination') }}</footer>
        @endif
    </div>
</section>
