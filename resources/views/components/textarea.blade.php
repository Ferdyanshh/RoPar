@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'placeholder' => '',
    'xModel' => null,
    'rows' => 3,
    'maxlength' => null,
])

<div>
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-surface-700 mb-1.5">
        {{ $label }}
        @if($required) <span class="text-danger-500">*</span> @endif
    </label>
    @endif
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($xModel) x-model="{{ $xModel }}" @endif
        @if($required) required @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm text-surface-800 placeholder:text-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors resize-y']) }}
    ></textarea>
    <x-field-error :name="$name" />
</div>
