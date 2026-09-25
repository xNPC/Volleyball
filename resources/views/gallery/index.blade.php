<x-app-layout>
    <div>
        <x-breadcrumb :items="[['label' => 'Фотогалерея']]" />

        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                @svg('lucide-camera', 'h-7 w-7')
            </div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">Фотогалерея</h1>
            <p class="mt-2 text-slate-500">Альбомы с фотографиями</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($albums as $album)
                <x-card hoverable class="flex h-full flex-col overflow-hidden">
                    <a href="{{ route('gallery.show', $album->slug) }}" class="relative block">
                        <img src="{{ $album->cover_url ?? '/images/placeholder.jpg' }}"
                             alt="{{ $album->title }}"
                             class="aspect-[4/3] w-full object-cover transition duration-300 hover:scale-105">
                        <div class="absolute right-3 top-3">
                            <x-badge variant="dark">
                                @svg('lucide-image', 'h-3.5 w-3.5'){{ $album->photos_count }} фото
                            </x-badge>
                        </div>
                    </a>
                    <div class="flex grow flex-col p-5">
                        <a href="{{ route('gallery.show', $album->slug) }}"
                           class="font-display text-lg font-bold text-brand-800 transition hover:text-accent-600">
                            {{ $album->title }}
                        </a>
                        @if ($album->description)
                            <p class="mt-1 flex-1 text-sm text-slate-500">{{ Str::limit($album->description, 100) }}</p>
                        @endif
                        <div class="mt-4">
                            <x-btn href="{{ route('gallery.show', $album->slug) }}" variant="brand" size="sm" class="w-full">
                                @svg('lucide-eye', 'h-4 w-4')Смотреть фото
                            </x-btn>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card class="col-span-full p-8 text-center text-slate-500">
                    @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
                    Альбомов пока нет
                </x-card>
            @endforelse
        </div>
    </div>
</x-app-layout>