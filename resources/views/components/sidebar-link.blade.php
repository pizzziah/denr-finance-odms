@props([
    'route',
    'icon',
    'label',
])

@php
    $isActive = request()->routeIs($route . '*');
@endphp

<style>
.sidebar-link{
    display: flex;
    gap: 1em;
    padding: .9rem 1rem;
    align-items: center;
    border: 1px solid var(--primary);
    border-radius: 8px;
    color: var(--primary);
    transition: .2s;
    font-size: large;
    text-decoration: none;
}

.sidebar-link:hover{
    background: var(--secondary-variant);
}

.sidebar-link.active{
    background: var(--primary);
    color: white;
    font-weight: bold;
}

.sidebar-link.active i{
    color: white;
}
</style>

<a href="{{ route($route) }}"
   class="sidebar-link {{ $isActive ? 'active' : '' }}">

    <i class="{{ $icon }}"></i>
    <span>{{ $label }}</span>
</a>