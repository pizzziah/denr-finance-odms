@props([
  'statuses' => [],
  'metrics' => [],
  'timelineLabel' => '',
  'rowClass' => 'row row-cols-2 row-cols-sm-3 row-cols-md-6 g-2 justify-content-center',
  'colClass' => 'col',
  'cardMinHeight' => '110px'
])

@php
  $totalSum = array_sum($metrics['statusCounts'] ?? []);
  $totalSum = $totalSum > 0 ? $totalSum : 1;
@endphp

<div class="card glass-card card-h p-3">
  <h6 class="fw-bold mb-0 p-0 text-center text-uppercase" style="color: var(--primary)">
    Workflow Status
  </h6>
  <p class="mb-3 text-center">
    <small><i>{{ $timelineLabel }}</i></small>
  </p>
  
  <div class="{{ $rowClass }} text-center">
    @foreach($statuses as $status)
    @php
      $count = $metrics['statusCounts'][$status['key']] ?? 0;
      $percentage = ($count / $totalSum) * 100;
      $offset = 113 - (113 * $percentage) / 100;
    @endphp
    
    <div class="{{ $colClass }}">
      <div class="p-0 py-2 border rounded h-100 d-flex flex-column align-items-center justify-content-center" 
        style="border-color: {{ $status['color'] }} !important; background: {{ $status['bg'] }}; min-height: {{ $cardMinHeight }}; cursor: pointer;">
        <div class="position-relative mb-2" style="width: 60px; height: 60px;">
          <svg class="w-100 h-100" viewBox="0 0 40 40" style="transform: rotate(-90deg);">
            <circle cx="20" cy="20" r="18" fill="transparent" stroke="rgba(0,0,0,0.05)" stroke-width="3"></circle>
            <circle cx="20" cy="20" r="18" fill="transparent" 
              stroke="{{ $status['color'] }}" stroke-width="3" stroke-dasharray="113" stroke-dashoffset="{{ $offset }}" stroke-linecap="round" style="transition: stroke-dashoffset 0.5s ease-in-out;">
            </circle>
          </svg>
          <div class="position-absolute top-50 start-50 translate-middle fw-bold" style="color: {{ $status['color'] }}; font-size: 1.1rem;">
            {{ $count }}
          </div>
        </div>
        
        <span class="small text-muted d-block fw-semibold text-center px-1" style="font-size: 0.70rem; color: {{ $status['color'] }} !important; line-height: 1.1;">
          {{ $status['label'] }}
        </span>
      </div>
    </div>
    @endforeach
  </div>
</div>