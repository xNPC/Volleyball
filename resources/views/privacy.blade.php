<x-app-layout>
    <div class="mx-auto max-w-3xl">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                @svg('lucide-shield-check', 'h-7 w-7')
            </div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800 sm:text-4xl">
                Политика конфиденциальности
            </h1>
            <p class="mt-2 text-slate-500">Как мы используем данные и файлы cookie на портале</p>
        </div>

        <x-card class="p-6 sm:p-8">
            <div class="space-y-6 text-sm leading-relaxed text-slate-600">
                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">1. Введение</h2>
                    <p class="mt-2">
                        Портал волейбольных турниров уважает вашу конфиденциальность. Эта страница
                        объясняет, какие данные сохраняются при посещении сайта, зачем они нужны
                        и как вы можете управлять этим.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">2. Файлы cookie</h2>
                    <p class="mt-2">При посещении сайта в вашем браузере могут сохраняться следующие файлы cookie:</p>
                    <ul class="mt-3 space-y-2.5">
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-700">
                                @svg('lucide-shield', 'h-3.5 w-3.5')
                            </span>
                            <span>
                                <span class="font-semibold text-brand-900">laravel_session</span> — идентификатор сессии. Необходим для работы сайта: авторизация, подача заявок, корзина состава команды. Хранится до закрытия браузера.
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-700">
                                @svg('lucide-shield', 'h-3.5 w-3.5')
                            </span>
                            <span>
                                <span class="font-semibold text-brand-900">XSRF-TOKEN</span> — защита форм от CSRF-атак. Технический cookie.
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-700">
                                @svg('lucide-shield', 'h-3.5 w-3.5')
                            </span>
                            <span>
                                <span class="font-semibold text-brand-900">remember_web_*</span> — «запомнить меня» при входе в личный кабинет, если вы отметили эту опцию.
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-accent-50 text-accent-600">
                                @svg('lucide-bar-chart-3', 'h-3.5 w-3.5')
                            </span>
                            <span>
                                <span class="font-semibold text-brand-900">Яндекс.Метрика (_ym_* и др.)</span> — обезличенная статистика посещаемости. Скрипт Метрики загружается только после того, как вы нажмёте «Принять» в уведомлении о cookie.
                            </span>
                        </li>
                    </ul>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">3. Какие данные мы храним</h2>
                    <p class="mt-2">
                        При регистрации вы указываете имя, email и дополнительные данные (город, позиция,
                        фото). Эти данные используются для участия в турнирах и отображаются на портале
                        в составе команд и заявок. Пароли хранятся в зашифрованном виде.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">4. Согласие и его отзыв</h2>
                    <p class="mt-2">
                        Нажимая «Принять» в уведомлении о cookie, вы соглашаетесь на загрузку
                        Яндекс.Метрики. Вы можете в любой момент отозвать согласие, очистив
                        файлы cookie и сайта, и localStorage в настройках браузера — Метрика перестанет загружаться.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">5. Контакты</h2>
                    <p class="mt-2">
                        По вопросам обработки персональных данных вы можете связаться с администрацией
                        портала через форму обратной связи на сайте.
                    </p>
                </section>
            </div>
        </x-card>
    </div>
</x-app-layout>