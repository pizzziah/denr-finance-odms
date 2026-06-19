@props([
    'route',
    'icon',
    'label',
])

@php
    $isActive = request()->routeIs($route . '*');
@endphp

<a href="{{ route($route) }}"
   class="d-flex align-items-center gap-2 px-3 py-2 rounded border
          {{ $isActive ? 'bg-success text-white border-success fw-semibold' : 'text-success border-success' }}
          text-decoration-none">

    <i class="{{ $icon }}"></i>
    <span>{{ $label }}</span>
</a>