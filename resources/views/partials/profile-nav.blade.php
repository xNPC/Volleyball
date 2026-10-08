<div class="mb-6 flex flex-wrap gap-1 rounded-xl border border-slate-100 bg-slate-50/70 p-2">
    <a href="{{ route('profile.index') }}"
       class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('profile.index') ? 'bg-brand-700 text-white shadow-sm' : 'text-brand-700 hover:bg-slate-200 hover:text-brand-800' }}">
        @svg('lucide-volleyball', 'h-4 w-4')Мои игры
    </a>
    <a href="{{ route('profile.show') }}"
       class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('profile.show') ? 'bg-brand-700 text-white shadow-sm' : 'text-brand-700 hover:bg-slate-200 hover:text-brand-800' }}">
        @svg('lucide-user', 'h-4 w-4')Аккаунт
    </a>
</div>
