@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'value' => null, 'hint' => null])
<div class="auth-field">
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="auth-input-wrap">
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($type === 'email' || $type === 'password') dir="ltr" @endif
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            @if ($hint || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif
            {{ $attributes->merge(['required' => true]) }}>
        @if ($type === 'password')
            <button class="auth-reveal" type="button" data-password-toggle="{{ $name }}" aria-controls="{{ $name }}" aria-pressed="false" aria-label="إظهار {{ $label }}">إظهار</button>
        @endif
    </div>
    @if ($hint || $errors->has($name))
        <p id="{{ $name }}-help" class="{{ $errors->has($name) ? 'auth-error' : 'auth-hint' }}">{{ $errors->first($name) ?: $hint }}</p>
    @endif
</div>
