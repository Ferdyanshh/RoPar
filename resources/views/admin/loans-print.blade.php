<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Peminjaman — RoPar</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 24px;
        }

        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h1 {
            margin: 0 0 4px;
            color: #111827;
            font-size: 18px;
        }

        p {
            margin: 3px 0;
        }

        .header,
        .summary,
        .footer {
            width: 100%;
        }

        .header td {
            padding-bottom: 12px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .summary {
            margin: 8px 0 14px;
            border-collapse: collapse;
            background: #f3f4f6;
        }

        .summary td {
            width: 25%;
            padding: 8px;
            border: 1px solid #d1d5db;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .report th,
        .report td {
            padding: 5px 4px;
            border: 1px solid #9ca3af;
            vertical-align: top;
        }

        .report th {
            background: #e5e7eb;
            text-align: left;
        }

        .report tr {
            page-break-inside: avoid;
        }

        .report tfoot {
            font-weight: bold;
        }

        .footer {
            margin-top: 24px;
        }

        .signature {
            width: 210px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>RoPar — SISTEM PEMINJAMAN ROBOT PACAR</h1>
                <p class="muted">Laporan Resmi Transaksi Peminjaman &amp; Pengembalian Unit</p>
            </td>
            <td class="right">
                <p><span class="muted">Tanggal Cetak:</span> {{ $printedAt->format('d/m/Y H:i') }}</p>
                <p><span class="muted">Dicetak Oleh:</span> {{ auth()->user()->name }} (Admin)</p>
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td>
                <strong>Periode Tanggal</strong><br>
                @if ($monthStart)
                    {{ $monthStart->format('m/Y') }}
                @elseif (isset($filters['from']) || isset($filters['to']))
                    {{ isset($filters['from']) ? \Carbon\CarbonImmutable::parse($filters['from'])->format('d/m/Y') : 'Awal' }}
                    s/d
                    {{ isset($filters['to']) ? \Carbon\CarbonImmutable::parse($filters['to'])->format('d/m/Y') : 'Sekarang' }}
                @else
                    Semua Periode
                @endif
            </td>
            <td>
                <strong>Status</strong><br>
                {{ $filters['status'] ?? 'Semua Status' }}
            </td>
            <td>
                <strong>Filter Anggota</strong><br>
                {{ $member ? ($member->profile?->full_name ?: $member->name) : 'Semua Anggota' }}
            </td>
            <td>
                <strong>Total Data</strong><br>
                {{ $loans->count() }} Transaksi
                @if (! empty($filters['q']))
                    <br><span class="muted">Kata kunci: {{ $filters['q'] }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>No</th>
                <th>Peminjam</th>
                <th>Model Robot</th>
                <th>Kode Unit</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th>Terlambat</th>
                <th>Denda</th>
                <th>Pembayaran Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $loan)
                @php
                    $latestPayment = $loan->finePayments->first();
                    $paymentStatus = match ($latestPayment?->status?->value) {
                        'lunas' => 'Lunas',
                        'menunggu' => 'Menunggu verifikasi',
                        'ditolak' => 'Ditolak',
                        default => 'Belum dibayar',
                    };
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $loan->member->profile?->full_name ?: $loan->member->name }}</strong><br>
                        <span class="muted">{{ $loan->member->email }}</span>
                    </td>
                    <td>{{ $loan->unit->robot->name ?? '-' }}</td>
                    <td>{{ $loan->unit->unit_code }}</td>
                    <td>{{ $loan->loan_date?->format('d/m/Y') }}</td>
                    <td>{{ $loan->due_date?->format('d/m/Y') }}</td>
                    <td>{{ $loan->return_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $loan->late_days ? $loan->late_days.' hari' : '-' }}</td>
                    <td>Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</td>
                    <td>{{ $loan->fine_amount > 0 ? $paymentStatus : '-' }}</td>
                    <td>{{ $loan->status->value }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">Tidak ada transaksi yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="right">Total Denda Terverifikasi:</td>
                <td>Rp {{ number_format($totalFineAmount, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td class="muted">
                <p>Dokumen ini dicetak secara otomatis oleh Sistem Informasi RoPar.</p>
                <p>Ref ID: ROPAR-RPT-{{ $printedAt->format('YmdHis') }}</p>
            </td>
            <td class="signature">
                <p>Petugas Administrator,</p>
                <br><br><br>
                <strong><u>{{ auth()->user()->name }}</u></strong>
                <p class="muted">RoPar Administrator Team</p>
            </td>
        </tr>
    </table>
</body>
</html>
