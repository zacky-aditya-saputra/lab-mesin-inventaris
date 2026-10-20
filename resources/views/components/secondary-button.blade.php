<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 whitespace-nowrap px-4 py-2.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium tracking-tight border border-slate-300/90 hover:border-slate-400 shadow-xs active:bg-slate-100 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 disabled:opacity-25 disabled:pointer-events-none transition-all duration-150 cursor-pointer']) }}>
    {{ $slot }}
</button>
