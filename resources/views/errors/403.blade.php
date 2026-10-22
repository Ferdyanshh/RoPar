<x-layouts.guest title="Akses Ditolak">
    <div class="w-full max-w-md mx-auto text-center px-6 py-12">
        <div class="w-20 h-20 mx-auto bg-danger-50 rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-10 h-10 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-surface-900 mb-2">403</h1>
        <p class="text-lg text-surface-600 mb-2">Akses Ditolak</p>
        <p class="text-sm text-surface-400 mb-8">Maaf, Anda tidak punya izin untuk mengakses halaman ini.</p>
        <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl font-semibold text-sm hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Dashboard
        </a>
    </div>
</x-layouts.guest>
