<x-layouts.app title="Profil Saya">
    <div class="max-w-2xl mx-auto" x-data="profileForm()" x-init="init()">
        <div class="bg-white rounded-3xl border border-surface-200 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-4 mb-8 pb-6 border-b border-surface-100">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-accent-500 text-white flex items-center justify-center font-bold text-2xl shadow">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-surface-900" x-text="form.name || 'Profil Pengguna'"></h2>
                        <x-badge-status :status="auth()->user()->role->value" />
                    </div>
                    <p class="text-xs text-surface-400 mt-0.5" x-text="email"></p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1.5">
                        Username <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" x-model="form.name" required maxlength="100"
                           class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                    <template x-if="errors?.name"><template x-for="msg in errors.name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1.5">
                        Email
                    </label>
                    <input type="email" :value="email" readonly disabled
                           class="w-full px-3.5 py-2.5 bg-surface-100 border border-surface-200 rounded-xl text-sm text-surface-500 cursor-not-allowed">
                    <p class="text-xs text-surface-400 mt-1">Alamat email akun tidak dapat diubah.</p>
                </div>

                <div class="pt-2">
                    <h3 class="text-xs font-bold text-surface-400 uppercase tracking-wider mb-4">Informasi Pribadi</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">
                                Nama Lengkap <span class="text-danger-500">*</span>
                            </label>
                            <input type="text" x-model="form.full_name" required maxlength="150"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.full_name"><template x-for="msg in errors.full_name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">
                                Nomor Telepon / HP <span class="text-danger-500">*</span>
                            </label>
                            <input type="text" x-model="form.phone" required pattern="\d{10,15}" inputmode="numeric" maxlength="15"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.phone"><template x-for="msg in errors.phone" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">
                                Alamat Domisili
                            </label>
                            <textarea x-model="form.address" rows="2"
                                      class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors resize-y"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">
                                Tanggal Lahir
                            </label>
                            <input type="date" x-model="form.birth_date" :max="yesterday()"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.birth_date"><template x-for="msg in errors.birth_date" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-surface-100 flex items-center justify-end gap-3">
                    <button type="submit" :disabled="saving"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-bold text-sm shadow disabled:opacity-50 cursor-pointer transition">
                        <span x-show="saving" class="spinner"></span>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function profileForm() {
            return {
                email: '',
                form: {
                    name: '',
                    full_name: '',
                    phone: '',
                    address: '',
                    birth_date: ''
                },
                errors: {},
                saving: false,

                yesterday() {
                    const d = new Date();
                    d.setDate(d.getDate() - 1);
                    return d.toISOString().substring(0, 10);
                },

                async init() {
                    try {
                        const res = await api.get('/backend/profile');
                        const data = res.data || {};
                        this.email = data.email || '';
                        this.form.name = data.name || '';
                        if (data.profile) {
                            this.form.full_name = data.profile.full_name || '';
                            this.form.phone = data.profile.phone || '';
                            this.form.address = data.profile.address || '';
                            if (data.profile.birth_date) {
                                this.form.birth_date = data.profile.birth_date.substring(0, 10);
                            }
                        }
                    } catch (err) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal memuat profil', type: 'error' } }));
                    }
                },

                validate() {
                    this.errors = {};
                    if (!this.form.name) this.errors.name = ['Username wajib diisi.'];
                    if (!this.form.full_name) this.errors.full_name = ['Nama lengkap wajib diisi.'];
                    if (!this.form.phone) this.errors.phone = ['No. telepon wajib diisi.'];
                    else if (!/^\d{10,15}$/.test(this.form.phone)) this.errors.phone = ['Nomor telepon harus 10–15 digit angka.'];
                    if (this.form.birth_date && this.form.birth_date >= new Date().toISOString().substring(0, 10)) {
                        this.errors.birth_date = ['Tanggal lahir harus sebelum hari ini.'];
                    }
                    return Object.keys(this.errors).length === 0;
                },

                async submit() {
                    if (!this.validate()) return;
                    this.saving = true;
                    this.errors = {};

                    try {
                        await api.patch('/backend/profile', this.form);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Profil berhasil diperbarui!', type: 'success' } }));
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
                }
            }
        }
    </script>
</x-layouts.app>
