@props([
    'route',
    'icon',
    'label',
])

@php
    $isActive = request()->routeIs($route . '*');
@endphp

<style>
    
.sidebar {
    width: 280px;
    background: var(--surface);
    transition: width .3s ease;
    overflow-x: hidden;
    flex-shrink: 0;  
}

.sidebar.collapsed {
    width: 90px !important;
}

.sidebar.collapsed .sidebar-text {
    display: none !important;
}

.sidebar.collapsed .sidebar-link {
    justify-content: center !important;
    gap: 0 !important;
    padding: .9rem 0 !important;
}

.sidebar.collapsed .logout-btn {
    width: 100% !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

.sidebar.collapsed .sidebar-brand {
    justify-content: center !important;
    padding: 0 !important;
}

.sidebar.collapsed .sdbr-logo {
    margin: auto !important;
    max-width: 45px; /* Keeps the DENR logo from looking huge when text vanishes */
}

.sidebar.collapsed .collapsed-icon {
    display: block !important;
}

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
    <span class="sidebar-text">{{ $label }}</span>
</a>