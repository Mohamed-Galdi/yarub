@props(['label', 'secondary' => false])
<button type="submit" class="{{ $secondary ? 'auth-secondary' : 'auth-submit' }}" data-label="{{ $label }}">
    <span class="auth-spinner" hidden aria-hidden="true"></span>
    <span data-submit-label>{{ $label }}</span>
</button>
