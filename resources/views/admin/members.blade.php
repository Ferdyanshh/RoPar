<x-layouts.app title="Kelola Anggota">
    <div x-data="adminMembers()" x-init="init()" class="space-y-6">
        {{-- Header & Search --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-surface-900">Data Anggota Peminjam</h2>
                <p class="text-xs text-surface-400 mt-1">Kelola akun dan informasi profil pengguna yang terdaftar</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="q" @input.debounce.300ms="loadData(1)"
                           placeholder="Cari nama, email, HP..."
                           class="w-full pl-9 pr-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/40">
                    <svg class="w-3.5 h-3.5 text-surface-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <button @click="openCreateModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer transition whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Anggota</span>
                </button>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            <template x-if="loading">
                <div class="p-8 text-center text-surface-400">
                    <span class="spinner text-primary-500 mr-2"></span> Memuat data anggota...
                </div>
            </template>

            <template x-if="!loading && members.length === 0">
                <x-empty-state title="Tidak Ada Anggota" description="Tidak ada data anggota yang cocok dengan pencarian." />
            </template>

            <div class="overflow-x-auto" x-show="!loading && members.length > 0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Username / Email</th>
                            <th class="px-6 py-4 font-semibold">Nama Lengkap</th>
                            <th class="px-6 py-4 font-semibold">No. Telepon</th>
                            <th class="px-6 py-4 font-semibold">Tgl Lahir / Usia</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="m in members" :key="m.id">
                            <tr class="hover:bg-surface-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs">
                                            <span x-text="m.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-surface-900" x-text="m.name"></p>
                                            <p class="text-xs text-surface-400" x-text="m.email"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-surface-800" x-text="m.profile?.full_name || '-'"></td>
                                <td class="px-6 py-4 font-mono text-xs text-surface-600" x-text="m.profile?.phone || '-'"></td>
                                <td class="px-6 py-4 text-xs text-surface-500" x-text="formatDate(m.profile?.birth_date)"></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(m)"
                                                class="px-2.5 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg text-xs font-semibold cursor-pointer transition">
                                            Edit
                                        </button>
                                        <button @click="openDeleteModal(m)"
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

        {{-- Form Modal (Create / Edit) --}}
        <x-modal maxWidth="xl">
            <div class="p-6">
                <h3 class="font-bold text-surface-900 text-lg mb-4" x-text="isEdit ? 'Edit Data Anggota' : 'Tambah Anggota Baru'"></h3>
                <form @submit.prevent="saveMember" class="space-y-4">
                    {{-- Section 1: Account --}}
                    <div class="p-4 rounded-2xl bg-surface-50 border border-surface-200 space-y-3">
                        <h4 class="text-xs font-bold text-surface-400 uppercase tracking-wider">Data Akun Login</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">Username <span class="text-danger-500">*</span></label>
                                <input type="text" x-model="form.name" required maxlength="100"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                                <template x-if="errors?.name"><template x-for="msg in errors.name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">Email <span class="text-danger-500">*</span></label>
                                <input type="email" x-model="form.email" required maxlength="150"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                                <template x-if="errors?.email"><template x-for="msg in errors.email" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">
                                    Kata Sandi <span class="text-danger-500" x-show="!isEdit">*</span>
                                    <span class="text-[10px] text-surface-400 font-normal" x-show="isEdit">(Kosongkan jika tidak diubah)</span>
                                </label>
                                <input type="password" x-model="form.password" :required="!isEdit" placeholder="Min. 8 karakter"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                                <template x-if="errors?.password"><template x-for="msg in errors.password" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">
                                    Konfirmasi Sandi <span class="text-danger-500" x-show="!isEdit || form.password">*</span>
                                </label>
                                <input type="password" x-model="form.password_confirmation" :required="!isEdit || !!form.password"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Profile --}}
                    <div class="p-4 rounded-2xl bg-surface-50 border border-surface-200 space-y-3">
                        <h4 class="text-xs font-bold text-surface-400 uppercase tracking-wider">Data Profil Anggota</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">Nama Lengkap <span class="text-danger-500">*</span></label>
                                <input type="text" x-model="form.full_name" required maxlength="150"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                                <template x-if="errors?.full_name"><template x-for="msg in errors.full_name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">Nomor Telepon <span class="text-danger-500">*</span></label>
                                <input type="tel" x-model="form.phone" required pattern="(?:[0-9]{8,15}|\+[0-9]{7,14})" inputmode="tel" maxlength="15"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                                <template x-if="errors?.phone"><template x-for="msg in errors.phone" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">Tanggal Lahir</label>
                                <input type="date" x-model="form.birth_date" :max="yesterday()"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                                <template x-if="errors?.birth_date"><template x-for="msg in errors.birth_date" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-surface-700 mb-1">Alamat <span class="text-danger-500">*</span></label>
                                <textarea x-model="form.address" rows="2" required
                                          class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40 resize-y"></textarea>
                                <template x-if="errors?.address"><template x-for="msg in errors.address" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                        </div>
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
                <h3 class="text-base font-bold text-surface-900 mb-1">Hapus Anggota?</h3>
                <p class="text-xs text-surface-500 mb-6">Akun "<span class="font-bold text-surface-800" x-text="selectedMember?.name"></span>" akan dihapus.</p>
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
        function adminMembers() {
            return {
                members: [],
                q: '',
                currentPage: 1,
                lastPage: 1,
                loading: true,
                showModal: false,
                showDeleteModal: false,
                isEdit: false,
                selectedMember: null,
                form: { name: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', birth_date: '' },
                errors: {},
                saving: false,
                deleting: false,

                yesterday() {
                    const d = new Date();
                    d.setDate(d.getDate() - 1);
                    return d.toISOString().substring(0, 10);
                },

                async init() {
                    this.loadData(1);
                },

                async loadData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    try {
                        const params = { page: this.currentPage };
                        if (this.q) params.q = this.q;
                        const res = await api.get('/backend/admin/members', params);
                        this.members = res.data || [];
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
                    this.form = { name: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', birth_date: '' };
                    this.errors = {};
                    this.showModal = true;
                },

                openEditModal(member) {
                    this.isEdit = true;
                    this.selectedMember = member;
                    this.form = {
                        name: member.name,
                        email: member.email,
                        password: '',
                        password_confirmation: '',
                        full_name: member.profile?.full_name || '',
                        phone: member.profile?.phone || '',
                        address: member.profile?.address || '',
                        birth_date: member.profile?.birth_date ? member.profile.birth_date.substring(0, 10) : ''
                    };
                    this.errors = {};
                    this.showModal = true;
                },

                openDeleteModal(member) {
                    this.selectedMember = member;
                    this.showDeleteModal = true;
                },

                validate() {
                    this.errors = {};
                    if (!this.form.name) this.errors.name = ['Username wajib diisi.'];
                    if (!this.form.email) this.errors.email = ['Email wajib diisi.'];
                    if (!this.isEdit && !this.form.password) this.errors.password = ['Kata sandi wajib diisi.'];
                    if (this.form.password && this.form.password.length < 8) this.errors.password = ['Kata sandi minimal 8 karakter.'];
                    if (this.form.password && this.form.password !== this.form.password_confirmation) {
                        this.errors.password_confirmation = ['Konfirmasi sandi tidak sesuai.'];
                    }
                    if (!this.form.full_name) this.errors.full_name = ['Nama lengkap wajib diisi.'];
                    if (!this.form.phone) this.errors.phone = ['Nomor telepon wajib diisi.'];
                    else if (!/^(?:[0-9]{8,15}|\+[0-9]{7,14})$/.test(this.form.phone)) this.errors.phone = ['No. HP harus 8–15 karakter angka dan boleh diawali tanda +.'];
                    if (!this.form.address.trim()) this.errors.address = ['Alamat wajib diisi.'];

                    return Object.keys(this.errors).length === 0;
                },

                async saveMember() {
                    if (!this.validate()) return;
                    this.saving = true;

                    const payload = { ...this.form };
                    if (this.isEdit && !payload.password) {
                        delete payload.password;
                        delete payload.password_confirmation;
                    }

                    try {
                        if (this.isEdit) {
                            await api.patch('/backend/admin/members/' + this.selectedMember.id, payload);
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Data anggota berhasil diperbarui.', type: 'success' } }));
                        } else {
                            await api.post('/backend/admin/members', payload);
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Anggota berhasil ditambahkan.', type: 'success' } }));
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
                    if (!this.selectedMember) return;
                    this.deleting = true;
                    try {
                        await api.delete('/backend/admin/members/' + this.selectedMember.id);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Anggota berhasil dihapus.', type: 'success' } }));
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
