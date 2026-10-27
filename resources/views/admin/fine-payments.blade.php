<x-layouts.app title="Verifikasi Pembayaran Denda">
    <div x-data="adminFinePayments()" x-init="init()" class="space-y-6">
        <div class="flex flex-col gap-4 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-surface-900">Verifikasi Pembayaran Denda</h2>
                <p class="mt-1 text-xs text-surface-500">Periksa bukti bayar anggota, lalu setujui atau tolak dengan alasan.</p>
            </div>
            <label class="text-xs font-semibold text-surface-500">
                Filter status
                <select x-model="status" @change="loadData(1)" class="mt-1 block rounded-xl border border-surface-200 bg-surface-50 px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="menunggu">Menunggu verifikasi</option>
                    <option value="lunas">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </label>
        </div>

        <div class="overflow-hidden rounded-3xl border border-surface-200 bg-white shadow-sm">
            <template x-if="loading">
                <div class="p-8 text-center text-sm text-surface-500">Memuat konfirmasi pembayaran...</div>
            </template>
            <template x-if="!loading && payments.length === 0">
                <div class="p-8 text-center text-sm text-surface-500">Belum ada konfirmasi pembayaran untuk filter ini.</div>
            </template>
            <div class="overflow-x-auto" x-show="!loading && payments.length > 0">
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-50 text-xs uppercase text-surface-500">
                        <tr>
                            <th class="px-5 py-3">Anggota</th>
                            <th class="px-5 py-3">Pinjaman</th>
                            <th class="px-5 py-3">Jumlah</th>
                            <th class="px-5 py-3">Bukti & Status</th>
                            <th class="px-5 py-3 text-right">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <template x-for="payment in payments" :key="payment.id">
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-surface-900" x-text="payment.loan?.member?.profile?.full_name || payment.loan?.member?.name || '-'"></p>
                                    <p class="text-xs text-surface-500" x-text="payment.loan?.member?.email"></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold" x-text="payment.loan?.unit?.robot?.name || '-'"></p>
                                    <p class="text-xs text-surface-500" x-text="(payment.loan?.unit?.unit_code || '-') + ' · ' + formatDate(payment.loan?.loan_date)"></p>
                                </td>
                                <td class="px-5 py-4 font-bold" x-text="formatCurrency(payment.amount)"></td>
                                <td class="px-5 py-4">
                                    <a :href="'/backend/admin/fine-payments/' + payment.id + '/proof'" target="_blank" class="font-semibold text-primary-600 underline">Lihat bukti</a>
                                    <div class="mt-1">
                                        <span class="rounded-full px-2 py-1 text-[10px] font-bold"
                                              :class="payment.status === 'lunas' ? 'bg-success-50 text-success-700' : (payment.status === 'ditolak' ? 'bg-danger-50 text-danger-700' : 'bg-warning-50 text-warning-700')"
                                              x-text="statusLabel(payment.status)"></span>
                                    </div>
                                    <p x-show="payment.rejection_reason" class="mt-1 max-w-xs text-xs text-danger-600" x-text="payment.rejection_reason"></p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <template x-if="payment.status === 'menunggu'">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="approve(payment)" :disabled="processingId === payment.id" class="rounded-lg bg-success-600 px-3 py-2 text-xs font-bold text-white disabled:opacity-50">Setujui</button>
                                            <button type="button" @click="reject(payment)" :disabled="processingId === payment.id" class="rounded-lg bg-danger-600 px-3 py-2 text-xs font-bold text-white disabled:opacity-50">Tolak</button>
                                        </div>
                                    </template>
                                    <span x-show="payment.status !== 'menunggu'" class="text-xs text-surface-400" x-text="payment.reviewer?.name ? 'Diproses oleh ' + payment.reviewer.name : 'Sudah diproses'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="!loading && lastPage > 1" class="flex items-center justify-between border-t border-surface-100 p-4 text-xs">
                <button type="button" @click="loadData(currentPage - 1)" :disabled="currentPage <= 1" class="rounded-lg border border-surface-200 px-3 py-2 disabled:opacity-40">Sebelumnya</button>
                <span x-text="'Halaman ' + currentPage + ' dari ' + lastPage"></span>
                <button type="button" @click="loadData(currentPage + 1)" :disabled="currentPage >= lastPage" class="rounded-lg border border-surface-200 px-3 py-2 disabled:opacity-40">Berikutnya</button>
            </div>
        </div>
    </div>

    <script>
        function adminFinePayments() {
            return {
                payments: [],
                status: 'menunggu',
                loading: true,
                processingId: null,
                currentPage: 1,
                lastPage: 1,

                async init() {
                    await this.loadData(1);
                },

                async loadData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    const params = { page, per_page: 15 };
                    if (this.status) params.status = this.status;

                    try {
                        const response = await api.get('/backend/admin/fine-payments', params);
                        this.payments = response.data || [];
                        this.currentPage = response.current_page || 1;
                        this.lastPage = response.last_page || 1;
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: error.message || 'Data pembayaran gagal dimuat.', type: 'error' } }));
                    } finally {
                        this.loading = false;
                    }
                },

                statusLabel(status) {
                    return { menunggu: 'Menunggu verifikasi', lunas: 'Denda sudah dibayar', ditolak: 'Ditolak' }[status] || status;
                },

                async approve(payment) {
                    if (!window.confirm('Setujui pembayaran denda sebesar ' + formatCurrency(payment.amount) + '?')) return;
                    await this.process(payment, 'approve', {});
                },

                async reject(payment) {
                    const reason = window.prompt('Masukkan alasan penolakan bukti pembayaran:');
                    if (reason === null) return;
                    if (!reason.trim()) {
                        window.alert('Alasan penolakan wajib diisi.');
                        return;
                    }
                    await this.process(payment, 'reject', { reason });
                },

                async process(payment, action, payload) {
                    this.processingId = payment.id;
                    try {
                        await api.patch('/backend/admin/fine-payments/' + payment.id + '/' + action, payload);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: action === 'approve' ? 'Pembayaran disetujui.' : 'Pembayaran ditolak.', type: 'success' } }));
                        await this.loadData(this.currentPage);
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: error.message || 'Konfirmasi gagal diproses.', type: 'error' } }));
                    } finally {
                        this.processingId = null;
                    }
                }
            }
        }
    </script>
</x-layouts.app>
