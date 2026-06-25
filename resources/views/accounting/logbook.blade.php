@extends('layouts.app')

@section('title', 'Accounting Log Book')

@section('content')

<div class="container-fluid mt-3 px-0">

  {{-- TOP BAR --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    @include('layouts.subtab')
  </div>

  {{-- CARD --}}
  <div class="card p-3 pb-0 m-0" style="min-height:70vh; width:80vw">

    {{-- HEADER CONTROLS --}}
    <div class="px-3 pt-3 pb-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

      <h5 class="m-0 fw-bold">Accounting Log Book</h5>

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
                style="min-width:100px;border-color:#bebebe;">
          <small>
            <i class="bi bi-funnel"></i> Filter
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

          <button class="btn" type="submit" style="border-color:#bebebe;">
            <i class="bi bi-search"></i>
          </button>

          {{-- RESET --}}
          @if(request('search') || request('month') !== 'all' || request('status') !== 'all' || request('sort') !== 'latest')
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

    {{-- TABLE --}}
    <div class="card-body">
      <div class="table-responsive" style="max-height:60vh; overflow:auto;">

        <table class="table table-bordered table-hover table-sm align-middle">

          <thead class="table-dark sticky-top">
            <tr>
              <th>Date Received</th>
              <th>Date Processed</th>
              <th>OBR Date</th>
              <th>OBR No.</th>
              <th>DV No.</th>
              <th>Payee</th>
              <th>Particulars</th>
              <th>Particulars Remark</th>
              <th>UACS Code</th>
              <th>Debit</th>
              <th>Credit</th>
              <th>Tax %</th>
              <th>Status</th>
              <th>Date Signed</th>
              <th>Date Forwarded</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>

            @forelse($records as $record)
              <tr>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td>{{ $record->date_processed ?? '-' }}</td>
                <td>{{ $record->obr_date ?? '-' }}</td>
                <td><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td>{{ $record->payee ?? '-' }}</td>
                <td>{{ $record->particulars ?? '-' }}</td>
                <td>{{ $record->particulars_remark ?? '-' }}</td>
                <td>{{ $record->uacs_code ?? '-' }}</td>

                <td>
                  ₱{{ number_format((float) str_replace(',', '', $record->debit ?? 0), 2) }}
                </td>

                <td>
                  ₱{{ number_format((float) str_replace(',', '', $record->credit ?? 0), 2) }}
                </td>

                <td>{{ $record->tax_percent ?? '-' }}</td>
                <td>{{ $record->status ?? '-' }}</td>
                <td>{{ $record->date_signed ?? '-' }}</td>
                <td>{{ $record->date_forwarded ?? '-' }}</td>
                <td>
                      @if(!empty($record->dv_no))

                          <div class="d-flex gap-1">

                              {{-- VIEW --}}
                              <a href="{{ route('accounting.logbook.show', ['dv_no' => $record->dv_no]) }}"
                                class="btn btn-sm btn-outline-info"
                                title="View">
                                  <i class="bi bi-eye"></i>
                              </a>

                              {{-- EDIT --}}
                              <a href="{{ route('accounting.logbook.edit', ['dv_no' => $record->dv_no]) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Edit">
                                  <i class="bi bi-pencil"></i>
                              </a>

                              {{-- DELETE --}}
                              <form action="{{ route('accounting.logbook.destroy', ['dv_no' => $record->dv_no]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this transaction?')">

                                  @csrf
                                  @method('DELETE')

                                  <button type="submit"
                                          class="btn btn-sm btn-outline-danger"
                                          title="Delete">
                                      <i class="bi bi-trash"></i>
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
                <td colspan="16" class="text-center text-muted py-3">
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

{{-- FILTER MODAL --}}
<div class="modal fade" id="filterModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <form method="GET" action="{{ route('accounting.logbook') }}">

        {{-- preserve search --}}
        <input type="hidden" name="search" value="{{ request('search') }}">

        <div class="modal-header">
          <h5 class="modal-title fw-bold">Filter Accounting Logbook</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          {{-- MONTH --}}
          <div class="mb-3">
            <label class="form-label">Month</label>
            <select name="month" class="form-select">
              <option value="all" @selected(request('month','all')=='all')>All Months</option>
              <option value="january" @selected(request('month')=='january')>January</option>
              <option value="february" @selected(request('month')=='february')>February</option>
              <option value="march" @selected(request('month')=='march')>March</option>
              <option value="april" @selected(request('month')=='april')>April</option>
              <option value="may" @selected(request('month')=='may')>May</option>
              <option value="june" @selected(request('month')=='june')>June</option>
            </select>
          </div>

          {{-- STATUS --}}
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="all" @selected(request('status','all')=='all')>All Status</option>
              <option value="Pending" @selected(request('status')=='Pending')>Pending</option>
              <option value="Processing" @selected(request('status')=='Processing')>Processing</option>
              <option value="Completed" @selected(request('status')=='Completed')>Completed</option>
            </select>
          </div>

          {{-- SORT --}}
          <div class="mb-3">
            <label class="form-label">Sort</label>
            <select name="sort" class="form-select">
              <option value="latest" @selected(request('sort','latest')=='latest')>Latest Date Processed</option>
              <option value="obr_asc" @selected(request('sort')=='obr_asc')>DV No. (Asc)</option>
              <option value="obr_desc" @selected(request('sort')=='obr_desc')>DV No. (Desc)</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <a href="{{ route('accounting.logbook') }}" class="btn btn-secondary">
            Reset
          </a>
          <button type="submit" class="btn btn-success">
            Apply Filters
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

@endsection