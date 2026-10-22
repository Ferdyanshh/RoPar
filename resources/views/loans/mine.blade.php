<x-layouts.app title="Pinjaman Saya">
    <div x-data="myLoans()" x-init="init()" class="space-y-6">
        {{-- Header & Info Box --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-surface-900">Daftar Pinjaman Robot Pacar</h2>
                <p class="text-xs text-surface-400 mt-1">Pantau unit yang sedang Anda bawa kencan dan riwayat kencan sebelumnya</p>
            </div>

            <div class="p-3.5 bg-primary-50 border border-primary-200/60 rounded-2xl flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-primary-600 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-xs text-primary-900 leading-snug space-y-0.5">
                    <p><span class="font-bold">Ketentuan Peminjaman:</span> Maksimal 2 unit per anggota, durasi maksimal 5 hari.</p>
                    <p class="text-primary-700">Jika lebih dari 5 hari, dikenakan denda Rp5.000 / hari keterlambatan. Anda dapat mengembalikan unit secara mandiri kapan saja.</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-2 border-b border-surface-200 pb-2">
            <button @click="activeTab = 'aktif'"
                    :class="activeTab === 'aktif'
                        ? 'border-primary-600 text-primary-600 font-bold border-b-2'
                        : 'text-surface-500 hover:text-surface-800 font-semibold'"
                    class="px-4 py-2 text-sm transition cursor-pointer flex items-center gap-2">
                <span>Sedang Berjalan</span>
                <span class="px-2 py-0.5 rounded-full text-xs"
                      :class="activeTab === 'aktif' ? 'bg-primary-100 text-primary-700 font-bold' : 'bg-surface-100 text-surface-500'"
                      x-text="activeLoansList.length"></span>
            </button>
            <button @click="activeTab = 'riwayat'"
                    :class="activeTab === 'riwayat'
                        ? 'border-primary-600 text-primary-600 font-bold border-b-2'
                        : 'text-surface-500 hover:text-surface-800 font-semibold'"
                    class="px-4 py-2 text-sm transition cursor-pointer flex items-center gap-2">
                <span>Riwayat Pengembalian</span>
                <span class="px-2 py-0.5 rounded-full text-xs"
                      :class="activeTab === 'riwayat' ? 'bg-primary-100 text-primary-700 font-bold' : 'bg-surface-100 text-surface-500'"
                      x-text="historyLoansList.length"></span>
            </button>
        </div>

        {{-- Loading Skeleton --}}
        <div x-show="loading" class="bg-white rounded-3xl border border-surface-200 p-8 text-center text-surface-400">
            <span class="spinner text-primary-500 mr-2"></span> Memuat daftar pinjaman Anda...
        </div>

        {{-- Table Content --}}
        <div x-show="!loading" class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            {{-- Empty active --}}
            <template x-if="activeTab === 'aktif' && activeLoansList.length === 0">
                <x-empty-state title="Tidak Ada Pinjaman Aktif" description="Anda sedang tidak meminjam unit robot apa pun saat ini.">
                    <x-slot:action>
                        <a href="/robots" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition shadow">
                            Cari Robot Sekarang →
                        </a>
                    </x-slot:action>
                </x-empty-state>
            </template>

            {{-- Empty history --}}
            <template x-if="activeTab === 'riwayat' && historyLoansList.length === 0">
                <x-empty-state title="Belum Ada Riwayat" description="Anda belum pernah mengembalikan robot di RoPar." />
            </template>

            {{-- Table --}}
            <div class="overflow-x-auto" x-show="(activeTab === 'aktif' && activeLoansList.length > 0) || (activeTab === 'riwayat' && historyLoansList.length > 0)">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Robot & Foto</th>
                            <th class="px-6 py-4 font-semibold">Kode Unit</th>
                            <th class="px-6 py-4 font-semibold">Tgl Pinjam</th>
                            <th class="px-6 py-4 font-semibold">Jatuh Tempo</th>
                            <th class="px-6 py-4 font-semibold" x-text="activeTab === 'aktif' ? 'Sisa Waktu' : 'Tgl Kembali'"></th>
                            <th class="px-6 py-4 font-semibold">Denda</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="loan in (activeTab === 'aktif' ? activeLoansList : historyLoansList)" :key="loan.id">
                            <tr :class="activeTab === 'aktif' && calculateLateDays(loan.due_date) > 0 ? 'bg-danger-50/40' : 'hover:bg-surface-50/50'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-surface-100 border border-surface-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            <template x-if="loan.unit?.robot?.photo">
                                                <img :src="'/storage/' + loan.unit.robot.photo" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!loan.unit?.robot?.photo">
                                                <svg class="w-6 h-6 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="6" y="10" width="12" height="9" rx="2"/><circle cx="9.5" cy="14.5" r="1.5"/><circle cx="14.5" cy="14.5" r="1.5"/><path stroke-linecap="round" d="M12 3v4M8 7h8"/></svg>
                                            </template>
                                        </div>
                                        <div>
                                            <p class="font-bold text-surface-900" x-text="loan.unit?.robot?.name || '-'"></p>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                <template x-for="cat in loan.unit?.robot?.categories || []" :key="cat.id">
                                                    <span class="text-[10px] px-1.5 py-0.5 bg-accent-50 text-accent-700 rounded" x-text="cat.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-primary-600 text-xs" x-text="loan.unit?.unit_code"></td>
                                <td class="px-6 py-4 text-xs" x-text="formatDate(loan.loan_date)"></td>
                                <td class="px-6 py-4 text-xs font-semibold" x-text="formatDate(loan.due_date)"></td>
                                <td class="px-6 py-4 text-xs">
                                    <template x-if="activeTab === 'aktif'">
                                        <div>
                                            <template x-if="calculateLateDays(loan.due_date) > 0">
                                                <span class="text-xs font-bold text-danger-600">
                                                    Terlambat <span x-text="calculateLateDays(loan.due_date)"></span> hari
                                                </span>
                                            </template>
                                            <template x-if="calculateLateDays(loan.due_date) === 0">
                                                <span class="text-xs font-semibold text-success-600">
                                                    Sisa <span x-text="calculateRemainingDays(loan.due_date)"></span> hari
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="activeTab === 'riwayat'">
                                        <span x-text="formatDate(loan.return_date)"></span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold">
                                    <template x-if="activeTab === 'aktif'">
                                        <div>
                                            <template x-if="calculateLateDays(loan.due_date) > 0">
                                                <span class="text-danger-600" x-text="formatRupiah(calculateFine(loan.due_date)) + ' (berjalan)'"></span>
                                            </template>
                                            <template x-if="calculateLateDays(loan.due_date) === 0">
                                                <span class="text-surface-400">Rp0</span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="activeTab === 'riwayat'">
                                        <span :class="loan.fine_amount > 0 ? 'text-danger-600' : 'text-surface-600'"
                                              x-text="formatRupiah(loan.fine_amount || 0)"></span>
                                    </template>
                                </td>
                                <td class="px-6 py-4">
                                    {{-- Active: Dipinjam or Terlambat --}}
                                    <template x-if="activeTab === 'aktif'">
                                        <div>
                                            <template x-if="calculateLateDays(loan.due_date) > 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-danger-50 text-danger-700 border border-danger-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-danger-500 animate-pulse"></span>
                                                    <span>Terlambat</span>
                                                </span>
                                            </template>
                                            <template x-if="calculateLateDays(loan.due_date) === 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-warning-50 text-warning-700 border border-warning-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-warning-500"></span>
                                                    <span>Dipinjam</span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                    {{-- History: Dikembalikan --}}
                                    <template x-if="activeTab === 'riwayat'">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-success-50 text-success-700 border border-success-200">
                                            <svg class="w-3.5 h-3.5 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>Dikembalikan</span>
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <template x-if="activeTab === 'aktif'">
                                        <button type="button" @click="openReturnModal(loan)"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                            </svg>
                                            <span>Kembalikan</span>
                                        </button>
                                    </template>
                                    <template x-if="activeTab === 'riwayat'">
                                        <span class="text-xs text-surface-400 font-medium italic">Selesai</span>
                                    </template>
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
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-surface-900 text-base">Konfirmasi Pengembalian & Kalkulasi Denda</h3>
                        <p class="text-xs text-surface-500">Pilih tanggal pengembalian untuk melihat estimasi denda secara real-time</p>
                    </div>
                </div>

                {{-- Robot Info Card --}}
                <div class="flex items-center justify-between p-3.5 bg-surface-50 rounded-xl border border-surface-200 text-sm mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/><rect x="8" y="14" width="8" height="6" rx="2"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-surface-900 text-sm" x-text="selectedLoan?.unit?.robot?.name || '-'"></h4>
                            <span class="text-xs text-surface-500 font-mono">Unit: <strong class="text-primary-600" x-text="selectedLoan?.unit?.unit_code"></strong></span>
                        </div>
                    </div>
                </div>

                {{-- Timeline: Tanggal Pinjam → Jatuh Tempo → Pengembalian --}}
                <div class="p-4 bg-surface-50/70 rounded-2xl border border-surface-200 space-y-4 mb-4">

                    {{-- Date Info Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Tanggal Pinjam (readonly) --}}
                        <div class="p-3 bg-white rounded-xl border border-surface-200 text-center">
                            <div class="text-[10px] font-semibold text-surface-500 uppercase tracking-wider mb-1">📅 Tanggal Pinjam</div>
                            <div class="text-sm font-bold text-surface-900" x-text="formatDate(selectedLoan?.loan_date)"></div>
                        </div>

                        {{-- Jatuh Tempo (readonly) --}}
                        <div class="p-3 bg-primary-50 rounded-xl border border-primary-200 text-center">
                            <div class="text-[10px] font-semibold text-primary-600 uppercase tracking-wider mb-1">⏰ Jatuh Tempo (5 Hari)</div>
                            <div class="text-sm font-bold text-primary-700" x-text="formatDate(selectedLoan?.due_date)"></div>
                        </div>

                        {{-- Tanggal Pengembalian (editable!) --}}
                        <div class="p-3 bg-white rounded-xl border-2 border-emerald-300 text-center">
                            <div class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider mb-1">🗓️ Tanggal Kembali</div>
                            <input type="date" x-model="customReturnDate" :min="toLocalDateString(selectedLoan?.loan_date)" @change="recalcReturnFine()"
                                   class="w-full px-2 py-1 bg-emerald-50 border border-emerald-200 rounded-lg text-xs font-bold text-center text-emerald-800 focus:ring-2 focus:ring-emerald-400 focus:border-transparent outline-none">
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
                                +1 Hari Telat (Rp5.000)
                            </button>
                            <button type="button" @click="setReturnDate(addDays(selectedLoan?.due_date, 3))"
                                    :class="returnLateDays === 3 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                +3 Hari Telat (Rp15.000)
                            </button>
                            <button type="button" @click="setReturnDate(addDays(selectedLoan?.due_date, 5))"
                                    :class="returnLateDays === 5 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                +5 Hari Telat (Rp25.000)
                            </button>
                        </div>
                    </div>

                    {{-- LIVE FINE CALCULATION DISPLAY --}}
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
                                    Pengembalian tanggal <strong x-text="formatDate(customReturnDate)"></strong> masih dalam/sebelum jatuh tempo (<strong x-text="formatDate(selectedLoan?.due_date)"></strong>). <strong>Total Denda: Rp0</strong>.
                                </p>
                            </div>
                        </template>

                        <template x-if="returnLateDays > 0">
                            <div class="p-3.5 bg-rose-50 border-2 border-rose-300 rounded-xl text-rose-900 transition-all">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-1.5 font-bold text-xs text-rose-700">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>KENA DENDA KETERLAMBATAN</span>
                                    </div>
                                    <span class="px-2 py-0.5 bg-rose-200 text-rose-800 text-[10px] font-bold rounded-md animate-pulse">
                                        Terlambat <span x-text="returnLateDays"></span> Hari
                                    </span>
                                </div>
                                <div class="text-xs text-rose-800 space-y-1.5">
                                    <p>
                                        Pengembalian tanggal <strong x-text="formatDate(customReturnDate)"></strong> melebihi jatuh tempo (<strong x-text="formatDate(selectedLoan?.due_date)"></strong>) sebanyak <strong><span x-text="returnLateDays"></span> hari</strong>.
                                    </p>
                                    <div class="flex items-center justify-between pt-2 border-t border-rose-200">
                                        <span class="font-semibold">Perhitungan Denda:</span>
                                        <span class="text-sm font-extrabold text-rose-700">
                                            <span x-text="returnLateDays"></span> hari × Rp5.000 = <span x-text="formatRupiah(returnFineAmount)"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-surface-600 hover:bg-surface-100 rounded-xl text-sm font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="confirmReturn" :disabled="returning"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                        <span x-show="returning" class="spinner"></span>
                        <span x-text="returning ? 'Memproses...' : (returnLateDays > 0 ? 'Kembalikan (Denda ' + formatRupiah(returnFineAmount) + ')' : 'Ya, Kembalikan Sekarang')"></span>
                    </button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        function myLoans() {
            return {
                allLoans: [],
                activeLoansList: [],
                historyLoansList: [],
                activeTab: 'aktif',
                currentPage: 1,
                lastPage: 1,
                loading: true,
                showModal: false,
                selectedLoan: null,
                returning: false,
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
                        const res = await api.get('/backend/loans', { page: this.currentPage });
                        this.allLoans = res.data || [];
                        this.currentPage = res.current_page || 1;
                        this.lastPage = res.last_page || 1;

                        this.activeLoansList = this.allLoans.filter(l => l.status === 'dipinjam');
                        this.historyLoansList = this.allLoans.filter(l => l.status === 'dikembalikan');
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.loading = false;
                    }
                },

                openReturnModal(loan) {
                    this.selectedLoan = loan;
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
                    this.returning = true;
                    try {
                        await api.post('/my-loans/' + this.selectedLoan.id + '/return', {
                            return_date: this.customReturnDate
                        });
                        const fineMsg = this.returnFineAmount > 0
                            ? ' Denda: ' + formatRupiah(this.returnFineAmount) + ' (' + this.returnLateDays + ' hari keterlambatan).'
                            : ' Tidak ada denda (tepat waktu).';
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Robot berhasil dikembalikan!' + fineMsg, type: 'success' } }));
                        this.showModal = false;
                        this.selectedLoan = null;
                        await this.loadData(1);
                    } catch (err) {
                        if (err.message) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                        }
                    } finally {
                        this.returning = false;
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
