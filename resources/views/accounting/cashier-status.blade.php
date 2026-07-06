@extends('layouts.app')

@section('content')

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">


  {{-- FILTER & SEARCH CARD --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <h5 class="m-0 text-muted"><i class="bi bi-wallet2 text-primary"></i> Pending Cashier Settlements</h5>

      {{-- SEARCH + SORT FORM --}}
      <form action="{{ route('accounting.cashier-status') }}"
            method="GET"
            class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
        <input type="hidden" name="sort" value="{{ request('sort','latest') }}">

        {{-- SORT SELECTOR UTILITY --}}
        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="border-color:#bebebe; width: 140px;">
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Processed</option>
            <option value="obr_asc" {{ request('sort') == 'obr_asc' ? 'selected' : '' }}>DV No. (Asc)</option>
            <option value="obr_desc" {{ request('sort') == 'obr_desc' ? 'selected' : '' }}>DV No. (Desc)</option>
        </select>

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
          @if(request('search') || request('sort') !== 'latest')
            <a href="{{ route('accounting.cashier-status') }}"
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

  {{-- DATA TABLE CARD --}}
  <div class="card m-0 w-100" style="min-height: 55vh; display: grid; min-width: 0;">
    <div class="card-body p-3" style="min-width: 0;">
      
      <div class="table-responsive" style="max-height: 60vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table table-bordered table-hover table-sm align-middle m-0">
          <thead class="table-dark sticky-top" style="z-index: 5;">
            <tr>
              <th style="min-width: 80px;">DV No.</th>
              <th>Date Received</th>
              <th>Date Processed</th>
              <th style="min-width: 70px;">OBR No.</th>
              <th style="min-width: 160px;">Payee</th>
              <th style="min-width: 300px;">Particulars</th>
              <th style="min-width: 160px;">Particulars Remark</th>
              <th style="min-width:130px;">Amount</th>
              <th style="min-width: 150px;">Status</th>
              <th style="min-width:120px;">Accounting Entries</th>
              <th style="min-width: 150px;" class="text-center">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              <tr>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td>{{ $record->date_processed ?? '-' }}</td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td><i>{{ $record->particulars_remark ?? '-' }}</i></td>
                <td class="text-end fw-bold">
                    ₱{{ number_format((float) str_replace(',', '', $record->total_debit ?? 0), 2) }}
                </td>
                <td>
                    <span class="badge px-2 py-1 small fw-bold" style="background-color: var(--secondary-variant); color: var(--primary);">
                        {{ trim($record->status) }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-primary">
                        {{ $record->total_entries }} Entries
                    </span>
                </td>
                <td>
                  @if(!empty($record->dv_no))
                  <div class="d-flex gap-1 justify-content-center">
                      {{-- VIEW BUTTON --}}
                      <button type="button"
                              class="btn btn-sm btn-outline-info action-btn"
                              data-action="view"
                              data-dv="{{ $record->dv_no }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-eye"></i>
                      </button>

                      {{-- CHANGE STATUS FORM --}}
                      <form action="{{ route('accounting.cashier-status.pay', $record->dv_no) }}" 
                            method="POST" 
                            class="m-0"
                            onsubmit="return confirm('Are you sure you want to mark DV No. {{ $record->dv_no }} as Paid? This will shift it to Archives.');">
                          @csrf
                          @method('PUT')
                          <button type="submit" class="btn btn-sm btn-success fw-bold px-2">
                              <i class="bi bi-check2-circle"></i> Mark Paid
                          </button>
                      </form>
                  </div>
                  @else
                      <span class="text-muted">No DV No.</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="11" class="text-center text-muted py-3">
                  No records waiting on cashier dispatch.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
    </div>
  </div>
</div>

@include('accounting.partials.action-modal')
@include('accounting.partials.details-modal')
@include('accounting.partials.scripts')
@endsection

@php
    $pageTitle = 'Cashier Status';
@endphp