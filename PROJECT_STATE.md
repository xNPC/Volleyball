# PROJECT_STATE.md

Снимок состояния проекта для возобновления работы. Обновлять по ходу новых изменений.

## Общая информация
- **Проект**: Портал волейбольных турниров: публичный сайт + админ-панель.
- **Стек**: PHP 8.2+, Laravel 12, Orchid Platform 14, Livewire 3, Jetstream + Fortify, Vite, Intervention Image.
- **ОС**: Windows (OSPanel), shell: PowerShell 5.1.
- **AGENTS.md**: `C:\OSPanel\home\volleyball.local\AGENTS.md` — конвенции проекта (обязательны к соблюдению).
- Все пользовательские тексты — на русском.

---

## Ключевые конвенции (см. также AGENTS.md)
- Публичные свойства Orchid-экранов — **без тайпхинтов**, с дефолтом (`public $x = [];`), т.к. `fillPublicProperty()` читает их до инициализации; данные для layout брать из `$this-><свойство>` или Repository.
- `Switcher` для boolean — всегда `->sendTrueOrFalse()`.
- Кнопки «Выйти/Отмена» в формах с required-полями — `->novalidate()`.
- HTML из Quill — рендерить `{!! !!}`, не через `Label` (экранирует).
- Создание + редактирование — один Screen: `query(Model $model = null)`, ветвление по `$model->exists`.
- **DateTimer (важно!)**: `->format('d.m.Y')` задаёт формат **отправляемого** значения по умолчанию (`Y-m-d H:i:S`) → в DATE-колонку уходит `d.m.Y` → MySQL strict error. Использовать только `->altFormat('d.m.Y')` (показ) + `->allowInput()`; наружу уходит ISO.
- Новые разделы: permission в `PlatformProvider::permissions()`, `permission()` на экранах, пункты меню с `->permission(...)`.

---

## Модели и особенности
- **User** (`app/Models/User.php`): extends `Orchid\Platform\Models\User` → `hasAccess()` для permission. `fillable` + `gender`; константа `GENDERS` (Мужской/Женский). Casts: `birthday => date:Y-m-d`, `permissions => array`. **НЕТ модели Player** (мёртвый код в `Team::players()`).
- **Tournament** (`app/Models/Tournament.php`): `addition_deadline_male/female`, `transfer_deadline_male/female` (DATE nullable), `transfers_limit` (int nullable).
- **TournamentApplication**: константы `STATUS_*`, `IS_COMPLETE_*`. Relations: `roster()`, `schedules()`, `team()`, `tournament()`.
- **ApplicationRoster**: `SoftDeletes` (история частично сохраняется), константа `POSITIONS`.
- **RosterRequest** (`app/Models/RosterRequest.php`): `TYPES` = [Дозаявка, Переход, Отзаявка], `STATUSES` = [На рассмотрении, Утверждена, Отклонена]. Relations: `tournamentApplication()`, `applicationRoster()`, `user()`, `approvedBy()`. Scope: `scopeBlocking()`. Метод: `isPending()`.

---

## Выполнено

### Этап 1 — Раздел «Документация»
- Экраны: `MainScreen` (публичный accordion + HTML), `ListScreen` (CRUD-список с preview), `EditScreen` (create/edit/delete, Quill, `sendTrueOrFalse`, `novalidate`).
- Blade views: `resources/views/orchid/documentation/item.blade.php`, `empty.blade.php`, `style.blade.php`.
- Маршруты: 4 в `routes/platform.php` (main, list, create, `{doc}/edit`).
- Permission `platform.content.docs` (группа «Контент»).
- Меню: «Документация» (bs.book) / «Управление инструкциями» (bs.pencil-square).

### Этап 2 — Система дозаявок / переходов / отзаявок
- Миграции (написаны и **запущены**, см. раздел «Миграции»):
  - `..._000001_add_gender_to_users_table`
  - `..._000002_add_roster_rules_to_tournaments_table`
  - `..._000003_create_roster_requests_table`
- **RosterRequestFormScreen** (`app/Orchid/Screens/RosterRequest/RosterRequestFormScreen.php`): подача запроса.
  - Тип определяется автоматически: игрок в другой команде турнира → переход (подсветка, перечень перехода); был заявлен ранее (мягко удалён/переведён) → принудительный переход с расходом лимита; новичок → дозаявка. Отзаявка — без дедлайна.
  - Проверки: дедлайны по полу игрока (дозаявка/переход), лимит переходов (`approved+pending >= limit` → отказ), пол не указан → блок, дубликат pending → блок.
  - Права: капитан — только свои approved-заявки; админ с `platform.applications.edit` — любые.
- **RosterRequestListScreen**: permission `platform.applications.approve`; капитаны видят только свои запросы; approve/reject транзакцией (addition → создать строку ростера, transfer → мягкое удаление старой + создание новой, removal → мягкое удаление); фиксируются кто/когда рассмотрел; modal для причины отклонения.
- Маршруты: `admin/roster-requests/create/{application?}` и `admin/roster-requests` (префикс `platform.roster.requests.*`).
- Меню: «Дозаявки и переходы»; permission `platform.applications.approve` (группа «Заявки»).
- **TournamentEditLayout**: 4 `DateTimer` (дедлайны, `altFormat`) + `Input` (лимит переходов). `TournamentEditScreen::save()` — валидация новых полей.
- **UserEditLayout**: `Select::make('user.gender')` (опции `User::GENDERS`), `DateTimer birthday` (`altFormat('d.m.Y')` + `allowInput`), `Input phone`. `UserEditScreen::save()` — валидация `user.birthday => nullable|date`.
- **ApplicationEditScreen**: кнопка «Отзаявить» (`Link`, импорт `Link`) в DropDown состава → `route('platform.roster.requests.create', ['application' => ...]) . '?type=removal&player=' . $roster->user_id`, `canSee($this->application->exists)`.
- **GamesListScreen**: `ModalToggle` «Внести результат» — `->canSee(auth()->user()->hasAccess('platform.games.result'))`.

### Этап 3 — Bug fixes
- **MySQL Invalid datetime `19.08.2026`**: причина — `DateTimer->format('d.m.Y')` менял отправляемое значение. Фикс: убрать `->format()`, оставить `altFormat('d.m.Y')` + `allowInput`; + валидация `nullable|date`.
- **RosterRequestFormScreen «Undefined variable $options»**: в `query()` возвращался устаревший `$options` вместо `$this->applicationOptions` (рефакторинг). Фикс: `'applicationOptions' => $this->applicationOptions`.

---

## Миграции (статус)
Все три миграции **запущены** (`php artisan migrate --force` выполнен успешно). Отдельно запускать не нужно.

---

## Структура ключевых файлов
```
app/
  Models/
    User.php                    — gender, GENDERS
    Tournament.php              — дедлайны, transfers_limit
    RosterRequest.php           — новый (TYPES, STATUSES)
    TournamentApplication.php   — STATUS, IS_COMPLETE
    ApplicationRoster.php       — SoftDeletes, POSITIONS
  Orchid/
    Screens/
      Documentation/EditScreen.php, ListScreen.php, MainScreen.php
      RosterRequest/RosterRequestFormScreen.php, RosterRequestListScreen.php
      Application/ApplicationEditScreen.php        — + кнопка «Отзаявить»
      Tournament/TournamentEditScreen.php          — правила дозаявок
      Tournament/GamesListScreen.php               — фикс canSee
      User/UserEditScreen.php                      — валидация birthday
    Layouts/
      User/UserEditLayout.php                      — gender, birthday, phone
      Tournament/TournamentEditLayout.php          — дедлайны, лимит
    PlatformProvider.php       — permissions, menu items
  database/migrations/
    2026_08_25_000001_add_gender_to_users_table.php
    2026_08_25_000002_add_roster_rules_to_tournaments_table.php
    2026_08_25_000003_create_roster_requests_table.php
routes/platform.php           — документация + roster-requests
resources/views/orchid/documentation/  — item, empty, style
AGENTS.md                     — конвенции проекта
PROJECT_STATE.md              — этот файл
```

---

## Pending / TODO
- Убедиться, что у всех игроков заполнен `gender` (без него подача/утверждение запросов блокируется).
- Полное UI/UX-тестирование флоу: подача запроса (капитан) → рассмотрение (админ) → отображение в заявке.
- Проверить отображение «Отзаявить» на экране заявки и корректность перехода в форму удаления.
- Проверить лимиты/дедлайны на реальном турнире.
- Возможные future-задачи: раздел «Галерея» (permission `platform.content.gallery` уже заведён в меню), публичный показ сеток плей-офф и т.д.

---

## Справочные сервисы (бизнес-логика)
- `app/Services/GroupStandingsService.php` — расчёт турнирных таблиц.
- `app/Services/PlayoffBracketGenerator.php` — генерация сетки плей-офф.
