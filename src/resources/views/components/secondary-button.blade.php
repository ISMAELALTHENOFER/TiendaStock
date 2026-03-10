<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-2 px-6 py-3 bg-white border border-purple-200 rounded-lg font-semibold text-purple-700 shadow-sm hover:bg-purple-50 hover:shadow-md hover:border-purple-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
