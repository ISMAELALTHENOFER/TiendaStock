<button {{ $attributes->merge(['type' => 'submit', 'class' => 'action-button w-full sm:w-auto gap-2 px-5 py-3 bg-brand-600 hover:bg-brand-700 text-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-700 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
