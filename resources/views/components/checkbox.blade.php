<div class="form-check">
    <input type="checkbox" class="form-check-input" id="{{ $name }}" name="{{ $name }}" {{ $checked ? 'checked' : '' }}>
    @if ($label)
    <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
    @endif
</div>