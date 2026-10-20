@section('title', 'Политика конфиденциальности')
@section('description', 'Политика конфиденциальности и использования файлов cookie на портале волейбольных турниров.')
@section('og_type', 'website')
@section('og_title', 'Политика конфиденциальности')
@section('canonical', request()->url())
@php $canonicalUrl = request()->url(); @endphp

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
                        Портал волейбольных турниров уважает вашу конфиденциальность. Оператором
                        персональных данных является физическое лицо — администратор портала. Эта
                        политика объясняет, какие данные сохраняются при посещении сайта и при
                        регистрации, зачем они нужны и как вы можете управлять этим.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">2. Какие данные мы храним</h2>
                    <p class="mt-2">
                        При регистрации вы указываете имя, email и дополнительные данные (дата
                        рождения, пол, город, позиция, фото). Эти данные используются для участия
                        в турнирах и отображаются на портале в составе команд и заявок в
                        соответствии с вашими настройками конфиденциальности. Пароли хранятся
                        в зашифрованном виде.
                    </p>
                    <p class="mt-2">
                        Полный перечень обрабатываемых данных, целей, правовых оснований и порядка
                        обработки установлены в
                        <a href="{{ route('legal.consent') }}" class="font-medium text-accent-600 transition hover:text-accent-700">согласии на обработку персональных данных</a>.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">3. Файлы cookie</h2>
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
                    <h2 class="font-display text-base font-bold text-brand-800">4. Сроки хранения и передача третьим лицам</h2>
                    <p class="mt-2">
                        Персональные данные хранятся с момента регистрации до достижения целей
                        обработки, удаления учётной записи или отзыва согласия. После отзыва согласия
                        обработка прекращается, а данные уничтожаются (обезличиваются) в срок не
                        более 30 календарных дней, за исключением случаев, когда хранение
                        предусмотрено законодательством Российской Федерации.
                    </p>
                    <p class="mt-2">
                        Для работы портала используются хостинг-провайдер и почтовый сервис —
                        они получают данные исключительно в объёме, необходимом для оказания
                        этих услуг. Передача данных иным третьим лицам, а также трансграничная
                        передача персональных данных не осуществляются.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">5. Ваши права</h2>
                    <p class="mt-2">
                        Вы вправе получать информацию об обработке ваших персональных данных,
                        требовать уточнения, блокирования или уничтожения данных, если они неполны,
                        неточны, устарели или получены не в соответствии с законом, а также отозвать
                        согласие на обработку и обжаловать действия Оператора уполномоченному по
                        защите прав субъектов персональных данных или в суд.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">6. Согласие и его отзыв</h2>
                    <p class="mt-2">
                        Нажимая «Принять» в уведомлении о cookie, вы соглашаетесь на загрузку
                        Яндекс.Метрики. Вы можете в любой момент отозвать согласие, очистив
                        файлы cookie и localStorage в настройках браузера — Метрика перестанет
                        загружаться.
                    </p>
                    <p class="mt-2">
                        При регистрации вы даёте согласие на обработку персональных данных на условиях
                        <a href="{{ route('legal.consent') }}" class="font-medium text-accent-600 transition hover:text-accent-700">отдельного документа</a>.
                    </p>
                    <p class="mt-2">
                        Отозвать согласие можно в любой момент через форму обратной связи на сайте
                        с указанием адреса электронной почты, использованного при регистрации.
                        Обработка персональных данных при этом прекращается, а данные уничтожаются
                        в срок не более 30 календарных дней. Отзыв согласия влечёт невозможность
                        дальнейшего использования портала.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-base font-bold text-brand-800">7. Контакты</h2>
                    <p class="mt-2">
                        По вопросам обработки персональных данных вы можете связаться с администрацией
                        портала через форму обратной связи на сайте.
                    </p>
                </section>
            </div>
        </x-card>
    </div>
</x-app-layout>