@extends('layouts.app')

@section('title', 'Archives')

@section('content')

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">

  {{-- ARCHIVE FILTER CONTROL BLOCK --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-end align-items-md-center gap-3">

      {{-- FILTER & SEARCH PANEL --}}
      <form action="{{ route('accounting.archives') }}"
            method="GET"
            class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">

        {{-- YEAR FILTER --}}
        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="border-color:#bebebe; width: 110px;">
            <option value="all">All Years</option>
            @for ($y = 2024; $y <= 2027; $y++)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>

        {{-- MONTH FILTER --}}
        <select name="month" class="form-select form-select-sm" onchange="this.form.submit()" style="border-color:#bebebe; width: 130px;">
            <option value="all">All Months</option>
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                </option>
            @endforeach
        </select>

        {{-- SORT PARAMETER --}}
        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="border-color:#bebebe; width: 140px;">
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Settled</option>
            <option value="obr_asc" {{ request('sort') == 'obr_asc' ? 'selected' : '' }}>DV No. (Asc)</option>
            <option value="obr_desc" {{ request('sort') == 'obr_desc' ? 'selected' : '' }}>DV No. (Desc)</option>
        </select>

        {{-- FULLTEXT SEARCH --}}
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

          {{-- REVERT ACTION --}}
          @if(request('search') || request('month') !== 'all' || request('year') !== 'all' || request('sort') !== 'latest')
            <a href="{{ route('accounting.archives') }}"
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

  {{-- ARCHIVED LOG ENTRIES DISPLAY --}}
  <div class="card m-0 w-100" style="min-height: 55vh; display: grid; min-width: 0;">
    <div class="card-body p-3" style="min-width: 0;">
      
      <div class="table-responsive" style="max-height: 60vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table table-bordered table-hover table-sm align-middle m-0">
          <thead class="table-dark sticky-top" style="z-index: 5;">
            <tr>
              <th>Date Received</th>
              <th style="width: 80px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>DV No.</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort_dv_no' => 'asc']) }}" class="btn p-0 px-1 text-white {{ request('sort_dv_no') === 'asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_dv_no' => 'desc']) }}" class="btn p-0 px-1 text-white  {{ request('sort_dv_no', 'desc') === 'desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th>Date Processed</th>
              <th>OBR Date</th>
              <th style="min-width: 70px;">OBR No.</th>
              <th style="min-width: 160px;">Payee</th>
              <th style="min-width: 300px;">Particulars</th>
              <th style="min-width: 160px;">Particulars Remark</th>
              <th style="min-width:130px;">Amount</th>
              <th style="min-width: 150px;">Status</th>
              <th style="min-width:120px;">Accounting Entries</th>
              <th style="min-width: 100px;">Signed</th>
              <th>Date Signed</th>
              <th>Date Forwarded</th>
              <th style="min-width: 150px;">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              <tr>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td>{{ $record->date_processed ?? '-' }}</td>
                <td>{{ $record->obr_date ?? '-' }}</td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td><i>{{ $record->particulars_remark ?? '-' }}</i></td>
                <td class="text-end fw-bold">
                    ₱{{ number_format((float) str_replace(',', '', $record->total_debit ?? 0), 2) }}
                </td>
                {{-- STATUS COLUMN --}}
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
                    <span class="badge px-2 py-1 small fw-bold" style="{{ $statusStyles }}">{{ $status }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>

                <td class="text-center">
                    <span class="badge bg-primary">
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
                <td>{{ $record->date_signed ?? '-' }}</td>
                <td>{{ $record->date_forwarded ?? '-' }}</td>
                <td class="text-center">
                  @if(!empty($record->dv_no))
                    <button type="button"
                            class="btn btn-sm btn-outline-info view-details-btn"
                            data-id="{{ $record->transaction_id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#detailsModal">
                        <i class="bi bi-eye"></i>
                    </button>
                  @else
                      <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="14" class="text-center text-muted py-3">
                  No records found matching parameters.
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
    $pageTitle = 'Archives';
@endphp