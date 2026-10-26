@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — RoPar</title>
    <meta name="description" content="RoPar — Sistem Peminjaman Robot Pacar">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-50" x-data="{ sidebarOpen: false }">

    {{-- Toast container --}}
    <div x-data="toastManager()" x-ref="toastContainer"
         @toast.window="addToast($event.detail)"
         class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-full max-w-sm no-print">
        <template x-for="toast in toasts" :key="toast.id">
            <div :class="[
                    'toast-enter rounded-xl px-4 py-3 shadow-lg text-sm font-medium flex items-center gap-3 border',
                    toast.type === 'success' ? 'bg-success-50 text-success-700 border-success-500/30' : '',
                    toast.type === 'error' ? 'bg-danger-50 text-danger-700 border-danger-500/30' : '',
                    toast.type === 'info' ? 'bg-info-50 text-info-600 border-info-500/30' : '',
                    toast.type === 'warning' ? 'bg-warning-50 text-warning-600 border-warning-500/30' : '',
                 ]">
                <span x-text="toast.message" class="flex-1"></span>
                <button @click="removeToast(toast.id)" class="opacity-50 hover:opacity-100 cursor-pointer">✕</button>
            </div>
        </template>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 backdrop-blur-xs z-40 lg:hidden" style="display:none;">
    </div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 z-50 h-full w-64 bg-white border-r border-surface-200 transition-transform duration-300 lg:translate-x-0 flex flex-col no-print">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-surface-100">
            <div class="w-9 h-9 bg-gradient-to-br from-primary-400 to-accent-500 rounded-xl flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <rect x="8" y="14" width="8" height="6" rx="2"/>
                    <circle cx="10" cy="7" r="1" fill="currentColor"/>
                    <circle cx="14" cy="7" r="1" fill="currentColor"/>
                    <line x1="10" y1="9" x2="14" y2="9" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="text-lg font-semibold bg-gradient-to-r from-primary-600 to-accent-600 bg-clip-text text-transparent">RoPar</span>
            <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-surface-400 hover:text-surface-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @if(auth()->user()->role === \App\Enums\UserRole::Admin)
                {{-- Admin nav --}}
                <x-sidebar-link href="/dashboard" icon="home" :active="request()->is('dashboard')">Dashboard</x-sidebar-link>

                <div class="pt-4 pb-1 px-3">
                    <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Katalog</p>
                </div>
                <x-sidebar-link href="/robots" icon="robot" :active="request()->is('robots*')">Katalog Robot</x-sidebar-link>

                <div class="pt-4 pb-1 px-3">
                    <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Kelola</p>
                </div>
                <x-sidebar-link href="/admin/categories" icon="tag" :active="request()->is('admin/categories*')">Kategori</x-sidebar-link>
                <x-sidebar-link href="/admin/robots" icon="cpu" :active="request()->is('admin/robots*')">Robot</x-sidebar-link>
                <x-sidebar-link href="/admin/units" icon="box" :active="request()->is('admin/units*')">Unit Robot</x-sidebar-link>
                <x-sidebar-link href="/admin/members" icon="users" :active="request()->is('admin/members*')">Anggota</x-sidebar-link>

                <div class="pt-4 pb-1 px-3">
                    <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Peminjaman</p>
                </div>
                <x-sidebar-link href="/admin/loans/active" icon="clock" :active="request()->is('admin/loans/active')">Pinjaman Aktif</x-sidebar-link>
                <x-sidebar-link href="/admin/loans/history" icon="history" :active="request()->is('admin/loans/history')">Riwayat</x-sidebar-link>
                <a href="/admin/fine-payments" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors {{ request()->is('admin/fine-payments*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-surface-600 hover:bg-surface-100 hover:text-surface-800' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="flex-1">Verifikasi Denda</span>
                    <span x-data="finePaymentNotice()" x-init="init()" x-show="count > 0" x-text="count" class="rounded-full bg-danger-600 px-2 py-0.5 text-[10px] font-bold text-white"></span>
                </a>
            @else
                {{-- Member nav --}}
                <x-sidebar-link href="/dashboard" icon="home" :active="request()->is('dashboard')">Dashboard</x-sidebar-link>
                <x-sidebar-link href="/robots" icon="robot" :active="request()->is('robots*')">Katalog Robot</x-sidebar-link>
                <x-sidebar-link href="/my-loans" icon="bookmark" :active="request()->is('my-loans')">Pinjaman Saya</x-sidebar-link>
            @endif
        </nav>

        {{-- User footer --}}
        <div class="border-t border-surface-100 px-3 py-3">
            <x-sidebar-link href="/profile" icon="user" :active="request()->is('profile')">Profil Saya</x-sidebar-link>
            <button @click="
                api.post('/backend/logout').then(() => {
                    window.location.href = '/login';
                }).catch(() => {
                    window.location.href = '/login';
                })
            " class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-danger-600 hover:bg-danger-50 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3-3h-9m9 0l-3-3m3 3l-3 3"/>
                </svg>
                <span>Keluar</span>
            </button>
        </div>
    </aside>

    {{-- Main area --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">
        {{-- Top navbar --}}
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-surface-200 no-print">
            <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden text-surface-500 hover:text-surface-700 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-surface-800">{{ $title }}</h1>
                </div>

                <div class="flex items-center gap-3" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-surface-100 transition-colors cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-accent-500 flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm text-surface-700 font-medium">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-transition
                         class="absolute right-4 top-14 w-48 bg-white rounded-xl shadow-lg border border-surface-200 py-1 z-50" style="display:none;">
                        <a href="/profile" class="block px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">Profil Saya</a>
                        <hr class="my-1 border-surface-100">
                        <button @click="
                            api.post('/backend/logout').then(() => { window.location.href = '/login'; }).catch(() => { window.location.href = '/login'; })
                        " class="w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 cursor-pointer">Keluar</button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6">
            {{ $slot }}
        </main>
    </div>

    <script>
        function finePaymentNotice() {
            return {
                count: 0,
                async init() {
                    await this.refresh();
                    setInterval(() => this.refresh(), 30000);
                },
                async refresh() {
                    try {
                        const response = await api.get('/backend/admin/fine-payments/pending-count');
                        this.count = Number(response.count || 0);
                    } catch (_) {
                        this.count = 0;
                    }
                }
            }
        }

        function toastManager() {
            return {
                toasts: [],
                counter: 0,
                addToast(detail) {
                    const id = ++this.counter;
                    this.toasts.push({ id, message: detail.message, type: detail.type || 'info' });
                    setTimeout(() => this.removeToast(id), 5000);
                },
                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }
            }
        }
    </script>
</body>
</html>
