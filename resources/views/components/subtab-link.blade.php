@props([
    'status',
    'label',
])

@php
    $isActive = request('status', 'all') == $status;

    $params = array_merge(request()->query(), [
        'status' => $status
    ]);
@endphp

<a href="{{ request()->url() . '?' . http_build_query($params) }}"
   class="subtab-link {{ $isActive ? 'active' : '' }}">
    {{ $label }}
</a>

<style>
.subtab-link{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:.75rem 1.25rem;

    border:1px solid var(--primary);
    border-radius:8px;

    color:var(--primary);
    background:var(--secondary-variant);

    text-decoration:none;
    transition:.2s;
}

.subtab-link:hover{
    background:var(--secondary-variant);
}

.subtab-link.active{
    background:var(--primary);
    color:white;
    font-weight:bold;
}
</style>