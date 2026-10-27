@foreach($typeGroups as $group)
    <optgroup label="{{ $group['label'] }}">
        @foreach($group['items'] as $type)
            @php($value = $group['prefix'].':'.$type->id)
            <option value="{{ $value }}" @selected(($selectedType ?? '') === $value) @disabled(($activeOnly ?? false) && !$type->{$group['active']} && ($selectedType ?? '') !== $value)>{{ $type->title }}{{ !$type->{$group['active']} ? ' (غير متاح)' : '' }}</option>
        @endforeach
    </optgroup>
@endforeach
