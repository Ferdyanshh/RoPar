<x-layouts.app title="Dashboard">
    @if(auth()->user()->role === \App\Enums\UserRole::Admin)
        {{-- ================= ADMIN DASHBOARD ================= --}}
        <div x-data="adminDashboard()" x-init="init()" class="space-y-6">
            {{-- Welcome greeting --}}
            <div class="bg-gradient-to-r from-primary-600 via-accent-600 to-accent-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg">
                <div class="max-w-2xl">
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold mb-3">Administrator Portal</span>
                    <h2 class="text-2xl sm:text-3xl font-bold">Halo, {{ auth()->user()->name }}! 👋</h2>
                    <p class="text-primary-100 text-sm mt-2">Selamat datang kembali di sistem RoPar. Pantau transaksi peminjaman robot pacar dan inventaris hari ini.</p>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <x-stat-card title="Total Model Robot" value="stats.totalRobots" icon="robot" color="primary" />
                <x-stat-card title="Total Unit Robot" value="stats.totalUnits" icon="box" color="info" />
                <x-stat-card title="Total Anggota" value="stats.totalMembers" icon="users" color="accent" />
                <x-stat-card title="Pinjaman Aktif" value="stats.activeLoans" icon="clock" color="warning" />
                <x-stat-card title="Unit Terlambat" value="stats.overdueLoans" icon="alert" color="danger" />
            </div>

            {{-- Loans Due Soon Table --}}
            <div class="bg-white rounded-2xl border border-surface-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-surface-800">Pinjaman Jatuh Tempo Terdekat</h3>
                        <p class="text-xs text-surface-400 mt-0.5">Daftar unit yang sedang dipinjam diurutkan dari jatuh tempo paling awal</p>
                    </div>
                    <a href="/admin/loans/active" class="text-xs font-semibold text-primary-600 hover:text-primary-700">Lihat Semua →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-50 text-surface-500 text-xs uppercase border-b border-surface-100">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold">Peminjam</th>
                                <th class="px-6 py-3.5 font-semibold">Robot & Unit</th>
                                <th class="px-6 py-3.5 font-semibold">Tgl Pinjam</th>
                                <th class="px-6 py-3.5 font-semibold">Jatuh Tempo</th>
                                <th class="px-6 py-3.5 font-semibold">Status / Denda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 text-surface-700">
                            <template x-if="loading">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-surface-400">
                                        <span class="spinner text-primary-500 mr-2"></span> Memuat data...
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!loading && urgentLoans.length === 0">
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-surface-400">
                                        Tidak ada pinjaman aktif saat ini.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="loan in urgentLoans" :key="loan.id">
                                <tr :class="calculateLateDays(loan.due_date) > 0 ? 'bg-danger-50/40' : 'hover:bg-surface-50/60'">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-surface-800" x-text="loan.member?.profile?.full_name || loan.member?.name"></p>
                                        <p class="text-xs text-surface-400" x-text="loan.member?.email"></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-surface-800" x-text="loan.unit?.robot?.name || '-'"></p>
                                        <span class="inline-block font-mono text-xs text-primary-600 font-semibold" x-text="loan.unit?.unit_code"></span>
                                    </td>
                                    <td class="px-6 py-4 text-xs" x-text="formatDate(loan.loan_date)"></td>
                                    <td class="px-6 py-4 text-xs font-semibold" x-text="formatDate(loan.due_date)"></td>
                                    <td class="px-6 py-4">
                                        <template x-if="calculateLateDays(loan.due_date) > 0">
                                            <div class="flex items-center gap-2">
                                                <x-badge-status status="terlambat" />
                                                <span class="text-xs font-bold text-danger-600" x-text="'+ ' + formatRupiah(calculateFine(loan.due_date))"></span>
                                            </div>
                                        </template>
                                        <template x-if="calculateLateDays(loan.due_date) === 0">
                                            <x-badge-status status="dipinjam" />
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            function adminDashboard() {
                return {
                    stats: {
                        totalRobots: 0,
                        totalUnits: 0,
                        totalMembers: 0,
                        activeLoans: 0,
                        overdueLoans: 0,
                    },
                    urgentLoans: [],
                    loading: true,

                    async init() {
                        window.addEventListener('currency-rate-updated', () => this.updateFineSubtitle());

                        try {
                            const [resRobots, resUnits, resMembers, resLoans] = await Promise.all([
                                api.get('/backend/admin/robots', { per_page: 1 }),
                                api.get('/backend/admin/robot-units', { per_page: 1 }),
                                api.get('/backend/admin/members', { per_page: 1 }),
                                api.get('/backend/admin/loans', { per_page: 100 })
                            ]);

                            this.stats.totalRobots = resRobots.total || 0;
                            this.stats.totalUnits = resUnits.total || 0;
                            this.stats.totalMembers = resMembers.total || 0;
                            this.stats.activeLoans = resLoans.total || 0;

                            const loansList = resLoans.data || [];
                            let overdueCount = 0;
                            loansList.forEach(l => {
                                if (calculateLateDays(l.due_date) > 0) overdueCount++;
                            });
                            this.stats.overdueLoans = overdueCount;

                            this.urgentLoans = loansList.slice(0, 5);
                        } catch (err) {
                            console.error(err);
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>

    @else
        {{-- ================= MEMBER DASHBOARD ================= --}}
        @php($loanSettings = \App\Models\AppSetting::current())
        <div x-data="memberDashboard({{ $loanSettings->borrowed_robot_limit }}, {{ $loanSettings->reservation_robot_limit }})" x-init="init()" class="space-y-6">
            {{-- Welcome greeting --}}
            <div class="bg-gradient-to-r from-primary-500 via-primary-600 to-accent-600 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="max-w-xl relative z-10">
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold mb-3">Anggota RoPar</span>
                    <h2 class="text-2xl sm:text-3xl font-bold">Halo, {{ auth()->user()->name }}! 💖</h2>
                    <p class="text-primary-100 text-sm mt-2">Mau kencan dengan siapa hari ini? Temukan robot pacar idamanmu dengan teknologi interaktif terbaik.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="/robots" class="px-5 py-2.5 bg-white text-primary-600 rounded-xl font-bold text-sm shadow hover:bg-primary-50 transition-colors">
                            Cari Robot Sekarang →
                        </a>
                        <a href="/my-loans" class="px-5 py-2.5 bg-primary-700/50 hover:bg-primary-700 text-white rounded-xl font-semibold text-sm transition-colors">
                            Pinjaman Saya
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stat Cards for Member --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card title="Kuota Robot" value="borrowedCount + '/' + borrowedLimit + ' · ' + reservationCount + '/' + reservationLimit" icon="clock" color="primary" subtitle="'Dipinjam · reservasi'" />
                <x-stat-card title="Jatuh Tempo Terdekat" value="nearestDueText" icon="alert" color="warning" subtitle="'Durasi pinjam standar 5 hari'" />
                <x-stat-card title="Total Denda" value="formatRupiah(totalFine)" icon="money" color="danger" subtitle="fineSubtitle" />
            </div>

            {{-- Denda Alert Banner --}}
            <template x-if="!loading && totalFine > 0">
                <div class="bg-danger-50 border border-danger-200 rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-danger-600 text-white flex items-center justify-center flex-shrink-0 shadow">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-base text-danger-900">Total Denda Belum Lunas: <span class="text-danger-700" x-text="formatRupiah(totalFine)"></span></h4>
                                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-danger-200 text-danger-900 tracking-wider">Perlu Dibayar</span>
                            </div>
                            <p class="text-xs text-danger-700 mt-0.5" x-text="fineSubtitle + '. Pembayaran menunggu persetujuan admin setelah bukti dikirim.'"></p>
                        </div>
                    </div>
                    <a href="/my-loans" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-danger-600 hover:bg-danger-700 text-white text-xs font-bold rounded-xl transition shadow flex-shrink-0">
                        <span>Lihat Riwayat di Pinjaman Saya</span>
                        <span>→</span>
                    </a>
                </div>
            </template>

            {{-- Loans List with Tabs --}}
            <div class="bg-white rounded-3xl border border-surface-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 pb-4 border-b border-surface-100">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="tab = 'active'"
                                :class="tab === 'active'
                                    ? 'text-primary-600 font-bold border-b-2 border-primary-600 pb-2'
                                    : 'text-surface-500 hover:text-surface-800 pb-2 font-semibold'"
                                class="text-sm transition cursor-pointer flex items-center gap-2">
                            <span>Pinjaman yang Sedang Berjalan</span>
                            <span class="px-2 py-0.5 text-xs rounded-full font-bold"
                                  :class="tab === 'active' ? 'bg-primary-100 text-primary-700' : 'bg-surface-100 text-surface-500'"
                                  x-text="activeLoans.length"></span>
                        </button>
                        <button type="button" @click="tab = 'history'"
                                :class="tab === 'history'
                                    ? 'text-primary-600 font-bold border-b-2 border-primary-600 pb-2'
                                    : 'text-surface-500 hover:text-surface-800 pb-2 font-semibold'"
                                class="text-sm transition cursor-pointer flex items-center gap-2">
                            <span>Riwayat Pengembalian</span>
                            <span class="px-2 py-0.5 text-xs rounded-full font-bold"
                                  :class="tab === 'history' ? 'bg-primary-100 text-primary-700' : 'bg-surface-100 text-surface-500'"
                                  x-text="historyLoans.length"></span>
                        </button>
                    </div>
                    <a href="/my-loans" class="text-xs font-semibold text-primary-600 hover:text-primary-700">Lihat Semua di Pinjaman Saya →</a>
                </div>

                <template x-if="loading">
                    <div class="py-8 text-center text-surface-400">
                        <span class="spinner text-primary-500 mr-2"></span> Memuat pinjaman Anda...
                    </div>
                </template>

                {{-- Active Loans Tab --}}
                <div x-show="!loading && tab === 'active'">
                    <template x-if="activeLoans.length === 0">
                        <x-empty-state title="Belum Ada Pinjaman Aktif" description="Anda sedang tidak meminjam robot. Jelajahi katalog sekarang untuk mencari robot pacar impianmu!">
                            <x-slot:action>
                                <a href="/robots" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl transition">
                                    Buka Katalog Robot
                                </a>
                            </x-slot:action>
                        </x-empty-state>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="activeLoans.length > 0">
                        <template x-for="loan in activeLoans" :key="loan.id">
                            <div class="border border-surface-200 rounded-2xl p-5 hover:border-primary-300 transition relative bg-surface-50/50">
                                <div class="flex items-start gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-white border border-surface-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                        <template x-if="loan.unit?.robot?.photo">
                                            <img :src="'/storage/' + loan.unit.robot.photo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!loan.unit?.robot?.photo">
                                            <svg class="w-8 h-8 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <rect x="6" y="10" width="12" height="9" rx="2"/><circle cx="9.5" cy="14.5" r="1.5"/><circle cx="14.5" cy="14.5" r="1.5"/><path stroke-linecap="round" d="M12 3v4M8 7h8"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="font-bold text-surface-800 truncate" x-text="loan.unit?.robot?.name"></h4>
                                            <span class="font-mono text-xs font-semibold px-2 py-0.5 bg-primary-100 text-primary-700 rounded-md" x-text="loan.unit?.unit_code"></span>
                                        </div>
                                        <p class="text-xs text-surface-500 mt-1">
                                            Pinjam: <span class="font-medium text-surface-700" x-text="formatDate(loan.loan_date)"></span>
                                        </p>
                                        <p class="text-xs text-surface-500">
                                            Jatuh Tempo: <span class="font-semibold text-surface-800" x-text="formatDate(loan.due_date)"></span>
                                        </p>

                                        <div class="mt-3 pt-3 border-t border-surface-200/60 flex items-center justify-between">
                                            <template x-if="isScheduled(loan)">
                                                <span class="rounded-lg bg-info-50 px-2.5 py-1 text-xs font-semibold text-info-700">Mulai pinjam <span x-text="formatDate(loan.loan_date)"></span></span>
                                            </template>
                                            <template x-if="!isScheduled(loan) && calculateLateDays(loan.due_date) > 0">
                                                <div>
                                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-danger-100 text-danger-700">
                                                        Terlambat <span x-text="calculateLateDays(loan.due_date)"></span> hari
                                                    </span>
                                                    <p class="text-xs text-danger-600 font-bold mt-0.5" x-text="'Denda: ' + formatRupiah(calculateFine(loan.due_date))"></p>
                                                </div>
                                            </template>
                                            <template x-if="!isScheduled(loan) && calculateLateDays(loan.due_date) === 0">
                                                <span class="text-xs font-semibold text-success-600 bg-success-50 px-2.5 py-1 rounded-lg">
                                                    Sisa <span x-text="calculateRemainingDays(loan.due_date)"></span> hari lagi
                                                </span>
                                            </template>
                                            <button type="button" x-show="!isScheduled(loan)"
                                                    @click="returnLoan(loan.id)"
                                                    :disabled="returningLoanId === loan.id"
                                                    class="text-xs font-bold text-primary-600 transition hover:text-primary-700 disabled:cursor-wait disabled:opacity-60"
                                                    x-text="returningLoanId === loan.id ? 'Memproses...' : 'Kembalikan Unit →'"></button>
                                            <button type="button" x-show="isScheduled(loan)"
                                                    @click="cancelReservation(loan.id)"
                                                    :disabled="returningLoanId === loan.id"
                                                    class="text-xs font-bold text-danger-600 transition hover:text-danger-700 disabled:cursor-wait disabled:opacity-60"
                                                    x-text="returningLoanId === loan.id ? 'Memproses...' : 'Batalkan Reservasi'"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- History Loans Tab --}}
                <div x-show="!loading && tab === 'history'">
                    <template x-if="historyLoans.length === 0">
                        <x-empty-state title="Belum Ada Riwayat" description="Anda belum memiliki riwayat pengembalian robot." />
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="historyLoans.length > 0">
                        <template x-for="loan in historyLoans" :key="loan.id">
                            <div class="border rounded-2xl p-5 hover:border-surface-300 transition relative"
                                 :class="loan.fine_amount > 0 && latestFinePaymentStatus(loan) !== 'lunas' ? 'border-danger-200 bg-danger-50/20' : 'border-surface-200 bg-white'">
                                <div class="flex items-start gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-surface-100 border border-surface-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                        <template x-if="loan.unit?.robot?.photo">
                                            <img :src="'/storage/' + loan.unit.robot.photo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!loan.unit?.robot?.photo">
                                            <svg class="w-8 h-8 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <rect x="6" y="10" width="12" height="9" rx="2"/><circle cx="9.5" cy="14.5" r="1.5"/><circle cx="14.5" cy="14.5" r="1.5"/><path stroke-linecap="round" d="M12 3v4M8 7h8"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="font-bold text-surface-800 truncate" x-text="loan.unit?.robot?.name || '-'"></h4>
                                            <span class="font-mono text-xs font-semibold px-2 py-0.5 bg-surface-100 text-surface-700 rounded-md" x-text="loan.unit?.unit_code"></span>
                                        </div>
                                        <p class="text-xs text-surface-500 mt-1">
                                            Pinjam: <span class="font-medium text-surface-700" x-text="formatDate(loan.loan_date)"></span> • Kembali: <span class="font-medium text-surface-700" x-text="formatDate(loan.return_date)"></span>
                                        </p>
                                        <p class="text-xs text-surface-500">
                                            Jatuh Tempo: <span class="text-surface-700" x-text="formatDate(loan.due_date)"></span>
                                        </p>

                                        <div class="mt-3 pt-3 border-t border-surface-200/60 flex items-center justify-between">
                                            <div>
                                                <template x-if="loan.fine_amount > 0 && latestFinePaymentStatus(loan) !== 'lunas'">
                                                    <div>
                                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-danger-100 text-danger-700">
                                                            Terlambat <span x-text="loan.late_days || 0"></span> hari
                                                        </span>
                                                        <p class="text-xs text-danger-600 font-bold mt-0.5" x-text="'Denda: ' + formatRupiah(loan.fine_amount)"></p>
                                                    </div>
                                                </template>
                                                <template x-if="!loan.fine_amount || loan.fine_amount === 0">
                                                    <span class="text-xs font-semibold text-success-600 bg-success-50 px-2.5 py-1 rounded-lg">
                                                        Tepat Waktu (Denda <span x-text="formatRupiah(0)"></span>)
                                                    </span>
                                                </template>
                                            </div>
                                            <template x-if="loan.fine_amount > 0 && latestFinePaymentStatus(loan) === 'lunas'">
                                                <span class="rounded-lg border border-success-200 bg-success-50 px-2.5 py-1 text-xs font-bold text-success-700">Denda sudah dibayar</span>
                                            </template>
                                            <template x-if="loan.fine_amount > 0 && latestFinePaymentStatus(loan) === 'menunggu'">
                                                <span class="rounded-lg border border-warning-200 bg-warning-50 px-2.5 py-1 text-xs font-bold text-warning-700">Menunggu verifikasi</span>
                                            </template>
                                            <template x-if="loan.fine_amount > 0 && latestFinePaymentStatus(loan) === 'ditolak'">
                                                <span class="max-w-44 text-right text-xs font-semibold text-danger-600" x-text="'Ditolak: ' + loan.fine_payments[0].rejection_reason"></span>
                                            </template>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-success-50 text-success-700 border border-success-200">
                                                <svg class="w-3.5 h-3.5 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span>Dikembalikan</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function memberDashboard(borrowedLimit, reservationLimit) {
                return {
                    tab: 'active',
                    activeLoans: [],
                    historyLoans: [],
                    borrowedCount: 0,
                    reservationCount: 0,
                    borrowedLimit: borrowedLimit,
                    reservationLimit: reservationLimit,
                    nearestDueText: '-',
                    totalFine: 0,
                    activeFine: 0,
                    historyFine: 0,
                    finedHistoryCount: 0,
                    returningLoanId: null,
                    fineSubtitle: formatRupiah(5000) + ' / hari keterlambatan',
                    loading: true,

                    async init() {
                        try {
                            const res = await api.get('/backend/loans', { page: 1 });
                            let allLoans = [...(res.data || [])];
                            if (res.last_page && res.last_page > 1) {
                                const promises = [];
                                for (let p = 2; p <= res.last_page; p++) {
                                    promises.push(api.get('/backend/loans', { page: p }));
                                }
                                const extra = await Promise.all(promises);
                                extra.forEach(ep => {
                                    if (ep.data) allLoans.push(...ep.data);
                                });
                            }

                            // Filter active (status = 'dipinjam') & history (status = 'dikembalikan')
                            this.activeLoans = allLoans.filter(item => item.status === 'dipinjam');
                            this.historyLoans = allLoans.filter(item => item.status === 'dikembalikan');
                            this.borrowedCount = this.activeLoans.filter(loan => !this.isScheduled(loan)).length;
                            this.reservationCount = this.activeLoans.filter(loan => this.isScheduled(loan)).length;

                            let sumActiveFine = 0;
                            let sumHistoryFine = 0;
                            let earliestDue = null;
                            let finedCount = 0;

                            this.activeLoans.forEach(loan => {
                                const fine = calculateFine(loan.due_date);
                                sumActiveFine += fine;

                                if (loan.due_date) {
                                    const d = new Date(loan.due_date.substring(0, 10));
                                    if (!earliestDue || d < earliestDue) {
                                        earliestDue = d;
                                    }
                                }
                            });

                            this.historyLoans.forEach(loan => {
                                const hFine = Number(loan.fine_amount || 0);
                                if (hFine > 0 && this.latestFinePaymentStatus(loan) !== 'lunas') {
                                    sumHistoryFine += hFine;
                                    finedCount++;
                                }
                            });

                            this.activeFine = sumActiveFine;
                            this.historyFine = sumHistoryFine;
                            this.finedHistoryCount = finedCount;
                            this.totalFine = sumActiveFine + sumHistoryFine;

                            this.updateFineSubtitle();

                            if (earliestDue) {
                                this.nearestDueText = formatDate(earliestDue.toISOString());
                            } else {
                                this.nearestDueText = 'Tidak ada';
                            }
                        } catch (err) {
                            console.error(err);
                        } finally {
                            this.loading = false;
                        }
                    },

                    updateFineSubtitle() {
                        if (this.totalFine > 0) {
                            if (this.activeFine > 0 && this.historyFine > 0) {
                                this.fineSubtitle = 'Riwayat: ' + formatRupiah(this.historyFine) + ' • Berjalan: ' + formatRupiah(this.activeFine);
                            } else if (this.historyFine > 0) {
                                this.fineSubtitle = 'Denda riwayat pengembalian (' + this.finedHistoryCount + ' unit terlambat)';
                            } else {
                                this.fineSubtitle = 'Estimasi denda unit belum dikembalikan';
                            }
                        } else {
                            this.fineSubtitle = formatRupiah(5000) + ' / hari keterlambatan';
                        }
                    },

                    async returnLoan(loanId) {
                        if (!window.confirm('Kembalikan robot ini sekarang? Tanggal pengembalian dan denda dihitung otomatis oleh sistem.')) {
                            return;
                        }

                        this.returningLoanId = loanId;

                        try {
                            await api.post('/backend/loans/' + loanId + '/return', {});
                            window.location.reload();
                        } catch (error) {
                            window.alert(error.message || 'Pengembalian robot gagal diproses. Silakan coba lagi.');
                            this.returningLoanId = null;
                        }
                    },

                    async cancelReservation(loanId) {
                        if (!window.confirm('Batalkan reservasi unit ini? Unit akan kembali tersedia untuk anggota lain.')) {
                            return;
                        }

                        this.returningLoanId = loanId;
                        try {
                            await api.post('/backend/loans/' + loanId + '/cancel', {});
                            window.location.reload();
                        } catch (error) {
                            window.alert(error.message || 'Reservasi gagal dibatalkan.');
                            this.returningLoanId = null;
                        }
                    },

                    latestFinePaymentStatus(loan) {
                        return loan.fine_payments?.[0]?.status || null;
                    },

                    isScheduled(loan) {
                        return loan.status === 'dipinjam' && toLocalDateString(loan.loan_date) > todayDate();
                    }
                }
            }
        </script>
    @endif
</x-layouts.app>
