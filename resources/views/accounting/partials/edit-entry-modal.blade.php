{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
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
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Received <span class="text-danger">*</span></label>
                    <input type="date" id="edit_date_received" name="date_received" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">OBR Date</label>
                    <input type="date" id="edit_obr_date" name="obr_date" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">OBR No.</label>
                    <input type="number" id="edit_obr_no" name="obr_no" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">ORS No.</label>
                    <input type="number" id="edit_ors_no" name="ors_no" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold">Payee</label>
                    <input type="text" id="edit_payee" name="payee" class="form-control form-control-sm bg-light" readonly>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label small fw-semibold">Particulars</label>
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
                    <label class="form-label small fw-semibold">Date Processed</label>
                    <input type="date" id="edit_date_processed" name="date_processed" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">DV No.</label>
                    <input type="number" id="edit_dv_no" name="dv_no" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code</label>
                    <select class="form-select form-select-sm" name="uacs_code" disabled>
                      <option value="">(To be implemented)</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-outline-dark w-100" id="edit_btn_add_uacs">
                      <i class="bi bi-plus-circle me-1"></i> Add UACS
                    </button>
                  </div>
                </div>

                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Debit</label>
                    <input type="number" step="0.01" id="edit_debit" name="total_debit" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Credit</label>
                    <input type="number" step="0.01" id="edit_credit" name="credit" class="form-control form-control-sm">
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
                    <div id="accountingRows"></div>
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
                    <label class="form-label small fw-semibold">Signed By</label>
                    <input type="text" id="edit_signed_by_accountant" name="signed_by_accountant" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Signed</label>
                    <select id="edit_signed" name="signed" class="form-select form-select-sm">
                      <option value="No">No</option>
                      <option value="Yes">Yes</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Signed</label>
                    <input type="date" id="edit_date_signed" name="date_signed" class="form-control form-control-sm">
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
                      <option value="Returned">Returned</option>
                      <option value="Cancelled">Cancelled</option>
                      <option value="Forwarded to Cashier">Forwarded to Cashier</option>
                      <option value="Paid">Paid</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Forwarded</label>
                    <input type="date" id="edit_date_forwarded" name="date_forwarded" class="form-control form-control-sm">
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

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalContexts = [
        { prefix: 'add_', formId: 'addForm' },
        { prefix: 'edit_', formId: 'editForm' }
    ];

    modalContexts.forEach(context => {
        const statusEl = document.getElementById(`${context.prefix}status`);
        const signedEl = document.getElementById(`${context.prefix}signed`);
        const signedByEl = document.getElementById(`${context.prefix}signed_by_accountant`);
        const dateSignedEl = document.getElementById(`${context.prefix}date_signed`);
        const dateForwardedEl = document.getElementById(`${context.prefix}date_forwarded`);
        const formEl = document.getElementById(context.formId);

        if (!statusEl) return;

        function updateFormInteractivity() {
            const status = statusEl.value;
            const signed = signedEl.value;
            const signedByHasValue = signedByEl && signedByEl.value.trim() !== '';
            const dateSignedHasValue = dateSignedEl && dateSignedEl.value !== '';

            // Rule 1: Enable/disable routing choices based on verification states
            Array.from(statusEl.options).forEach(opt => {
                if (opt.value === 'Forwarded to Cashier') {
                    const dynamicValid = (signed === 'Yes' && signedByHasValue && dateSignedHasValue);
                    opt.disabled = !dynamicValid;
                    if (!dynamicValid && status === 'Forwarded to Cashier') {
                        statusEl.value = 'Processing';
                    }
                }
                if (opt.value === 'Paid') {
                    opt.disabled = (status !== 'Forwarded to Cashier' && status !== 'Paid');
                }
            });

            // Rule 2: Core baseline input locking states
            if (['Pending', 'Cancelled', 'Returned', 'Paid'].includes(status)) {
                if(signedEl) signedEl.disabled = true;
                if(signedByEl) signedByEl.disabled = true;
                if(dateSignedEl) dateSignedEl.disabled = true;
                if(dateForwardedEl) dateForwardedEl.disabled = true;
            } else if (status === 'Processing') {
                if(signedEl) signedEl.disabled = false;
                if (signed === 'Yes') {
                    if(signedByEl) signedByEl.disabled = false;
                    if(dateSignedEl) dateSignedEl.disabled = false;
                } else {
                    if(signedByEl) signedByEl.disabled = true;
                    if(dateSignedEl) dateSignedEl.disabled = true;
                }
                if(dateForwardedEl) dateForwardedEl.disabled = true;
            } else if (status === 'Forwarded to Cashier') {
                if(signedEl) signedEl.disabled = false;
                if(signedByEl) signedByEl.disabled = false;
                if(dateSignedEl) dateSignedEl.disabled = false;
                if(dateForwardedEl) dateForwardedEl.disabled = false;
            }

            // Rule 3: Absolute view lockdown configs for terminal logs
            if (['Paid', 'Cancelled'].includes(status)) {
                Array.from(formEl.elements).forEach(el => {
                    if (el !== statusEl && el.type !== 'submit' && el.name !== '_token' && el.name !== '_method') {
                        el.disabled = true;
                    }
                });
            } else {
                Array.from(formEl.elements).forEach(el => {
                    if (!['signed', 'signed_by_accountant', 'date_signed', 'date_forwarded', 'uacs_code'].includes(el.name) && el.type !== 'submit') {
                        if (el.name === 'payee') return; // Keep readonly payee clean
                        el.disabled = false;
                    }
                });
            }
        }

        // Attach listeners across all trigger variables
        statusEl.addEventListener('change', updateFormInteractivity);
        if(signedEl) signedEl.addEventListener('change', updateFormInteractivity);
        if(signedByEl) signedByEl.addEventListener('input', updateFormInteractivity);
        if(dateSignedEl) dateSignedEl.addEventListener('change', updateFormInteractivity);

        // Run baseline setup pass
        updateFormInteractivity();
    });
});
</script>