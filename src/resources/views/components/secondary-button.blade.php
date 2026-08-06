<button {{ $attributes->merge(['type' => 'button', 'class' => 'action-button w-full sm:w-auto gap-2 px-5 py-3 bg-white border border-stone-300 text-slate-700 shadow-sm hover:bg-brand-50 hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-700 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
