<x-app-layout>
    <div>
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-3xl font-bold tracking-tight text-brand-800">Настройки</h1>
            <a href="{{ route('profile.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:border-slate-300 hover:bg-slate-50">
                @svg('lucide-volleyball', 'h-4 w-4')Мои игры
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="mr-4 bg-indigo-100 rounded-full p-3">
                        @svg('lucide-gauge', 'h-6 w-6 text-indigo-600')
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-900">Панель управления</h3>
                        <p class="text-sm text-gray-600">Заявки, команды, дозаявки и переходы — всё в личном кабинете.</p>
                    </div>
                </div>
                <a href="{{ route(config('platform.index')) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Панель управления
                </a>
            </div>
        </div>

        <x-section-border />

        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')

            <x-section-border />
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div class="mt-10 sm:mt-0">
                @livewire('profile.update-password-form')
            </div>

            <x-section-border />
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div class="mt-10 sm:mt-0">
                @livewire('profile.two-factor-authentication-form')
            </div>

            <x-section-border />
        @endif

        <div class="mt-10 sm:mt-0">
            @livewire('profile.logout-other-browser-sessions-form')
        </div>

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <x-section-border />

            <div class="mt-10 sm:mt-0">
                @livewire('profile.delete-user-form')
            </div>
        @endif
    </div>
</x-app-layout>
