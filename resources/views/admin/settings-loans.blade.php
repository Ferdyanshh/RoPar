<x-layouts.app title="Pengaturan Peminjaman">
    <div x-data="adminLoanSettings()" x-init="init()" class="space-y-6">
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm">
            <h2 class="text-xl font-bold text-surface-900">Kebijakan Peminjaman</h2>
            <p class="text-xs text-surface-500 mt-1">Atur kuota robot yang dapat dimiliki setiap anggota.</p>
        </div>

        <form @submit.prevent="save()" class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm space-y-6">
            <template x-if="loading">
                <p class="text-sm text-surface-400">Memuat pengaturan...</p>
            </template>

            <div x-show="loadError" class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700">
                <p x-text="loadError"></p>
                <button type="button" @click="init()" class="mt-2 font-semibold underline">Coba lagi</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-show="!loading && !loadError">
                <label class="block">
                    <span class="block text-sm font-semibold text-surface-800">Maksimal robot yang sedang dipinjam</span>
                    <span class="block text-xs text-surface-500 mt-1">Menghitung pinjaman dengan tanggal mulai hari ini atau sebelumnya.</span>
                    <input type="number" min="0" step="1" required x-model.number="settings.borrowed_robot_limit"
                           class="mt-3 w-full px-3 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                </label>

                <label class="block">
                    <span class="block text-sm font-semibold text-surface-800">Maksimal reservasi robot</span>
                    <span class="block text-xs text-surface-500 mt-1">Menghitung pinjaman dengan tanggal mulai di masa mendatang.</span>
                    <input type="number" min="0" step="1" required x-model.number="settings.reservation_robot_limit"
                           class="mt-3 w-full px-3 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                </label>
            </div>

            <p class="text-xs text-surface-500" x-show="!loading && !loadError">
                Nilai awal masing-masing kuota adalah 1. Perubahan berlaku untuk permintaan baru dan tidak membatalkan pinjaman atau reservasi yang sudah ada.
            </p>

            <div class="flex justify-end" x-show="!loading && !loadError">
                <button type="submit" :disabled="saving"
                        class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold transition disabled:opacity-50">
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan Pengaturan'"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function adminLoanSettings() {
            return {
                loading: true,
                saving: false,
                loadError: null,
                settings: { borrowed_robot_limit: 1, reservation_robot_limit: 1 },

                async init() {
                    this.loading = true;
                    this.loadError = null;
                    try {
                        const response = await api.get('/backend/admin/loan-settings');
                        this.settings = response.data;
                    } catch (error) {
                        this.loadError = error.message || 'Pengaturan gagal dimuat.';
                    } finally {
                        this.loading = false;
                    }
                },

                async save() {
                    this.saving = true;
                    try {
                        const response = await api.patch('/backend/admin/loan-settings', this.settings);
                        this.settings = response.data;
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Pengaturan peminjaman berhasil disimpan.', type: 'success' }
                        }));
                    } catch (error) {
                        const messages = error.errors
                            ? Object.values(error.errors).flat()
                            : [error.message || 'Pengaturan gagal disimpan.'];
                        messages.forEach(message => window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message, type: 'error' }
                        })));
                    } finally {
                        this.saving = false;
                    }
                }
            }
        }
    </script>
</x-layouts.app>
