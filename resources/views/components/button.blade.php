@props([
  'type' => 'button',
  'variant' => 'primary',
  'as' => 'button', // NEW
])

@php
$base = 'btn btn-sm d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded';

$styles = [
  'primary' => 'background-color: var(--primary); color: var(--background);',
  'secondary' => 'background-color: var(--secondary-variant); color: var(--primary); border: 1px solid var(--primary);',
  'header' => 'background-color: var(--secondary-variant); border: 1px solid var(--primary); color: var(--primary); font-weight: bold;',
  'success' => 'background-color: var(--secondary-variant); border: 1px solid var(--primary); color: var(--primary);',
  'alert' => 'background-color: #ffe3e3; border: 1px solid var(--error); color: var(--error);',
  'edit' => 'background-color: #FFEECC; border: 1px solid #9D6B0B; color: #9D6B0B;',
  'lock' => 'background-color: #BCC3F6; border: 1px solid #271ECE; color: #271ECE;',
];

$style = $styles[$variant] ?? $styles['primary'];
@endphp

@if($as === 'a')
<a {{ $attributes->merge(['class' => $base]) }} style="{{ $style }}">
  {{ $slot }}
</a>
@else
<button type="{{ $type }}"
  {{ $attributes->merge(['class' => $base]) }}
  style="{{ $style }}">
  {{ $slot }}
</button>
@endif