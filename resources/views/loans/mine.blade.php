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
                    <p class="text-primary-700">Jika terlambat, denda Rp5.000 per hari dicatat saat admin memproses pengembalian. Silakan hubungi admin untuk mengembalikan unit.</p>
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
                                        <span class="text-xs text-surface-500">Hubungi admin</span>
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
