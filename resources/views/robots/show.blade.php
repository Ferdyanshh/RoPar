<x-layouts.app title="Detail Robot Pacar">
@php
    $robotId = $id ?? request()->route('id') ?? request()->segment(2);
    $robot = \App\Models\Robot::with(['categories:id,name', 'units'])->find($robotId);

    $user = auth()->user();
    $activeLoansCount = 0;
    if ($user && $user->role === \App\Enums\UserRole::Member) {
        $activeLoansCount = \App\Models\Loan::where('user_id', $user->id)
            ->where('status', \App\Enums\LoanStatus::Borrowed->value)
            ->count();
    }
@endphp

    <div x-data="robotDetail(@js($robot), {{ $activeLoansCount }})" class="space-y-6">
        {{-- Back Button --}}
        <div>
            <a href="/robots" class="inline-flex items-center gap-2 text-sm font-semibold text-surface-500 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Katalog
            </a>
        </div>

        @if(!$robot)
            <div class="bg-white rounded-3xl border border-surface-200 p-12 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-danger-50 text-danger-500 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-surface-900">Robot Tidak Ditemukan</h2>
                <p class="text-sm text-surface-500 mt-2">Model robot yang Anda cari tidak tersedia atau ID tidak valid.</p>
                <div class="mt-6">
                    <a href="/robots" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold transition shadow">
                        Kembali ke Katalog
                    </a>
                </div>
            </div>
        @else
            {{-- Robot Details Card --}}
            <div class="bg-white rounded-3xl border border-surface-200 overflow-hidden shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 sm:p-8">
                    {{-- Robot Photo --}}
                    <div class="lg:col-span-5">
                        <div class="rounded-2xl overflow-hidden bg-surface-100 aspect-square flex items-center justify-center border border-surface-200 shadow-inner relative">
                            @if($robot->photo)
                                <img src="/storage/{{ $robot->photo }}" alt="{{ $robot->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-center text-primary-300 p-6">
                                    <svg class="w-24 h-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="6" y="10" width="12" height="9" rx="2"/><circle cx="9.5" cy="14.5" r="1.5"/><circle cx="14.5" cy="14.5" r="1.5"/><path stroke-linecap="round" d="M12 3v4M8 7h8"/>
                                    </svg>
                                    <p class="text-sm font-medium text-surface-400 mt-3">Ilustrasi RoPar</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Robot Info --}}
                    <div class="lg:col-span-7 flex flex-col">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            @forelse($robot->categories as $cat)
                                <span class="px-3 py-1 bg-accent-50 text-accent-700 rounded-full text-xs font-semibold">{{ $cat->name }}</span>
                            @empty
                                <span class="px-3 py-1 bg-surface-100 text-surface-500 rounded-full text-xs font-semibold">Umum</span>
                            @endforelse
                        </div>

                        <h1 class="text-3xl font-extrabold text-surface-900">{{ $robot->name }}</h1>
                        <p class="text-sm font-mono text-primary-600 font-semibold mt-1">{{ $robot->model ? 'Model: ' . $robot->model : 'Model Standar' }}</p>

                        <div class="mt-6 border-t border-surface-100 pt-6 flex-1">
                            <h3 class="text-sm font-bold text-surface-800 uppercase tracking-wider mb-2">Deskripsi & Karakteristik</h3>
                            <p class="text-sm text-surface-600 leading-relaxed whitespace-pre-line">{{ $robot->description ?: 'Tidak ada deskripsi detail untuk model ini.' }}</p>
                        </div>

                        {{-- Member Active Loans Indicator --}}
                        @if($user && $user->role === \App\Enums\UserRole::Member)
                            <div class="mt-6 p-4 rounded-2xl border transition-colors {{ $activeLoansCount >= 2 ? 'bg-danger-50/60 border-danger-200' : 'bg-primary-50/40 border-primary-200/60' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-base flex-shrink-0 {{ $activeLoansCount >= 2 ? 'bg-danger-100 text-danger-700' : 'bg-primary-100 text-primary-700' }}">
                                        {{ $activeLoansCount }}/2
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-surface-800">Status Kuota Peminjaman Anda</p>
                                        @if($activeLoansCount >= 2)
                                            <p class="text-xs text-danger-600 font-medium mt-0.5">
                                                Batas maksimal 2 unit pinjaman aktif telah tercapai. Kembalikan salah satu unit sebelum meminjam lagi.
                                            </p>
                                        @else
                                            <p class="text-xs text-success-600 font-medium mt-0.5">
                                                Anda masih bisa meminjam <span class="font-bold">{{ 2 - $activeLoansCount }}</span> unit lagi (maksimal 5 hari per pinjaman).
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Units Section --}}
                <div class="border-t border-surface-200 bg-surface-50/50 p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-surface-900">Daftar Unit Robot</h2>
                            <p class="text-xs text-surface-500">Pilih unit yang berstatus <span class="text-success-600 font-bold">Tersedia</span> untuk meminjam. Maksimal durasi 5 hari (dikenakan denda jika lewat 5 hari).</p>
                        </div>
                        <span class="px-3.5 py-1.5 bg-white border border-surface-200 rounded-xl text-xs font-bold text-surface-700 shadow-xs">
                            {{ $robot->units->where('status', \App\Enums\RobotUnitStatus::Available)->count() }} dari {{ $robot->units->count() }} unit tersedia
                        </span>
                    </div>

                    @if($robot->units->isEmpty())
                        <div class="bg-white rounded-2xl border border-surface-200 p-8 text-center">
                            <p class="text-sm font-semibold text-surface-700">Belum ada unit terdaftar untuk model robot ini.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($robot->units as $unit)
                                @php
                                    $isAvailable = ($unit->status === \App\Enums\RobotUnitStatus::Available);
                                @endphp
                                <div class="bg-white rounded-2xl border border-surface-200 p-5 shadow-xs flex flex-col justify-between hover:border-primary-400 transition {{ !$isAvailable ? 'opacity-75 bg-surface-50/50' : '' }}">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono font-bold text-primary-700 bg-primary-50 px-3 py-1 rounded-lg text-sm">{{ $unit->unit_code }}</span>
                                            <x-badge-status :status="$unit->status->value" />
                                        </div>
                                        <p class="text-xs text-surface-500 mt-3">
                                            Kondisi: <span class="text-surface-700 font-medium">{{ $unit->condition_note ?: 'Sangat Baik / Siap Kencan' }}</span>
                                        </p>
                                    </div>

                                    @if($user && $user->role === \App\Enums\UserRole::Member)
                                        <div class="mt-4 pt-3 border-t border-surface-100">
                                            @if(!$isAvailable)
                                                <button type="button" disabled
                                                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold bg-surface-100 text-surface-400 cursor-not-allowed">
                                                    <span>Unit Sedang {{ ucfirst($unit->status->value) }}</span>
                                                </button>
                                            @elseif($activeLoansCount >= 2)
                                                <button type="button" disabled
                                                        title="Batas maksimal 2 unit pinjaman aktif telah tercapai"
                                                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold bg-surface-200 text-surface-400 cursor-not-allowed">
                                                    <span>Batas Maksimal 2 Unit Tercapai</span>
                                                </button>
                                            @else
                                                <button type="button" @click="openBorrowModal(@js($unit))"
                                                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white transition shadow cursor-pointer">
                                                    <span>Pinjam Unit Ini</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-4 pt-3 border-t border-surface-100">
                                            <span class="text-xs text-surface-400 italic">Peminjaman hanya untuk akun Anggota</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Borrow Confirmation Modal --}}
            {{-- Borrow Confirmation Modal --}}
            <x-modal maxWidth="lg">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-surface-900 text-base">Konfirmasi Peminjaman & Simulasi Denda</h3>
                            <p class="text-xs text-surface-500">Durasi standar 5 hari bebas denda. Cek estimasi denda di bawah jika rencana pinjam lebih dari 5 hari.</p>
                        </div>
                    </div>

                    {{-- Robot and Unit Info --}}
                    <div class="flex items-center justify-between p-3.5 bg-surface-50 rounded-xl border border-surface-200 text-sm mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-200 overflow-hidden shrink-0">
                                <img :src="robot?.photo_url || 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=200&q=80'" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="font-bold text-surface-900 text-sm" x-text="robot?.name"></h4>
                                <span class="text-xs text-surface-500 font-mono">Unit: <strong class="text-primary-600" x-text="selectedUnit?.unit_code"></strong></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Unit Tersedia
                            </span>
                        </div>
                    </div>

                    {{-- Interactive Calendar & Date Selection --}}
                    <div class="p-4 bg-surface-50/70 rounded-2xl border border-surface-200 space-y-4 mb-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Tanggal Mulai Pinjam --}}
                            <div>
                                <label class="block text-xs font-semibold text-surface-700 mb-1">
                                    📅 Tanggal Mulai Pinjam
                                </label>
                                <input type="date" x-model="borrowStartDate" :min="todayDate()" @change="recalculateDuration"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                                <span class="text-[10px] text-surface-400 mt-0.5 block">Unit direservasi mulai tanggal ini</span>
                            </div>

                            {{-- Tanggal Rencana Pengembalian --}}
                            <div>
                                <label class="block text-xs font-semibold text-surface-700 mb-1">
                                    🗓️ Rencana Pengembalian
                                </label>
                                <input type="date" x-model="borrowPlanDate" :min="borrowStartDate" @change="recalculateDuration"
                                       class="w-full px-3 py-2 bg-white border border-surface-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none">
                                <span class="text-[10px] text-primary-600 font-medium mt-0.5 block">Jatuh Tempo Standar: <span x-text="formatDate(dueDate)"></span></span>
                            </div>
                        </div>

                        {{-- Quick Selection Duration Chips --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold text-surface-600">⚡ Simulasi Durasi Cepat:</span>
                                <span class="text-[11px] text-surface-400">Pilih durasi untuk cek denda:</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="setPlanDays(1)"
                                        :class="borrowDurationDays === 1 ? 'bg-primary-600 text-white font-bold' : 'bg-white hover:bg-surface-100 text-surface-700 border border-surface-200'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                    1 Hari
                                </button>
                                <button type="button" @click="setPlanDays(3)"
                                        :class="borrowDurationDays === 3 ? 'bg-primary-600 text-white font-bold' : 'bg-white hover:bg-surface-100 text-surface-700 border border-surface-200'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                    3 Hari
                                </button>
                                <button type="button" @click="setPlanDays(5)"
                                        :class="borrowDurationDays === 5 ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-semibold'"
                                        class="px-3 py-1 rounded-lg text-xs transition cursor-pointer">
                                    ⭐ 5 Hari (Maksimal Bebas Denda)
                                </button>
                                <button type="button" @click="setPlanDays(6)"
                                        :class="borrowDurationDays === 6 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                    6 Hari (+1 Hari Telat)
                                </button>
                                <button type="button" @click="setPlanDays(7)"
                                        :class="borrowDurationDays === 7 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                    7 Hari (+2 Hari Telat)
                                </button>
                                <button type="button" @click="setPlanDays(10)"
                                        :class="borrowDurationDays === 10 ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                                    10 Hari (+5 Hari Telat)
                                </button>
                            </div>
                        </div>

                        {{-- DYNAMIC LIVE CALCULATION DISPLAY (Jawaban pertanyaan user) --}}
                        <div class="mt-3">
                            <template x-if="borrowDurationDays <= 5">
                                <div class="p-3.5 bg-emerald-50 border border-emerald-300 rounded-xl text-emerald-900 transition-all">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-800">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>BEBAS DENDA (Rp 0)</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-emerald-200 text-emerald-800 text-[10px] font-bold rounded-md">
                                            Durasi: <span x-text="borrowDurationDays"></span> Hari (≤ 5 Hari)
                                        </span>
                                    </div>
                                    <p class="text-xs text-emerald-700">
                                        Rencana pengembalian <strong x-text="formatDate(borrowPlanDate)"></strong> masih dalam batas standar 5 hari (jatuh tempo: <strong x-text="formatDate(dueDate)"></strong>). <strong>Estimasi Denda: <span x-text="formatRupiah(0)"></span></strong>.
                                    </p>
                                </div>
                            </template>

                            <template x-if="borrowDurationDays > 5">
                                <div class="p-3.5 bg-rose-50 border-2 border-rose-300 rounded-xl text-rose-900 transition-all">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-rose-700">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>KENA DENDA KETERLAMBATAN</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-rose-200 text-rose-800 text-[10px] font-bold rounded-md">
                                            Kelebihan: <span x-text="borrowDurationDays - 5"></span> Hari
                                        </span>
                                    </div>
                                    <div class="text-xs text-rose-800 space-y-1">
                                        <p>
                                            Rencana pinjam <strong x-text="borrowDurationDays"></strong> hari melebihi batas standar 5 hari. Keterlambatan: <strong><span x-text="borrowDurationDays - 5"></span> hari</strong> dari jatuh tempo (<span x-text="formatDate(dueDate)"></span>).
                                        </p>
                                        <div class="flex items-center justify-between pt-1 border-t border-rose-200 font-bold">
                                            <span>Perhitungan Denda:</span>
                                            <span class="text-sm text-rose-700">
                                                <span x-text="borrowDurationDays - 5"></span> hari × <span x-text="formatRupiah(5000)"></span> = <span x-text="formatRupiah((borrowDurationDays - 5) * 5000)"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Syarat Ringkas --}}
                    <div class="flex items-center justify-between px-3 py-2 bg-surface-100 rounded-xl text-[11px] text-surface-600 mb-5">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Maksimal 2 unit pinjaman aktif per anggota
                        </span>
                        <span class="font-bold text-surface-800">Pinjaman saat ini: {{ $activeLoansCount }}/2</span>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-surface-600 hover:bg-surface-100 rounded-xl text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" @click="confirmBorrow" :disabled="borrowing"
                                class="px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-xl text-sm font-bold shadow transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                            <span x-show="borrowing" class="spinner"></span>
                            <span x-text="borrowing ? 'Memproses...' : (borrowStartDate > todayDate() ? 'Reservasi Unit' : 'Ya, Pinjam Sekarang')"></span>
                        </button>
                    </div>
                </div>
            </x-modal>
        @endif
    </div>

    <script>
        function robotDetail(initialRobot, activeLoansCount) {
            return {
                robot: initialRobot,
                activeLoansCount: activeLoansCount,
                showModal: false,
                selectedUnit: null,
                borrowing: false,
                borrowStartDate: todayDate(),
                borrowPlanDate: addDays(todayDate(), 5),
                dueDate: addDays(todayDate(), 5),
                borrowDurationDays: 5,

                openBorrowModal(unit) {
                    if (this.activeLoansCount >= 2) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Anggota hanya dapat memiliki maksimal 2 unit pinjaman aktif.', type: 'error' }
                        }));
                        return;
                    }
                    this.selectedUnit = unit;
                    this.borrowStartDate = todayDate();
                    this.borrowPlanDate = addDays(todayDate(), 5);
                    this.dueDate = addDays(todayDate(), 5);
                    this.borrowDurationDays = 5;
                    this.showModal = true;
                },

                setPlanDays(days) {
                    this.borrowDurationDays = days;
                    this.borrowPlanDate = addDays(this.borrowStartDate, days);
                },

                recalculateDuration() {
                    if (!this.borrowStartDate || this.borrowStartDate < todayDate()) this.borrowStartDate = todayDate();
                    this.dueDate = addDays(this.borrowStartDate, 5);
                    if (!this.borrowPlanDate || this.borrowPlanDate < this.borrowStartDate) {
                        this.borrowPlanDate = this.dueDate;
                    }
                    const [y1, m1, d1] = this.borrowStartDate.split('-').map(Number);
                    const [y2, m2, d2] = this.borrowPlanDate.split('-').map(Number);
                    const dt1 = new Date(y1, m1 - 1, d1);
                    const dt2 = new Date(y2, m2 - 1, d2);
                    const diff = Math.round((dt2 - dt1) / (1000 * 60 * 60 * 24));
                    this.borrowDurationDays = Math.max(1, diff);
                },

                async confirmBorrow() {
                    if (!this.selectedUnit) return;
                    this.borrowing = true;
                    try {
                        await api.post('/backend/loans', {
                            robot_unit_id: this.selectedUnit.id,
                            loan_date: this.borrowStartDate
                        });
                        const isReservation = this.borrowStartDate > todayDate();
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: isReservation ? 'Reservasi robot berhasil. Unit akan mulai dipinjam pada tanggal pilihan.' : 'Berhasil meminjam robot!',
                                type: 'success'
                            }
                        }));
                        setTimeout(() => {
                            window.location.href = '/my-loans';
                        }, 500);
                    } catch (err) {
                        this.showModal = false;
                        if (err.errors) {
                            const { fieldErrors, toastMessages } = separateErrors(err.errors);
                            if (toastMessages.length > 0) {
                                toastMessages.forEach(msg => {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
                                });
                            } else {
                                Object.values(fieldErrors).flat().forEach(msg => {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } }));
                                });
                            }
                        } else if (err.message) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message, type: 'error' } }));
                        }
                    } finally {
                        this.borrowing = false;
                    }
                }
            }
        }
    </script>
</x-layouts.app>
