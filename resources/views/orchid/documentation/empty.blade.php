<div class="text-center text-muted py-5">
    <p class="mb-3">Инструкций пока нет.</p>
    @if (auth()->user()?->hasAccess('platform.content.docs'))
        <a href="{{ route('platform.documentation.create') }}" class="btn btn-primary">Создать инструкцию</a>
    @endif
</div>
