@extends('layouts.app')

@section('title', 'Budget Archives')

@section('content')

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">

  {{-- ARCHIVE FILTER CONTROL BLOCK --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-end align-items-md-center gap-3">

      {{-- FILTER & SEARCH PANEL --}}
      <form action="{{ route('budget.archives') }}"
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
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Received</option>
            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest Received</option>
            <option value="ors_asc" {{ request('sort') == 'ors_asc' ? 'selected' : '' }}>ORS No. (Asc)</option>
            <option value="ors_desc" {{ request('sort') == 'ors_desc' ? 'selected' : '' }}>ORS No. (Desc)</option>
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
          @if(request('search') || request('month') || (request('year') && request('year') !== 'all') || (request('sort') && request('sort') !== 'latest'))
            <a href="{{ route('budget.archives') }}"
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
              <th>Due Date</th>
              <th style="width: 100px;">
                <div class="d-flex align-items-center justify-content-between">
                  <span>ORS No.</span>
                  <div class="btn-group btn-group-xs ms-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'ors_asc']) }}" class="btn p-0 px-1 text-white {{ request('sort') === 'ors_asc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-down"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'ors_desc']) }}" class="btn p-0 px-1 text-white {{ request('sort') === 'ors_desc' ? 'opacity-100 fw-bold' : 'opacity-50' }}"><i class="bi bi-sort-numeric-up-alt"></i></a>
                  </div>
                </div>
              </th>
              <th style="min-width: 140px;">Issuing Office</th>
              <th style="min-width: 140px;">Payee</th>
              <th style="min-width: 130px;">Classification</th>
              <th style="min-width: 250px;">Particulars</th>
              <th style="min-width: 150px;">Particulars Remark</th>
              <th style="min-width: 110px;">UAC Codes</th>
              <th style="min-width: 120px;">Amount</th>
              <th style="min-width: 150px;">Status</th>
              <th style="min-width: 150px;">Final Remarks</th>
              <th>Date Fwd (Accounting)</th>
              <th style="min-width: 90px;" class="text-center">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              <tr>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td>{{ $record->due_date ?? '-' }}</td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->ors_no ?? '-' }}</strong></td>
                <td>{{ $record->issuing_office ?? '-' }}</td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><span class="badge bg-secondary text-light">{{ $record->classification ?? '-' }}</span></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td><i>{{ $record->particulars_remark ?? '-' }}</i></td>
                <td><code>{{ $record->uac_codes ?? '-' }}</code></td>
                <td class="text-end fw-bold">
                    ₱{{ number_format((float)($record->amount ?? 0), 2) }}
                </td>
                {{-- STATUS COLUMN --}}
                <td>
                  @if(!empty($record->status))
                    @php
                      $status = trim($record->status);
                      $statusStyles = match($status) {
                        'Paid'                    => 'background-color: #DEF5C4; color: var(--secondary);',
                        'Cancelled'                => 'background-color: #FFC2C2; color: var(--error);',
                        default                    => 'background-color: #F8F9FA; color: #6C757D;'
                      };
                    @endphp
                    <span class="badge px-2 py-1 small fw-bold" style="{{ $statusStyles }}">{{ $status }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td><small>{{ $record->final_remarks ?? '-' }}</small></td>
                <td>{{ $record->date_forwarded_accounting ?? '-' }}</td>
                <td class="text-center">
                  @if(!empty($record->budget_id))
                      <button type="button"
                          class="btn btn-sm btn-outline-info view-btn"
                          data-budget-id="{{ $record->budget_id }}"
                          data-bs-target="detailsModal"
                          data-archive="true">
                          <i class="bi bi-eye"></i> View
                      </button>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="14" class="text-center text-muted py-3">
                  No archived budget records found matching parameters.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('budget.partials.action-modal')
@include('budget.partials.details-modal')
@include('budget.partials.scripts')
@endsection

@php
    $pageTitle = 'Archives';
@endphp