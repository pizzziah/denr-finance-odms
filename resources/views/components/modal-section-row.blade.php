@props([
    'title' => '',
    'colTitle' => 'col-2',
    'colContent' => 'col-10',
    'titleClass' => 'fw-bold fs-5',
    'titleColor' => ''
])

<div {{ $attributes->merge(['class' => 'row py-1']) }}>
    <div class="{{ $colTitle }} {{ $titleClass }}" @if($titleColor) style="color: {{ $titleColor }};" @endif>
        {!! $title !!}
    </div>
    <div class="{{ $colContent }}">
        {{ $slot }}
    </div>
</div>