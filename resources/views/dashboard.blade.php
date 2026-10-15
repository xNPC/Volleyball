@section('title', 'Личный кабинет')
@section('description', 'Личный кабинет на портале волейбольных турниров: мои игры, команды, турниры, медиатека и настройки аккаунта.')
@section('og_type', 'website')
@php $canonicalUrl = request()->url(); @endphp

<x-app-layout>
    <div>
        <div class="mb-6">
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800">Личный кабинет</h1>
            <p class="mt-2 text-slate-500">Добро пожаловать, {{ Auth::user()->name }}</p>
        </div>

        @include('partials.profile-nav')

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('profile.index') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                        @svg('lucide-volleyball', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Мои игры</span>
                        <span class="mt-1 block text-sm text-slate-500">Ближайшие и прошедшие игры ваших команд</span>
                    </span>
                </x-card>
            </a>

            <a href="{{ route('profile.show') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                        @svg('lucide-user', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Аккаунт</span>
                        <span class="mt-1 block text-sm text-slate-500">Профиль, фото, пароль и активные сеансы</span>
                    </span>
                </x-card>
            </a>

            <a href="{{ route('tournaments.index') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                        @svg('lucide-trophy', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Турниры</span>
                        <span class="mt-1 block text-sm text-slate-500">Расписание, таблицы, сетки плей-офф</span>
                    </span>
                </x-card>
            </a>

            <a href="{{ route('teams.index') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                        @svg('lucide-shield', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Команды</span>
                        <span class="mt-1 block text-sm text-slate-500">Составы, заявки и история выступлений</span>
                    </span>
                </x-card>
            </a>

            <a href="{{ route('users.index') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                        @svg('lucide-users', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Игроки</span>
                        <span class="mt-1 block text-sm text-slate-500">Профили, статистика и достижения</span>
                    </span>
                </x-card>
            </a>

            <a href="{{ route('gallery.index') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                        @svg('lucide-images', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Медиатека</span>
                        <span class="mt-1 block text-sm text-slate-500">Фотоальбомы с матчей и турниров</span>
                    </span>
                </x-card>
            </a>

            <a href="{{ route('broadcasts.index') }}" class="group block">
                <x-card hoverable class="flex h-full items-start gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                        @svg('lucide-tv', 'h-5 w-5')
                    </span>
                    <span class="min-w-0">
                        <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Трансляции</span>
                        <span class="mt-1 block text-sm text-slate-500">Записи и прямые эфиры матчей</span>
                    </span>
                </x-card>
            </a>

            @if (Auth::user()->hasAccess('platform.index'))
                <a href="{{ route(config('platform.index')) }}" class="group block">
                    <x-card hoverable class="flex h-full items-start gap-4 p-5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                            @svg('lucide-gauge', 'h-5 w-5')
                        </span>
                        <span class="min-w-0">
                            <span class="block font-display text-base font-bold text-brand-800 group-hover:text-accent-600">Панель управления</span>
                            <span class="mt-1 block text-sm text-slate-500">Администрирование портала</span>
                        </span>
                    </x-card>
                </a>
            @endif
        </div>
    </div>
</x-app-layout>
