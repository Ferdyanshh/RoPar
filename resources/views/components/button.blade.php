@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
])

@php
$base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';

$variants = [
    'primary' => 'bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 focus:ring-primary-500 shadow-sm hover:shadow-md',
    'secondary' => 'bg-surface-100 text-surface-700 hover:bg-surface-200 focus:ring-surface-400',
    'accent' => 'bg-gradient-to-r from-accent-500 to-accent-600 text-white hover:from-accent-600 hover:to-accent-700 focus:ring-accent-500 shadow-sm',
    'danger' => 'bg-danger-500 text-white hover:bg-danger-600 focus:ring-danger-500 shadow-sm',
    'success' => 'bg-success-500 text-white hover:bg-success-600 focus:ring-success-500 shadow-sm',
    'ghost' => 'text-surface-600 hover:bg-surface-100 focus:ring-surface-400',
    'outline' => 'border border-surface-300 text-surface-700 hover:bg-surface-50 focus:ring-primary-500',
];

$sizes = [
    'xs' => 'px-2.5 py-1 text-xs',
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-6 py-3 text-base',
];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}
    @if($disabled) disabled @endif
>
    {{ $slot }}
</button>
