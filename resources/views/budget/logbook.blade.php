@extends('layouts.app')

@section('content')

<div class="container-fluid mt-3 px-0">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    @include('layouts.subtab')
  </div>

  {{-- CARD CONTAINER --}}
  <div class="card p-3 pb-0 m-0" style="min-height:70vh; width: 80vw">
    <div class="px-3 pt-3 pb-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <x-button variant="header" data-bs-toggle="modal" data-bs-target="#addRecordModal">
        <i class="bi bi-file-earmark-plus"></i>
        Add Record
      </x-button>
      
      {{-- SEARCH AND FILTER CONTAINER --}}
      <form action="{{ route('budget.logbook') }}" method="GET" class="d-flex align-items-center gap-2 m-0 flex-wrap flex-md-nowrap">
        <input type="hidden" name="year" value="{{ request('year', 'all') }}">
        <input type="hidden" name="month" value="{{ request('month') }}">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
        <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

        <button type="button" class="btn p-1" data-bs-toggle="modal" data-bs-target="#filterModal" style="min-width: 100px; border-color: #bebebe;">
          <small><i class="bi bi-funnel" class=""></i> Filter</small>
        </button>

        <div class="input-group input-group-sm" style="min-width: 260px;">
          <input type="text" name="search" class="form-control p-1" style="border-color: #bebebe" placeholder="Search ORS, Payee, Office..." value="{{ request('search') }}">
          <button class="btn" style="border-color: #bebebe" type="submit">
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

    {{-- CARD BODY & DATA TABLE --}}
    <div class="card-body">
      <div class="table-responsive" style="max-height:60vh; overflow:auto;">
        <table class="table table-bordered table-hover align-middle">
          <thead class="sticky-top">
            {{-- FIRST HEADER ROW --}}
            <tr class="header-main">
              <th rowspan="2" style="min-width:80px;">ORS No.</th>
              <th rowspan="2">Date Received</th>
              <th rowspan="2" style="min-width:100px;">Issuing Office</th>
              <th rowspan="2" style="min-width:160px;">Payee</th>
              <th rowspan="2" style="min-width:230px;">Particulars</th>
              <th rowspan="2">Classification</th>
              <th rowspan="2">Particulars Remark</th>
              <th rowspan="2">Amount</th>
              <th rowspan="2" style="min-width:150px;">Status</th>

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
              <th rowspan="2">Action</th>
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
                <td>{{ $record->issuing_office ?? '-' }}</td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td>{{ $record->classification ?? '-' }}</td>
                <td>{{ $record->particulars_remark ?? '-' }}</td>
                <td><strong>₱{{ number_format((float) str_replace(',', '', $record->amount ?? 0), 2) }}</strong></td>
                <td>{{ $record->status ?? '-' }}</td>
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
                      <div class="d-flex gap-1">
                          <button type="button"
                                  class="btn btn-sm btn-outline-info action-btn"
                                  data-action="view"
                                  data-ors="{{ $record->ors_no }}"
                                  data-payee="{{ $record->payee }}"
                                  data-status="{{ $record->status }}"
                                  data-bs-toggle="modal"
                                  data-bs-target="#actionModal">
                              <i class="bi bi-eye"></i>
                          </button>
                          <button type="button"
                                  class="btn btn-sm btn-outline-primary action-btn"
                                  data-action="edit"
                                  data-ors="{{ $record->ors_no }}"
                                  data-status="{{ $record->status }}"
                                  data-bs-toggle="modal"
                                  data-bs-target="#actionModal">
                              <i class="bi bi-pencil"></i>
                          </button>
                          <button type="button"
                                  class="btn btn-sm btn-outline-danger action-btn"
                                  data-action="delete"
                                  data-ors="{{ $record->ors_no }}"
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
              <tr>
                <td colspan="20" class="text-center text-muted py-3">No records found matching parameters.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      @if(method_exists($records, 'links'))
        <div class="mt-1">
          {{ $records->withQueryString()->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

{{-- FILTER MODAL --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-start">
      <form method="GET" action="{{ route('budget.logbook') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">

        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-filter-square-fill me-2 text-secondary"></i>Filter Logbook</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body text-dark">
          {{-- YEAR --}}
          <div class="mb-3">
            <label class="form-label fw-semibold small">Year</label>
            <select name="year" class="form-select">
              <option value="all" @selected(request('year', 'all') == 'all')>All (2025-2026)</option>
              <option value="2026" @selected(request('year') == '2026')>2026</option>
              <option value="2025" @selected(request('year') == '2025')>2025</option>
            </select>
          </div>

          {{-- MONTH --}}
          <div class="mb-3">
            <label class="form-label fw-semibold small">Month</label>
            <select name="month" class="form-select">
              <option value="">All Months</option>
              <option value="01" @selected(request('month') == '01')>January</option>
              <option value="02" @selected(request('month') == '02')>February</option>
              <option value="03" @selected(request('month') == '03')>March</option>
              <option value="04" @selected(request('month') == '04')>April</option>
              <option value="05" @selected(request('month') == '05')>May</option>
              <option value="06" @selected(request('month') == '06')>June</option>
              <option value="07" @selected(request('month') == '07')>July</option>
              <option value="08" @selected(request('month') == '08')>August</option>
              <option value="09" @selected(request('month') == '09')>September</option>
              <option value="10" @selected(request('month') == '10')>October</option>
              <option value="11" @selected(request('month') == '11')>November</option>
              <option value="12" @selected(request('month') == '12')>December</option>
            </select>
          </div>

          {{-- STATUS --}}
          <div class="mb-3">
            <label class="form-label fw-semibold small">Status</label>
            <select name="status" class="form-select">
              <option value="all" @selected(request('status', 'all') == 'all')>All Status</option>
              <option value="for_obligation" @selected(request('status') == 'for_obligation')>For Obligation</option>
              <option value="forwarded_to_accounting" @selected(request('status') == 'forwarded_to_accounting')>Forwarded to Accounting</option>
            </select>
          </div>

          {{-- SORT --}}
          <div class="mb-3">
              <label class="form-label fw-semibold small">Sort Order</label>
              <select name="sort" class="form-select">
                  <option value="latest" @selected(request('sort', 'latest') == 'latest')>Latest Date Received</option>
                  <option value="ors_2025_asc" @selected(request('sort') == 'ors_2025_asc')>ORS No. 2025 (Asc)</option>
                  <option value="ors_2025_desc" @selected(request('sort') == 'ors_2025_desc')>ORS No. 2025 (Desc)</option>
                  <option value="ors_2026_asc" @selected(request('sort') == 'ors_2026_asc')>ORS No. 2026 (Asc)</option>
                  <option value="ors_2026_desc" @selected(request('sort') == 'ors_2026_desc')>ORS No. 2026 (Desc)</option>
              </select>
          </div>           
        </div>

        <div class="modal-footer bg-light">
          <a href="{{ route('budget.logbook') }}" class="btn btn-secondary btn-sm">Reset</a>
          <button type="submit" class="btn btn-success btn-sm fw-bold">Apply Filters</button>
        </div>
      </form>
    </div>
  </div>

</div>

{{-- ACTION MODAL --}}
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionTitle"></h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body" id="actionBody">
            </div>
            <div class="modal-footer" id="actionFooter">
            </div>
        </div>
    </div>
</div>

{{-- ACTION SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function () {
            let action = this.dataset.action;
            let ors = this.dataset.ors;
            let payee = this.dataset.payee ?? '';
            let status = this.dataset.status ?? '';
            let title = document.getElementById('actionTitle');
            let body = document.getElementById('actionBody');
            let footer = document.getElementById('actionFooter');

            if(action === 'view'){
                title.innerHTML = 'View Transaction';
                body.innerHTML = `
                    <p><strong>ORS No:</strong> ${ors}</p>
                    <p><strong>Payee:</strong> ${payee}</p>
                    <p><strong>Status:</strong> ${status}</p>
                `;

                footer.innerHTML = `
                    <button class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Close
                    </button>
                `;
            }

            if(action === 'edit'){

                title.innerHTML = 'Edit Status';

                body.innerHTML = `
                    <form id="editForm"
                          method="POST"
                          action="/accounting/logbook/${payee}/update">

                        @csrf
                        @method('PUT')

                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </form>
                `;

                footer.innerHTML = `
                    <button form="editForm"
                            class="btn btn-success">
                        Save
                    </button>
                `;
            }

            if(action === 'delete'){
                title.innerHTML = 'Delete Transaction';
                body.innerHTML = `
                    Are you sure you want to delete
                    <strong>${ors}</strong>?
                `;

                footer.innerHTML = `
                    <form method="POST"
                          action="/accounting/logbook/${payee}/destroy">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">
                            Delete
                        </button>
                    </form>
                `;
            }
        });
    });
});
</script>
@endsection

@php
    $pageTitle = 'Logbook';
@endphp
