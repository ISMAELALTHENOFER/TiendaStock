@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'form-control w-full px-4 py-3 shadow-sm placeholder-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-500']) }}>
