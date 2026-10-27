@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'xModel' => null,
    'disabled' => false,
])

<div>
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-surface-700 mb-1.5">
        {{ $label }}
        @if($required) <span class="text-danger-500">*</span> @endif
    </label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($xModel) x-model="{{ $xModel }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm text-surface-800 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors disabled:bg-surface-100 disabled:text-surface-500 cursor-pointer']) }}
    >
        {{ $slot }}
    </select>
    <x-field-error :name="$name" />
</div>
