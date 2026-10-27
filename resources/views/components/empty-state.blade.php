@props([
    'title' => 'Tidak ada data',
    'description' => 'Belum ada data yang dapat ditampilkan.',
])

<div class="flex flex-col items-center justify-center py-12 text-center">
    <div class="w-20 h-20 bg-surface-100 rounded-2xl flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-surface-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold text-surface-600">{{ $title }}</h3>
    <p class="text-sm text-surface-400 mt-1 max-w-sm">{{ $description }}</p>
    @if(isset($action))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
