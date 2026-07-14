<div class="modal fade" id="editRecordModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable" style="max-width: 90%;">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="fw-bold">Edit Accounting Record</h4>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="editForm" method="POST">
          @csrf
          @method('PUT')

          <div class="container-fluid">

            {{-- ================= RECORD INFORMATION ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">
                Record<br>Information
              </div>
              <div class="col-10">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="edit_lbl_date_received">Date Received <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" id="edit_date_received" name="date_received" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">OBR Date</label>
                    <input type="date" id="edit_obr_date" name="obr_date" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">OBR No.</label>
                    <input type="text" id="edit_obr_no" name="obr_no" class="form-control form-control-sm" readonly>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold" id="edit_lbl_payee">Payee <span class="text-danger d-none">*</span></label>
                    <input type="text" id="edit_payee" name="payee" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold" id="edit_lbl_particulars">Particulars <span class="text-danger d-none">*</span></label>
                    <textarea id="edit_particulars" name="particulars" rows="2" class="form-control form-control-sm"></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Particular Remarks</label>
                    <textarea id="edit_particulars_remark" name="particulars_remark" rows="2" class="form-control form-control-sm"></textarea>
                  </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= ACCOUNTING PROCESSING ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">
                Accounting<br>Processing
              </div>
              <div class="col-10">
                <div class="row g-2 mb-2 align-items-end">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold" id="edit_lbl_date_processed">Date Processed <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" id="edit_date_processed" name="date_processed" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">DV No.</label>
                    <input type="text" id="edit_dv_no" name="dv_no" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code</label>
                    <select class="form-select form-select-sm" name="uacs_code" id="edit_uacs_code">
                      <option value="">Select UACS Code...</option>
                      @foreach($uacCodes as $uac)
                        <option value="{{ $uac->uac_codes }}">
                          {{ $uac->uac_codes }} — {{ $uac->order_title }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary">
                      <i class="bi bi-plus-circle me-1"></i> Add UACS
                    </button disabled>
                  </div>
                </div>

                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Debit</label>
                    <input type="number" step="0.01" min="0" id="edit_total_debit" name="total_debit" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Credit</label>
                    <input type="number" step="0.01" value="0" id="edit_credit" name="credit" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax %</label>
                    <input type="number" step="0.01" id="edit_tax_percentage" name="tax_percentage" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tax Remarks</label>
                    <input type="text" id="edit_tax_remarks" name="tax_remarks" class="form-control form-control-sm">
                  </div>
                </div>

                <div class="border rounded p-3 mt-3 bg-light">
                  <div class="row g-2">
                    <div id="editAccountingRows"></div>
                  </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= SIGNATURE ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">
                Signature
              </div>
              <div class="col-10">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="edit_lbl_signed">Signed <span class="text-danger d-none">*</span></label>
                    <select id="edit_signed" name="signed" class="form-select form-select-sm">
                      <option value="No">No</option>
                      <option value="Yes">Yes</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="edit_lbl_signed_by_accountant">Signed By <span class="text-danger d-none">*</span></label>
                    <input type="text" id="edit_signed_by_accountant" name="signed_by_accountant" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="edit_lbl_date_signed">Date Signed <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" id="edit_date_signed" name="date_signed" class="form-control form-control-sm">
                  </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= ROUTING & STATUS ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">
                Routing &<br>Status
              </div>
              <div class="col-10">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                    <select id="edit_status" name="status" class="form-select form-select-sm" required>
                      <option value="Pending">Pending</option>
                      <option value="Processing">Processing</option>
                      <option value="Returned to End User">Returned to End User</option>
                      <option value="Returned to Budget">Returned to Budget</option>
                      <option value="Cancelled">Cancelled</option>
                      <option value="Forwarded to Cashier">Forwarded to Cashier</option>
                      <option value="Paid">Paid</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold" id="edit_lbl_date_forwarded">Date Forwarded <span class="text-danger d-none">*</span></label>
                    <input type="datetime-local" id="edit_date_forwarded" name="date_forwarded" class="form-control form-control-sm">
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="editForm" class="btn btn-success">Save Changes</button>
      </div>
      <template id="editUacsRowTemplate">
        <div class="border rounded p-3 mt-3 bg-light edit-uacs-row">
          <div class="row g-2">
            <div class="col-md-3">
              <label class="form-label small fw-semibold">
                UACS
              </label>
              <select
                class="form-select form-select-sm"
                name="rows[][uac_codes]">
                <option value="">
                  Select UACS...
                </option>
                @foreach($uacCodes as $uac)
                  <option value="{{ $uac->uac_codes }}">
                    {{ $uac->uac_codes }} — {{ $uac->order_title }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">
                Credit
              </label>
              <input
                type="number"
                step="0.01"
                class="form-control form-control-sm"
                name="rows[][credit]">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold">
                Tax %
              </label>
              <input
                type="number"
                step="0.01"
                class="form-control form-control-sm"
                name="rows[][tax_percent]">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">
                Tax Remarks
              </label>
              <input
                class="form-control form-control-sm"
                name="rows[][tax_remarks]">
            </div>
            <div class="col-md-1 d-flex align-items-end">
              <button
                type="button"
                class="btn btn-danger remove-edit-uacs">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>
