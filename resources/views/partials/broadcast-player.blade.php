@php
    $embedSrc = $broadcast->embed_src;
    $embedCode = $broadcast->embed_code;
    $preview = $broadcast->preview_url;
@endphp

@if ($embedSrc)
    <div class="aspect-video w-full overflow-hidden bg-black">
        <iframe src="{{ $embedSrc }}"
                class="h-full w-full"
                frameborder="0"
                allowfullscreen
                allow="autoplay; encrypted-media; fullscreen; picture-in-picture"></iframe>
    </div>
@elseif ($embedCode)
    <div class="overflow-hidden bg-black">
        {!! $embedCode !!}
    </div>
@elseif ($preview)
    <div class="relative aspect-video w-full overflow-hidden bg-black">
        <img src="{{ $preview }}" alt="{{ $broadcast->title }}" class="h-full w-full object-cover opacity-80">
        <div class="absolute inset-0 flex items-center justify-center">
            @svg('lucide-circle-play', 'h-14 w-14 text-white/90')
        </div>
    </div>
@else
    <div class="flex aspect-video w-full items-center justify-center bg-slate-100 text-slate-400">
        @svg('lucide-video-off', 'h-10 w-10')
    </div>
@endif
