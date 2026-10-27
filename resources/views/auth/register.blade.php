<x-layouts.guest title="Registrasi">
    <div class="w-full max-w-lg mx-auto px-6 py-8" x-data="registerForm()">
        {{-- Brand --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-primary-400 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/><rect x="8" y="14" width="8" height="6" rx="2"/><circle cx="10" cy="7" r="1" fill="currentColor"/><circle cx="14" cy="7" r="1" fill="currentColor"/><line x1="10" y1="9" x2="14" y2="9" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-accent-600 bg-clip-text text-transparent">Daftar Akun</h1>
            <p class="text-sm text-surface-500 mt-1">Buat akun baru untuk mulai meminjam robot pacar</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-surface-200/50 p-6 sm:p-8">
            <form @submit.prevent="submit" class="space-y-5">
                {{-- Data Akun --}}
                <div>
                    <h3 class="text-sm font-semibold text-surface-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold">1</span>
                        Data Akun
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label for="name" class="block text-sm font-medium text-surface-700 mb-1.5">Username <span class="text-danger-500">*</span></label>
                            <input type="text" id="name" x-model="form.name" required maxlength="100" placeholder="username"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.name"><template x-for="msg in errors.name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email <span class="text-danger-500">*</span></label>
                            <input type="email" id="email" x-model="form.email" required maxlength="150" placeholder="nama@email.com"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.email"><template x-for="msg in errors.email" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">Kata Sandi <span class="text-danger-500">*</span></label>
                                <input type="password" id="password" x-model="form.password" required minlength="8" placeholder="Min. 8 karakter"
                                       class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                                <template x-if="errors?.password"><template x-for="msg in errors.password" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-surface-700 mb-1.5">Konfirmasi <span class="text-danger-500">*</span></label>
                                <input type="password" id="password_confirmation" x-model="form.password_confirmation" required placeholder="Ulangi kata sandi"
                                       class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors"
                                       :class="form.password_confirmation && form.password !== form.password_confirmation ? 'border-danger-400 ring-1 ring-danger-400/30' : ''">
                                <p x-show="form.password_confirmation && form.password !== form.password_confirmation" class="text-xs text-danger-600 mt-1">Konfirmasi kata sandi tidak cocok.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-surface-100">

                {{-- Data Profil --}}
                <div>
                    <h3 class="text-sm font-semibold text-surface-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-accent-100 text-accent-600 flex items-center justify-center text-xs font-bold">2</span>
                        Data Profil
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Lengkap <span class="text-danger-500">*</span></label>
                            <input type="text" id="full_name" x-model="form.full_name" required maxlength="150" placeholder="Nama lengkap Anda"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.full_name"><template x-for="msg in errors.full_name" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-surface-700 mb-1.5">No. Telepon <span class="text-danger-500">*</span></label>
                            <input type="tel" id="phone" x-model="form.phone" required pattern="(?:[0-9]{8,15}|\+[0-9]{7,14})" inputmode="tel" placeholder="08xxxxxxxxxx" maxlength="15"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.phone"><template x-for="msg in errors.phone" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-surface-700 mb-1.5">Alamat <span class="text-danger-500">*</span></label>
                            <textarea id="address" x-model="form.address" rows="2" required placeholder="Alamat lengkap"
                                      class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors resize-y"></textarea>
                            <template x-if="errors?.address"><template x-for="msg in errors.address" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-surface-700 mb-1.5">Tanggal Lahir</label>
                            <input type="date" id="birth_date" x-model="form.birth_date" :max="yesterday()"
                                   class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                            <template x-if="errors?.birth_date"><template x-for="msg in errors.birth_date" :key="msg"><p class="text-xs text-danger-600 mt-1" x-text="msg"></p></template></template>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-xl font-semibold text-sm hover:from-primary-600 hover:to-accent-600 transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <span x-show="loading" class="spinner"></span>
                    <span x-text="loading ? 'Memproses...' : 'Daftar Sekarang'"></span>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-surface-500 mt-6">
            Sudah punya akun?
            <a href="/login" class="text-primary-600 hover:text-primary-700 font-semibold">Masuk</a>
        </p>
    </div>

    <script>
        function registerForm() {
            return {
                form: { name: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', birth_date: '' },
                errors: {},
                loading: false,

                yesterday() {
                    const d = new Date();
                    d.setDate(d.getDate() - 1);
                    return d.toISOString().substring(0, 10);
                },

                validate() {
                    this.errors = {};
                    if (!this.form.name) this.errors.name = ['Username wajib diisi.'];
                    else if (this.form.name.length > 100) this.errors.name = ['Username maksimal 100 karakter.'];
                    if (!this.form.email) this.errors.email = ['Email wajib diisi.'];
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) this.errors.email = ['Format email tidak valid.'];
                    else if (this.form.email.length > 150) this.errors.email = ['Email maksimal 150 karakter.'];
                    if (!this.form.password) this.errors.password = ['Kata sandi wajib diisi.'];
                    else if (this.form.password.length < 8) this.errors.password = ['Kata sandi minimal 8 karakter.'];
                    if (this.form.password !== this.form.password_confirmation) this.errors.password_confirmation = ['Konfirmasi kata sandi tidak cocok.'];
                    if (!this.form.full_name) this.errors.full_name = ['Nama lengkap wajib diisi.'];
                    else if (this.form.full_name.length > 150) this.errors.full_name = ['Nama lengkap maksimal 150 karakter.'];
                    if (!this.form.phone) this.errors.phone = ['No. telepon wajib diisi.'];
                    else if (!/^(?:[0-9]{8,15}|\+[0-9]{7,14})$/.test(this.form.phone)) this.errors.phone = ['No. telepon harus 8–15 karakter angka dan boleh diawali tanda +.'];
                    if (!this.form.address.trim()) this.errors.address = ['Alamat wajib diisi.'];
                    if (this.form.birth_date && this.form.birth_date >= new Date().toISOString().substring(0, 10)) {
                        this.errors.birth_date = ['Tanggal lahir harus sebelum hari ini.'];
                    }
                    return Object.keys(this.errors).length === 0;
                },

                async submit() {
                    if (!this.validate()) return;
                    this.loading = true;
                    this.errors = {};

                    try {
                        await api.post('/backend/register', this.form);
                        window.location.href = '/dashboard';
                    } catch (err) {
                        this.loading = false;
                        if (err.errors) {
                            const { fieldErrors, toastMessages } = separateErrors(err.errors);
                            this.errors = fieldErrors;
                            toastMessages.forEach(msg => {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
                            });
                        } else if (err.message) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                        }
                    }
                }
            }
        }
    </script>
</x-layouts.guest>
