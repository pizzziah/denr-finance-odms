@extends('layouts.app')

@section('title', 'Accounting Log Book')

@section('content')

<div class="container m-0 mt-4 p-0">

  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    @include('layouts.subtab')
  </div>

  {{-- CARD --}}
  <div class="card p-3 pb-0 m-0" style="min-height:70vh; width: 80vw">

    {{-- HEADER --}}
    <div class="px-3 pt-3 pb-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

      {{-- SEARCH + FILTER --}}
      <form action="{{ route('accounting.logbook') }}"
            method="GET"
            class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">

        {{-- preserve filters --}}
        <input type="hidden" name="month" value="{{ request('month','all') }}">
        <input type="hidden" name="status" value="{{ request('status','all') }}">
        <input type="hidden" name="sort" value="{{ request('sort','latest') }}">

        {{-- FILTER BUTTON --}}
        <button type="button"
                class="btn p-1"
                data-bs-toggle="modal"
                data-bs-target="#filterModal"
                style="min-width: 100px; border:1px solid #bebebe;">
          <small><i class="bi bi-funnel"></i> Filter</small>
        </button>

        {{-- SEARCH --}}
        <div class="input-group input-group-sm" style="min-width:260px;">
          <input type="text"
                 name="search"
                 class="form-control p-1"
                 placeholder="Search DV, OBR, Payee..."
                 value="{{ request('search') }}">

          <button class="btn btn-outline-secondary" type="submit">
            <i class="bi bi-search"></i>
          </button>

          @if(request('search') || request('month') || request('status') !== 'all')
            <a href="{{ route('accounting.logbook') }}"
               class="btn btn-outline-danger"
               title="Clear Filters">
              <i class="bi bi-x-circle"></i>
            </a>
          @endif
        </div>

      </form>
    </div>

    {{-- TABLE --}}
    <div class="card-body">

      <div class="table-responsive" style="max-height:60vh; overflow:auto;">

        <table class="table table-sm table-bordered table-hover align-middle">

          <thead class="table-dark sticky-top">
            <tr>
              <th>Date Received</th>
              <th>Date Processed</th>
              <th>OBR No.</th>
              <th>DV No.</th>
              <th>Payee</th>
              <th>Particulars</th>
              <th>UACS Code</th>
              <th>Debit</th>
              <th>Credit</th>
              <th>Status</th>
              <th>Date Signed</th>
              <th>Date Forwarded</th>
            </tr>
          </thead>

          <tbody>

            @forelse($records as $record)
              <tr>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td>{{ $record->date_processed ?? '-' }}</td>
                <td><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td>{{ $record->dv_no ?? '-' }}</td>
                <td>{{ $record->payee ?? '-' }}</td>
                <td>{{ $record->particulars ?? '-' }}</td>
                <td>{{ $record->uacs_code ?? '-' }}</td>

                <td>
                  ₱{{ number_format((float) str_replace(',', '', $record->debit ?? 0), 2) }}
                </td>

                <td>
                  ₱{{ number_format((float) str_replace(',', '', $record->credit ?? 0), 2) }}
                </td>

                <td>{{ $record->status ?? '-' }}</td>
                <td>{{ $record->date_signed ?? '-' }}</td>
                <td>{{ $record->date_forwarded ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="12" class="text-center text-muted py-3">
                  No records found.
                </td>
              </tr>
            @endforelse

          </tbody>

        </table>

      </div>
    </div>

  </div>
</div>

{{-- FILTER MODAL --}}
<div class="modal fade" id="filterModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <form method="GET" action="{{ route('accounting.logbook') }}">

        <input type="hidden" name="search" value="{{ request('search') }}">

        <div class="modal-header">
          <h5 class="modal-title">Filter Logbook</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          {{-- MONTH --}}
          <div class="mb-3">
            <label class="form-label">Month</label>
            <select name="month" class="form-select">

              <option value="all" @selected(request('month','all')=='all')>All</option>
              <option value="1" @selected(request('month')=='1')>January</option>
              <option value="2" @selected(request('month')=='2')>February</option>
              <option value="3" @selected(request('month')=='3')>March</option>
              <option value="4" @selected(request('month')=='4')>April</option>
              <option value="5" @selected(request('month')=='5')>May</option>
              <option value="6" @selected(request('month')=='6')>June</option>
              <option value="7" @selected(request('month')=='7')>July</option>
              <option value="8" @selected(request('month')=='8')>August</option>
              <option value="9" @selected(request('month')=='9')>September</option>
              <option value="10" @selected(request('month')=='10')>October</option>
              <option value="11" @selected(request('month')=='11')>November</option>
              <option value="12" @selected(request('month')=='12')>December</option>

            </select>
          </div>

          {{-- STATUS --}}
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">

              <option value="all" @selected(request('status','all')=='all')>All</option>
              <option value="Pending" @selected(request('status')=='Pending')>Pending</option>
              <option value="Processing" @selected(request('status')=='Processing')>Processing</option>
              <option value="Completed" @selected(request('status')=='Completed')>Completed</option>

            </select>
          </div>

          {{-- SORT --}}
          <div class="mb-3">
            <label class="form-label">Sort</label>
            <select name="sort" class="form-select">

              <option value="latest" @selected(request('sort','latest')=='latest')>
                Latest
              </option>

              <option value="obr_asc" @selected(request('sort')=='obr_asc')>
                OBR No (Asc)
              </option>

              <option value="obr_desc" @selected(request('sort')=='obr_desc')>
                OBR No (Desc)
              </option>

            </select>
          </div>

        </div>

        <div class="modal-footer">
          <a href="{{ route('accounting.logbook') }}" class="btn btn-secondary">Reset</a>
          <button type="submit" class="btn btn-success">Apply</button>
        </div>

      </form>

    </div>
  </div>
</div>

@endsection