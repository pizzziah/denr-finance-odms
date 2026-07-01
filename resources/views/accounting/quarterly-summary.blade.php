@extends('layouts.app')
@section('title', 'Quarterly Summary')
@section('content')
<div class="container-fluid mt-4 px-4">
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card glass-card p-3">
    
    <div class="px-3 pt-3 pb-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <h5 class="fw-bold m-0">Quarterly Summary</h5>
      
      <div class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
        @if(!$isLocked && auth()->user()->department === 'Accounting')
          <form action="{{ route('accounting.quarterly-summary.manual-lock') }}" method="POST" class="m-0 me-1">
            @csrf
            <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold shadow-sm">
              <i class="bi bi-lock-fill me-1"></i> Lock Quarter
            </button>
          </form>
        @endif

        @if($isLocked && auth()->user()->department === 'Accounting')
          <form action="{{ route('accounting.quarterly-summary.request-unlock') }}" method="POST" class="m-0 me-1">
            @csrf
            <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            <button type="submit" class="btn btn-sm btn-warning fw-bold shadow-sm" @disabled($requiresAdminRequest)>
              <i class="bi bi-shield-lock-fill me-1"></i> {{ $requiresAdminRequest ? 'Unlock Pending' : 'Request Unlock' }}
            </button>
          </form>
        @endif

        <form action="{{ route('accounting.quarterly-summary') }}" method="GET" class="d-flex align-items-center gap-2 m-0 flex-wrap flex-md-nowrap">
          <select name="quarter" class="form-select form-select-sm" style="min-width: 120px;" onchange="this.form.submit()">
            @for ($q = 1; $q <= 4; $q++)
              <option value="{{ $q }}" {{ $selectedQuarter == $q ? 'selected' : '' }}>Quarter {{ $q }}</option>
            @endfor
          </select>

          <select name="year" class="form-select form-select-sm" style="min-width: 120px;" onchange="this.form.submit()">
            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
              <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>

          <div class="input-group input-group-sm" style="min-width: 260px;">
            <input type="text"
                   name="search"
                   class="form-control p-1"
                   placeholder="Search particulars..."
                   value="{{ request('search') }}"
                   style="border-color:#bebebe;">

            <button class="btn btn-dark" type="submit" style="border-color:#bebebe;">
              <i class="bi bi-search"></i>
            </button>  
            @if(request('search') || request('quarter') || request('year'))
              <a href="{{ route('accounting.quarterly-summary') }}" class="btn btn-outline-danger" title="Clear Filters">
                <i class="bi bi-x-circle"></i>
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>

    <div class="ms-3 me-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
      <x-button variant="header" data-bs-toggle="modal" data-bs-target="#addSummaryEntryModal" :disabled="$isLocked">
        <i class="bi bi-plus-circle-fill me-1"></i> Add Record Entry
      </x-button>

      <div class="d-flex gap-3 text-end small border p-2 rounded bg-light shadow-sm">
        <div>Total Received: <span class="fw-bold text-success">₱{{ $totalReceived }}</span></div>
        <div class="border-start ps-3">Total Downloaded: <span class="fw-bold text-danger">₱{{ $totalDownloaded }}</span></div>
        <div class="border-start ps-3">Running Balance: <span class="fw-bold text-dark">₱{{ $currentBalance }}</span></div>
      </div>
    </div>

    <div class="card-body" style="background-color: transparent;">
      <table class="table table-bordered table-hover align-middle">
        <thead class="sticky-top">
          <tr>
            <th>
              <div class="d-flex align-items-center justify-content-between dropdown-toggle-no-caret">
                <span>EMDS Date</span>
                <div class="d-flex flex-column line-height-1 small ps-2">
                  <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'asc', 'sort_processed' => null]) }}" class="text-dark p-0 m-0 line-height-1 {{ request('sort_date') === 'asc' ? 'fw-bold' : 'opacity-50' }}" style="font-size: 10px;"><i class="bi bi-caret-up-fill"></i></a>
                  <a href="{{ request()->fullUrlWithQuery(['sort_date' => 'desc', 'sort_processed' => null]) }}" class="text-dark p-0 m-0 line-height-1 {{ request('sort_date') === 'desc' ? 'fw-bold' : 'opacity-50' }}" style="font-size: 10px;"><i class="bi bi-caret-down-fill"></i></a>
                </div>
              </div>
            </th>
            <th>
              <div class="d-flex align-items-center justify-content-between dropdown-toggle-no-caret">
                <span>Date Processed</span>
                <div class="d-flex flex-column line-height-1 small ps-2">
                  <a href="{{ request()->fullUrlWithQuery(['sort_processed' => 'asc', 'sort_date' => null]) }}" class="text-dark p-0 m-0 line-height-1 {{ request('sort_processed') === 'asc' ? 'fw-bold' : 'opacity-50' }}" style="font-size: 10px;"><i class="bi bi-caret-up-fill"></i></a>
                  <a href="{{ request()->fullUrlWithQuery(['sort_processed' => 'desc', 'sort_date' => null]) }}" class="text-dark p-0 m-0 line-height-1 {{ request('sort_processed') === 'desc' ? 'fw-bold' : 'opacity-50' }}" style="font-size: 10px;"><i class="bi bi-caret-down-fill"></i></a>
                </div>
              </div>
            </th>
            <th>Particulars</th>
            <th>Amount (Adj)</th>
            <th>NCA/NTA Received</th>
            <th>NCA/NTA Downloaded</th>
            <th>Balance</th>
            <th>ADA/Check No.</th>
            <th>Remarks</th>
            <th width="140">Actions</th>
          </tr>
        </thead>

        <tbody>
        @forelse($records as $record)
          @php
            $rowId = $record->getKey();
            $cleanReceived = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->nca_nta_received);
            $cleanDownloaded = \App\Models\Accounting\AccountingQuarterlySummary::parseMoney($record->nca_nta_downloaded);
            
            $txType = 'adjustment';
            if ($cleanReceived > 0) { $txType = 'received'; }
            elseif ($cleanDownloaded > 0) { $txType = 'downloaded'; }
            
            $rawAmount = 0.00;
            if ($txType === 'adjustment') {
                $rawAmount = (float)str_replace(',', '', $record->amount ?? 0);
            } else {
                $rawAmount = $cleanReceived > 0 ? $cleanReceived : $cleanDownloaded;
            }
          @endphp
          <tr>
            <td>{{ $record->emds_date }}</td>
            <td>{{ $record->date_processed }}</td>
            <td class="text-wrap" style="max-width: 240px;">{{ $record->particulars }}</td>
            <td class="text-end fw-bold">
              {{ !empty($record->amount) && (float)str_replace(',', '', $record->amount) > 0 ? '₱' . number_format((float)str_replace(',', '', $record->amount), 2) : '-' }}
            </td>
            <td class="text-end text-success fw-bold">
              {{ $cleanReceived > 0 ? '₱' . number_format($cleanReceived, 2) : '-' }}
            </td>
            <td class="text-end text-danger fw-bold">
              {{ $cleanDownloaded > 0 ? '₱' . number_format($cleanDownloaded, 2) : '-' }}
            </td>
            <td class="text-end fw-bold" style="background-color: var(--secondary-variant); color: var(--primary); border-left: 2px solid var(--primary);">
              ₱{{ $record->balance }}
            </td>
            <td>{{ $record->ada_no ?? '-' }}</td>
            <td class="text-wrap" style="max-width: 200px;">{{ $record->remarks ?? '-' }}</td>
            <td>
              <div class="d-flex gap-2 justify-content-center align-items-center">
                <x-button variant="edit" type="button" class="px-2" data-bs-toggle="modal" data-bs-target="#editSummaryModal{{ $rowId }}" :disabled="$isLocked">
                  <i class="bi bi-pencil-square"></i>
                </x-button>

                <x-button variant="alert" type="button" class="px-2" data-bs-toggle="modal" data-bs-target="#deleteSummaryModal{{ $rowId }}" :disabled="$isLocked">
                  <i class="bi bi-trash3"></i>
                </x-button>

                <div class="modal fade" id="deleteSummaryModal{{ $rowId }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-start">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold m-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Record Removal</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-dark py-3">
                        <p class="mb-1">Are you sure you want to remove this transaction entry from the summary matrix ledger?</p>
                        <strong class="text-danger">{{ $record->particulars }}</strong>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                          <i class="bi bi-info-circle-fill me-1"></i> Running balance dependencies below this entry will step through cascading updates immediately.
                        </div>
                      </div>
                      <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('accounting.quarterly-summary.destroy', $rowId) }}" method="POST" class="m-0">
                          @csrf
                          @method('DELETE')
                          <input type="hidden" name="target_quarter" value="{{ $selectedQuarter }}">
                          <button type="submit" class="btn btn-danger btn-sm fw-bold">Delete Entry</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>

          @if(!$isLocked)
            @include('accounting.partials.edit-entry-quarterly-summary-modal', ['record' => $record, 'rowId' => $rowId, 'txType' => $txType, 'rawAmount' => $rawAmount, 'targetQuarter' => $selectedQuarter])
          @endif
        @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-4 bg-light">No ledger entries registered matching current quarterly criteria parameters.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('accounting.partials.add-entry-quarterly-summary-modal', ['targetQuarter' => $selectedQuarter])
@endsection

@php
  $pageTitle = 'Quarterly Summary';
@endphp