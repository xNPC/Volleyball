# AGENTS.md

## Проект
Портал волейбольных турниров: публичный сайт + админ-панель.

- Стек: PHP 8.2+, Laravel 12, Orchid Platform 14 (админка), Livewire 3,
  Jetstream + Fortify (авторизация), Intervention Image, Vite.
- ОС разработки: Windows (OSPanel), shell — PowerShell 5.1.

## Границы сканирования
Не читать и не искать в служебных папках:
`vendor/`, `node_modules/`, `storage/`, `.idea/`, `.osp/`, `public/storage/`.
Исключения — только точечные чтения конкретных файлов вендора при разборе API.

## Структура
- Публичная часть: `app/Http/Controllers`, views в `resources/views`
- Админка (Orchid): экраны `app/Orchid/Screens`, лейауты `app/Orchid/Layouts`,
  маршруты `routes/platform.php`, меню и permissions `app/Orchid/PlatformProvider.php`
- Бизнес-логика: `app/Services` (турнирные таблицы, сетки плей-офф)

## Конвенции Orchid (обязательно)
- Публичные свойства экранов — без тайпхинтов, с дефолтом (`public $docs = [];`),
  т.к. `fillPublicProperty()` читает их до инициализации; данные для layout()
  брать из `$this-><свойство>` или Repository.
- `Switcher` для boolean-полей → всегда `->sendTrueOrFalse()`.
- Кнопки «Выйти/Отмена» внутри форм с required-полями → `->novalidate()`.
- HTML из Quill рендерить через blade `{!! !!}`, не через Label (экранирует).
- Создание и редактирование — один Screen: `query(Model $model = null)`,
  ветвление по `$model->exists` (паттерн AlbumFormScreen).
- Новые разделы: permission в `PlatformProvider::permissions()`,
  `permission()` на экранах, пункты меню с `->permission(...)`.

## Язык интерфейса
Весь пользовательский текст (названия, кнопки, алерты, confirm'ы) — на русском.

## Проверка изменений
- Синтаксис PHP: `php -l <файл>`
- Маршруты: `php artisan route:list` (фильтр по нужному префиксу)
- Миграции не запускать без запроса; БД настроена в `.env`

## Прочее
- Комментарии в коде не добавлять без запроса.
- Git-команды (commit/push) — только по явной просьбе.
