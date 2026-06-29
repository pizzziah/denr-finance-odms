@extends('layouts.app')

@section('title', 'Quarterly Summary')

@section('content')
<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">

  {{-- OVERVIEW METRIC CARD STRIPS (Dynamically follows selected filters) --}}
  <div class="row g-3 mb-3">
    {{-- CURRENT BALANCE --}}
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
            <i class="bi bi-database-exclamation"></i>
          </div>  
        </div>
      </div>
    </div>

    {{-- TOTAL RECEIVED --}}
    <div class="col-12 col-sm-6 col-md-4">
      <div class="card glass-card-hover card-c p-0 h-80 border-0 border-start border-4" style="border-color: var(--success) !important;">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-uppercase fw-bold p-0 m-0" style="color: var(--success)">
              Total Received
            </h6>
            <h2 class="fw-bold fs-2 m-0" style="color: var(--success)">
              ₱{{ $totalReceived }}
            </h2>
          </div>
          <div class="fs-1 opacity-60" style="color: var(--success);">
            <i class="bi bi-arrow-down-left-square"></i>
          </div>  
        </div>
      </div>
    </div>

    {{-- TOTAL DOWNLOADED --}}
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
            <i class="bi bi-arrow-up-right-square"></i>
          </div>  
        </div>
      </div>
    </div>
  </div>

  {{-- LOCK ACTION CALLOUT NOTIFICATION NOTICE BANNER --}}
  @if($isLocked)
    <div class="alert alert-warning d-flex align-items-center mb-3 py-2 border-start border-warning border-4" role="alert">
      <i class="bi bi-lock-fill fs-5 me-2"></i>
      <div>
        <strong>Quarter Locked:</strong> This quarter is completed. Form additions and structural ledger entries have been locked.
      </div>
    </div>
  @endif

  {{-- CONTROL FILTERS BAR INTERFACES --}}
  <div class="card p-3 mb-3 m-0 w-100 bg-white shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        @if($isLocked)
          <button type="button" class="btn btn-secondary" onclick="alert('Cannot add entries. This operational quarter table has been closed.');" disabled>
            <i class="bi bi-lock"></i> Quarter Locked
          </button>
        @else
          <button type="button" class="btn" style="background-color: var(--secondary-variant); border: 1px solid var(--primary); color: var(--primary); font-weight: bold;" data-bs-toggle="modal" data-bs-target="#addSummaryModal">
            <i class="bi bi-file-earmark-plus"></i> Add Entry
          </button>
        @endif
      </div>

      <form action="{{ route('accounting.quarterly-summary') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
        <div class="d-flex align-items-center gap-1">
          <label class="small fw-bold text-nowrap mb-0 me-1">Scope View:</label>
          <select name="quarter" class="form-select form-select-sm fw-bold border-secondary" style="min-width: 140px;" onchange="this.form.submit()">
            <option value="1" @selected($selectedQuarter == 1)>1st Quarter @if($currentQuarter > 1) (Locked) @endif</option>
            <option value="2" @selected($selectedQuarter == 2)>2nd Quarter @if($currentQuarter > 2) (Locked) @endif</option>
            <option value="3" @selected($selectedQuarter == 3)>3rd Quarter @if($currentQuarter > 3) (Locked) @endif</option>
            <option value="4" @selected($selectedQuarter == 4)>4th Quarter @if($currentQuarter > 4) (Locked) @endif</option>
          </select>
        </div>

        <div class="input-group input-group-sm" style="min-width:260px;">
          <input type="text" name="search" class="form-control" placeholder="Search Details, DV, ADA..." value="{{ request('search') }}">
          <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
          @if(request('search'))
            <a href="{{ route('accounting.quarterly-summary', ['quarter' => $selectedQuarter]) }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i></a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- LEDGER DATA CONTAINER FEATURING FIXED HEADER HEIGHT AND VERTICAL SCROLLBAR --}}
  <div class="card m-0 w-100 shadow-sm">
    <div class="card-body p-2">
      
      {{-- Vertical scroll frame handling viewport constraint parameters --}}
      <div style="max-height: 520px; overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6;">
        <table class="table table-bordered table-hover table-sm align-middle m-0" style="min-width: 1300px;">
          <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
            <tr>
              <th style="width: 150px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>EMDS Date</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'desc']) }}" 
                      class="btn p-0 px-1 text-white {{ request('sort_date', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}" 
                      title="Sort Recent First">
                      <i class="bi bi-sort-numeric-down"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'asc']) }}" 
                      class="btn p-0 px-1 text-white {{ request('sort_date') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}" 
                      title="Sort Oldest First">
                      <i class="bi bi-sort-numeric-up-alt"></i>
                    </a>
                  </div>
                </div>
              </th>
              <th style="width: 320px;">Particulars</th>
              <th style="width: 120px;">DV No.</th>
              <th style="width: 160px;">NCA/NTA Received</th>
              <th style="width: 160px;">NCA/NTA Downloaded</th>
              <th style="width: 160px; background-color: #212529;">Balance</th>
              <th style="width: 150px;">ADA/Check No.</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            @forelse($records as $record)
              <tr>
                <td class="small">{{ $record->emds_date ?? '-' }}</td>
                <td class="fw-semibold text-wrap" style="word-break: break-word;">{{ $record->particulars ?? '-' }}</td>
                <td>{{ $record->dv_no ?? '-' }}</td>
                <td class="text-success fw-bold">
                  {{ !empty($record->nca_nta_received) ? '₱'.$record->nca_nta_received : '-' }}
                </td>
                <td class="text-danger fw-bold">
                  {{ !empty($record->nca_nta_downloaded) ? '₱'.$record->nca_nta_downloaded : '-' }}
                </td>
                <td class="fw-bold table-active text-primary">
                  ₱{{ $record->balance ?? '0.00' }}
                </td>
                <td >{{ $record->ada_check_no ?? '-' }}</td>
                <td class="text-wrap "><em>{{ $record->remarks ?? '-' }}</em></td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-centerpy-5 fs-6 bg-light">No ledger entries matching parameters logged in this operational quarter.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
@include('accounting.partials.add-entry-modal')
@endsection