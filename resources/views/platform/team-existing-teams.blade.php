<div class="p-3">

    <div class="alert alert-warning" role="alert">
        <p class="fw-bold mb-1">Не создавайте команду, которая у вас уже есть</p>
        <p class="mb-2">Команды из списка ниже уже зарегистрированы в системе. Если вы хотите заявиться на турнир от одной из них — просто перейдите в «Заявки» и подайте заявку от нужной команды. Создавать её заново не нужно, иначе получится дубликат.</p>
        <p class="mb-0 fw-bold text-danger">Создавать новую команду можно, только если она действительно нужна как отдельная, вторая команда — с другим названием и составом.</p>
    </div>

    <p class="mb-2 text-muted">Ваши команды в системе:</p>

    @if($myTeams->isNotEmpty())
        <ul class="list-group mb-3">
            @foreach($myTeams as $team)
                <li class="list-group-item d-flex align-items-center gap-3">
                    @if($team->logo_url)
                        <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" style="width: 32px; height: 32px; object-fit: cover; flex-shrink: 0;">
                    @else
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white fw-bold" style="width: 32px; height: 32px; flex-shrink: 0;">
                            {{ \Illuminate\Support\Str::of($team->name)->trim()->substr(0, 2)->upper() }}
                        </span>
                    @endif
                    <span>{{ $team->name }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="d-flex align-items-center justify-content-between gap-3">
        <span class="text-muted small">Дубликат команды создавать не нужно</span>
        <a href="{{ route('platform.applications.list') }}" class="btn btn-primary">
            Перейти к заявкам
        </a>
    </div>

</div>