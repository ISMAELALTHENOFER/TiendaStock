@props(['status'])

@if ($status)
<x-alert type="success" class="mb-4">
    {{ $status }}
</x-alert>
@endif
