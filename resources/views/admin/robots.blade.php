<x-layouts.app title="Kelola Robot">
    <div x-data="adminRobots()" x-init="init()" class="space-y-6">
        {{-- Header & Add Button --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-surface-900">Katalog Model Robot</h2>
                <p class="text-xs text-surface-400 mt-1">Daftar tipe robot pacar yang dapat dipinjam oleh anggota</p>
            </div>
            <button @click="openCreateModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold shadow-xs cursor-pointer transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Model Robot</span>
            </button>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            <template x-if="loading">
                <div class="p-8 text-center text-surface-400">
                    <span class="spinner text-primary-500 mr-2"></span> Memuat data robot...
                </div>
            </template>

            <template x-if="!loading && robots.length === 0">
                <x-empty-state title="Belum Ada Model Robot" description="Tambahkan robot pacar pertama ke dalam sistem." />
            </template>

            <div class="overflow-x-auto" x-show="!loading && robots.length > 0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Foto & Nama</th>
                            <th class="px-6 py-4 font-semibold">Model</th>
                            <th class="px-6 py-4 font-semibold">Kategori</th>
                            <th class="px-6 py-4 font-semibold text-center">Unit Fisik</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="r in robots" :key="r.id">
                            <tr class="hover:bg-surface-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-surface-100 border border-surface-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            <template x-if="r.photo">
                                                <img :src="'/storage/' + r.photo" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!r.photo">
                                                <svg class="w-6 h-6 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="6" y="10" width="12" height="9" rx="2"/><circle cx="9.5" cy="14.5" r="1.5"/><circle cx="14.5" cy="14.5" r="1.5"/><path stroke-linecap="round" d="M12 3v4M8 7h8"/></svg>
                                            </template>
                                        </div>
                                        <div>
                                            <p class="font-bold text-surface-900" x-text="r.name"></p>
                                            <p class="text-xs text-surface-400 line-clamp-1 max-w-xs" x-text="r.description || '-'"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-surface-600" x-text="r.model || '-'"></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="cat in r.categories" :key="cat.id">
                                            <span class="text-[11px] px-2 py-0.5 bg-accent-50 text-accent-700 rounded-md font-medium" x-text="cat.name"></span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 bg-surface-100 text-surface-800 font-bold rounded-lg text-xs" x-text="r.units_count || 0"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="'/admin/units?robot_id=' + r.id" class="px-2.5 py-1.5 bg-surface-100 hover:bg-surface-200 text-surface-700 rounded-lg text-xs font-semibold transition" title="Lihat unit fisik robot ini">
                                            Unit
                                        </a>
                                        <button @click="openEditModal(r)" class="px-2.5 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg text-xs font-semibold cursor-pointer transition">
                                            Edit
                                        </button>
                                        <button @click="openDeleteModal(r)" class="px-2.5 py-1.5 bg-danger-50 hover:bg-danger-100 text-danger-700 rounded-lg text-xs font-semibold cursor-pointer transition">
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
        <x-modal maxWidth="xl">
            <div class="p-6">
                <h3 class="font-bold text-surface-900 text-lg mb-4" x-text="isEdit ? 'Edit Model Robot' : 'Tambah Model Robot Baru'"></h3>
                <form @submit.prevent="saveRobot" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">
                                Nama Robot <span class="text-danger-500">*</span>
                            </label>
                            <input type="text" x-model="form.name" required maxlength="120" placeholder="Contoh: Aoi v2"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.name"><template x-for="msg in errors.name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Model / Tipe</label>
                            <input type="text" x-model="form.model" maxlength="80" placeholder="Contoh: R-Series 2026"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.model"><template x-for="msg in errors.model" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                    </div>

                    {{-- Categories (Checkbox min 1) --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-2">
                            Pilih Kategori <span class="text-danger-500">* (minimal 1)</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-36 overflow-y-auto p-3 border border-surface-200 rounded-xl bg-surface-50">
                            <template x-for="cat in allCategories" :key="cat.id">
                                <label class="flex items-center gap-2 text-xs text-surface-700 cursor-pointer">
                                    <input type="checkbox" :value="cat.id" x-model="form.category_ids"
                                           class="rounded border-surface-300 text-primary-600 focus:ring-primary-500/40">
                                    <span x-text="cat.name"></span>
                                </label>
                            </template>
                        </div>
                        <template x-if="errors?.category_ids"><template x-for="msg in errors.category_ids" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Deskripsi & Kepribadian</label>
                        <textarea x-model="form.description" rows="3" placeholder="Jelaskan karakteristik robot pacar..."
                                  class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors resize-y"></textarea>
                    </div>

                    {{-- Photo Upload with Preview --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Foto Robot (JPG, JPEG, PNG, maks 2 MB)</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-surface-100 border border-surface-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!photoPreview && isEdit && selectedRobot?.photo">
                                    <img :src="'/storage/' + selectedRobot.photo" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!photoPreview && (!isEdit || !selectedRobot?.photo)">
                                    <svg class="w-6 h-6 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                </template>
                            </div>
                            <input type="file" @change="handlePhotoChange($event)" accept="image/jpeg,image/png,image/jpg"
                                   class="text-xs text-surface-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        </div>
                        <template x-if="errors?.photo"><template x-for="msg in errors.photo" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
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
                <h3 class="text-base font-bold text-surface-900 mb-1">Hapus Model Robot?</h3>
                <p class="text-xs text-surface-500 mb-6">Robot "<span class="font-bold text-surface-800" x-text="selectedRobot?.name"></span>" akan dihapus.</p>
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
        function adminRobots() {
            return {
                robots: [],
                allCategories: [],
                currentPage: 1,
                lastPage: 1,
                loading: true,
                showModal: false,
                showDeleteModal: false,
                isEdit: false,
                selectedRobot: null,
                form: { name: '', model: '', description: '', category_ids: [] },
                photoFile: null,
                photoPreview: null,
                errors: {},
                saving: false,
                deleting: false,

                async init() {
                    try {
                        const catRes = await api.get('/backend/categories');
                        this.allCategories = catRes.data || [];
                    } catch (e) {
                        console.error(e);
                    }
                    this.loadData(1);
                },

                async loadData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    try {
                        const res = await api.get('/backend/admin/robots', { page: this.currentPage });
                        this.robots = res.data || [];
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
                    this.form = { name: '', model: '', description: '', category_ids: [] };
                    this.photoFile = null;
                    this.photoPreview = null;
                    this.errors = {};
                    this.showModal = true;
                },

                openEditModal(robot) {
                    this.isEdit = true;
                    this.selectedRobot = robot;
                    this.form = {
                        name: robot.name,
                        model: robot.model || '',
                        description: robot.description || '',
                        category_ids: (robot.categories || []).map(c => c.id)
                    };
                    this.photoFile = null;
                    this.photoPreview = null;
                    this.errors = {};
                    this.showModal = true;
                },

                openDeleteModal(robot) {
                    this.selectedRobot = robot;
                    this.showDeleteModal = true;
                },

                handlePhotoChange(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                        this.errors.photo = ['Format file harus JPG, JPEG, atau PNG.'];
                        event.target.value = '';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        this.errors.photo = ['Ukuran foto maksimal 2 MB.'];
                        event.target.value = '';
                        return;
                    }

                    this.errors.photo = null;
                    this.photoFile = file;
                    this.photoPreview = URL.createObjectURL(file);
                },

                async saveRobot() {
                    this.errors = {};
                    if (!this.form.name) this.errors.name = ['Nama robot wajib diisi.'];
                    if (!this.form.category_ids || this.form.category_ids.length === 0) {
                        this.errors.category_ids = ['Minimal pilih 1 kategori.'];
                    }

                    if (Object.keys(this.errors).length > 0) return;
                    this.saving = true;

                    const formData = new FormData();
                    formData.append('name', this.form.name);
                    if (this.form.model) formData.append('model', this.form.model);
                    if (this.form.description) formData.append('description', this.form.description);
                    this.form.category_ids.forEach(id => {
                        formData.append('category_ids[]', id);
                    });
                    if (this.photoFile) {
                        formData.append('photo', this.photoFile);
                    }

                    try {
                        if (this.isEdit) {
                            await api.upload('/backend/admin/robots/' + this.selectedRobot.id, formData, 'PATCH');
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Robot berhasil diperbarui.', type: 'success' } }));
                        } else {
                            await api.upload('/backend/admin/robots', formData, 'POST');
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Robot berhasil ditambahkan.', type: 'success' } }));
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
                    if (!this.selectedRobot) return;
                    this.deleting = true;
                    try {
                        await api.delete('/backend/admin/robots/' + this.selectedRobot.id);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Robot berhasil dihapus.', type: 'success' } }));
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
