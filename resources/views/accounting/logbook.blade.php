@extends('layouts.app')

@section('title', 'Log Book')

@section('content')
@php
  $showStatusColumn = request('status', 'all') === 'all';
@endphp
<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">
  {{-- ERROR / SUCCESS MESSAGES --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
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

  {{-- TOP BAR --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    @include('layouts.subtab')
  </div>

  {{-- 1ST CARD --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div class="col-auto">
          @if($showStatusColumn)
              <x-button variant="header"
                        data-bs-toggle="modal"
                        data-bs-target="#addRecordModal">
                  <i class="bi bi-file-earmark-plus"></i>
                  Add Record
              </x-button>
          @endif
      </div>

      {{-- SEARCH + FILTER --}}
      <form action="{{ route('accounting.logbook') }}"
            method="GET"
            class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
        <input type="hidden" name="month" value="{{ request('month','all') }}">
        <input type="hidden" name="status" value="{{ request('status','all') }}">
        <input type="hidden" name="sort" value="{{ request('sort','latest') }}">
        <input type="hidden" name="year" value="{{ request('year','all') }}">

        {{-- FILTER BUTTON --}}
        <button type="button"
                class="btn p-1"
                data-bs-toggle="modal"
                data-bs-target="#filterModal"
                style="min-width:100px;border-color:#bebebe;">
          <small>
            <i class="bi bi-funnel"></i> Filter
          </small>
        </button>

        {{-- SORT BUTTON --}}
        <button type="button"
                class="btn p-1"
                data-bs-toggle="modal"
                data-bs-target="#sortModal"
                style="min-width:100px;border-color:#bebebe;">
            <small>
                <i class="bi bi-sort-down"></i> Sort
            </small>
        </button>

        {{-- SEARCH INPUT --}}
        <div class="input-group input-group-sm" style="min-width:260px;">
          <input type="text"
                 name="search"
                 class="form-control p-1"
                 placeholder="Search DV, OBR, Payee..."
                 value="{{ request('search') }}"
                 style="border-color:#bebebe;">

          <button class="btn btn-dark" type="submit" style="border-color:#bebebe;">
            <i class="bi bi-search"></i>
          </button>  

          {{-- RESET --}}
          @if(request('search') || request('year') !== 'all' || request('month') !== 'all' || request('status') !== 'all' || request('sort') !== 'latest')
            <a href="{{ route('accounting.logbook') }}"
               class="btn"
               title="Clear Filters"
               style="border-color:var(--error)">
              <i class="bi bi-x-circle"></i>
            </a>
          @endif
        </div>

      </form>
    </div>
  </div>

  {{-- 2ND CARD --}}
  <div class="card m-0 w-100" style="min-height: 55vh; display: grid; min-width: 0;">
    <div class="card-body p-3" style="min-width: 0;">
      <div class="table-responsive" style="max-height: 60vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table table-bordered table-hover table-sm align-middle m-0" id="accountingTable">
          <thead class="table-dark sticky-top" style="z-index: 5;">
            <tr>
              <th style="min-width:100px;">Date Received</th>
              <th style="min-width: 70px;">DV No.</th>
              <th style="min-width:100px;">Date Processed</th>
              <th style="min-width:100px;">OBR Date</th>
              <th style="min-width: 70px;">OBR No.</th>
              <th style="min-width: 160px;">Payee</th>
              <th style="min-width: 280px;">Particulars</th>
              <th style="min-width: 210px;">Particulars Remark</th>
              <th style="min-width: 130px;">Amount</th>
              <th style="min-width: 150px;">Status</th>
              <th style="min-width: 200px;">Returned Remarks</th>
              <th style="min-width:120px;">Accounting Entries</th>
              <th style="min-width: 100px;">Signed</th>
              <th style="min-width:100px;">Date Signed</th>
              <th style="min-width:100px;">Date Forwarded</th>
              <th style="min-width: 150px;">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
            
            @php
                      $status = trim($record->status);
                      $statusStyles = match($status) {
                        'Pending'              => 'background-color: #FFEECC; color: #9D6B0B;',
                        'Processing'           => 'background-color: #FFDEC5; color: #BB400D;',
                        'Returned to End User' => 'background-color: #EFDFFF; color: #7909FF;',
                        'Returned to Budget'   => 'background-color: #EBFEFF; color: #0B879D;',
                        'Paid'                 => 'background-color: #DEF5C4; color: var(--secondary);',
                        'Forwarded to Cashier' => 'background-color: var(--secondary-variant); color: var(--primary);',
                        default                => 'background-color: #F8F9FA; color: #6C757D;'
                      };
                    @endphp
               <tr 
               @if(request('highlight')==$record->transaction_id) class="table-warning" @endif
               >
                <td>
                  {{-- Display Date with Time --}}
                  @if(!empty($record->date_received) && $record->date_received !== '-')
                    {{ date('Y-m-d h:i A', strtotime($record->date_received)) }}
                  @else
                    -
                  @endif
                </td>
                <td style="color: #9D6B0B; background-color:#FFEECC"><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td>
                  {{-- Display Date with Time --}}
                  @if(!empty($record->date_processed) && $record->date_processed !== '-')
                    {{ date('Y-m-d h:i A', strtotime($record->date_processed)) }}
                  @else
                    -
                  @endif
                </td>
                <td>{{ $record->obr_date ?? '-' }}</td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->ors_no ?? $record->obr_no }}</strong></td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td><i>{{ $record->particulars_remark ?? '-' }}</i></td>
                <td class="fw-bold">
                    ₱{{ number_format((float) str_replace(',', '', $record->total_debit ?? 0), 2) }}
                </td>
                {{-- STATUS COLUMN --}}
                <td>
                  @if(!empty($record->status))
                    
                    <span class="badge fw-bold" style="{{ $statusStyles }}; font-size: 1em;" >{{ $status }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>

                {{-- RETURNED REMARKS COLUMN --}}
                <td><i>{{ $record->returned_remarks ?? '-' }}</i></td>

                <td class="text-center">
                    <span class="badge fw-bold" style="{{ $statusStyles }}; background-color: #BCC3F6; color: #271ECE; font-size: 1em;">
                        {{ $record->total_entries }} Entries
                    </span>
                </td>
                
                {{-- SIGNED COLUMN (Ready for Yes/No DB field addition) --}}
                <td>
                  @if(isset($record->signed) && !is_null($record->signed))
                    @php
                      $signedVal = trim(strtolower($record->signed));
                    @endphp
                    @if($signedVal === 'yes' || $signedVal === '1' || $record->signed === true)
                      <span class="badge px-2 py-1 small fw-bold" style="background-color: var(--secondary-variant); color: var(--primary);">Yes</span>
                    @elseif($signedVal === 'no' || $signedVal === '0' || $record->signed === false)
                      <span class="badge px-2 py-1 small fw-bold" style="background-color: #FFC2C2; color: var(--error);">No</span>
                    @else
                      <span class="badge px-2 py-1 small bg-light text-dark fw-bold">{{ $record->signed }}</span>
                    @endif
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>

                <td>
                  {{-- Display Date with Time --}}
                  @if(!empty($record->date_signed) && $record->date_signed !== '-')
                    {{ date('Y-m-d h:i A', strtotime($record->date_signed)) }}
                  @else
                    -
                  @endif
                </td>
                <td>
                  {{-- Display Date with Time --}}
                  @if(!empty($record->date_forwarded) && $record->date_forwarded !== '-')
                    {{ date('Y-m-d h:i A', strtotime($record->date_forwarded)) }}
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if(!empty($record->transaction_id))
                  <div class="d-flex gap-1 justify-content-center">
                      <button type="button"
                              class="btn btn-sm btn-outline-info action-btn"
                              data-action="view"
                              data-dv="{{ $record->transaction_id }}"
                              data-entries="{{ $record->total_entries }}"
                              data-amount="{{ $record->total_credit }}"
                              data-payee="{{ $record->payee }}"
                              data-status="{{ $record->status }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-eye"></i>
                      </button>
                      <button type="button"
                              class="btn btn-sm btn-outline-primary action-btn"
                              data-action="edit"
                              data-dv="{{ $record->transaction_id }}"
                              data-status="{{ $record->status }}"
                              data-bs-toggle="modal"
                              data-bs-target="#editRecordModal">
                          <i class="bi bi-pencil"></i>
                      </button>
                      <button type="button"
                              class="btn btn-sm btn-outline-danger action-btn"
                              data-action="delete"
                              data-dv="{{ $record->transaction_id }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-trash"></i>
                      </button>
                  </div>
                  @else
                      <span class="text-muted">No DV No.</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr class="empty-row-placeholder">
                <td colspan="16" class="text-center text-muted py-3">
                  No records found matching parameters.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      @if(method_exists($records, 'links'))
        <div class="mt-3">
          {{ $records->withQueryString()->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@include('accounting.partials.action-modal')
@include('accounting.partials.details-modal')
@include('accounting.partials.scripts')
@include('accounting.partials.edit-entry-modal')
@include('accounting.partials.add-entry-modal')
@endsection

@php
  $pageTitle = 'Logbook';
@endphp