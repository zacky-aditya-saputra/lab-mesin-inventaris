<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 whitespace-nowrap px-4 py-2.5 rounded-lg bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white text-sm font-medium tracking-tight shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none transition-all duration-150 cursor-pointer']) }}>
    {{ $slot }}
</button>
