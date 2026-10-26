<x-layouts.app title="Riwayat Peminjaman">
    <div x-data="adminLoansHistory()" x-init="init()" class="space-y-6">
        {{-- Header & Filters --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-surface-900">Laporan & Riwayat Peminjaman</h2>
                    <p class="text-xs text-surface-400 mt-1">Pantau seluruh catatan kencan robot yang pernah berlangsung</p>
                </div>
                <button @click="printReport()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-surface-800 hover:bg-surface-900 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>

            {{-- Filter Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-3 border-t border-surface-100">
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Dari Tanggal Pinjam</label>
                    <input type="date" x-model="filter.from" @change="validateDates() && loadData(1)"
                           class="w-full px-3 py-1.5 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Sampai Tanggal Pinjam</label>
                    <input type="date" x-model="filter.to" @change="validateDates() && loadData(1)"
                           class="w-full px-3 py-1.5 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Status Pinjaman</label>
                    <select x-model="filter.status" @change="loadData(1)"
                            class="w-full px-3 py-1.5 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                        <option value="">Semua Status</option>
                        <option value="dipinjam">Dipinjam (Aktif)</option>
                        <option value="dikembalikan">Dikembalikan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Anggota</label>
                    <select x-model="filter.member_id" @change="loadData(1)"
                            class="w-full px-3 py-1.5 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                        <option value="">Semua Anggota</option>
                        <template x-for="m in allMembers" :key="m.id">
                            <option :value="m.id" x-text="m.name + ' (' + (m.profile?.full_name || m.email) + ')'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Cari Kata Kunci</label>
                    <input type="text" x-model="filter.q" @input.debounce.300ms="loadData(1)" placeholder="Nama, kode unit..."
                           class="w-full px-3 py-1.5 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:ring-2 focus:ring-primary-500/40">
                </div>
            </div>
            <p x-show="dateError" class="text-xs text-danger-600 font-semibold" x-text="dateError"></p>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-3xl border border-surface-200 shadow-sm overflow-hidden">
            <template x-if="loading">
                <div class="p-8 text-center text-surface-400">
                    <span class="spinner text-primary-500 mr-2"></span> Memuat riwayat peminjaman...
                </div>
            </template>

            <template x-if="!loading && loans.length === 0">
                <x-empty-state title="Tidak Ada Data Riwayat" description="Tidak ada transaksi yang cocok dengan filter yang Anda tentukan." />
            </template>

            <div class="overflow-x-auto" x-show="!loading && loans.length > 0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Peminjam</th>
                            <th class="px-5 py-3.5 font-semibold">Robot & Unit</th>
                            <th class="px-5 py-3.5 font-semibold">Tgl Pinjam</th>
                            <th class="px-5 py-3.5 font-semibold">Jatuh Tempo</th>
                            <th class="px-5 py-3.5 font-semibold">Tgl Kembali</th>
                            <th class="px-5 py-3.5 font-semibold">Terlambat</th>
                            <th class="px-5 py-3.5 font-semibold">Denda</th>
                            <th class="px-5 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold">Diproses Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 text-surface-700">
                        <template x-for="loan in loans" :key="loan.id">
                            <tr class="hover:bg-surface-50/50">
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-surface-900" x-text="loan.member?.profile?.full_name || loan.member?.name"></p>
                                    <p class="text-xs text-surface-400" x-text="loan.member?.email"></p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-surface-800" x-text="loan.unit?.robot?.name || '-'"></p>
                                    <span class="font-mono text-xs font-bold text-primary-600" x-text="loan.unit?.unit_code"></span>
                                </td>
                                <td class="px-5 py-3.5 text-xs" x-text="formatDate(loan.loan_date)"></td>
                                <td class="px-5 py-3.5 text-xs font-semibold" x-text="formatDate(loan.due_date)"></td>
                                <td class="px-5 py-3.5 text-xs" x-text="formatDate(loan.return_date)"></td>
                                <td class="px-5 py-3.5 text-xs">
                                    <span :class="loan.late_days > 0 ? 'text-danger-600 font-bold' : 'text-surface-500'"
                                          x-text="loan.late_days ? loan.late_days + ' hari' : '-'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-xs font-bold">
                                    <span :class="loan.fine_amount > 0 ? 'text-danger-600' : 'text-surface-500'"
                                          x-text="formatRupiah(loan.fine_amount || 0)"></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold capitalize"
                                          :class="{
                                              'bg-warning-50 text-warning-600 ring-1 ring-warning-500/20': loan.status === 'dipinjam',
                                              'bg-success-50 text-success-700 ring-1 ring-success-500/20': loan.status === 'dikembalikan'
                                          }"
                                          x-text="loan.status"></span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-surface-500" x-text="loan.return_processor?.name || '-'"></td>
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
        function adminLoansHistory() {
            return {
                loans: [],
                allMembers: [],
                filter: {
                    from: '',
                    to: '',
                    status: '',
                    member_id: '',
                    q: ''
                },
                dateError: '',
                currentPage: 1,
                lastPage: 1,
                loading: true,

                async init() {
                    this.fetchAllMembers();
                    this.loadData(1);
                },

                async fetchAllMembers() {
                    try {
                        const res = await api.get('/backend/admin/members', { per_page: 100 });
                        this.allMembers = res.data || [];
                    } catch (e) {
                        console.error(e);
                    }
                },

                validateDates() {
                    this.dateError = '';
                    if (this.filter.from && this.filter.to && this.filter.from > this.filter.to) {
                        this.dateError = 'Tanggal awal tidak boleh setelah tanggal akhir.';
                        return false;
                    }
                    return true;
                },

                async loadData(page = 1) {
                    if (!this.validateDates()) return;
                    this.loading = true;
                    this.currentPage = page;

                    const params = {
                        page: this.currentPage,
                        per_page: 15
                    };
                    if (this.filter.from) params.from = this.filter.from;
                    if (this.filter.to) params.to = this.filter.to;
                    if (this.filter.status) params.status = this.filter.status;
                    if (this.filter.member_id) params.member_id = this.filter.member_id;
                    if (this.filter.q) params.q = this.filter.q;

                    try {
                        const res = await api.get('/backend/admin/reports/loans', params);
                        this.loans = res.data || [];
                        this.currentPage = res.current_page || 1;
                        this.lastPage = res.last_page || 1;
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.loading = false;
                    }
                },

                printReport() {
                    const params = new URLSearchParams();
                    if (this.filter.from) params.append('from', this.filter.from);
                    if (this.filter.to) params.append('to', this.filter.to);
                    if (this.filter.status) params.append('status', this.filter.status);
                    if (this.filter.member_id) params.append('member_id', this.filter.member_id);
                    if (this.filter.q) params.append('q', this.filter.q);

                    window.open('/admin/loans/history/print?' + params.toString(), '_blank');
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
