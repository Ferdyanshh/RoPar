<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Peminjaman — RoPar</title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 17px;
        }

        p {
            margin: 3px 0;
        }

        .header {
            border-bottom: 2px solid #111827;
            margin-bottom: 14px;
            padding-bottom: 10px;
        }

        .meta {
            color: #4b5563;
            margin-bottom: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #9ca3af;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #e5e7eb;
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $formatDate = static fn ($date): string => $date?->format('d/m/Y') ?? '-';
        $formatCurrency = static function (int|float|string|null $amount) use ($admin): string {
            $amount = (float) ($amount ?? 0);
            if ($admin->currency === 'USD') {
                $rate = (float) config('currency.idr_per_usd');
                if ($rate <= 0) {
                    throw new LogicException('The IDR per USD exchange rate must be a positive number.');
                }

                return '$'.number_format($amount / $rate, 2, '.', ',');
            }

            return 'Rp '.number_format($amount, 0, ',', '.');
        };
        $dateRange = 'Semua Periode';
        if (isset($filters['month'])) {
            $dateRange = Carbon\CarbonImmutable::parse($filters['month'].'-01')->locale('id')->translatedFormat('F Y');
        } elseif (isset($filters['from']) || isset($filters['to'])) {
            $dateRange = ($filters['from'] ?? 'Awal').' s/d '.($filters['to'] ?? 'Sekarang');
        }
        $totalPaidFine = $loans->sum(static function ($loan) use ($paidFineStatus): int {
            $latestPayment = $loan->finePayments->first();

            return $latestPayment?->status === $paidFineStatus ? (int) $latestPayment->amount : 0;
        });
    @endphp

    <div class="header">
        <h1>RoPar — SISTEM PEMINJAMAN ROBOT PACAR</h1>
        <p>Laporan Resmi Transaksi Peminjaman &amp; Pengembalian Unit</p>
        <p>Tanggal Cetak: {{ $printedAt->format('d/m/Y H:i') }} | Dicetak Oleh: {{ $admin->name }} (Admin)</p>
    </div>

    <div class="meta">
        <strong>Periode:</strong> {{ $dateRange }} &nbsp;|&nbsp;
        <strong>Status:</strong> {{ $filters['status'] ?? 'Semua Status' }} &nbsp;|&nbsp;
        <strong>Anggota:</strong> {{ isset($filters['member_id']) ? ($loans->first()?->member?->name ?? 'ID '.$filters['member_id']) : 'Semua Anggota' }} &nbsp;|&nbsp;
        <strong>Total Data:</strong> {{ $loans->count() }} transaksi
        @if (isset($filters['q']))
            &nbsp;|&nbsp; <strong>Kata Kunci:</strong> {{ $filters['q'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Peminjam</th>
                <th>Model Robot</th>
                <th class="center">Kode Unit</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th class="center">Terlambat</th>
                <th class="right">Denda</th>
                <th class="center">Pembayaran Denda</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $index => $loan)
                @php($latestPayment = $loan->finePayments->first())
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $loan->member->profile?->full_name ?? $loan->member->name }}<br>{{ $loan->member->email }}</td>
                    <td>{{ $loan->unit->robot->name }}</td>
                    <td class="center">{{ $loan->unit->unit_code }}</td>
                    <td>{{ $formatDate($loan->loan_date) }}</td>
                    <td>{{ $formatDate($loan->due_date) }}</td>
                    <td>{{ $formatDate($loan->return_date) }}</td>
                    <td class="center">{{ $loan->late_days > 0 ? $loan->late_days.' hr' : '-' }}</td>
                    <td class="right">{{ $formatCurrency($loan->fine_amount) }}</td>
                    <td class="center">
                        @if ($loan->fine_amount < 1)
                            -
                        @elseif (! $latestPayment)
                            Belum dibayar
                        @else
                            {{ ['lunas' => 'Lunas', 'menunggu' => 'Menunggu verifikasi', 'ditolak' => 'Ditolak'][$latestPayment->status->value] ?? 'Belum dibayar' }}
                        @endif
                    </td>
                    <td class="center">{{ $loan->status->value }}</td>
                </tr>
            @empty
                <tr>
                    <td class="center" colspan="11">Tidak ada transaksi yang sesuai dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th class="right" colspan="9">Total Denda Terverifikasi:</th>
                <th class="right">{{ $formatCurrency($totalPaidFine) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dokumen dicetak otomatis oleh Sistem Informasi RoPar.</p>
        <p>Petugas Administrator,</p>
        <br><br><br>
        <p><strong>{{ $admin->name }}</strong></p>
    </div>
</body>
</html>
