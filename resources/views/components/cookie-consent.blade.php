<div
    x-data="{
        shown: false,
        init() {
            if (!window.localStorage.getItem('cookie_consent')) {
                this.shown = true;
            }
        },
        accept() {
            window.localStorage.setItem('cookie_consent', 'accepted');
            this.shown = false;
            window.dispatchEvent(new CustomEvent('cookie-consent:accepted'));
        },
        decline() {
            window.localStorage.setItem('cookie_consent', 'declined');
            this.shown = false;
        }
    }"
    x-cloak
    x-show="shown"
    x-transition
>
    <div class="pointer-events-auto fixed inset-x-4 bottom-4 z-50 mx-auto max-w-lg">
        <div class="rounded-xl border border-slate-200/70 bg-white p-5 shadow-card">
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-500/10 text-accent-500">
                    @svg('lucide-cookie', 'h-5 w-5')
                </span>
                <div class="min-w-0">
                    <h3 class="font-display text-sm font-bold text-brand-800">Мы используем cookie</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">
                        Сайт сохраняет данные, необходимые для работы (сессия, безопасность), и, с вашего согласия, использует Яндекс.Метрику для анализа посещаемости.
                    </p>
                    <a href="{{ route('privacy') }}" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-accent-600 transition hover:text-accent-700">
                        Подробнее о политике конфиденциальности
                        @svg('lucide-arrow-right', 'h-3.5 w-3.5')
                    </a>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" @click="accept()" class="inline-flex items-center gap-1.5 rounded-lg bg-accent-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-accent-600">
                    @svg('lucide-check', 'h-4 w-4')Принять
                </button>
                <button type="button" @click="decline()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-slate-50 hover:text-brand-800">
                    Отклонить
                </button>
            </div>
        </div>
    </div>
</div>