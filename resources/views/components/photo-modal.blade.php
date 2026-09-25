<div
    x-data
    x-cloak
    x-show="$store.photo.open"
    x-transition.opacity.duration.150
    x-effect="document.body.classList.toggle('overflow-hidden', $store.photo.open)"
    @keydown.escape.window="$store.photo.close()"
    @click.self="$store.photo.close()"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background: rgba(3, 48, 79, 0.92);"
>
    <button
        type="button"
        @click="$store.photo.close()"
        class="absolute right-4 top-4 z-10 rounded-full text-white/80 transition hover:text-white"
        aria-label="Закрыть"
    >
        @svg('lucide-x', 'h-8 w-8')
    </button>

    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
        <div x-show="$store.photo.loading" class="flex flex-col items-center gap-3 text-white">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-accent-500 border-t-transparent"></div>
            <p class="text-sm text-white/70">Загрузка...</p>
        </div>
    </div>

    <img
        :src="$store.photo.src"
        :alt="$store.photo.name ? 'Фото ' + $store.photo.name : 'Фото'"
        x-on:load="$store.photo.loaded()"
        x-on:error="$store.photo.loaded()"
        class="relative max-h-[80vh] max-w-full rounded-xl object-contain shadow-2xl"
    >
</div>