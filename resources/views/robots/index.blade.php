<x-layouts.app title="Katalog Robot Pacar">
    <div x-data="robotCatalog()" x-init="init()" class="space-y-6">
        {{-- Header & Filters --}}
        <div class="bg-white rounded-3xl border border-surface-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-surface-900">Temukan Robot Pacar Impianmu</h2>
                    <p class="text-xs text-surface-400 mt-1">Pilih teman kencan robot dengan kepribadian dan tipe terbaik untukmu</p>
                </div>

                {{-- Search Bar --}}
                <div class="relative w-full md:w-80">
                    <input type="text" x-model="q" @input.debounce.300ms="loadRobots(1)"
                           placeholder="Cari nama robot..."
                           class="w-full pl-10 pr-4 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 transition-colors">
                    <svg class="w-4 h-4 text-surface-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>
            </div>

            {{-- Category Filter Chips --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-2">
                <button @click="setCategory('')"
                        :class="selectedCategory === '' ? 'bg-primary-600 text-white shadow-xs' : 'bg-surface-100 text-surface-600 hover:bg-surface-200'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition cursor-pointer">
                    Semua Kategori
                </button>
                <template x-for="cat in categories" :key="cat.id">
                    <button @click="setCategory(cat.id)"
                            :class="selectedCategory == cat.id ? 'bg-primary-600 text-white shadow-xs' : 'bg-surface-100 text-surface-600 hover:bg-surface-200'"
                            class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition cursor-pointer"
                            x-text="cat.name">
                    </button>
                </template>
            </div>
        </div>

        {{-- Loading Skeleton --}}
        <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="i in 8" :key="i">
                <div class="bg-white rounded-3xl border border-surface-200 overflow-hidden p-4 space-y-3">
                    <div class="w-full h-48 skeleton"></div>
                    <div class="h-5 w-3/4 skeleton"></div>
                    <div class="h-4 w-1/2 skeleton"></div>
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <div x-show="!loading && robots.length === 0">
            <x-empty-state title="Robot Tidak Ditemukan" description="Coba ubah kata kunci pencarian atau pilih kategori lain." />
        </div>

        {{-- Robot Grid --}}
        <div x-show="!loading && robots.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="robot in robots" :key="robot.id">
                <div class="bg-white rounded-3xl border border-surface-200 overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col group">
                    {{-- Image / Placeholder --}}
                    <div class="relative h-56 bg-surface-100 overflow-hidden flex items-center justify-center">
                        <template x-if="robot.photo">
                            <img :src="'/storage/' + robot.photo" :alt="robot.name"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </template>
                        <template x-if="!robot.photo">
                            <div class="flex flex-col items-center justify-center text-primary-300">
                                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <rect x="6" y="10" width="12" height="9" rx="2"/><circle cx="9.5" cy="14.5" r="1.5"/><circle cx="14.5" cy="14.5" r="1.5"/><path stroke-linecap="round" d="M12 3v4M8 7h8"/><rect x="4" y="13" width="2" height="4" rx="1"/><rect x="18" y="13" width="2" height="4" rx="1"/>
                                </svg>
                                <span class="text-xs font-semibold text-surface-400 mt-2">RoPar Model</span>
                            </div>
                        </template>

                        {{-- Available Units Badge --}}
                        <div class="absolute top-3 right-3">
                            <template x-if="robot.available_units_count > 0">
                                <span class="px-3 py-1 bg-success-500/90 backdrop-blur-md text-white text-xs font-bold rounded-full shadow">
                                    <span x-text="robot.available_units_count"></span> Unit Tersedia
                                </span>
                            </template>
                            <template x-if="robot.available_units_count === 0">
                                <span class="px-3 py-1 bg-danger-500/90 backdrop-blur-md text-white text-xs font-bold rounded-full shadow">
                                    Habis Dipinjam
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-lg text-surface-900 group-hover:text-primary-600 transition-colors" x-text="robot.name"></h3>
                                <p class="text-xs font-mono text-surface-400" x-text="robot.model || 'Model Standar'"></p>
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            <template x-for="cat in robot.categories" :key="cat.id">
                                <span class="px-2.5 py-0.5 bg-accent-50 text-accent-700 rounded-md text-[11px] font-medium" x-text="cat.name"></span>
                            </template>
                        </div>

                        <p class="text-xs text-surface-500 line-clamp-2 mt-3 flex-1" x-text="robot.description || 'Tidak ada deskripsi detail.'"></p>

                        {{-- Action Button --}}
                        <div class="mt-4 pt-3 border-t border-surface-100">
                            <a :href="'/robots/' + robot.id"
                               class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold transition shadow-xs"
                               :class="robot.available_units_count > 0
                                    ? 'bg-primary-50 text-primary-700 hover:bg-primary-600 hover:text-white'
                                    : 'bg-surface-100 text-surface-500 hover:bg-surface-200'">
                                <span>Lihat Detail & Pinjam</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        <div x-show="!loading && lastPage > 1">
            <x-pagination />
        </div>
    </div>

    <script>
        function robotCatalog() {
            return {
                robots: [],
                categories: [],
                q: '',
                selectedCategory: '',
                currentPage: 1,
                lastPage: 1,
                loading: true,

                async init() {
                    try {
                        const catRes = await api.get('/backend/categories');
                        this.categories = catRes.data || [];
                    } catch (e) {
                        console.error(e);
                    }
                    this.loadRobots(1);
                },

                setCategory(catId) {
                    this.selectedCategory = catId;
                    this.loadRobots(1);
                },

                async loadRobots(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    try {
                        const params = {
                            page: this.currentPage,
                            per_page: 12
                        };
                        if (this.q) params.q = this.q;
                        if (this.selectedCategory) params.category_id = this.selectedCategory;

                        const res = await api.get('/backend/robots', params);
                        this.robots = res.data || [];
                        this.currentPage = res.current_page || 1;
                        this.lastPage = res.last_page || 1;
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.loading = false;
                    }
                },

                goToPage(p) {
                    if (p >= 1 && p <= this.lastPage) {
                        this.loadRobots(p);
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
