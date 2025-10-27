{{--<!DOCTYPE html>--}}
{{--<html lang="ru">--}}
{{--<head>--}}
{{--    <meta charset="UTF-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0">--}}
{{--    <title>Система турниров</title>--}}
{{--    @livewireStyles--}}
{{--    <script src="//unpkg.com/alpinejs" defer></script>--}}
{{--    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">--}}
{{--</head>--}}
{{--<body class="bg-gray-100">--}}
{{--<div class="min-h-screen">--}}
{{--    <!-- Header -->--}}
{{--    <header class="bg-white shadow">--}}
{{--        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">--}}
{{--            <h1 class="text-3xl font-bold text-gray-900">--}}
{{--                Система управления турнирами--}}
{{--            </h1>--}}
{{--        </div>--}}
{{--    </header>--}}

{{--    <!-- Main Content -->--}}
{{--    <main>--}}
{{--        <livewire:tournament-list />--}}
{{--    </main>--}}
{{--</div>--}}

{{--@livewireScripts--}}
{{--</body>--}}
{{--</html>--}}
@extends('layouts.app')

@section('title', 'Турниры')

@section('content')
    <!-- Дополнительный контент страницы (если нужен) -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Турнирная система</h1>
        <p class="mt-2 text-gray-600">Здесь вы можете просмотреть все турниры и их структуру</p>
    </div>

    <!-- Livewire компонент -->
    <livewire:tournament-list />
@endsection
