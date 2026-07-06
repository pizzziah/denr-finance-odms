@extends('layouts.app')

@section('title', 'Archives')

@section('content')

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">

  {{-- ARCHIVE FILTER CONTROL BLOCK --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <h5 class="m-0 text-muted"><i class="bi bi-archive text-success"></i> Settlement Archives</h5>

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
                 placeholder="Search Archive..."
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
              <th style="min-width: 80px;">DV No.</th>
              <th>Date Received</th>
              <th>Date Settled</th>
              <th style="min-width: 70px;">OBR No.</th>
              <th style="min-width: 160px;">Payee</th>
              <th style="min-width: 300px;">Particulars</th>
              <th style="min-width:130px;">Amount</th>
              <th style="min-width: 120px;">Status</th>
              <th style="min-width:120px;" class="text-center">Entries</th>
              <th style="min-width: 80px;" class="text-center">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              <tr>
                <td class="text-muted" style="background-color: #f8f9fa"><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td>{{ $record->date_processed ?? '-' }}</td> {{-- Displays compilation completion date --}}
                <td class="text-muted" style="background-color: #f8f9fa"><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td>{{ $record->payee ?? '-' }}</td>
                <td>{{ $record->particulars ?? '-' }}</td>
                <td class="text-end fw-bold text-success">
                    ₱{{ number_format((float) str_replace(',', '', $record->total_debit ?? 0), 2) }}
                </td>
                <td>
                    <span class="badge px-2 py-1 small fw-bold" style="background-color: #DEF5C4; color: var(--secondary);">
                        <i class="bi bi-shield-check"></i> {{ trim($record->status) }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary">
                        {{ $record->total_entries }} Items
                    </span>
                </td>
                <td class="text-center">
                  @if(!empty($record->dv_no))
                      <button type="button"
                              class="btn btn-sm btn-outline-info action-btn"
                              data-action="view"
                              data-dv="{{ $record->dv_no }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-eye"></i> View
                      </button>
                  @else
                      <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center text-muted py-3">
                  No historical entries match your filter boundaries.
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