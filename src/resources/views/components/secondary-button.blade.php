<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-2 px-6 py-3 bg-white border border-brand-300 text-brand-300 rounded-lg font-semibold shadow-sm hover:bg-brand-50 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
