{{-- Modal component - controlled by Alpine x-show binding --}}
@props(['maxWidth' => 'lg'])

@php
$maxWidths = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
];
$width = $maxWidths[$maxWidth] ?? $maxWidths['lg'];
@endphp

<div x-show="showModal" x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="modal-backdrop" style="display:none;" @click.self="showModal = false">
    <div x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full {{ $width }} mx-4 max-h-[90vh] overflow-y-auto"
         @click.stop>
        {{ $slot }}
    </div>
</div>
