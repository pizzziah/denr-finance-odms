@extends('layouts.app')

@section('title', 'Quarterly Summary')

@section('content')
<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">

  {{-- OVERVIEW METRIC CARD STRIPS --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-md-4">
      <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--primary) !important;">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--primary)">
              Current Balance
            </h6>
            <h2 class="fw-bold fs-2 m-0" style="color: var(--primary)">
              ₱{{ $currentBalance }}
            </h2>
          </div>
          <div class="fs-1 opacity-60" style="color: var(--primary);">
            <i class="bi bi-wallet2"></i>
          </div>  
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
      <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: #9D6B0B !important;">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-uppercase fw-bold p-0 m-0" style="color: #9D6B0B">
              Total Received
            </h6>
            <h2 class="fw-bold fs-2 m-0" style="color: #9D6B0B">
              ₱{{ $totalReceived }}
            </h2>
          </div>
          <div class="fs-1 opacity-60" style="color: #9D6B0B;">
             <i class="bi bi-layer-forward"></i>
            </div>  
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
      <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--error) !important;">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--error)">
              Total Downloaded
            </h6>
            <h2 class="fw-bold fs-2 m-0" style="color: var(--error)">
              ₱{{ $totalDownloaded }}
            </h2>
          </div>
          <div class="fs-1 opacity-60" style="color: var(--error);">
            <i class="bi bi-layer-backward"></i>
          </div>  
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2 mb-3">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger py-2 mb-3">
      {{ session('error') }}
    </div>
  @endif

  @if($isLocked)
    <div class="alert alert-warning d-flex align-items-center mb-3 py-2 border-2" style="background: #FFC2C2; border-color: var(--error); color: var(--error);" role="alert">
      <i class="bi bi-lock-fill fs-5 me-2"></i>
      <div><strong>Quarter Locked:</strong> This quarter is completed. Form additions and structural ledger entries have been locked.</div>
    </div>
  @endif

  {{-- CONTROL FILTERS BAR INTERFACES --}}
  <div class="card p-3 mb-3 m-0 w-100 bg-white shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      
      <div class="d-flex align-items-center gap-2">
        @if($isLocked)
          <button type="button" class="btn btn-sm" style="background: #FFC2C2; border-color: var(--error); color: var(--error);" disabled>
            <i class="bi bi-lock"></i> Quarter Locked
          </button>
        @else
          <x-button type="button" variant="secondary" data-bs-toggle="modal" data-bs-target="#addSummaryModal">
            <i class="bi bi-file-earmark-plus"></i> Add Entry
          </x-button>
        @endif

        @if(auth()->user()->department === 'Accounting' && auth()->user()->permission_level === 'special')
          @if(!$isLocked)
            <x-button type="button" variant="alert" data-bs-toggle="modal" data-bs-target="#lockQuarterModal">
              <i class="bi bi-lock-fill me-1"></i> Lock Quarter
            </x-button>
          @else
            @if($requiresAdminRequest)
                <button type="button"
                        class="btn btn-sm btn-secondary fw-bold shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#cancelUnlockModal">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Unlock Pending
                </button>

            @else

                <form action="{{ route('accounting.quarterly-summary.request-unlock') }}" method="POST">
                    @csrf

                    <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">

                    <button type="submit"
                            class="btn btn-sm btn-warning fw-bold">
                        <i class="bi bi-unlock"></i>
                        Request Unlock
                    </button>
                </form>
            @endif
          @endif
        @endif
      </div>

      <form action="{{ route('accounting.quarterly-summary') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
        <div class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap">
          <label class="small fw-bold text-nowrap mb-0">Scope View:</label>
          
          <select name="year" class="form-select form-select-sm fw-bold border-secondary" style="min-width: 100px;" onchange="this.form.submit()">
            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
              <option value="{{ $y }}" @selected(($selectedYear ?? date('Y')) == $y)>{{ $y }}</option>
            @endfor
          </select>

          <select name="quarter" class="form-select form-select-sm fw-bold border-secondary" style="min-width: 140px;" onchange="this.form.submit()">
            <option value="1" @selected($selectedQuarter == 1)>1st Quarter @if($currentQuarter > 1 && ($selectedYear ?? date('Y')) == date('Y')) (Locked) @endif</option>
            <option value="2" @selected($selectedQuarter == 2)>2nd Quarter @if($currentQuarter > 2 && ($selectedYear ?? date('Y')) == date('Y')) (Locked) @endif</option>
            <option value="3" @selected($selectedQuarter == 3)>3rd Quarter @if($currentQuarter > 3 && ($selectedYear ?? date('Y')) == date('Y')) (Locked) @endif</option>
            <option value="4" @selected($selectedQuarter == 4)>4th Quarter @if($currentQuarter > 4 && ($selectedYear ?? date('Y')) == date('Y')) (Locked) @endif</option>
          </select>
        </div>

        <div class="input-group input-group-sm" style="min-width:260px;">
          <input type="text"
                 name="search"
                 class="form-control"
                 placeholder="Search Details, DV, ADA..."
                 value="{{ request('search') }}"
                 style="border-color:#bebebe;">

          <button class="btn btn-dark" type="submit" style="border-color:#bebebe;">
            <i class="bi bi-search"></i>
          </button>      
          @if(request('search'))
            <a href="{{ route('accounting.quarterly-summary', ['quarter' => $selectedQuarter, 'year' => $selectedYear ?? date('Y')]) }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i></a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- REORDERED LEDGER DATA TABLE --}}
  <div class="card m-0 w-100 shadow-sm">
    <div class="card-body p-2">
      <div style="max-height: 520px; overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6;">
        <table class="table table-bordered table-hover table-sm align-middle m-0" style="min-width: 1450px;">
          <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
            <tr>
              <th style="width: 120px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>Date Processed</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_processed' => 'asc']) }}" class="btn p-0 px-1 text-white {{ request('sort_processed') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_processed' => 'desc']) }}" class="btn p-0 px-1 text-white  {{ request('sort_processed', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th style="width: 120px;">DV Number</th>
              <th style="width: 120px;">Amount</th>
              <th style="width: 160px;">NCA/NTA Received</th>
              <th style="width: 160px;">NCA/NTA Downloaded</th>
              <th style="width: 150px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>EMDS Date</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'asc']) }}" class="btn p-0 px-1 text-white {{ request('sort_date') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'desc']) }}" class="btn p-0 px-1 text-white  {{ request('sort_date', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th style="width: 160px;">Balance</th>
              <th style="width: 100px;">ADA/Check No.</th>
              <th>Remarks</th>
              <th style="width: 150px; text-align: center;">Actions</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              @php
                $rowId = $record->getKey();
                $cleanReceived = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->nca_nta_received);
                $cleanDownloaded = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->nca_nta_downloaded);
                $txType = $cleanReceived > 0 ? 'received' : 'downloaded';
                
if ($cleanReceived > 0) {
    $rawAmount = $cleanReceived;
} elseif ($cleanDownloaded > 0) {
    $rawAmount = $cleanDownloaded;
} else {
    $rawAmount = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->amount);
}
              @endphp

              <tr>
                <td class="small">{{ $record->date_processed ?? '-' }}</td>
                <td style="color: var(--primary);"><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td style="color: #7909FF;">{{ !empty($record->amount) && (float)str_replace(',', '', $record->amount) > 0 ? '₱' . number_format((float)str_replace(',', '', $record->amount), 2) : '-' }}</td>
                <td style="color: #9D6B0B;"><strong>{{ $cleanReceived > 0 ? '₱' . number_format($cleanReceived, 2) : '-' }}</strong></td>
                <td style="color: var(--error);"><strong>{{ $cleanDownloaded > 0 ? '₱' . number_format($cleanDownloaded, 2) : '-' }}</strong></td>
                <td class="small">{{ $record->emds_date ?? '-' }}</td>
                <td style="background-color: var(--secondary-variant); color: var(--primary);"><strong>₱{{ $record->balance ?? '0.00' }}</strong></td>
                <td>{{ $record->ada_no ?? '-' }}</td>
                <td class="text-wrap"><em>{{ $record->remarks ?? '-' }}</em></td>
                <td>
                  <div class="d-flex gap-2 justify-content-center align-items-center">
                    <x-button type="button" variant="edit" data-bs-toggle="modal" data-bs-target="#editSummaryModal{{ $rowId }}" :disabled="$isLocked">
                      <i class="bi bi-pencil-square"></i>
                    </x-button>
                    <x-button type="button" variant="alert" data-bs-toggle="modal" data-bs-target="#deleteRowModal{{ $rowId }}" :disabled="$isLocked">
                      <i class="bi bi-trash3"></i>
                    </x-button>
                  </div>
                </td>
              </tr>

              {{-- INLINE EDIT MODAL GENERATOR PER ROW --}}
              @if(!$isLocked)
                @include('accounting.partials.edit-entry-quarterly-summary-modal', ['record' => $record, 'rowId' => $rowId, 'txType' => $txType, 'rawAmount' => $rawAmount, 'targetQuarter' => $selectedQuarter])
                
                <div class="modal fade" id="deleteRowModal{{ $rowId }}" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                      <div class="modal-header bg-danger text-white py-2">
                        <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Elimination</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-start py-3">
                        <p class="mb-2 text-dark">Are you completely sure you want to permanently delete this structural ledger framework record?</p>
                        <blockquote class="bg-light p-2 border-start border-3 border-danger rounded text-muted small mb-0">
                          <strong>DV Number:</strong> {{ $record->particulars ?? '-' }}<br>
                          <strong>Date:</strong> {{ $record->date_processed ?? '-' }}
                        </blockquote>
                        <p class="text-danger small mt-2 mb-0 fw-bold"><i class="bi bi-info-circle-fill"></i> Balance transaction histories down the stream will automatically calculate and shift.</p>
                      </div>
                      <div class="modal-footer bg-light py-2 gap-2 d-flex justify-content-end">
                        <x-button type="button" variant="secondary" data-bs-dismiss="modal">Cancel</x-button>
                        <form action="{{ route('accounting.quarterly-summary.destroy', $rowId) }}" method="POST" class="m-0">
                          @csrf
                          @method('DELETE')
                          <input type="hidden" name="target_quarter" value="{{ $selectedQuarter }}">
                          <x-button type="submit" variant="primary">Save Changes</x-button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            @empty
              <tr>
                <td colspan="10" class="text-center py-5 small bg-light">No data available for this quarter.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- LOCK QUARTER MODAL --}}
@if(auth()->user()->department === 'Accounting' && auth()->user()->permission_level === 'special' && !$isLocked)
<div class="modal fade" id="lockQuarterModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2">
        <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-lock-fill text-warning me-2"></i>Lock Ledger Structural Framework</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start py-3">
        <p class="mb-2">You are preparing to formalize and lock structural parameters for <strong>Quarter {{ $selectedQuarter }} ({{ $selectedYear ?? date('Y') }})</strong>.</p>
        <p class="text-muted small mb-0">This layout workflow configuration restricts regular operational staff layers from modifying data vectors or introducing new line items until an authorized administrative unlock approval execution occurs.</p>
      </div>
      <div class="modal-footer bg-light py-2 gap-2 d-flex justify-content-end">
        <x-button type="button" variant="secondary" data-bs-dismiss="modal">Cancel</x-button>
        <form action="{{ route('accounting.quarterly-summary.manual-lock') }}" method="POST" class="m-0">
          @csrf
          <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">
          <input type="hidden" name="year" value="{{ $selectedYear ?? date('Y') }}">
          <x-button type="submit" variant="primary">Save Changes</x-button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

@include('accounting.partials.add-entry-quarterly-summary-modal', ['targetQuarter' => $selectedQuarter])

@if($requiresAdminRequest)
<div class="modal fade" id="cancelUnlockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('accounting.quarterly-summary.cancel-unlock') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">

                <div class="modal-body">
                    Are you sure you want to cancel the pending unlock request for Year {{ $selectedYear }} - Q{{ $selectedQuarter }}?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@php
  $pageTitle = 'Quarterly Summary';
@endphp
