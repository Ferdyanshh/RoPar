@props(['status'])

@php
$styles = [
    // Unit statuses
    'tersedia' => 'bg-success-50 text-success-700 ring-1 ring-success-500/20',
    'dipinjam' => 'bg-warning-50 text-warning-600 ring-1 ring-warning-500/20',
    'perawatan' => 'bg-info-50 text-info-600 ring-1 ring-info-500/20',
    'hilang' => 'bg-danger-50 text-danger-600 ring-1 ring-danger-500/20',

    // Loan statuses
    'dikembalikan' => 'bg-success-50 text-success-700 ring-1 ring-success-500/20',
    'terlambat' => 'bg-danger-50 text-danger-600 ring-1 ring-danger-500/20',

    // Roles
    'admin' => 'bg-accent-50 text-accent-700 ring-1 ring-accent-500/20',
    'anggota' => 'bg-primary-50 text-primary-700 ring-1 ring-primary-500/20',
];

$label = ucfirst($status);
$style = $styles[$status] ?? 'bg-surface-100 text-surface-600 ring-1 ring-surface-300/20';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold $style"]) }}>
    {{ $label }}
</span>
