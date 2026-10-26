<button id="student-account-trigger" type="button" class="users-menu-trigger" popovertarget="student-account-menu" aria-label="إجراءات الطالب {{ $summary['name'] }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg></button>
<nav id="student-account-menu" popover class="users-action-menu" aria-label="إجراءات الحساب" data-users-menu data-trigger="student-account-trigger">
    <form method="POST" action="{{ route($summary['blocked'] ? 'admin.students.restore' : 'admin.students.delete', ['student_id' => $summary['id']]) }}" data-student-account-form data-name="{{ $summary['name'] }}" data-action="{{ $summary['blocked'] ? 'restore' : 'block' }}">
        @csrf @method('DELETE')
        @foreach($list as $key => $value)<input type="hidden" name="list[{{ $key }}]" value="{{ $value }}">@endforeach
        @foreach($state as $key => $value)<input type="hidden" name="detail[{{ $key }}]" value="{{ $value }}">@endforeach
        <button type="submit" class="{{ $summary['blocked'] ? '' : 'users-danger' }}">{{ $summary['blocked'] ? 'إزالة الحظر' : 'حظر الطالب' }}</button>
    </form>
</nav>
