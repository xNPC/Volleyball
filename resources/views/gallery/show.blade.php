<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Фотогалерея', 'url' => route('gallery.index')],
            ['label' => $album->title],
        ]" />

        <x-card class="mb-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h1 class="font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">
                        @svg('lucide-camera', 'inline h-7 w-7 text-accent-500'){{ $album->title }}
                    </h1>
                    @if ($album->description)
                        <p class="mt-1 text-slate-500">{{ $album->description }}</p>
                    @endif
                </div>
                <div class="shrink-0">
                    <x-badge variant="accent">
                        @svg('lucide-image', 'h-3.5 w-3.5'){{ $album->photos->count() }} фото
                    </x-badge>
                </div>
            </div>
        </x-card>

        @if ($album->photos->count() > 0)
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($album->photos as $photo)
                    <button type="button"
                            data-photo="{{ $photo->url }}"
                            data-name="{{ $photo->original_name }}"
                            class="group overflow-hidden rounded-xl border border-slate-200/70 bg-white shadow-card transition duration-300 hover:opacity-95"
                            title="{{ $photo->original_name }}">
                        <img src="{{ $photo->thumbnail_url }}"
                             alt="{{ $photo->original_name }}"
                             loading="lazy"
                             class="aspect-square w-full object-cover transition duration-300 group-hover:scale-105">
                    </button>
                @endforeach
            </div>
        @else
            <x-card class="p-8 text-center text-slate-500">
                @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
                В этом альбоме пока нет фотографий
            </x-card>
        @endif
    </div>
</x-app-layout>