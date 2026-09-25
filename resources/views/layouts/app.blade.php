<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <title>@yield('title', 'Волейбольные Турниры') | {{ config('app.name', 'Laravel') }}</title>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=105404352', 'ym');

        ym(105404352, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/105404352" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=sora:600,700,800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 font-sans text-brand-900 antialiased">
    <nav class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/95 backdrop-blur" x-data="{ open: false }">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-brand-600 to-accent-500 text-white shadow-sm">
                        @svg('lucide-volleyball', 'h-5 w-5')
                    </span>
                    <span class="font-display text-lg font-bold tracking-tight text-brand-800">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    <a href="{{ route('home') }}" class="nav-link-item @if(request()->routeIs('home')) nav-link-item-active @endif">
                        @svg('lucide-home', 'h-4 w-4')Главная
                    </a>
                    <a href="{{ route('tournaments.index') }}" class="nav-link-item @if(request()->routeIs('tournaments.*')) nav-link-item-active @endif">
                        @svg('lucide-trophy', 'h-4 w-4')Турниры
                    </a>
                    <a href="{{ route('users.index') }}" class="nav-link-item @if(request()->routeIs('users.*')) nav-link-item-active @endif">
                        @svg('lucide-users', 'h-4 w-4')Игроки
                    </a>
                    <a href="{{ route('teams.index') }}" class="nav-link-item @if(request()->routeIs('teams.*')) nav-link-item-active @endif">
                        @svg('lucide-shield', 'h-4 w-4')Команды
                    </a>
                    <a href="{{ route('gallery.index') }}" class="nav-link-item @if(request()->routeIs('gallery.*')) nav-link-item-active @endif">
                        @svg('lucide-camera', 'h-4 w-4')Фотоальбом
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <div class="relative hidden lg:block" x-data="{ userOpen: false }" @click.outside="userOpen = false">
                        <button type="button" @click="userOpen = !userOpen" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 text-sm font-medium text-brand-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                                @svg('lucide-user', 'h-4 w-4')
                            </span>
                            <span class="max-w-[140px] truncate">{{ Auth::user()->name }}</span>
                            @svg('lucide-chevron-down', 'h-4 w-4 text-slate-400', ['x-show' => '!userOpen'])
                            @svg('lucide-chevron-up', 'h-4 w-4 text-slate-400', ['x-show' => 'userOpen', 'x-cloak' => ''])
                        </button>

                        <div x-cloak x-show="userOpen" x-transition.opacity.origin.top.right class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-card">
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-brand-700 transition hover:bg-slate-50 hover:text-accent-600">
                                @svg('lucide-circle-user', 'h-4 w-4 text-slate-400')Профиль
                            </a>
                            <a href="{{ route(config('platform.index')) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-brand-700 transition hover:bg-slate-50 hover:text-accent-600">
                                @svg('lucide-gauge', 'h-4 w-4 text-slate-400')Панель управления
                            </a>
                            <div class="my-1 border-t border-slate-100"></div>
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-accent-600">
                                    @svg('lucide-log-out', 'h-4 w-4 text-slate-400')Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="hidden items-center gap-2 lg:flex">
                        <a href="{{ route('login') }}" class="nav-link-item">Войти</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-accent-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-accent-600 hover:shadow-card">
                                @svg('lucide-user-plus', 'h-4 w-4')Регистрация
                            </a>
                        @endif
                    </div>
                @endauth

                <button type="button" @click="open = !open" class="rounded-lg border border-slate-200 p-2 text-brand-700 transition hover:border-slate-300 hover:bg-slate-50 lg:hidden" aria-label="Меню">
                    @svg('lucide-menu', 'h-5 w-5', ['x-show' => '!open'])
                    @svg('lucide-x', 'h-5 w-5', ['x-show' => 'open', 'x-cloak' => ''])
                </button>
            </div>
        </div>

        <div x-cloak x-show="open" x-transition @click="open = false" class="border-t border-slate-100 bg-white px-4 py-3 lg:hidden">
            <div class="flex flex-col gap-1">
                <a href="{{ route('home') }}" class="nav-link-item @if(request()->routeIs('home')) nav-link-item-active @endif">
                    @svg('lucide-home', 'h-4 w-4')Главная
                </a>
                <a href="{{ route('tournaments.index') }}" class="nav-link-item @if(request()->routeIs('tournaments.*')) nav-link-item-active @endif">
                    @svg('lucide-trophy', 'h-4 w-4')Турниры
                </a>
                <a href="{{ route('users.index') }}" class="nav-link-item @if(request()->routeIs('users.*')) nav-link-item-active @endif">
                    @svg('lucide-users', 'h-4 w-4')Игроки
                </a>
                <a href="{{ route('teams.index') }}" class="nav-link-item @if(request()->routeIs('teams.*')) nav-link-item-active @endif">
                    @svg('lucide-shield', 'h-4 w-4')Команды
                </a>
                <a href="{{ route('gallery.index') }}" class="nav-link-item @if(request()->routeIs('gallery.*')) nav-link-item-active @endif">
                    @svg('lucide-camera', 'h-4 w-4')Фотоальбом
                </a>
            </div>

            @auth
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <a href="{{ route('profile.show') }}" class="nav-link-item">
                        @svg('lucide-circle-user', 'h-4 w-4')Профиль
                    </a>
                    <a href="{{ route(config('platform.index')) }}" class="nav-link-item">
                        @svg('lucide-gauge', 'h-4 w-4')Панель управления
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link-item w-full text-left text-slate-600">
                            @svg('lucide-log-out', 'h-4 w-4')Выйти
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <a href="{{ route('login') }}" class="nav-link-item">
                        @svg('lucide-log-in', 'h-4 w-4')Войти
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link-item text-accent-600">
                            @svg('lucide-user-plus', 'h-4 w-4')Регистрация
                        </a>
                    @endif
                </div>
            @endauth
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="mt-16 bg-brand-900 text-brand-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-accent-500 to-brand-500 text-white">
                        @svg('lucide-volleyball', 'h-5 w-5')
                    </span>
                    <span class="font-display text-lg font-bold text-white">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <p class="mt-4 text-sm leading-relaxed text-brand-200">Платформа для организации и проведения волейбольных турниров</p>
            </div>

            <div>
                <h6 class="font-display text-sm font-semibold uppercase tracking-wider text-white">Навигация</h6>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-brand-200 transition hover:text-accent-400">@svg('lucide-home', 'h-4 w-4')Главная</a></li>
                    <li><a href="{{ route('tournaments.index') }}" class="inline-flex items-center gap-2 text-brand-200 transition hover:text-accent-400">@svg('lucide-trophy', 'h-4 w-4')Турниры</a></li>
                    <li><a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-brand-200 transition hover:text-accent-400">@svg('lucide-users', 'h-4 w-4')Игроки</a></li>
                    <li><a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 text-brand-200 transition hover:text-accent-400">@svg('lucide-shield', 'h-4 w-4')Команды</a></li>
                    <li><a href="{{ route('gallery.index') }}" class="inline-flex items-center gap-2 text-brand-200 transition hover:text-accent-400">@svg('lucide-camera', 'h-4 w-4')Фотоальбом</a></li>
                    <li><a href="https://1liga42.ru" class="inline-flex items-center gap-2 text-brand-200 transition hover:text-accent-400">@svg('lucide-book-open', 'h-4 w-4')История 1 лиги</a></li>
                </ul>
            </div>

            <div>
                <h6 class="font-display text-sm font-semibold uppercase tracking-wider text-white">О портале</h6>
                <p class="mt-4 text-sm leading-relaxed text-brand-200">Следите за турнирами, командами и игроками. Результаты, сетки плей-офф и статистика — всё в одном месте.</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto max-w-7xl px-4 py-5 text-center text-xs text-brand-300 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Все права защищены.
            </div>
        </div>
    </footer>

    <x-photo-modal />

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.store('photo', {
                open: false,
                src: '',
                name: '',
                loading: true,

                show(src, name = '') {
                    this.src = src;
                    this.name = name;
                    this.loading = true;
                    this.open = true;
                },

                loaded() {
                    this.loading = false;
                },

                close() {
                    this.open = false;
                    this.src = '';
                },
            });
        });
    </script>

    @livewireScripts
</body>
</html>