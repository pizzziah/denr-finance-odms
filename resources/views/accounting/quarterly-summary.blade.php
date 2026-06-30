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

  @if($isLocked)
    <div class="alert alert-warning d-flex align-items-center mb-3 py-2 border-2" style="background: #FFC2C2; border-color: var(--error); color: var(--error);" role="alert">
      <i class="bi bi-lock-fill fs-5"></i>
      <div><strong>Quarter Locked:</strong> This quarter is completed. Form additions and structural ledger entries have been locked.</div>
    </div>
  @endif

  {{-- CONTROL FILTERS BAR INTERFACES --}}
  <div class="card p-3 mb-3 m-0 w-100 bg-white shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        @if($isLocked)
          <button type="button" class="btn"  style="background: #FFC2C2; border-color: var(--error); color: var(--error);"  disabled>
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
            <a href="{{ route('accounting.quarterly-summary', ['quarter' => $selectedQuarter]) }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i></a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- LEDGER DATA CONTAINER FEATURING FIXED HEADER HEIGHT AND VERTICAL SCROLLBAR --}}
  <div class="card m-0 w-100 shadow-sm">
    <div class="card-body p-2">
      <div style="max-height: 520px; overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6;">
        <table class="table table-bordered table-hover table-sm align-middle m-0" style="min-width: 1450px;">
          <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
            <tr>
              <th style="width: 150px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>EMDS Date</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'asc']) }}" class="btn p-0 px-1 text-white {{ request('sort_date') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'desc']) }}" class="btn p-0 px-1 text-white  {{ request('sort_date', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th style="width: 150px;">Date Processed</th>
              <th style="width: 200px;">Particulars</th>
              <th style="width: 120px;">Amount</th>
              <th style="width: 160px;">NCA/NTA Received</th>
              <th style="width: 160px;">NCA/NTA Downloaded</th>
              <th style="width: 160px;">Balance</th>
              <th style="width: 150px;">ADA/Check No.</th>
              <th>Remarks</th>
              <th style="width: 110px; text-align: center;">Actions</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)

              @php
                  $rowId = $record->getKey();

                  $cleanReceived = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->nca_nta_received);
                  $cleanDownloaded = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->nca_nta_downloaded);
                  
                  $txType = $cleanReceived > 0 ? 'received' : 'downloaded';
              @endphp

              <tr>
                <td class="small">{{ $record->emds_date ?? '-' }}</td>
                <td class="small">{{ $record->date_processed ?? '-' }}</td>
                <td class="fw-semibold text-wrap" style="word-break: break-word;">{{ $record->particulars ?? '-' }}</td>
                <td class="fw-bold text-end">
                  {{ !empty($record->amount) ? '₱' . number_format((float)str_replace(',', '', $record->amount), 2) : '-' }}
                </td>
                <td class="text-success fw-bold">
                  {{ $cleanReceived > 0 ? '₱' . number_format($cleanReceived, 2) : '-' }}
                </td>
                <td class="text-danger fw-bold">
                  {{ $cleanDownloaded > 0 ? '₱' . number_format($cleanDownloaded, 2) : '-' }}
                </td>

                <td class="fw-bold table-success text-success">₱{{ $record->balance ?? '0.00' }}</td>
                <td>{{ $record->ada_no ?? '-' }}</td>
                <td class="text-wrap"><em>{{ $record->remarks ?? '-' }}</em></td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1" title="Edit row"
                            data-bs-toggle="modal" data-bs-target="#editSummaryModal{{ $rowId }}" @disabled($isLocked)>
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <form action="{{ route('accounting.quarterly-summary.destroy', ['id' => $rowId]) }}" method="POST" onsubmit="return confirm('Delete this ledger row record? Balance histories will be automatically shifted.')">
                        @csrf
                      @method('DELETE')
                      <input type="hidden" name="target_quarter" value="{{ $selectedQuarter }}">
                      <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-1" title="Delete row" @disabled($isLocked)>
                        <i class="bi bi-trash3"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>

              {{-- INLINE EDIT MODAL GENERATOR PER ROW --}}
              @if(!$isLocked)
                @php
    $rowId = $record->getKey();

    $received = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney(
        $record->nca_nta_received
    );

    $downloaded = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney(
        $record->nca_nta_downloaded
    );

    if ($received > 0) {
        $txType = 'received';
        $rawAmount = $received;
    } else {
        $txType = 'downloaded';
        $rawAmount = $downloaded;
    }
@endphp
              @endif
            @empty
              <tr>
                <td colspan="9" class="text-center py-5 small bg-light">No data available for this quarter.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('accounting.partials.add-entry-quarterly-summary-modal')

@endsection