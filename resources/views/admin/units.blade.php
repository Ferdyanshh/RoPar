<x-layouts.app title="Kelola Unit Robot">
    <div x-data="adminUnits()" x-init="init()" class="space-y-6">
        {{-- Header & Filters --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-surface-900">Unit Fisik Robot</h2>
                    <p class="text-xs text-surface-400 mt-1">Kelola nomor seri unit, kondisi fisik, dan status operasional</p>
                </div>
                <button @click="openCreateModal()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold shadow-xs cursor-pointer transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Unit Baru</span>
                </button>
            </div>

            {{-- Client-side Filter Controls --}}
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-surface-100">
                <div class="w-full sm:w-48">
                    <select x-model="filterStatus" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-xs text-surface-700 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                        <option value="">Semua Status Unit</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="dipinjam">Dipinjam</option>
                        <option value="rusak">Rusak</option>
                    </select>
                </div>
                <div class="w-full sm:w-60">
                    <select x-model="filterRobot" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-xs text-surface-700 focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                        <option value="">Semua Model Robot</option>
                        <template x-for="r in allRobots" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </div>
                <div class="text-xs text-surface-400 ml-auto" x-text="'Menampilkan ' + filteredUnits.length + ' unit'"></div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            <template x-if="loading">
                <div class="p-8 text-center text-surface-400">
                    <span class="spinner text-primary-500 mr-2"></span> Memuat data unit...
                </div>
            </template>

            <template x-if="!loading && filteredUnits.length === 0">
                <x-empty-state title="Tidak Ada Unit" description="Belum ada unit yang sesuai dengan filter atau belum ada data unit." />
            </template>

            <div class="overflow-x-auto" x-show="!loading && filteredUnits.length > 0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Kode Unit</th>
                            <th class="px-6 py-4 font-semibold">Model Robot</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Catatan Kondisi</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="unit in filteredUnits" :key="unit.id">
                            <tr class="hover:bg-surface-50/50">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-bold text-primary-700 bg-primary-50 px-2.5 py-1 rounded-md text-xs" x-text="unit.unit_code"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-surface-900" x-text="unit.robot?.name || '-'"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold capitalize"
                                          :class="{
                                              'bg-success-50 text-success-700 ring-1 ring-success-500/20': unit.status === 'tersedia',
                                              'bg-warning-50 text-warning-600 ring-1 ring-warning-500/20': unit.status === 'dipinjam',
                                              'bg-danger-50 text-danger-600 ring-1 ring-danger-500/20': unit.status === 'rusak'
                                          }"
                                          x-text="unit.status"></span>
                                </td>
                                <td class="px-6 py-4 text-xs text-surface-500 max-w-xs truncate" x-text="unit.condition_note || '-'"></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(unit)"
                                                :disabled="unit.status === 'dipinjam'"
                                                :title="unit.status === 'dipinjam' ? 'Diubah otomatis saat pengembalian' : 'Edit unit'"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition"
                                                :class="unit.status === 'dipinjam' ? 'bg-surface-100 text-surface-400 cursor-not-allowed' : 'bg-primary-50 hover:bg-primary-100 text-primary-700 cursor-pointer'">
                                            Edit
                                        </button>
                                        <button @click="openDeleteModal(unit)"
                                                class="px-2.5 py-1.5 bg-danger-50 hover:bg-danger-100 text-danger-700 rounded-lg text-xs font-semibold cursor-pointer transition">
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

        {{-- Form Modal (Create) --}}
        <x-modal maxWidth="md">
            <div class="p-6">
                <h3 class="font-bold text-surface-900 text-lg mb-4" x-text="isEdit ? 'Edit Unit Robot' : 'Tambah Unit Robot Baru'"></h3>
                <form @submit.prevent="saveUnit" class="space-y-4">
                    {{-- Robot Select --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">
                            Pilih Model Robot <span class="text-danger-500">*</span>
                        </label>
                        <select x-model="form.robot_id" required
                                class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <option value="">-- Pilih Model Robot --</option>
                            <template x-for="r in allRobots" :key="r.id">
                                <option :value="r.id" x-text="r.name"></option>
                            </template>
                        </select>
                        <template x-if="errors?.robot_id"><template x-for="msg in errors.robot_id" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                    </div>

                    {{-- Unit Code --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">
                            Kode Unit <span class="text-danger-500">* (Pola: RBT- + min 3 digit, misal: RBT-001)</span>
                        </label>
                        <input type="text" x-model="form.unit_code" @input="form.unit_code = form.unit_code.toUpperCase()"
                               required placeholder="Contoh: RBT-001 atau AYA-001" maxlength="50"
                               class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl font-mono text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors uppercase">
                        <template x-if="errors?.unit_code"><template x-for="msg in errors.unit_code" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                    </div>

                    {{-- Status (Only in Edit mode) --}}
                    <template x-if="isEdit">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">
                                Status Operasional <span class="text-danger-500">*</span>
                            </label>
                            <select x-model="form.status" required
                                    class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                                <option value="tersedia">Tersedia</option>
                                <option value="rusak">Rusak</option>
                            </select>
                        </div>
                    </template>

                    {{-- Condition Note --}}
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Catatan Kondisi Fisik</label>
                        <textarea x-model="form.condition_note" rows="2" placeholder="Kondisi baterai, bodi, sensor..."
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
                <h3 class="text-base font-bold text-surface-900 mb-1">Hapus Unit Robot?</h3>
                <p class="text-xs text-surface-500 mb-6">Unit "<span class="font-bold text-surface-800" x-text="selectedUnit?.unit_code"></span>" akan dihapus.</p>
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
        function adminUnits() {
            return {
                units: [],
                allRobots: [],
                filterStatus: '',
                filterRobot: '',
                currentPage: 1,
                lastPage: 1,
                loading: true,
                showModal: false,
                showDeleteModal: false,
                isEdit: false,
                selectedUnit: null,
                form: { robot_id: '', unit_code: '', condition_note: '', status: 'tersedia' },
                initialEditData: {},
                errors: {},
                saving: false,
                deleting: false,

                get filteredUnits() {
                    return this.units.filter(u => {
                        if (this.filterStatus && u.status !== this.filterStatus) return false;
                        if (this.filterRobot && u.robot_id != this.filterRobot) return false;
                        return true;
                    });
                },

                async init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('robot_id')) {
                        this.filterRobot = urlParams.get('robot_id');
                    }
                    this.fetchAllRobots();
                    this.loadData(1);
                },

                async fetchAllRobots() {
                    let page = 1;
                    let last = 1;
                    const accumulated = [];
                    try {
                        do {
                            const res = await api.get('/backend/robots', { per_page: 50, page });
                            accumulated.push(...(res.data || []));
                            last = res.last_page || 1;
                            page++;
                        } while (page <= last);
                        this.allRobots = accumulated;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    try {
                        const res = await api.get('/backend/admin/robot-units', { page: this.currentPage });
                        this.units = res.data || [];
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
                    this.form = { robot_id: this.filterRobot || '', unit_code: '', condition_note: '' };
                    this.errors = {};
                    this.showModal = true;
                },

                openEditModal(unit) {
                    this.isEdit = true;
                    this.selectedUnit = unit;
                    this.initialEditData = {
                        robot_id: unit.robot_id,
                        unit_code: unit.unit_code,
                        status: unit.status,
                        condition_note: unit.condition_note || ''
                    };
                    this.form = { ...this.initialEditData };
                    this.errors = {};
                    this.showModal = true;
                },

                openDeleteModal(unit) {
                    this.selectedUnit = unit;
                    this.showDeleteModal = true;
                },

                async saveUnit() {
                    this.errors = {};
                    if (!this.form.robot_id) this.errors.robot_id = ['Robot wajib dipilih.'];
                    if (!this.form.unit_code) this.errors.unit_code = ['Kode unit wajib diisi.'];
                    else if (this.form.unit_code.length > 50) this.errors.unit_code = ['Kode unit maksimal 50 karakter.'];

                    if (Object.keys(this.errors).length > 0) return;
                    this.saving = true;

                    try {
                        if (this.isEdit) {
                            // Only send changed fields
                            const changedPayload = {};
                            if (this.form.robot_id != this.initialEditData.robot_id) changedPayload.robot_id = this.form.robot_id;
                            if (this.form.unit_code !== this.initialEditData.unit_code) changedPayload.unit_code = this.form.unit_code;
                            if (this.form.status !== this.initialEditData.status) changedPayload.status = this.form.status;
                            if (this.form.condition_note !== this.initialEditData.condition_note) changedPayload.condition_note = this.form.condition_note;

                            await api.patch('/backend/admin/robot-units/' + this.selectedUnit.id, changedPayload);
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Unit robot berhasil diperbarui.', type: 'success' } }));
                        } else {
                            await api.post('/backend/admin/robot-units', {
                                robot_id: this.form.robot_id,
                                unit_code: this.form.unit_code,
                                condition_note: this.form.condition_note
                            });
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Unit robot berhasil ditambahkan.', type: 'success' } }));
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
                    if (!this.selectedUnit) return;
                    this.deleting = true;
                    try {
                        await api.delete('/backend/admin/robot-units/' + this.selectedUnit.id);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Unit berhasil dihapus.', type: 'success' } }));
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
