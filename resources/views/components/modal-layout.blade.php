@props([
    'id',
    'title' => '',
    'icon' => '',
    'size' => 'modal-lg',
    'scrollable' => true,
    'centered' => true,
    'headerBg' => 'var(--primary)',
    'headerClass' => 'text-white',
    'showFooter' => true,
    'formId' => null,
    'action' => null,
    'method' => 'POST',
    'backdrop' => null,
    'keyboard' => null,
    'maxWidth' => null
])

<div 
    class="modal fade" 
    id="{{ $id }}" 
    tabindex="-1" 
    aria-hidden="true"
    @if($backdrop) data-bs-backdrop="{{ $backdrop }}" @endif
    @if($keyboard !== null) data-bs-keyboard="{{ $keyboard ? 'true' : 'false' }}" @endif
>
    <div 
        class="modal-dialog {{ $size }} {{ $centered ? 'modal-dialog-centered' : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }}"
        @if($maxWidth) style="max-width: {{ $maxWidth }};" @endif
    >
        <div class="modal-content">
            
            {{-- Header --}}
            <div class="modal-header {{ $headerClass }}" style="background-color: {{ $headerBg }};">
                <h5 class="modal-title fw-bold">
                    @if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $title }}
                </h5>
                <button type="button" class="btn-close {{ str_contains($headerClass, 'text-white') ? 'btn-close-white' : '' }}" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Optional Sub-header / Top Slot --}}
            @if(isset($topSlot))
                <div class="px-4 mt-2">
                    {{ $topSlot }}
                </div>
                <hr class="my-2">
            @endif

            {{-- Body --}}
            <div class="modal-body">
                @if($formId && $action)
                    <form id="{{ $formId }}" method="POST" action="{{ $action }}">
                        @csrf
                        @if(!in_array(strtoupper($method), ['GET', 'POST']))
                            @method($method)
                        @endif
                        {{ $slot }}
                    </form>
                @else
                    {{ $slot }}
                @endif
            </div>

            {{-- Footer --}}
            @if($showFooter)
                <div class="modal-footer bg-light">
                    @if(isset($footer))
                        {{ $footer }}
                    @else
                        <x-button type="button" variant="secondary" data-bs-dismiss="modal">Cancel</x-button>
                        @if($formId)
                            <x-button type="submit" variant="primary" form="{{ $formId }}">Save Record</x-button>
                        @endif
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>