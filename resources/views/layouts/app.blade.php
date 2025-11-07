<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Волейбольные Турниры | {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Волейбольный стиль -->
    <style>
        :root {
            --volleyball-orange: #FF6B35;
            --volleyball-blue: #004E89;
            --volleyball-white: #FFFFFF;
            --court-green: #2E8B57;
        }

        .bg-volleyball-orange {
            background-color: var(--volleyball-orange);
        }

        .player-avatar-large {
            width: 80px;
            height: 80px;
            border: 3px solid var(--volleyball-orange);
        }

        .volleyball-bg {
            background: linear-gradient(135deg, var(--volleyball-blue) 0%, var(--volleyball-orange) 100%);
        }

        .court-bg {
            background-color: var(--court-green);
            background-image:
                linear-gradient(transparent 24px, rgba(255,255,255,0.1) 25px),
                linear-gradient(90deg, transparent 24px, rgba(255,255,255,0.1) 25px);
            background-size: 25px 25px;
        }

        .navbar-volleyball {
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 3px solid var(--volleyball-orange);
        }

        .card {
            border-radius: 0;
        }

        .card-volleyball {
            background: white;
            //border-radius: 15px;
            //border: 2px solid var(--volleyball-orange);
            box-shadow: 0 8px 25px rgba(0, 78, 137, 0.15);
            transition: all 0.3s ease;
        }

        .card-volleyball:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 107, 53, 0.2);
        }

        .btn-volleyball {
            background: var(--volleyball-orange);
            border: none;
            color: white;
            font-weight: bold;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }

        .btn-volleyball:hover {
            background: var(--volleyball-blue);
            transform: translateY(-2px);
            color: white;
        }

        .score-badge {
            background: var(--volleyball-orange);
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 20px;
        }

        .team-card {
            text-align: center;
            padding: 20px;
        }

        .team-logo {
            width: 80px;
            height: 80px;
            background: var(--volleyball-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2em;
        }

        .tournament-card {
            position: relative;
            overflow: hidden;
        }

        .tournament-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--volleyball-orange);
        }

        .net-icon {
            color: var(--volleyball-orange);
            font-size: 2em;
        }

        .player-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            //border: 3px solid var(--volleyball-orange);
        }

        .match-card {
            background: white;
            border-left: 5px solid var(--volleyball-orange);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .court-lines {
            position: relative;
        }

        .court-lines::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-50%);
        }

        .photo-modal-trigger {
            cursor: zoom-in;
            transition: all 0.3s ease;
            border: none;
            background: none;
            padding: 0;
            display: block;
        }

        .photo-modal-trigger:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        .photo-modal-trigger .player-avatar,
        .photo-modal-trigger .player-avatar-large,
        .photo-modal-trigger .player-avatar-sm,
        .photo-modal-trigger .player-avatar-xs {
            transition: all 0.3s ease;
        }

        .photo-modal-trigger:hover .player-avatar {
            box-shadow: 0 0 0 3px var(--volleyball-orange);
        }

        .photo-modal-trigger:hover .player-avatar-large {
            box-shadow: 0 0 0 4px var(--volleyball-orange);
        }

        .photo-modal-trigger:hover .player-avatar-sm {
            box-shadow: 0 0 0 2px var(--volleyball-orange);
        }

        .photo-modal-trigger:hover .player-avatar-xs {
            box-shadow: 0 0 0 1px var(--volleyball-orange);
        }
    </style>

    @livewireStyles
</head>
<body class="font-sans antialiased">
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-volleyball fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}" style="color: var(--volleyball-blue);">
            <i class="fas fa-volleyball-ball me-2"></i>{{ config('app.name', 'Laravel') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i>Главная
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="/tournaments">
                        <i class="fas fa-trophy me-1"></i>Турниры
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="{{ route('users.index') }}">
                        <i class="fas fa-users me-1"></i>Игроки
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="{{ route('teams.index') }}">
                        <i class="fas fa-users me-1"></i>Команды
                    </a>
                </li>
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link fw-semibold" href="#schedule">--}}
{{--                        <i class="fas fa-calendar-alt me-1"></i>Расписание--}}
{{--                    </a>--}}
{{--                </li>--}}
            </ul>
            <!-- Поиск в навигации -->
{{--            <form action="{{ route('users.index') }}" method="GET" class="d-flex me-3">--}}
{{--                <div class="input-group input-group-sm">--}}
{{--                    <input type="text"--}}
{{--                           name="search"--}}
{{--                           class="form-control form-control-sm"--}}
{{--                           placeholder="Поиск игроков..."--}}
{{--                           style="width: 200px;">--}}
{{--                    <button type="submit" class="btn btn-sm btn-volleyball">--}}
{{--                        <i class="fas fa-search"></i>--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </form>--}}

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="fas fa-user-circle me-2"></i>Профиль
                                </a></li>
                            <li><a class="dropdown-item" href="{{ route('platform.teams.list') }}">
                                    <i class="fas fa-volleyball-ball me-2"></i>Мои команды
                                </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Выйти
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>Войти
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Регистрация
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<main style="padding-top: 80px;">
    {{ $slot }}
</main>

<!-- Footer -->
<!-- Footer -->
<footer class="volleyball-bg text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="fw-bold">
                    <i class="fas fa-volleyball-ball me-2"></i>{{ config('app.name', 'Laravel') }}
                </h5>
                <p class="mb-3">Платформа для организации и проведения волейбольных турниров</p>
{{--                <div class="d-flex gap-3">--}}
{{--                    <a href="#" class="text-white"><i class="fab fa-telegram fa-lg"></i></a>--}}
{{--                    <a href="#" class="text-white"><i class="fab fa-vk fa-lg"></i></a>--}}
{{--                    <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>--}}
{{--                </div>--}}
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Навигация</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('home') }}" class="text-white text-decoration-none">
                            <i class="fas fa-home me-2"></i>Главная
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/tournaments" class="text-white text-decoration-none">
                            <i class="fas fa-trophy me-2"></i>Турниры
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('users.index') }}" class="text-white text-decoration-none">
                            <i class="fas fa-users me-2"></i>Игроки
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('teams.index') }}" class="text-white text-decoration-none">
                            <i class="fas fa-users me-2"></i>Команды
                        </a>
                    </li>
{{--                    <li class="mb-2">--}}
{{--                        <a href="#schedule" class="text-white text-decoration-none">--}}
{{--                            <i class="fas fa-calendar-alt me-2"></i>Расписание--}}
{{--                        </a>--}}
{{--                    </li>--}}
                </ul>
            </div>
{{--            <div class="col-md-4">--}}
{{--                <h6 class="fw-bold mb-3">Контакты</h6>--}}
{{--                <ul class="list-unstyled">--}}
{{--                    <li class="mb-2">--}}
{{--                        <i class="fas fa-phone me-2"></i>--}}
{{--                        <a href="tel:+79999999999" class="text-white text-decoration-none">+7 (999) 999-99-99</a>--}}
{{--                    </li>--}}
{{--                    <li class="mb-2">--}}
{{--                        <i class="fas fa-envelope me-2"></i>--}}
{{--                        <a href="mailto:info@volleyball.ru" class="text-white text-decoration-none">info@volleyball.ru</a>--}}
{{--                    </li>--}}
{{--                    <li class="mb-2">--}}
{{--                        <i class="fas fa-map-marker-alt me-2"></i>--}}
{{--                        <span>г. Москва, ул. Спортивная, 1</span>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </div>--}}
        </div>
        <div class="text-center mt-4 pt-3 border-top border-white-50">
            <small>&copy; 2025 {{ config('app.name', 'Laravel') }}. Все права защищены.</small>
        </div>
    </div>
</footer>

<!-- Модальное окно для фото -->
<x-photo-modal />

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@livewireScripts
</body>
</html>
