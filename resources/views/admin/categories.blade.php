<x-layouts.app title="Kelola Kategori">
    <div x-data="adminCategories()" x-init="init()" class="space-y-6">
        {{-- Header & Add Button --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-surface-900">Kategori Robot Pacar</h2>
                <p class="text-xs text-surface-400 mt-1">Kelola kelompok tipe kepribadian dan karakter robot</p>
            </div>
            <button @click="openCreateModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold shadow-xs cursor-pointer transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Kategori</span>
            </button>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            <template x-if="loading">
                <div class="p-8 text-center text-surface-400">
                    <span class="spinner text-primary-500 mr-2"></span> Memuat data kategori...
                </div>
            </template>

            <template x-if="!loading && categories.length === 0">
                <x-empty-state title="Belum Ada Kategori" description="Tambahkan kategori pertama untuk mengelompokkan robot pacar." />
            </template>

            <div class="overflow-x-auto" x-show="!loading && categories.length > 0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Nama Kategori</th>
                            <th class="px-6 py-4 font-semibold">Deskripsi</th>
                            <th class="px-6 py-4 font-semibold text-center">Jumlah Robot</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="cat in categories" :key="cat.id">
                            <tr class="hover:bg-surface-50/50">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-surface-900" x-text="cat.name"></span>
                                </td>
                                <td class="px-6 py-4 text-xs text-surface-500 max-w-md truncate" x-text="cat.description || '-'"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 bg-surface-100 text-surface-700 font-bold rounded-lg text-xs" x-text="cat.robots_count || 0"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(cat)" class="px-3 py-1.5 bg-surface-100 hover:bg-surface-200 text-surface-700 rounded-lg text-xs font-semibold cursor-pointer transition">
                                            Edit
                                        </button>
                                        <button @click="openDeleteModal(cat)" class="px-3 py-1.5 bg-danger-50 hover:bg-danger-100 text-danger-700 rounded-lg text-xs font-semibold cursor-pointer transition">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-surface-100" x-show="!loading && lastPage > 1">
                <x-pagination />
            </div>
        </div>

        {{-- Form Modal (Create / Edit) --}}
        <x-modal maxWidth="md">
            <div class="p-6">
                <h3 class="font-bold text-surface-900 text-lg mb-4" x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru'"></h3>
                <form @submit.prevent="saveCategory" class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-surface-700">
                                Nama Kategori <span class="text-danger-500">*</span>
                            </label>
                            <span class="text-xs text-surface-400" x-text="(form.name?.length || 0) + '/80'"></span>
                        </div>
                        <input type="text" x-model="form.name" required maxlength="80" placeholder="Contoh: Tsundere, Romantis, Android"
                               class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                        <template x-if="errors?.name"><template x-for="msg in errors.name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Deskripsi</label>
                        <textarea x-model="form.description" rows="3" placeholder="Karakteristik kategori robot..."
                                  class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors resize-y"></textarea>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-surface-600 hover:bg-surface-100 rounded-xl text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="saving"
                                class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold shadow transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                            <span x-show="saving" class="spinner"></span>
                            <span x-text="saving ? 'Menyimpan...' : (isEdit ? 'Perbarui' : 'Simpan')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <div x-show="showDeleteModal" x-transition class="modal-backdrop" style="display:none;" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center" @click.stop>
                <div class="w-12 h-12 rounded-full bg-danger-100 text-danger-600 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-base font-bold text-surface-900 mb-1">Hapus Kategori?</h3>
                <p class="text-xs text-surface-500 mb-6">Kategori "<span class="font-bold text-surface-800" x-text="selectedCat?.name"></span>" akan dihapus.</p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-surface-600 hover:bg-surface-100 rounded-xl text-sm font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="confirmDelete" :disabled="deleting" class="px-5 py-2 bg-danger-600 hover:bg-danger-700 text-white rounded-xl text-sm font-bold shadow transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                        <span x-show="deleting" class="spinner"></span>
                        <span x-text="deleting ? 'Menghapus...' : 'Ya, Hapus'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminCategories() {
            return {
                categories: [],
                currentPage: 1,
                lastPage: 1,
                loading: true,
                showModal: false,
                showDeleteModal: false,
                isEdit: false,
                selectedCat: null,
                form: { name: '', description: '' },
                errors: {},
                saving: false,
                deleting: false,

                async init() {
                    this.loadData(1);
                },

                async loadData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    try {
                        const res = await api.get('/backend/admin/categories', { page: this.currentPage });
                        this.categories = res.data || [];
                        this.currentPage = res.current_page || 1;
                        this.lastPage = res.last_page || 1;
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.loading = false;
                    }
                },

                openCreateModal() {
                    this.isEdit = false;
                    this.form = { name: '', description: '' };
                    this.errors = {};
                    this.showModal = true;
                },

                openEditModal(cat) {
                    this.isEdit = true;
                    this.selectedCat = cat;
                    this.form = { name: cat.name, description: cat.description || '' };
                    this.errors = {};
                    this.showModal = true;
                },

                openDeleteModal(cat) {
                    this.selectedCat = cat;
                    this.showDeleteModal = true;
                },

                async saveCategory() {
                    this.errors = {};
                    if (!this.form.name) {
                        this.errors.name = ['Nama kategori wajib diisi.'];
                        return;
                    }
                    this.saving = true;

                    try {
                        if (this.isEdit) {
                            await api.patch('/backend/admin/categories/' + this.selectedCat.id, this.form);
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Kategori berhasil diperbarui.', type: 'success' } }));
                        } else {
                            await api.post('/backend/admin/categories', this.form);
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Kategori berhasil ditambahkan.', type: 'success' } }));
                        }
                        this.showModal = false;
                        this.loadData(this.currentPage);
                    } catch (err) {
                        if (err.errors) {
                            const { fieldErrors, toastMessages } = separateErrors(err.errors);
                            this.errors = fieldErrors;
                            toastMessages.forEach(msg => {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
                            });
                        } else if (err.message) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                        }
                    } finally {
                        this.saving = false;
                    }
                },

                async confirmDelete() {
                    if (!this.selectedCat) return;
                    this.deleting = true;
                    try {
                        await api.delete('/backend/admin/categories/' + this.selectedCat.id);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Kategori berhasil dihapus.', type: 'success' } }));
                        this.showDeleteModal = false;
                        this.loadData(this.currentPage);
                    } catch (err) {
                        this.showDeleteModal = false;
                        if (err.errors) {
                            const { fieldErrors, toastMessages } = separateErrors(err.errors);
                            toastMessages.forEach(msg => {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
                            });
                        } else if (err.message) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                        }
                    } finally {
                        this.deleting = false;
                    }
                },

                goToPage(p) {
                    if (p >= 1 && p <= this.lastPage) {
                        this.loadData(p);
                    }
                },

                paginationRange() {
                    const current = this.currentPage;
                    const last = this.lastPage;
                    const delta = 2;
                    const range = [];
                    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                        range.push(i);
                    }
                    if (current - delta > 2) range.unshift('...');
                    if (current + delta < last - 1) range.push('...');
                    range.unshift(1);
                    if (last > 1) range.push(last);
                    return range;
                }
            }
        }
    </script>
</x-layouts.app>
