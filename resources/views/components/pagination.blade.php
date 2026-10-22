{{-- Pagination component (Alpine-driven) --}}
{{-- Expects Alpine context with: currentPage, lastPage, goToPage(page) --}}

<div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-sm">
    <p class="text-surface-500">
        Halaman <span x-text="currentPage"></span> dari <span x-text="lastPage"></span>
    </p>
    <div class="flex items-center gap-1">
        <button @click="goToPage(1)" :disabled="currentPage <= 1"
                class="px-2.5 py-1.5 rounded-lg border border-surface-300 text-surface-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs cursor-pointer">
            ««
        </button>
        <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                class="px-2.5 py-1.5 rounded-lg border border-surface-300 text-surface-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs cursor-pointer">
            «
        </button>

        <template x-for="p in paginationRange()" :key="p">
            <button @click="typeof p === 'number' && goToPage(p)"
                    :class="p === currentPage
                        ? 'bg-primary-500 text-white border-primary-500'
                        : (typeof p === 'number' ? 'border-surface-300 text-surface-600 hover:bg-surface-100 cursor-pointer' : 'border-transparent text-surface-400 cursor-default')"
                    class="px-2.5 py-1.5 rounded-lg border text-xs min-w-[2rem] text-center"
                    :disabled="typeof p !== 'number'"
                    x-text="p">
            </button>
        </template>

        <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage"
                class="px-2.5 py-1.5 rounded-lg border border-surface-300 text-surface-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs cursor-pointer">
            »
        </button>
        <button @click="goToPage(lastPage)" :disabled="currentPage >= lastPage"
                class="px-2.5 py-1.5 rounded-lg border border-surface-300 text-surface-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed text-xs cursor-pointer">
            »»
        </button>
    </div>
</div>
