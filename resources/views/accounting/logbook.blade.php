@extends('layouts.app')

@section('title', 'Log Book')

@section('content')

<div class="container-fluid mt-3 px-0" style="min-width: 0; overflow-x: hidden;">

  {{-- TOP BAR --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    @include('layouts.subtab')
  </div>

  {{-- 1ST CARD --}}
  <div class="card p-3 mb-3 m-0 w-100">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

      <h5 class="m-0 fw-bold">Accounting Log Book</h5>

      {{-- SEARCH + FILTER --}}
      <form action="{{ route('accounting.logbook') }}"
            method="GET"
            class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap m-0">
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
  </div>

  {{-- 2ND CARD --}}
  <div class="card m-0 w-100" style="min-height: 55vh; display: grid; min-width: 0;">
    <div class="card-body p-3" style="min-width: 0;">
      
      <div class="table-responsive" style="max-height: 60vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table table-bordered table-hover table-sm align-middle m-0">

          <thead class="table-dark sticky-top" style="z-index: 5;">
            <tr>
              <th style="min-width: 80px;">DV No.</th>
              <th>Date Received</th>
              <th>Date Processed</th>
              <th>OBR Date</th>
              <th style="min-width: 70px;">OBR No.</th>
              <th style="min-width: 160px;">Payee</th>
              <th style="min-width: 300px;">Particulars</th>
              <th style="min-width: 160px;">Particulars Remark</th>
              <th style="min-width: 150px;">Status</th>
              <th style="min-width: 100px;">UACS Code</th>
              <th style="min-width: 110px;">Debit</th>
              <th style="min-width: 110px;">Credit</th>
              <th style="min-width: 80px;">Tax %</th>
              <th style="min-width: 120px;">Tax Remarks</th>
              <th style="min-width: 100px;">Signed</th>
              <th>Date Signed</th>
              <th>Date Forwarded</th>
              <th style="min-width: 150px;">Action</th>
            </tr>
          </thead>

          <tbody>
            @forelse($records as $record)
              <tr>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->dv_no ?? '-' }}</strong></td>
                <td>{{ $record->date_received ?? '-' }}</td>
                <td>{{ $record->date_processed ?? '-' }}</td>
                <td>{{ $record->obr_date ?? '-' }}</td>
                <td style="color: var(--primary); background-color:var(--secondary-variant)"><strong>{{ $record->obr_no ?? '-' }}</strong></td>
                <td><strong>{{ $record->payee ?? '-' }}</strong></td>
                <td><strong>{{ $record->particulars ?? '-' }}</strong></td>
                <td><i>{{ $record->particulars_remark ?? '-' }}</i></td>
                
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

                <td>{{ $record->uac_codes ?? '-' }}</td>
                <td>₱{{ number_format((float) str_replace(',', '', $record->debit ?? 0), 2) }}</td>
                <td>₱{{ number_format((float) str_replace(',', '', $record->credit ?? 0), 2) }}</td>
                <td>{{ $record->tax_percent ?? '-' }}</td>
                
                {{-- TAX REMARKS COLUMN --}}
                <td>{{ $record->tax_remarks ?? '-' }}</td>
                
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
                <td>
                  @if(!empty($record->dv_no))
                  <div class="d-flex gap-1 justify-content-center">
                      <button type="button"
                              class="btn btn-sm btn-outline-info action-btn"
                              data-action="view"
                              data-dv="{{ $record->dv_no }}"
                              data-obr="{{ $record->obr_no }}"
                              data-payee="{{ $record->payee }}"
                              data-status="{{ $record->status }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-eye"></i>
                      </button>
                      <button type="button"
                              class="btn btn-sm btn-outline-primary action-btn"
                              data-action="edit"
                              data-dv="{{ $record->dv_no }}"
                              data-status="{{ $record->status }}"
                              data-bs-toggle="modal"
                              data-bs-target="#actionModal">
                          <i class="bi bi-pencil"></i>
                      </button>
                      <button type="button"
                              class="btn btn-sm btn-outline-danger action-btn"
                              data-action="delete"
                              data-dv="{{ $record->dv_no }}"
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
                <td colspan="18" class="text-center text-muted py-3">
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

<!-- FILTER MODAL -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="GET" action="{{ route('accounting.logbook') }}">

                {{-- Preserve Search --}}
                <input type="hidden" name="search" value="{{ request('search') }}">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="filterModalLabel">
                        <i class="bi bi-funnel"></i> Filter Accounting Logbook
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- MONTH --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Month</label>

                        <select name="month" class="form-select">
                            <option value="all" @selected(request('month','all')=='all')>
                                All Months
                            </option>

                            <option value="1" @selected(request('month')==1)>January</option>
                            <option value="2" @selected(request('month')==2)>February</option>
                            <option value="3" @selected(request('month')==3)>March</option>
                            <option value="4" @selected(request('month')==4)>April</option>
                            <option value="5" @selected(request('month')==5)>May</option>
                            <option value="6" @selected(request('month')==6)>June</option>
                            <option value="7" @selected(request('month')==7)>July</option>
                            <option value="8" @selected(request('month')==8)>August</option>
                            <option value="9" @selected(request('month')==9)>September</option>
                            <option value="10" @selected(request('month')==10)>October</option>
                            <option value="11" @selected(request('month')==11)>November</option>
                            <option value="12" @selected(request('month')==12)>December</option>
                        </select>
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>

                        <select name="status" class="form-select">
                            <option value="all" @selected(request('status','all')=='all')>
                                All Status
                            </option>

                            <option value="Pending" @selected(request('status')=='Pending')>
                                Pending
                            </option>

                            <option value="Processing" @selected(request('status')=='Processing')>
                                Processing
                            </option>

                            <option value="Returned" @selected(request('status')=='Returned')>
                                Returned
                            </option>

                            <option value="Forwarded to Cashier" @selected(request('status')=='Forwarded to Cashier')>
                                Forwarded to Cashier
                            </option>

                            <option value="Paid" @selected(request('status')=='Paid')>
                                Paid
                            </option>

                            <option value="Canceled" @selected(request('status')=='Canceled')>
                                Canceled
                            </option>
                        </select>
                    </div>

                    {{-- SORT --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Sort By</label>

                        <select name="sort" class="form-select">

                            <option value="latest"
                                @selected(request('sort','latest')=='latest')>
                                Latest Date Processed
                            </option>

                            <option value="obr_asc"
                                @selected(request('sort')=='obr_asc')>
                                DV No. (Ascending)
                            </option>

                            <option value="obr_desc"
                                @selected(request('sort')=='obr_desc')>
                                DV No. (Descending)
                            </option>

                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <a href="{{ route('accounting.logbook') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>

                    <button type="submit"
                            class="btn btn-success">
                        <i class="bi bi-check-circle"></i>
                        Apply Filters
                    </button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="actionBody"></div>
            <div class="modal-footer" id="actionFooter"></div>
        </div>
    </div>
</div>

{{-- ACTION SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function () {
            let action = this.dataset.action;
            let dv = this.dataset.dv;
            let obr = this.dataset.obr ?? '';
            let payee = this.dataset.payee ?? '';
            let status = this.dataset.status ?? '';
            let title = document.getElementById('actionTitle');
            let body = document.getElementById('actionBody');
            let footer = document.getElementById('actionFooter');

            if(action === 'view'){
                title.innerHTML = 'View Transaction';
                body.innerHTML = `
                    <p><strong>DV No:</strong> ${dv}</p>
                    <p><strong>OBR No:</strong> ${obr}</p>
                    <p><strong>Payee:</strong> ${payee}</p>
                    <p><strong>Status:</strong> ${status}</p>
                `;

                footer.innerHTML = `
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                `;
            }

            if(action === 'edit'){
                title.innerHTML = 'Edit Status';
                body.innerHTML = `
                    <form id="editForm" method="POST" action="/accounting/logbook/${dv}/update">
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
                    <button form="editForm" class="btn btn-success">Save</button>
                `;
            }

            if(action === 'delete'){
                title.innerHTML = 'Delete Transaction';
                body.innerHTML = `Are you sure you want to delete <strong>${dv}</strong>?`;
                footer.innerHTML = `
                    <form method="POST" action="/accounting/logbook/${dv}/destroy">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">Delete</button>
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