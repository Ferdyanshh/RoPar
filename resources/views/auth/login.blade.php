<x-layouts.guest title="Login">
    <div class="w-full max-w-md mx-auto px-6" x-data="loginForm()">
        {{-- Brand --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-primary-400 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <rect x="8" y="14" width="8" height="6" rx="2"/>
                    <circle cx="10" cy="7" r="1" fill="currentColor"/>
                    <circle cx="14" cy="7" r="1" fill="currentColor"/>
                    <line x1="10" y1="9" x2="14" y2="9" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-accent-600 bg-clip-text text-transparent">RoPar</h1>
            <p class="text-sm text-surface-500 mt-1">Sistem Peminjaman Robot Pacar</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-surface-200/50 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-surface-900 mb-6">Masuk ke akun Anda</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">
                        Email <span class="text-danger-500">*</span>
                    </label>
                    <input type="email" id="email" x-model="form.email" required placeholder="nama@email.com"
                           class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm text-surface-800 placeholder:text-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                    <template x-if="errors?.email">
                        <template x-for="msg in errors.email" :key="msg">
                            <p class="text-xs text-danger-600 mt-1" x-text="msg"></p>
                        </template>
                    </template>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">
                        Kata Sandi <span class="text-danger-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" x-model="form.password" required placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 bg-white border border-surface-300 rounded-xl text-sm text-surface-800 placeholder:text-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors pr-10">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-surface-400 hover:text-surface-600 cursor-pointer">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    <template x-if="errors?.password">
                        <template x-for="msg in errors.password" :key="msg">
                            <p class="text-xs text-danger-600 mt-1" x-text="msg"></p>
                        </template>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" x-model="form.remember"
                           class="rounded border-surface-300 text-primary-500 focus:ring-primary-500/40 cursor-pointer">
                    <label for="remember" class="text-sm text-surface-600 cursor-pointer">Ingat saya</label>
                </div>

                <button type="submit" :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl font-semibold text-sm hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <span x-show="loading" class="spinner"></span>
                    <span x-text="loading ? 'Memproses...' : 'Masuk'"></span>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-surface-500 mt-6">
            Belum punya akun?
            <a href="/register" class="text-primary-600 hover:text-primary-700 font-semibold">Daftar sekarang</a>
        </p>
    </div>

    <script>
        function loginForm() {
            return {
                form: { email: '', password: '', remember: false },
                errors: {},
                loading: false,
                showPassword: false,

                validate() {
                    this.errors = {};
                    if (!this.form.email) this.errors.email = ['Email wajib diisi.'];
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) this.errors.email = ['Format email tidak valid.'];
                    if (!this.form.password) this.errors.password = ['Kata sandi wajib diisi.'];
                    return Object.keys(this.errors).length === 0;
                },

                async submit() {
                    if (!this.validate()) return;
                    this.loading = true;
                    this.errors = {};

                    try {
                        await api.post('/backend/login', this.form);
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
