@extends('layouts.app')

@section('title', 'Log Book')

@section('content')

@php
    $showStatusColumn = request('status', 'all') === 'all';
@endphp

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;" >
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    @include('layouts.subtab')
  </div>

  {{-- 1ST CARD --}}
  <div class="card p-3 mb-3 m-0 w-100" >
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
      
      {{-- SEARCH AND FILTER CONTAINER --}}
      <form action="{{ route('budget.logbook') }}" method="GET" class="d-flex align-items-center gap-2 m-0 flex-wrap flex-md-nowrap">
        <input type="hidden" name="year" value="{{ request('year', 'all') }}">
        <input type="hidden" name="month" value="{{ request('month') }}">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
        <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

        {{-- Filter --}}
        <button type="button" class="btn btn-outline-primary d-inline-flex"data-bs-toggle="modal"data-bs-target="#filterModal">
            <i class="bi bi-funnel"></i> Filter
        </button>

        {{-- Sort --}}
        <button type="button"class="btn btn-outline-secondary d-inline-flex"data-bs-toggle="modal"data-bs-target="#sortModal">
            <i class="bi bi-sort-down"></i> <span>Sort</span>
        </button>

        <div class="input-group input-group-sm" style="min-width: 260px;">
          <input type="text"
                 name="search"
                 class="form-control p-1"
                 placeholder="Search ORS, Payee, Office..."
                 value="{{ request('search') }}"
                 style="border-color:#bebebe;">

          <button class="btn btn-dark" type="submit" style="border-color:#bebebe;">
            <i class="bi bi-search"></i>
          </button>
          @if(request('search') || request('year') !== 'all' || request('month') || request('status') !== 'all')
            <a href="{{ route('budget.logbook') }}" class="btn" title="Clear Filters" style="border-color: var(--error)">
              <i class="bi bi-x-circle"></i>
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- 2ND CARD --}}
  <div class="card m-0 w-100" style="min-height: 60vh; display: grid; min-width: 0;">
    <div class="card-body p-3" style="min-width: 0;">
      
      <div class="table-responsive" style="max-height: 55vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table table-bordered table-hover align-middle m-0">
          <thead class="sticky-top bg-white" style="z-index: 5;">
            {{-- FIRST HEADER ROW --}}
            <tr class="header-main">
              <th rowspan="2" style="min-width:80px;">ORS No.</th>
              <th rowspan="2">Date Received</th>
              <th rowspan="2" style="min-width:100px;">Issuing Office</th>
              <th rowspan="2" style="min-width:160px;">Payee</th>
              <th rowspan="2" style="min-width:230px;">Particulars</th>
              <th rowspan="2">Classification</th>
              <th rowspan="2">UAC Codes</th>
              <th rowspan="2">Particulars Remark</th>
              <th rowspan="2">Amount</th>
              @if($showStatusColumn)<th rowspan="2" style="min-width:150px;">Status</th>@endif

              {{-- GROUP 1 --}}
              <th colspan="3" style="background-color: #EFDFFF; color: #7909FF">Returned to End User</th>
              {{-- GROUP 2 --}}
              <th colspan="3" style="background-color: #FFEECC; color: #9D6B0B">Forwarded</th>
              {{-- GROUP 3 --}}
              <th colspan="2" style="background-color: #EBFEFF; color: #0B879D">Returned by Accounting</th>

              <th rowspan="2" style="min-width:120px;">Date Forwarded to Accounting</th>
              <th rowspan="2" style="min-width:100px;">Total Time in Budget</th>
              <th rowspan="2" style="min-width:100px;">Total Time</th>
              <th rowspan="2">Final Remark</th>
              <th rowspan="2" style="min-width: 150px;">Action</th>
            </tr>

            {{-- SECOND HEADER ROW --}}
            <tr class="header-sub">
              {{-- Returned to End User --}}
              <th style="background-color: #EFDFFF; color: #7909FF">Date Returned</th>
              <th style="background-color: #EFDFFF; color: #7909FF">Remarks</th>
              <th style="background-color: #EFDFFF; color: #7909FF">Date Received</th>

              {{-- Forwarded --}}
              <th style="background-color: #FFEECC; color: #9D6B0B">Date Forwarded</th>
              <th style="background-color: #FFEECC; color: #9D6B0B">Date ORS Received</th>
              <th style="background-color: #FFEECC; color: #9D6B0B">Remarks</th>

              {{-- Returned by Accounting --}}
              <th style="background-color: #EBFEFF; color: #0B879D">Date Returned</th>
              <th style="background-color: #EBFEFF; color: #0B879D">Date Received</th>
            </tr>
          </thead>
            <tbody>
              @forelse($records as $record)
                <tr>
                  <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->ors_no ?? '-' }}</strong></td>
                  <td>{{ $record->date_received ?? '-' }}</td>
                  
                  {{-- ISSUING OFFICE COLUMN WITH COLOR-CODING BADGES --}}
                  <td>
                    @if(!empty($record->issuing_office))
                      @php
                        $office = strtoupper(trim($record->issuing_office));
                        $officeStyles = match($office) {
                          'PMD'          => 'background-color: #E2F0D9; color: #385723;',
                          'SMD'          => 'background-color: #FFF2CC; color: #7F6000;',
                          'LPDD'         => 'background-color: #FCE4D6; color: #C65911;',
                          'ADMIN'        => 'background-color: #E9EDF4; color: #305496;',
                          'FD'           => 'background-color: #EDEDED; color: #595959;',
                          'ORED'         => 'background-color: #F2DCDB; color: #C00000;',
                          'ARD MS'       => 'background-color: #E4DFEC; color: #595959;',
                          'PERSONNEL'    => 'background-color: #D9E1F2; color: #1F4E78;',
                          'RSCIG'        => 'background-color: #E2EFDA; color: #375623;',
                          'LEGAL'        => 'background-color: #FBE5D6; color: #A61C00;',
                          'ARD TS'       => 'background-color: #FFF2CC; color: #D66000;',
                          'ED'           => 'background-color: #D0CECE; color: #3A3A3A;',
                          'CDD'          => 'background-color: #E1F5FE; color: #0288D1;',
                          'HRDS'         => 'background-color: #F3E5F5; color: #7B1FA2;',
                          'PROCUREMENT'  => 'background-color: #E8F5E9; color: #2E7D32;',
                          default        => 'background-color: #F8F9FA; color: #212529;'
                        };
                      @endphp
                      <span class="badge fw-bold" style="{{ $officeStyles }}; font-size: 1em;">{{ $office }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>

                  <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                  <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                  <td>{{ $record->classification ?? '-' }}</td>
                  <td>{{ $record->uac_codes ?? '-' }}</td>
                  <td>{{ $record->particulars_remark ?? '-' }}</td>
                  <td><strong>₱{{ number_format((float) str_replace(',', '', $record->amount ?? 0), 2) }}</strong></td>
                  
                  @if($showStatusColumn)
                      {{-- STATUS COLUMN WITH SPECIFIED VALUE COLOR-CODING --}}
                      <td>
                          @if(!empty($record->status))
                              @php
                                  $status = trim($record->status);
                                  $statusStyles = match($status) {
                                      'Pending'                 => 'background-color: #FFEECC; color: #9D6B0B;',
                                      'Processing'              => 'background-color: #FFDEC5; color: #BB400D;',
                                      'Returned'                => 'background-color: #EFDFFF; color: #7909FF;',
                                      'Paid'                    => 'background-color: #DEF5C4; color: var(--secondary);',
                                      'For Review'              => 'background-color: #CFF0F1; color: #066B6B;',
                                      'For Obligation'          => 'background-color: #BCC3F6; color: #271ECE;',
                                      'Canceled'                => 'background-color: #FFC2C2; color: var(--error);',
                                      'Forwarded to Accounting' => 'background-color: var(--secondary-variant); color: var(--primary);',
                                      default                   => 'background-color: #F8F9FA; color: #6C757D;'
                                  };
                              @endphp

                              <span class="badge fw-bold" style="{{ $statusStyles }}; font-size: 1em;">
                                  {{ $status }}
                              </span>
                          @else
                              <span class="text-muted">-</span>
                          @endif
                      </td>
                  @endif
                  <td>{{ $record->date_returned_1 ?? '-' }}</td>
                  <td>{{ $record->remarks_1 ?? '-' }}</td>
                  <td>{{ $record->date_received_1 ?? '-' }}</td>
                  <td>{{ $record->date_forwarded_1 ?? '-' }}</td>
                  <td>{{ $record->date_ors_received ?? '-' }}</td>
                  <td>{{ $record->date_returned_2 ?? '-' }}</td>
                  <td>{{ $record->remarks_2 ?? '-' }}</td>
                  <td>{{ $record->date_received_2 ?? '-' }}</td>
                  <td>{{ $record->date_forwarded_accounting ?? '-' }}</td>
                  <td>{{ $record->total_time_budget ?? '-' }}</td>
                  <td>{{ $record->total_time ?? '-' }}</td>
                  <td>{{ $record->final_remarks ?? '-' }}</td>
                  <td>
                      @if(!empty($record->payee))
                      <div class="d-flex gap-1 justify-content-center">
                          <!-- View -->
                          <button
                              type="button"
                              class="btn btn-sm btn-outline-info view-btn"
                              data-budget-id="{{ $record->budget_id }}">
                              <i class="bi bi-eye"></i>
                          </button>

                          <!-- Edit -->
                          <button
                              type="button"
                              class="btn btn-sm btn-outline-primary edit-btn"
                              data-budget-id="{{ $record->budget_id }}">
                              <i class="bi bi-pencil"></i>
                          </button>

                          <!-- Delete -->
                          <button
                              type="button"
                              class="btn btn-sm btn-outline-danger delete-btn"
                              data-budget-id="{{ $record->budget_id}}"
                              data-payee="{{ $record->budget_id }}">
                              <i class="bi bi-trash"></i>
                          </button>
                      @else
                          <span class="text-muted">No DV No.</span>
                      @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="22" class="text-center text-muted py-3">No records found matching parameters.</td>
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
@include('budget.partials.filter-modal')
@include('budget.partials.sort-modal')
@include('budget.partials.add-record-modal')
@include('budget.partials.action-modal')
@include('budget.partials.details-modal')
@include('budget.partials.scripts')
@include('budget.partials.edit-modal')

@endsection

@php
    $pageTitle = 'Logbook';
@endphp
