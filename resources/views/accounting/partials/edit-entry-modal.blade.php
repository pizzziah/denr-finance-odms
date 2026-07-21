{{-- EDIT RECORD MODAL --}}
<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%;">
    <div class="modal-content">
      
      {{-- HEADER --}}
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="editRecordModalLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Transaction Details: <span id="editTransactionLabel" class="text-warning"></span>
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
          <input type="hidden" id="editTransactionId" name="transaction_id">

          <div id="editFormBody" style="display: none;" class="container-fluid">

            {{-- SECTION 1: BASIC INFORMATION --}}
            <div class="row py-2">
              <div class="col-md-2 fw-bold text-secondary fs-5 border-end-md pe-md-4 mb-3 mb-md-0">
                Basic<br>Information
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Received <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_received" id="edit_date_received" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">OBR Date</label>
                    <input type="date" name="obr_date" id="edit_obr_date" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">OBR No.</label>
                    <input type="text" name="obr_no" id="edit_obr_no" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold">Payee <span class="text-danger">*</span></label>
                    <input type="text" name="payee" id="edit_payee" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars <span class="text-danger">*</span></label>
                    <textarea name="particulars" id="edit_particulars" rows="2" class="form-control form-control-sm" required></textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars Remark</label>
                    <textarea name="particulars_remark" id="edit_particulars_remark" rows="2" class="form-control form-control-sm"></textarea>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 2: DEBIT ENTRY --}}
            <div class="row py-2">
              <div class="col-md-2 fw-bold text-secondary fs-5 border-end-md pe-md-4 mb-3 mb-md-0">
                Debit<br>Entry
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Processed </label>
                    <input type="datetime-local" name="date_processed" id="edit_date_processed" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">DV No.</label>
                    <input type="text" name="dv_no" id="edit_dv_no" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code <span class="text-danger">*</span></label>
                    <select id="edit_uac_codes" name="uac_codes" class="form-select searchable-select" required>
                      <option value="">Select UACS Code</option>
                        @foreach($uacs as $u)
                            <option value="{{ $u->uac_codes }}">
                                {{ $u->uac_codes }} - {{ $u->classification }}
                            </option>
                        @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Debit Amount <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₱</span>
                      <input type="number" step="0.01" name="debit" id="edit_debit" class="form-control form-control-sm" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 3: CREDIT ENTRIES --}}
            <div class="row py-2">
              <div class="col-md-2 fw-bold text-secondary fs-5 border-end-md pe-md-4 mb-3 mb-md-0">
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
            <div class="row py-2">
              <div class="col-md-2 fw-bold text-secondary fs-5 border-end-md pe-md-4 mb-3 mb-md-0">
                Sign-off
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed</label>
                    <select name="signed" id="edit_signed" class="form-select form-select-sm signed-select" data-scope="edit">
                      <option value="No">No</option>
                      <option value="Yes">Yes</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed By Accountant</label>
                    <input type="text" name="signed_by_accountant" id="edit_signed_by_accountant" class="form-control form-control-sm signed-by-input" data-scope="edit" disabled>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Signed</label>
                    <input type="datetime-local" name="date_signed" id="edit_date_signed" class="form-control form-control-sm date-signed-input" data-scope="edit" disabled>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- SECTION 5: STATUS --}}
            <div class="row py-2">
              <div class="col-md-2 fw-bold text-secondary fs-5 border-end-md pe-md-4 mb-3 mb-md-0">
                Status &<br>Forwarding
              </div>
              <div class="col-md-10 ps-md-4">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" id="edit_status" class="form-select form-select-sm status-select" data-scope="edit" required>
                      <option value="Pending">Pending</option>
                      <option value="Processing">Processing</option>
                      <option value="Returned to End User">Returned to End User</option>
                      <option value="Returned to Budget">Returned to Budget</option>
                      <option value="Forwarded to Cashier">Forwarded to Cashier</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Forwarded</label>
                    <input type="datetime-local" name="date_forwarded" id="edit_date_forwarded" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-12 returned-remarks-wrap" data-scope="edit" style="display: none;">
                    <label class="form-label small fw-semibold text-danger">Returned Remarks</label>
                    <textarea name="returned_remarks" id="edit_returned_remarks" rows="2" class="form-control form-control-sm border-danger"></textarea>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>

      {{-- FOOTER --}}
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="editRecordForm" class="btn btn-success btn-sm px-4">Save Changes</button>
      </div>

    </div>
  </div>
</div>