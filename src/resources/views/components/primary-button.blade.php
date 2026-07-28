<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-300 hover:bg-brand-400 text-white font-semibold rounded-lg shadow-md hover:shadow-lg hover:shadow-brand-glow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
