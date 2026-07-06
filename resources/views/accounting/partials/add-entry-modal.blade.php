<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable" style="max-width: 90%;">
    <div class="modal-content">

      <form action="{{ route('accounting.logbook.store') }}" method="POST" id="addForm">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="addRecordModalLabel">
            <i class="bi bi-file-earmark-plus me-2"></i>
            Add Log Book Record
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="container-fluid">

            {{-- ================= RECORD INFORMATION ================= --}}
            <div class="row">
              <div class="col-md-2 fw-bold fs-4">
                Record<br>Information
              </div>
              <div class="col-md-10">
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Received <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-sm" name="date_received" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">OBR Date</label>
                    <input type="date" class="form-control form-control-sm" name="obr_date">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">OBR No.</label>
                    <input type="number" class="form-control form-control-sm" name="obr_no">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">ORS No.</label>
                    <input type="number" class="form-control form-control-sm" name="ors_no">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold">Payee</label>
                    <input type="text" class="form-control form-control-sm bg-light" name="payee" value="Fetched from budget logbook" readonly>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold">Particulars <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" rows="2" name="particulars" required></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Particular Remarks</label>
                    <textarea class="form-control form-control-sm" rows="2" name="particulars_remark"></textarea>
                  </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= ACCOUNTING PROCESSING ================= --}}
            <div class="row">
              <div class="col-md-2 fw-bold fs-4">
                Accounting<br>Processing
              </div>
              <div class="col-md-10">
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Processed</label>
                    <input type="date" class="form-control form-control-sm" name="date_processed">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">DV No.</label>
                    <input type="number" class="form-control form-control-sm" name="dv_no">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code</label>
                    <select class="form-select form-select-sm" name="uacs_code" disabled>
                      <option value="">(To be implemented)</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Debit</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="total_debit">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Credit</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="credit">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax %</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="tax_percentage">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tax Remarks</label>
                    <input type="text" class="form-control form-control-sm" name="tax_remarks">
                  </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= SIGNATURE ================= --}}
            <div class="row">
              <div class="col-md-2 fw-bold fs-4">
                Signature
              </div>
              <div class="col-md-10">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed By</label>
                    <input type="text" class="form-control form-control-sm" name="signed_by_accountant" id="add_signed_by_accountant">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed</label>
                    <select class="form-select form-select-sm" name="signed" id="add_signed">
                      <option value="No" selected>No</option>
                      <option value="Yes">Yes</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Signed</label>
                    <input type="date" class="form-control form-control-sm" name="date_signed" id="add_date_signed">
                  </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= ROUTING & STATUS ================= --}}
            <div class="row">
              <div class="col-md-2 fw-bold fs-4">
                Routing &<br>Status
              </div>
              <div class="col-md-10">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="status" id="add_status" required>
                      <option value="Pending" selected>Pending</option>
                      <option value="Processing">Processing</option>
                      <option value="Returned">Returned</option>
                      <option value="Cancelled">Cancelled</option>
                      <option value="Forwarded to Cashier">Forwarded to Cashier</option>
                      <option value="Paid">Paid</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Forwarded</label>
                    <input type="date" class="form-control form-control-sm" name="date_forwarded" id="add_date_forwarded">
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Save Record
          </button>
        </div>

      </form>

    </div>
  </div>
</div>