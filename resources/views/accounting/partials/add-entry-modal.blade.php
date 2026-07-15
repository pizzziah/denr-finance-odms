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
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="add_lbl_date_received">Date Received <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="date_received" id="add_date_received">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">
                      OBR No.
                    </label>
                    <input type="text" class="form-control form-control-sm" id="add_obr_no" name="obr_no" readonly>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">
                      OBR Date
                    </label>
                    <input type="date" class="form-control form-control-sm" id="add_obr_date" name="obr_date">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold" id="add_lbl_payee">Payee <span class="text-danger d-none">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="payee" id="add_payee">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold" id="add_lbl_particulars">Particulars <span class="text-danger d-none">*</span></label>
                    <textarea class="form-control form-control-sm" rows="2" name="particulars" id="add_particulars"></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Particular Remarks</label>
                    <textarea class="form-control form-control-sm" rows="2" name="particulars_remark" id="add_particulars_remark"></textarea>
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
                <div class="row g-2 mb-2 align-items-end">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold" id="add_lbl_date_processed">Date Processed <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="date_processed" id="add_date_processed">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">DV No.</label>
                    <input type="text" class="form-control form-control-sm" name="dv_no" id="add_dv_no">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code</label>
                    <select class="form-select form-select-sm" name="uacs_code" id="add_uacs_code">
                      <option value="">Select UACS Code...</option>
                      @foreach($uacCodes as $uac)
                        <option value="{{ $uac->uac_codes }}">{{ $uac->uac_codes }} - {{ $uac->order_title }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-outline-dark w-100" id="add_btn_add_uacs">
                      <i class="bi bi-plus-circle me-1"></i> Add UACS
                    </button disabled>
                  </div>
                </div>

                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Debit</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="total_debit" id="add_total_debit" min="0">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Credit</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="credit" value="0" id="add_credit">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax %</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="tax_percentage" id="add_tax_percentage">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax Remarks</label>
                    <input type="text" class="form-control form-control-sm" name="tax_remarks" id="add_tax_remarks">
                  </div>
                </div>
                <hr>
                <div id="addAccountingRows"></div>
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
                    <label class="form-label small fw-semibold" id="add_lbl_signed">Signed <span class="text-danger d-none">*</span></label>
                    <select class="form-select form-select-sm" name="signed" id="add_signed">
                      <option value="No" selected>No</option>
                      <option value="Yes">Yes</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="add_lbl_signed_by_accountant">Signed By <span class="text-danger d-none">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="signed_by_accountant" id="add_signed_by_accountant">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="add_lbl_date_signed">Date Signed <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="date_signed" id="add_date_signed">
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
                      <option value="Returned to End User">Returned to End User</option>
                      <option value="Returned to Budget">Returned to Budget</option>
                      <option value="Cancelled">Cancelled</option>
                      <option value="Forwarded to Cashier">Forwarded to Cashier</option>
                      <option value="Paid">Paid</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="add_lbl_date_forwarded">Date Forwarded <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" class="form-control form-control-sm" name="date_forwarded" id="add_date_forwarded">
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

        <template id="uacsRowTemplate">
          <div class="border rounded p-3 mt-3 bg-light uacs-row">
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label small fw-semibold">UACS</label>
                <!-- FIX: Ensured names conform to structured object keys array standard syntax -->
                <select class="form-select form-select-sm" name="rows[][uac_codes]">
                  <option value="">Select UACS...</option>
                  @foreach($uacCodes as $uac)
                    <option value="{{ $uac->uac_codes }}">{{ $uac->uac_codes }} — {{ $uac->order_title }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-semibold">Credit</label>
                <input type="number" step="0.01" class="form-control form-control-sm" name="rows[][credit]">
              </div>

              <div class="col-md-2">
                <label class="form-label small fw-semibold">Tax %</label>
                <input type="number" step="0.01" class="form-control form-control-sm" name="rows[][tax_percent]">
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-semibold">Tax Remarks</label>
                <input class="form-control form-control-sm" name="rows[][tax_remarks]">
              </div>

              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-uacs">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </template>
      </form>
    </div>
  </div>
</div>