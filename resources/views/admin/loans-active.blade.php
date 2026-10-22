<x-layouts.app title="Pinjaman Aktif">
    <div x-data="adminActiveLoans()" x-init="init()" class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-surface-900">Pinjaman Robot yang Sedang Aktif</h2>
                <p class="text-xs text-surface-400 mt-1">Daftar peminjaman berjalan diurutkan berdasarkan tanggal jatuh tempo</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-warning-50 text-warning-700 border border-warning-200 rounded-xl text-xs font-bold"
                      x-text="totalActive + ' Pinjaman Aktif'"></span>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            <template x-if="loading">
                <div class="p-8 text-center text-surface-400">
                    <span class="spinner text-primary-500 mr-2"></span> Memuat data pinjaman aktif...
                </div>
            </template>

            <template x-if="!loading && loans.length === 0">
                <x-empty-state title="Tidak Ada Pinjaman Aktif" description="Semua unit robot saat ini berada di tempat dan tidak ada yang sedang dipinjam." />
            </template>

            <div class="overflow-x-auto" x-show="!loading && loans.length > 0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Peminjam</th>
                            <th class="px-6 py-4 font-semibold">Robot & Kode Unit</th>
                            <th class="px-6 py-4 font-semibold">Tgl Pinjam</th>
                            <th class="px-6 py-4 font-semibold">Jatuh Tempo</th>
                            <th class="px-6 py-4 font-semibold">Status / Keterlambatan</th>
                            <th class="px-6 py-4 font-semibold">Estimasi Denda</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="loan in loans" :key="loan.id">
                            <tr :class="calculateLateDays(loan.due_date) > 0 ? 'bg-danger-50/50' : 'hover:bg-surface-50/50'">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-surface-900" x-text="loan.member?.profile?.full_name || loan.member?.name"></p>
                                    <p class="text-xs text-surface-400" x-text="loan.member?.email"></p>
                                    <p class="text-xs text-surface-400 font-mono" x-text="loan.member?.profile?.phone"></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-surface-800" x-text="loan.unit?.robot?.name || '-'"></p>
                                    <span class="font-mono text-xs font-bold text-primary-600" x-text="loan.unit?.unit_code"></span>
                                </td>
                                <td class="px-6 py-4 text-xs" x-text="formatDate(loan.loan_date)"></td>
                                <td class="px-6 py-4 text-xs font-bold" x-text="formatDate(loan.due_date)"></td>
                                <td class="px-6 py-4">
                                    <template x-if="calculateLateDays(loan.due_date) > 0">
                                        <div>
                                            <x-badge-status status="terlambat" />
                                            <p class="text-xs font-bold text-danger-700 mt-1" x-text="'Telat ' + calculateLateDays(loan.due_date) + ' hari'"></p>
                                        </div>
                                    </template>
                                    <template x-if="calculateLateDays(loan.due_date) === 0">
                                        <div>
                                            <x-badge-status status="dipinjam" />
                                            <p class="text-xs text-success-600 font-semibold mt-1" x-text="'Sisa ' + calculateRemainingDays(loan.due_date) + ' hari'"></p>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-6 py-4 font-bold text-xs">
                                    <template x-if="calculateLateDays(loan.due_date) > 0">
                                        <span class="text-danger-600" x-text="formatRupiah(calculateFine(loan.due_date))"></span>
                                    </template>
                                    <template x-if="calculateLateDays(loan.due_date) === 0">
                                        <span class="text-surface-400">Rp0</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openReturnModal(loan)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-success-600 hover:bg-success-700 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Proses Kembali</span>
                                    </button>
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

        {{-- Return Confirmation Modal --}}
        <x-modal maxWidth="lg">
            <div class="p-6">
                {{-- Header with Dynamic Icon & Alert --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                         :class="returnLateDays > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'">
                        <template x-if="returnLateDays > 0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </template>
                        <template x-if="returnLateDays === 0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="font-bold text-surface-900 text-base"
                            x-text="returnLateDays > 0 ? 'Proses Pengembalian Robot (Terlambat & Denda)' : 'Proses Pengembalian Robot'"></h3>
                        <p class="text-xs text-surface-500"
                           x-text="returnLateDays > 0 ? 'Unit robot dikembalikan melebihi jatuh tempo (terkena denda keterlambatan)' : 'Konfirmasi pengembalian unit robot dari anggota'"></p>
                    </div>
                </div>

                {{-- Member & Robot Card --}}
                <div class="flex items-center justify-between p-3.5 bg-surface-50 rounded-2xl border border-surface-200 text-sm mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-surface-900 text-sm" x-text="selectedLoan?.member?.profile?.full_name || selectedLoan?.member?.name"></h4>
                            <p class="text-xs text-surface-500" x-text="selectedLoan?.member?.email"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-surface-900 text-sm" x-text="selectedLoan?.unit?.robot?.name || '-'"></p>
                        <span class="inline-block font-mono text-xs font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-md" x-text="selectedLoan?.unit?.unit_code"></span>
                    </div>
                </div>

                {{-- Timeline: Tanggal Pinjam → Jatuh Tempo → Pengembalian --}}
                <div class="p-4 bg-surface-50/70 rounded-2xl border border-surface-200 space-y-4 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Tanggal Pinjam --}}
                        <div class="p-3 bg-white rounded-xl border border-surface-200 text-center">
                            <div class="text-[10px] font-semibold text-surface-500 uppercase tracking-wider mb-1">📅 Tanggal Pinjam</div>
                            <div class="text-sm font-bold text-surface-900" x-text="formatDate(selectedLoan?.loan_date)"></div>
                        </div>

                        {{-- Jatuh Tempo --}}
                        <div class="p-3 bg-primary-50 rounded-xl border border-primary-200 text-center">
                            <div class="text-[10px] font-semibold text-primary-600 uppercase tracking-wider mb-1">⏰ Jatuh Tempo (5 Hari)</div>
                            <div class="text-sm font-bold text-primary-700" x-text="formatDate(selectedLoan?.due_date)"></div>
                        </div>

                        {{-- Tanggal Pengembalian (Editable Date) --}}
                        <div class="p-3 bg-white rounded-xl border-2 text-center transition-colors"
                             :class="returnLateDays > 0 ? 'border-rose-300' : 'border-emerald-300'">
                            <div class="text-[10px] font-semibold uppercase tracking-wider mb-1"
                                 :class="returnLateDays > 0 ? 'text-rose-600' : 'text-emerald-600'">🗓️ Tanggal Kembali</div>
                            <input type="date" x-model="customReturnDate" :min="toLocalDateString(selectedLoan?.loan_date)" @change="recalcReturnFine()"
                                   class="w-full px-2 py-1 border rounded-lg text-xs font-bold text-center outline-none focus:ring-2"
                                   :class="returnLateDays > 0 ? 'bg-rose-50 border-rose-200 text-rose-800 focus:ring-rose-400' : 'bg-emerald-50 border-emerald-200 text-emerald-800 focus:ring-emerald-400'">
                        </div>
                    </div>

                    {{-- Quick Simulation Chips --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold text-surface-600">⚡ Simulasi Tanggal Pengembalian:</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="setReturnDate(todayDate())"
                                    :class="customReturnDate === todayDate() ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-white hover:bg-surface-100 text-surface-700 border border-surface-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                📌 Hari Ini
                            </button>
                            <button type="button" @click="setReturnDate(toLocalDateString(selectedLoan?.due_date))"
                                    :class="customReturnDate === toLocalDateString(selectedLoan?.due_date) ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-semibold'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                ⭐ Tepat Jatuh Tempo (0 Denda)
                            </button>
                            <button type="button" @click="setReturnDate(addDays(selectedLoan?.due_date, 1))"
                                    :class="returnLateDays === 1 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                ⚠️ +1 Hari Telat (Rp5.000)
                            </button>
                            <button type="button" @click="setReturnDate(addDays(selectedLoan?.due_date, 3))"
                                    :class="returnLateDays === 3 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                ⚠️ +3 Hari Telat (Rp15.000)
                            </button>
                            <button type="button" @click="setReturnDate(addDays(selectedLoan?.due_date, 5))"
                                    :class="returnLateDays === 5 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                ⚠️ +5 Hari Telat (Rp25.000)
                            </button>
                        </div>
                    </div>

                    {{-- LIVE FINE & LATE CALCULATION DISPLAY --}}
                    <div class="mt-3">
                        <template x-if="returnLateDays <= 0">
                            <div class="p-3.5 bg-emerald-50 border border-emerald-300 rounded-xl text-emerald-900 transition-all">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-800">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>BEBAS DENDA — Rp0</span>
                                    </div>
                                    <span class="px-2 py-0.5 bg-emerald-200 text-emerald-800 text-[10px] font-bold rounded-md">
                                        Tepat Waktu ✓
                                    </span>
                                </div>
                                <p class="text-xs text-emerald-700">
                                    Pengembalian tanggal <strong x-text="formatDate(customReturnDate)"></strong> masih dalam batas jatuh tempo (<strong x-text="formatDate(selectedLoan?.due_date)"></strong>). <strong>Total Denda: Rp0</strong>.
                                </p>
                            </div>
                        </template>

                        <template x-if="returnLateDays > 0">
                            <div class="p-3.5 bg-rose-50 border-2 border-rose-300 rounded-xl text-rose-900 transition-all">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-1.5 font-bold text-xs text-rose-700">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span class="font-extrabold tracking-wide uppercase">Kena Denda Keterlambatan</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 bg-rose-200 text-rose-900 text-xs font-extrabold rounded-md animate-pulse">
                                        Telat <span x-text="returnLateDays"></span> Hari
                                    </span>
                                </div>
                                <div class="text-xs text-rose-800 space-y-1.5">
                                    <p>
                                        Pengembalian tanggal <strong x-text="formatDate(customReturnDate)"></strong> melebihi batas jatuh tempo (<strong x-text="formatDate(selectedLoan?.due_date)"></strong>) sebanyak <strong><span x-text="returnLateDays"></span> hari telat</strong>.
                                    </p>
                                    <div class="flex items-center justify-between pt-2 border-t border-rose-200">
                                        <span class="font-semibold text-rose-900">Perhitungan Denda:</span>
                                        <span class="text-sm font-extrabold text-rose-700">
                                            <span x-text="returnLateDays"></span> hari telat × Rp5.000 = <span x-text="formatRupiah(returnFineAmount)"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Summary Row with Explicit 'Telat' label --}}
                    <div class="pt-3 border-t border-surface-200 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-surface-500 font-medium">Status Keterlambatan:</span>
                            <template x-if="returnLateDays > 0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-extrabold bg-rose-100 text-rose-700 border border-rose-200">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                    <span x-text="'Telat ' + returnLateDays + ' Hari'"></span>
                                </span>
                            </template>
                            <template x-if="returnLateDays === 0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Tepat Waktu (0 Hari)</span>
                                </span>
                            </template>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-surface-500 font-medium">Total Denda (Preview):</span>
                            <span class="font-extrabold text-base" :class="returnFineAmount > 0 ? 'text-rose-600' : 'text-surface-800'"
                                  x-text="formatRupiah(returnFineAmount)"></span>
                        </div>
                    </div>
                </div>

                {{-- Result from server after processed --}}
                <template x-if="returnResult">
                    <div class="p-4 mb-4 rounded-2xl border text-xs space-y-1.5"
                         :class="(returnResult.fine_amount || returnFineAmount) > 0 ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800'">
                        <p class="font-bold text-sm" x-text="(returnResult.fine_amount || returnFineAmount) > 0 ? 'Pengembalian Berhasil Diproses (Terlambat)' : 'Pengembalian Berhasil Diproses Tepat Waktu!'"></p>
                        <p>Tanggal Kembali: <span class="font-semibold" x-text="formatDate(customReturnDate || returnResult.return_date)"></span></p>
                        <p>Keterlambatan: <span class="font-bold" x-text="(returnResult.late_days || returnLateDays) > 0 ? 'Telat ' + (returnResult.late_days || returnLateDays) + ' hari' : 'Tepat Waktu (0 hari)'"></span></p>
                        <p>Denda Final: <span class="font-bold text-sm" :class="(returnResult.fine_amount || returnFineAmount) > 0 ? 'text-rose-700' : 'text-emerald-700'" x-text="formatRupiah(returnResult.fine_amount || returnFineAmount)"></span></p>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false; returnResult = null;" class="px-4 py-2 text-surface-600 hover:bg-surface-100 rounded-xl text-sm font-semibold transition cursor-pointer">
                        <span x-text="returnResult ? 'Tutup' : 'Batal'"></span>
                    </button>
                    <button type="button" x-show="!returnResult" @click="confirmReturn" :disabled="processing"
                            :class="returnLateDays > 0 ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold shadow transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                        <span x-show="processing" class="spinner"></span>
                        <span x-text="processing ? 'Memproses...' : (returnLateDays > 0 ? 'Konfirmasi Pengembalian (Telat ' + returnLateDays + ' Hari)' : 'Konfirmasi Pengembalian')"></span>
                    </button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        function adminActiveLoans() {
            return {
                loans: [],
                totalActive: 0,
                currentPage: 1,
                lastPage: 1,
                loading: true,
                showModal: false,
                selectedLoan: null,
                processing: false,
                returnResult: null,
                customReturnDate: todayDate(),
                returnLateDays: 0,
                returnFineAmount: 0,

                async init() {
                    this.loadData(1);
                },

                async loadData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    try {
                        const res = await api.get('/backend/admin/loans', { page: this.currentPage });
                        this.loans = res.data || [];
                        this.totalActive = res.total || this.loans.length;
                        this.currentPage = res.current_page || 1;
                        this.lastPage = res.last_page || 1;
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.loading = false;
                    }
                },

                openReturnModal(loan) {
                    this.selectedLoan = loan;
                    this.returnResult = null;
                    this.customReturnDate = todayDate();
                    this.recalcReturnFine();
                    this.showModal = true;
                },

                setReturnDate(dateStr) {
                    if (!dateStr) return;
                    this.customReturnDate = toLocalDateString(dateStr);
                    this.recalcReturnFine();
                },

                recalcReturnFine() {
                    if (!this.selectedLoan?.due_date || !this.customReturnDate) {
                        this.returnLateDays = 0;
                        this.returnFineAmount = 0;
                        return;
                    }
                    const dueStr = toLocalDateString(this.selectedLoan.due_date);
                    const [dy, dm, dd] = dueStr.split('-').map(Number);
                    const dueDate = new Date(dy, dm - 1, dd);

                    const retStr = toLocalDateString(this.customReturnDate);
                    const [ry, rm, rd] = retStr.split('-').map(Number);
                    const retDate = new Date(ry, rm - 1, rd);

                    const diffMs = retDate - dueDate;
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                    this.returnLateDays = Math.max(0, diffDays);
                    this.returnFineAmount = this.returnLateDays * 5000;
                },

                async confirmReturn() {
                    if (!this.selectedLoan) return;
                    this.processing = true;

                    try {
                        const res = await api.post('/backend/admin/loans/' + this.selectedLoan.id + '/return', {
                            return_date: this.customReturnDate
                        });
                        this.returnResult = res.data || res;
                        const fineMsg = this.returnLateDays > 0
                            ? ' Pengembalian tercatat Telat ' + this.returnLateDays + ' hari. Denda: ' + formatRupiah(this.returnFineAmount) + '.'
                            : ' Pengembalian tepat waktu (Bebas Denda).';
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Pengembalian robot berhasil diproses!' + fineMsg, type: this.returnLateDays > 0 ? 'warning' : 'success' } }));
                        setTimeout(() => {
                            this.showModal = false;
                            this.returnResult = null;
                            this.loadData(this.currentPage);
                        }, 1800);
                    } catch (err) {
                        if (err.errors) {
                            const { fieldErrors, toastMessages } = separateErrors(err.errors);
                            toastMessages.forEach(msg => {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
                            });
                        } else if (err.message) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                        }
                    } finally {
                        this.processing = false;
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
