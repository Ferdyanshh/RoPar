<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cetak Laporan Riwayat Peminjaman — RoPar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 1cm; }
        }
    </style>
</head>
<body class="bg-surface-100 p-6 min-h-screen" x-data="loansPrint()" x-init="init()">

    {{-- Control Bar (Screen Only) --}}
    <div class="max-w-6xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-surface-200 flex items-center justify-between no-print">
        <div class="flex items-center gap-3">
            <a href="/admin/loans/history" class="px-4 py-2 bg-surface-100 hover:bg-surface-200 text-surface-700 rounded-xl text-xs font-bold transition">
                ← Kembali ke Sistem
            </a>
            <span class="text-xs text-surface-500" x-show="loading">
                <span class="spinner text-primary-500 mr-1"></span> Mengambil seluruh data (<span x-text="allLoans.length"></span> baris)...
            </span>
            <span class="text-xs text-success-600 font-bold" x-show="!loading">
                ✓ Siap dicetak (<span x-text="allLoans.length"></span> data dimuat)
            </span>
        </div>
        <button @click="window.print()" :disabled="loading"
                class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-xs transition disabled:opacity-50 cursor-pointer print-btn">
            🖨️ Cetak Dokumen Sekarang
        </button>
    </div>

    {{-- Printable Paper Sheet --}}
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-md border border-surface-200 text-surface-900 print:shadow-none print:border-none print:p-0">
        {{-- Header / Kop Laporan --}}
        <div class="border-b-2 border-surface-900 pb-5 mb-6 flex justify-between items-start">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-primary-600 text-white flex items-center justify-center font-bold text-sm">
                        RP
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-surface-900">RoPar — SISTEM PEMINJAMAN ROBOT PACAR</h1>
                </div>
                <p class="text-xs text-surface-500 uppercase tracking-widest font-semibold">Laporan Resmi Transaksi Peminjaman & Pengembalian Unit</p>
            </div>
            <div class="text-right text-xs space-y-1">
                <p><span class="text-surface-400">Tanggal Cetak:</span> <span class="font-bold" x-text="printDate"></span></p>
                <p><span class="text-surface-400">Dicetak Oleh:</span> <span class="font-semibold">{{ auth()->user()->name }} (Admin)</span></p>
            </div>
        </div>

        {{-- Filter Parameters Summary --}}
        <div class="bg-surface-50 p-3 rounded-xl border border-surface-200 text-xs mb-6 grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div>
                <span class="text-surface-400 block">Periode Tanggal:</span>
                <span class="font-bold text-surface-800" x-text="filterSummary.dateRange"></span>
            </div>
            <div>
                <span class="text-surface-400 block">Status:</span>
                <span class="font-bold text-surface-800 capitalize" x-text="filterSummary.status || 'Semua Status'"></span>
            </div>
            <div>
                <span class="text-surface-400 block">Filter Anggota:</span>
                <span class="font-bold text-surface-800" x-text="filterSummary.member || 'Semua Anggota'"></span>
            </div>
            <div>
                <span class="text-surface-400 block">Total Data:</span>
                <span class="font-bold text-primary-700" x-text="allLoans.length + ' Transaksi'"></span>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border border-surface-300">
                <thead>
                    <tr class="bg-surface-100 text-surface-700 border-b border-surface-300">
                        <th class="p-2.5 border border-surface-300 font-bold text-center w-8">No</th>
                        <th class="p-2.5 border border-surface-300 font-bold">Peminjam</th>
                        <th class="p-2.5 border border-surface-300 font-bold">Model Robot</th>
                        <th class="p-2.5 border border-surface-300 font-bold text-center">Kode Unit</th>
                        <th class="p-2.5 border border-surface-300 font-bold">Tgl Pinjam</th>
                        <th class="p-2.5 border border-surface-300 font-bold">Jatuh Tempo</th>
                        <th class="p-2.5 border border-surface-300 font-bold">Tgl Kembali</th>
                        <th class="p-2.5 border border-surface-300 font-bold text-center">Terlambat</th>
                        <th class="p-2.5 border border-surface-300 font-bold text-right">Denda</th>
                        <th class="p-2.5 border border-surface-300 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(loan, idx) in allLoans" :key="loan.id">
                        <tr class="border-b border-surface-200">
                            <td class="p-2 border border-surface-300 text-center font-mono" x-text="idx + 1"></td>
                            <td class="p-2 border border-surface-300">
                                <p class="font-bold" x-text="loan.member?.profile?.full_name || loan.member?.name"></p>
                                <p class="text-[10px] text-surface-500" x-text="loan.member?.email"></p>
                            </td>
                            <td class="p-2 border border-surface-300 font-medium" x-text="loan.unit?.robot?.name || '-'"></td>
                            <td class="p-2 border border-surface-300 text-center font-mono font-bold" x-text="loan.unit?.unit_code"></td>
                            <td class="p-2 border border-surface-300" x-text="formatDate(loan.loan_date)"></td>
                            <td class="p-2 border border-surface-300" x-text="formatDate(loan.due_date)"></td>
                            <td class="p-2 border border-surface-300" x-text="formatDate(loan.return_date)"></td>
                            <td class="p-2 border border-surface-300 text-center" x-text="loan.late_days ? loan.late_days + ' hr' : '-'"></td>
                            <td class="p-2 border border-surface-300 text-right font-mono" x-text="formatRupiah(loan.fine_amount || 0)"></td>
                            <td class="p-2 border border-surface-300 text-center font-semibold capitalize" x-text="loan.status"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="bg-surface-50 font-bold border-t-2 border-surface-900">
                        <td colspan="8" class="p-3 text-right border border-surface-300 uppercase">Total Penerimaan Denda:</td>
                        <td class="p-3 text-right font-mono text-sm border border-surface-300 font-black text-surface-900" x-text="formatRupiah(totalFineAmount)"></td>
                        <td class="border border-surface-300"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Signature Footer for Print --}}
        <div class="mt-12 pt-8 flex justify-between items-end text-xs">
            <div class="text-surface-400">
                <p>Dokumen ini dicetak secara otomatis oleh Sistem Informasi RoPar.</p>
                <p class="font-mono text-[10px] mt-1">Ref ID: ROPAR-RPT-{{ date('YmdHis') }}</p>
            </div>
            <div class="text-center w-48">
                <p class="mb-16 text-surface-500">Petugas Administrator,</p>
                <p class="font-bold underline text-surface-900">{{ auth()->user()->name }}</p>
                <p class="text-surface-400 text-[10px]">RoPar Administrator Team</p>
            </div>
        </div>
    </div>

    <script>
        function loansPrint() {
            return {
                allLoans: [],
                loading: true,
                printDate: formatDate(new Date().toISOString()),
                filterSummary: {
                    dateRange: 'Semua Periode',
                    status: '',
                    member: ''
                },

                get totalFineAmount() {
                    return this.allLoans.reduce((sum, item) => sum + (Number(item.fine_amount) || 0), 0);
                },

                async init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const from = urlParams.get('from');
                    const to = urlParams.get('to');
                    const status = urlParams.get('status');
                    const member_id = urlParams.get('member_id');
                    const q = urlParams.get('q');

                    if (from || to) {
                        this.filterSummary.dateRange = (from ? formatDate(from) : 'Awal') + ' s/d ' + (to ? formatDate(to) : 'Sekarang');
                    }
                    if (status) this.filterSummary.status = status;

                    let page = 1;
                    let lastPage = 1;
                    const accumulated = [];

                    try {
                        do {
                            const params = {
                                per_page: 100,
                                page: page
                            };
                            if (from) params.from = from;
                            if (to) params.to = to;
                            if (status) params.status = status;
                            if (member_id) params.member_id = member_id;
                            if (q) params.q = q;

                            const res = await api.get('/backend/admin/reports/loans', params);
                            accumulated.push(...(res.data || []));
                            lastPage = res.last_page || 1;
                            page++;
                        } while (page <= lastPage);

                        this.allLoans = accumulated;
                    } catch (e) {
                        console.error('Gagal mengambil data cetak:', e);
                    } finally {
                        this.loading = false;
                        // Trigger print automatically after load
                        setTimeout(() => {
                            window.print();
                        }, 600);
                    }
                }
            }
        }
    </script>
</body>
</html>
