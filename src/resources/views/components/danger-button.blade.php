<button {{ $attributes->merge(['type' => 'submit', 'class' => 'action-button w-full sm:w-auto gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
