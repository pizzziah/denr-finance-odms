{{-- EDIT RECORD MODAL --}}
<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 80%;">
    <div class="modal-content">
      
      {{-- HEADER --}}
      <div class="modal-header text-white" style="background-color: var(--primary);">
        <h5 class="modal-title fw-bold" id="editRecordModalLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Transaction Details: <span id="editTransactionLabel"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- BODY --}}
      <div class="modal-body">
        
        {{-- Loading Spinner --}}
        <div id="editLoading" class="text-center my-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-2">Retrieving transaction details...</p>
        </div>

        <form id="editRecordForm" method="POST" action="">
          @csrf
          @method('PUT')
          <input type="hidden" id="editTransactionId" name="transaction_id" value="{{ old('transaction_id') }}">

          <div id="editFormBody" style="display: none;" class="container-fluid">

            {{-- Summary Error Alert Inside Modal --}}
            @if($errors->any())
              <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Please fix the following issues:</strong>
                <ul class="mb-0 small ps-3">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            {{-- SECTION 1: BASIC INFORMATION --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Basic<br>Information
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Received <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_received" id="edit_date_received" class="form-control form-control-sm @error('date_received') is-invalid @enderror" value="{{ old('date_received') }}" required>
                    @error('date_received')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">OBR Date</label>
                    <input type="date" name="obr_date" id="edit_obr_date" class="form-control form-control-sm @error('obr_date') is-invalid @enderror" value="{{ old('obr_date') }}">
                    @error('obr_date')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">OBR No.</label>
                    <input type="text" name="obr_no" id="edit_obr_no" class="form-control form-control-sm @error('obr_no') is-invalid @enderror" value="{{ old('obr_no') }}">
                    @error('obr_no')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-8">
                    <label class="form-label small fw-semibold">Payee <span class="text-danger">*</span></label>
                    <input type="text" name="payee" id="edit_payee" class="form-control form-control-sm @error('payee') is-invalid @enderror" value="{{ old('payee') }}" required>
                    @error('payee')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars <span class="text-danger">*</span></label>
                    <textarea name="particulars" id="edit_particulars" rows="2" class="form-control form-control-sm @error('particulars') is-invalid @enderror" required>{{ old('particulars') }}</textarea>
                    @error('particulars')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars Remark</label>
                    <textarea name="particulars_remark" id="edit_particulars_remark" rows="2" class="form-control form-control-sm @error('particulars_remark') is-invalid @enderror">{{ old('particulars_remark') }}</textarea>
                    @error('particulars_remark')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION REVIEW ENTRY --}}
            <div class="row section-review-processing">
              <div class="col-2 fw-bold fs-5">Review<br>Processing</div>
              <div class="col-10">
                <div class="row g-2">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold text-muted">Review Transactions</div>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="btnAddReview">+ Add Review</button>
                  </div>

                  <div id="additionalReviews"></div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 2: DEBIT ENTRY --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Debit<br>Entry
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Processed</label>
                    <input type="datetime-local" name="date_processed" id="edit_date_processed" class="form-control form-control-sm @error('date_processed') is-invalid @enderror" value="{{ old('date_processed') }}">
                    @error('date_processed')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">DV No.</label>
                    <input type="text" name="dv_no" id="edit_dv_no" class="form-control form-control-sm @error('dv_no') is-invalid @enderror" value="{{ old('dv_no') }}">
                    @error('dv_no')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code</label>
                    <select id="edit_uac_codes" name="uac_codes" class="form-select form-select-sm @error('uac_codes') is-invalid @enderror">
                        <option value="">Select UAC</option>
                        @foreach($uacs as $uac)
                            <option value="{{ $uac->uac_codes }}" class="small" {{ old('uac_codes') == $uac->uac_codes ? 'selected' : '' }}>
                              {{ $uac->uac_codes }}
                            </option>
                        @endforeach
                    </select>
                    @error('uac_codes')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Debit Amount</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₱</span>
                      <input type="number" class="form-control" id="edit_debit" name="debit" value="{{ old('debit') }}" readonly>
                    </div>
                    <small class="text-muted d-block mt-1">
                      Original debit from Budget cannot be modified.
                    </small>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                    <h6 class="mb-0 fw-bold">
                      Additional Debit Entries
                    </h6>

                    <button type="button" id="addDebitBtn-edit" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-plus-circle me-1"></i>Add Debit
                    </button>
                  </div>

                  <div id="editDebitRows"></div>

                  <div class="d-flex justify-content-between align-items-center bg-light p-2 border rounded mt-2">
                    <span class="small fw-bold text-muted">Total Debit: ₱<span id="editDebitRowsTotal">0.00</span></span>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 3: CREDIT ENTRIES --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Credit<br>Entries
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="small fw-bold text-muted text-uppercase">UACS / Breakdown</span>
                  <button type="button" class="btn btn-sm btn-outline-dark" id="addUacsBtn-edit">
                    <i class="bi bi-plus-circle me-1"></i>Add UACS
                  </button>
                </div>

                {{-- Row Container --}}
                <div id="editCreditRows"></div>

                {{-- Calculation Row --}}
                <div class="d-flex justify-content-between align-items-center bg-light p-2 border rounded mt-2">
                  <span class="small fw-bold text-muted">Total Debit: ₱<span id="editDebitTotal">0.00</span></span>
                  <span class="small fw-bold text-success">Total Credit: ₱<span id="editCreditTotal">0.00</span></span>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 4: SIGN-OFF --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Sign-off
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed</label>
                    <select name="signed" id="edit_signed" class="form-select form-select-sm signed-select" data-scope="edit">
                      <option value="No" {{ old('signed') == 'No' ? 'selected' : '' }}>No</option>
                      <option value="Yes" {{ old('signed') == 'Yes' ? 'selected' : '' }}>Yes</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed By Accountant</label>
                    <input type="text" name="signed_by_accountant" id="edit_signed_by_accountant" class="form-control form-control-sm signed-by-input" data-scope="edit" value="{{ old('signed_by_accountant') }}" disabled>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Signed</label>
                    <input type="datetime-local" name="date_signed" id="edit_date_signed" class="form-control form-control-sm date-signed-input" data-scope="edit" value="{{ old('date_signed') }}" disabled>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 5: STATUS --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Status &<br>Forwarding
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" id="edit_status" class="form-select form-select-sm status-select @error('status') is-invalid @enderror" data-scope="edit" required>
                      <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                      <option value="Processing" {{ old('status') == 'Processing' ? 'selected' : '' }}>Processing</option>
                      <option value="Returned to End User" {{ old('status') == 'Returned to End User' ? 'selected' : '' }}>Returned to End User</option>
                      <option value="Returned to Budget" {{ old('status') == 'Returned to Budget' ? 'selected' : '' }}>Returned to Budget</option>
                      <option value="Forwarded to Cashier" {{ old('status') == 'Forwarded to Cashier' ? 'selected' : '' }}>Forwarded to Cashier</option>
                      <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Forwarded</label>
                    <input type="datetime-local" name="date_forwarded" id="edit_date_forwarded" class="form-control form-control-sm @error('date_forwarded') is-invalid @enderror" value="{{ old('date_forwarded') }}">
                    @error('date_forwarded')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="col-md-12 returned-remarks-wrap" data-scope="edit" style="display: none;">
                    <label class="form-label small fw-semibold text-danger">Returned Remarks</label>
                    <textarea name="returned_remarks" id="edit_returned_remarks" rows="2" class="form-control form-control-sm border-danger">{{ old('returned_remarks') }}</textarea>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>

      {{-- FOOTER --}}
      <div class="modal-footer bg-light">
          <x-button type="button" variant="secondary" data-bs-dismiss="modal">Cancel</x-button>
          <x-button type="submit" variant="primary" form="editRecordForm">Save Record</x-button>
      </div>
    </div>
  </div>
</div>