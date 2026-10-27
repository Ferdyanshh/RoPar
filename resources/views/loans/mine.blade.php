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
                    <p class="text-primary-700">Tekan Kembalikan Robot saat siap. Jika memiliki denda, bayar di luar aplikasi lalu kirim bukti pada riwayat pinjaman untuk diverifikasi admin.</p>
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
                                        <span x-text="loan.status === 'dibatalkan' ? 'Dibatalkan' : formatDate(loan.return_date)"></span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold">
                                    <template x-if="activeTab === 'aktif'">
                                        <div>
                                            <template x-if="isScheduled(loan)">
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-info-200 bg-info-50 px-3 py-1 text-xs font-bold text-info-700">Dijadwalkan</span>
                                            </template>
                                            <template x-if="!isScheduled(loan) && calculateLateDays(loan.due_date) > 0">
                                                <span class="text-danger-600" x-text="formatCurrency(calculateFine(loan.due_date)) + ' (berjalan)'"></span>
                                            </template>
                                            <template x-if="!isScheduled(loan) && calculateLateDays(loan.due_date) === 0">
                                                <span class="text-surface-400" x-text="formatCurrency(0)"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="activeTab === 'riwayat'">
                                        <span :class="loan.fine_amount > 0 ? 'text-danger-600' : 'text-surface-600'"
                                              x-text="formatCurrency(loan.fine_amount || 0)"></span>
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
                                        <span x-show="loan.status === 'dibatalkan'" class="inline-flex items-center gap-1 rounded-lg border border-surface-200 bg-surface-100 px-2.5 py-1 text-xs font-bold text-surface-600">Reservasi dibatalkan</span>
                                        <span x-show="loan.status === 'dikembalikan'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-success-50 text-success-700 border border-success-200">
                                            <svg class="w-3.5 h-3.5 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>Dikembalikan</span>
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <template x-if="activeTab === 'aktif'">
                                        <button type="button" x-show="!isScheduled(loan)"
                                                @click="returnLoan(loan.id)"
                                                :disabled="returningLoanId === loan.id"
                                                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-primary-700 disabled:cursor-wait disabled:opacity-60">
                                            <span x-text="returningLoanId === loan.id ? 'Memproses...' : 'Kembalikan Robot'"></span>
                                        </button>
                                        <button type="button" x-show="isScheduled(loan)"
                                                @click="cancelReservation(loan.id)"
                                                :disabled="cancellingLoanId === loan.id"
                                                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-danger-200 bg-danger-50 px-3 py-2 text-xs font-bold text-danger-700 disabled:opacity-60">
                                            <span x-text="cancellingLoanId === loan.id ? 'Memproses...' : 'Batalkan Reservasi'"></span>
                                        </button>
                                    </template>
                                    <template x-if="activeTab === 'riwayat'">
                                        <div x-show="loan.status === 'dikembalikan'" class="flex flex-col items-center gap-2">
                                            <template x-if="Number(loan.fine_amount) > 0 && latestFinePayment(loan)?.status === 'lunas'">
                                                <div class="flex w-52 items-center justify-center gap-2 rounded-xl border border-success-200 bg-success-50 px-3 py-3 text-xs font-bold text-success-700">
                                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Denda sudah dibayar</span>
                                                </div>
                                            </template>
                                            <template x-if="Number(loan.fine_amount) > 0 && latestFinePayment(loan)?.status === 'menunggu'">
                                                <div class="flex w-52 flex-col items-center gap-1 rounded-xl border border-warning-200 bg-warning-50 px-3 py-3 text-center">
                                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-warning-700">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                                        Menunggu verifikasi
                                                    </span>
                                                    <span class="text-[10px] text-warning-700">Bukti sudah diterima admin</span>
                                                </div>
                                            </template>
                                            <template x-if="Number(loan.fine_amount) > 0 && latestFinePayment(loan)?.status === 'ditolak'">
                                                <div class="mb-1 w-52 rounded-lg border border-danger-200 bg-danger-50 px-2.5 py-2 text-left text-[10px] text-danger-700">
                                                    <p class="font-bold">Bukti perlu diperbaiki</p>
                                                    <p class="mt-0.5 break-words" x-text="latestFinePayment(loan).rejection_reason || 'Silakan kirim ulang bukti pembayaran.'"></p>
                                                </div>
                                            </template>
                                            <template x-if="Number(loan.fine_amount) > 0 && canSubmitFinePayment(loan)">
                                                <div class="w-52 space-y-2">
                                                    <label :for="'fine-proof-' + loan.id" class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-primary-300 bg-primary-50/60 px-3 py-2.5 text-left transition hover:border-primary-500 hover:bg-primary-50">
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-primary-600 shadow-sm">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5l5 5M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4"/></svg>
                                                        </span>
                                                        <span class="min-w-0 flex-1">
                                                            <span class="block text-[11px] font-bold text-primary-800" x-text="paymentFiles[loan.id]?.name || 'Pilih bukti pembayaran'"></span>
                                                            <span class="mt-0.5 block text-[10px] text-surface-500" x-text="paymentFiles[loan.id] ? (Math.max(1, Math.round(paymentFiles[loan.id].size / 1024)) + ' KB · siap dikirim') : 'JPG, PNG, atau PDF · maks. 5 MB'"></span>
                                                        </span>
                                                    </label>
                                                    <input type="file" :id="'fine-proof-' + loan.id" accept="image/jpeg,image/png,application/pdf" @change="setPaymentProof(loan.id, $event)" class="sr-only">
                                                    <button type="button" @click="submitFinePayment(loan.id)" :disabled="submittingLoanId === loan.id || !paymentFiles[loan.id]" :class="paymentFiles[loan.id] && submittingLoanId !== loan.id ? 'bg-primary-600 hover:bg-primary-700 shadow-sm' : 'cursor-not-allowed bg-surface-200 text-surface-500'" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-bold text-white transition disabled:cursor-not-allowed">
                                                        <svg x-show="submittingLoanId !== loan.id" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        <span x-text="submittingLoanId === loan.id ? 'Mengirim bukti...' : (paymentFiles[loan.id] ? 'Kirim Bukti Bayar' : 'Pilih file terlebih dahulu')"></span>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="Number(loan.fine_amount) === 0">
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-surface-50 px-3 py-2 text-xs font-medium italic text-surface-400">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Tidak ada denda
                                                </span>
                                            </template>
                                        </div>
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
                returningLoanId: null,
                cancellingLoanId: null,
                submittingLoanId: null,
                paymentFiles: {},
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
                        this.historyLoansList = this.allLoans.filter(l => ['dikembalikan', 'dibatalkan'].includes(l.status));
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

                async returnLoan(loanId) {
                    if (!window.confirm('Kembalikan robot ini sekarang? Tanggal pengembalian dan denda dihitung otomatis oleh sistem.')) {
                        return;
                    }

                    this.returningLoanId = loanId;

                    try {
                        await api.post('/backend/loans/' + loanId + '/return', {});
                        await this.loadData(this.currentPage);
                    } catch (error) {
                        window.alert(error.message || 'Pengembalian robot gagal diproses. Silakan coba lagi.');
                    } finally {
                        this.returningLoanId = null;
                    }
                },

                async cancelReservation(loanId) {
                    if (!window.confirm('Batalkan reservasi unit ini? Unit akan kembali tersedia untuk anggota lain.')) return;

                    this.cancellingLoanId = loanId;
                    try {
                        await api.post('/backend/loans/' + loanId + '/cancel', {});
                        await this.loadData(this.currentPage);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Reservasi berhasil dibatalkan.', type: 'success' } }));
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: error.message || 'Reservasi gagal dibatalkan.', type: 'error' } }));
                    } finally {
                        this.cancellingLoanId = null;
                    }
                },

                isScheduled(loan) {
                    return loan.status === 'dipinjam' && toLocalDateString(loan.loan_date) > todayDate();
                },

                latestFinePayment(loan) {
                    return loan.fine_payments?.[0] || null;
                },

                canSubmitFinePayment(loan) {
                    const status = this.latestFinePayment(loan)?.status;
                    return !status || status === 'ditolak';
                },

                setPaymentProof(loanId, event) {
                    this.paymentFiles[loanId] = event.target.files?.[0] || null;
                },

                async submitFinePayment(loanId) {
                    const proof = this.paymentFiles[loanId];
                    if (!proof) return;

                    this.submittingLoanId = loanId;
                    const formData = new FormData();
                    formData.append('proof', proof);

                    try {
                        await api.upload('/backend/loans/' + loanId + '/fine-payment', formData);
                        this.paymentFiles[loanId] = null;
                        await this.loadData(this.currentPage);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Bukti pembayaran terkirim dan menunggu verifikasi admin.', type: 'success' } }));
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: error.message || 'Bukti pembayaran gagal dikirim.', type: 'error' } }));
                    } finally {
                        this.submittingLoanId = null;
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
