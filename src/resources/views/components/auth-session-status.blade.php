@props(['status'])

@if ($status)
<div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
    {{ $status }}
</div>
@endif
