@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'xModel' => null,
    'disabled' => false,
    'maxlength' => null,
    'pattern' => null,
    'inputmode' => null,
    'max' => null,
    'min' => null,
    'accept' => null,
    'readonly' => false,
])

<div>
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-surface-700 mb-1.5">
        {{ $label }}
        @if($required) <span class="text-danger-500">*</span> @endif
    </label>
    @endif
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        @if($xModel) x-model="{{ $xModel }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($pattern) pattern="{{ $pattern }}" @endif
        @if($inputmode) inputmode="{{ $inputmode }}" @endif
        @if($max) max="{{ $max }}" @endif
        @if($min) min="{{ $min }}" @endif
        @if($accept) accept="{{ $accept }}" @endif
        {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm text-surface-800 placeholder:text-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors disabled:bg-surface-100 disabled:text-surface-500 read-only:bg-surface-50']) }}
    >
    <x-field-error :name="$name" />
</div>
