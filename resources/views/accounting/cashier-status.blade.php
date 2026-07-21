@extends('layouts.app')

@section('content')

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">
  {{-- FILTER & SEARCH CARD --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-end align-items-md-center gap-3">

      <form action="{{ route('accounting.cashier-status') }}"
            method="GET"
            class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
        <input type="hidden" name="sort" value="{{ request('sort','latest') }}">

        {{-- SEARCH INPUT --}}
        <div class="input-group input-group-sm" style="min-width:260px;">
          <input type="text"
                 name="search"
                 class="form-control p-1"
                 placeholder="Search"
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
              <th style="width: 100px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>Date Forwarded</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_forwarded' => 'asc']) }}" class="btn p-0 px-1 text-white {{ request('sort_forwarded') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_forwarded' => 'desc']) }}" class="btn p-0 px-1 text-white  {{ request('sort_forwarded', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th style="min-width: 80px;">DV No.</th>
              <th style="min-width: 80px;">OBR No.</th>
              <th style="min-width: 160px;">Payee</th>
              <th style="min-width: 300px;">Particulars</th>
              <th style="min-width: 220px;">Amount</th>
              <th style="min-width: 150px;">Status</th>
              <th style="min-width: 80px;">Signed</th>
              <th style="width: 100px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>Date Signed</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_signed' => 'asc']) }}" class="btn p-0 px-1 text-white {{ request('sort_signed') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_signed' => 'desc']) }}" class="btn p-0 px-1 text-white  {{ request('sort_signed', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th style="min-width:100px;">Accounting Entries</th>
              <th style="min-width: 200px;" class="text-center">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              <tr>
                <td>{{ $record->date_forwarded ?? '-' }}</td>
                <td style="color: #9D6B0B; background-color:#FFEECC"><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td class="fw-bold">
                    ₱{{ number_format((float) str_replace(',', '', $record->total_debit ?? 0), 2) }}
                </td>
                <td>
                  @if(!empty($record->status))
                    @php
                      $status = trim($record->status);
                      $statusStyles = match($status) {
                        'Pending'              => 'background-color: #FFEECC; color: #9D6B0B;',
                        'Processing'           => 'background-color: #FFDEC5; color: #BB400D;',
                        'Returned'             => 'background-color: #EFDFFF; color: #7909FF;',
                        'Paid'                 => 'background-color: #DEF5C4; color: var(--secondary);',
                        'Forwarded to Cashier' => 'background-color: var(--secondary-variant); color: var(--primary);',
                        default                => 'background-color: #F8F9FA; color: #6C757D;'
                      };
                    @endphp
                    <span class="badge fw-bold" style="{{ $statusStyles }}; font-size: 0.9em;">{{ $status }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                {{-- SIGNED COLUMN (Ready for Yes/No DB field addition) --}}
                <td>
                  @if(isset($record->signed) && !is_null($record->signed))
                    @php
                      $signedVal = trim(strtolower($record->signed));
                    @endphp
                    @if($signedVal === 'yes' || $signedVal === '1' || $record->signed === true)
                      <span class="badge fw-bold" style="background-color: var(--secondary-variant); color: var(--primary); font-size: 0.9em;">Yes</span>
                    @elseif($signedVal === 'no' || $signedVal === '0' || $record->signed === false)
                      <span class="badge fw-bold;" style="background-color: #FFC2C2; color: var(--error); font-size: 0.9em;">No</span>
                    @else
                      <span class="badge bg-light text-dark fw-bold" style="font-size: 0.9em;">{{ $record->signed }}</span>
                    @endif
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>{{ $record->date_signed ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge bg-primary" style="font-size: 0.9em;">
                        {{ $record->total_entries }} Entries
                    </span>
                </td>
                <td>
                  @if(!empty($record->dv_no))
                  <div class="d-flex gap-2 justify-content-center">
                      {{-- VIEW BUTTON --}}
                      <button type="button"
                              class="btn btn-sm btn-outline-info view-details-btn"
                              data-id="{{ $record->transaction_id }}"
                              data-bs-toggle="modal"
                              data-bs-target="#detailsModal">
                          <i class="bi bi-eye"></i>
                      </button>

                      {{-- CHANGE STATUS FORM --}}
                      <button type="button" 
                              class="btn btn-sm btn-success fw-bold px-2 action-btn" 
                              data-action="pay-confirm"
                              data-dv="{{ $record->dv_no }}"
                              data-url="{{ route('accounting.cashier-status.pay', $record->dv_no) }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-check2-circle"></i> Mark as Paid
                      </button>
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