@props([
  'user' => null,
  'selectedYear' => null,
  'selectedMonth' => null,
  'showFilters' => true
])

@php
  // Fallback if $currentUser was passed instead of $user
  $userObject = $user ?? $currentUser ?? null;
  $roleName = $userObject->role ?? 'User';

  // Format the role text consistently across dashboards
  if (in_array(strtolower($roleName), ['accountant', 'bookkeeper'])) {
    $displayRole = ucwords(strtolower($roleName));
  } elseif (strtolower($roleName) === 'accounting') {
    $displayRole = 'Accounting Team';
  } else {
    $displayRole = ucwords(str_replace('_', ' ', $roleName));
  }
@endphp

<div class="card glass-card-green card-a p-4 mb-4 text-white">
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
    <div>
      <h4 class="fw-bold mb-1">
        Welcome Back, {{ $displayRole }}!
      </h4>
      <h6 class="date mb-0 opacity-75">
        <i class="bi bi-calendar3-range"></i> {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y') }} | {{ \Carbon\Carbon::now('Asia/Manila')->format('h:i A') }}
      </h6>
    </div>

    {{-- Inject custom filters here if slot is filled and showFilters is true --}}
    @if($showFilters && isset($filters))
    <div class="bg-white p-2 rounded shadow-sm d-flex flex-column flex-sm-row align-items-sm-center gap-2 m-0" style="min-width: 320px;">
      {{ $filters }}
    </div>
    @endif
  </div>
</div>