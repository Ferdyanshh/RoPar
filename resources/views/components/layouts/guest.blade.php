<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RoPar' }} — RoPar</title>
    <meta name="description" content="RoPar — Sistem Peminjaman Robot Pacar yang modern dan mudah digunakan.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary-50 via-white to-accent-50">

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

    {{ $slot }}

    <script>
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
