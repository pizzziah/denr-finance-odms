@if(!$isLocked)
<div class="modal fade" id="addSummaryModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addSummaryForm" method="POST" action="{{ route('accounting.quarterly-summary.store') }}">
        @csrf
        <input type="hidden" name="target_quarter" value="{{ $selectedQuarter }}">
        
        <div class="modal-header">
          <h5 class="fw-bold mb-0">Add Quarterly Summary Entry</h5>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label class="fw-bold">Date Processed</label>
            <input type="date" name="date_processed" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="fw-bold">DV Number</label>
            <input type="number" name="particulars" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Amount <span class="fw-medium" style="color: var(--error);">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-white">₱</span>
              <input type="number" name="amount" id="amount_input" step="0.01" class="form-control font-monospace" placeholder="0.00" required>
            </div>
            <div class="mt-1 small font-monospace text-muted">
              Live Preview: <span id="amount_preview" class="fw-bold text-dark">₱0.00</span>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="fw-bold d-block mb-1">Transaction Type <span class="fw-medium" style="color: var(--error);">*</span></label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_adjustment" value="adjustment" checked required>
              <label class="form-check-label" style="color: #7909FF;" for="type_adjustment">Adjustment</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_received" value="received">
              <label class="form-check-label" style="color: #9D6B0B;" for="type_received">NCA/NTA Received</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_downloaded" value="downloaded">
              <label class="form-check-label" style="color: var(--error)" for="type_downloaded">NCA/NTA Downloaded</label>
            </div>
          </div>

          <hr class="my-3">

          <div class="mb-3">
            <label class="fw-bold">EMDS Date</label>
            <input type="date" name="emds_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
          </div>

          <div class="mb-3">
            <label class="fw-bold">ADA Check No.</label>
            <input type="text" name="ada_no" class="form-control" placeholder="Enter ADA Check Number...">
          </div>

          <div class="mb-3">
            <label class="fw-bold">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Enter remarks (if any)..."></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <x-button type="button" variant="secondary" data-bs-dismiss="modal"  id="cancelAddBtn">Cancel</x-button>
          <x-button type="submit" variant="primary">Save Entry</x-button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="addCancelConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center p-4">
        <div class="text-warning mb-3"><i class="bi bi-exclamation-circle-fill display-5"></i></div>
        <h5 class="fw-bold">Unsaved Changes</h5>
        <p class="small text-muted mb-4">You have modified this form. Do you want to discard your changes?</p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-sm btn-light border w-100" id="keepEditingAddBtn">No, Keep</button>
          <button type="button" class="btn btn-sm btn-warning w-100" id="discardAddChangesBtn">Yes, Discard</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('amount_input');
    const amountPreview = document.getElementById('amount_preview');
    const form = document.getElementById('addSummaryForm');
    
    let isFormDirty = false;
    form.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('change', () => isFormDirty = true);
        input.addEventListener('input', () => isFormDirty = true);
    });

    if (amountInput && amountPreview) {
      amountInput.addEventListener('input', function() {
        const val = parseFloat(this.value);
        amountPreview.textContent = !isNaN(val) ? new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val) : '₱0.00';
      });
    }

    const bsAddModal = new bootstrap.Modal(document.getElementById('addSummaryModal'));
    const bsCancelModal = new bootstrap.Modal(document.getElementById('addCancelConfirmModal'));

    document.getElementById('cancelAddBtn').addEventListener('click', function() {
        if (isFormDirty) {
            bsCancelModal.show();
        } else {
            bsAddModal.hide();
        }
    });

    document.getElementById('keepEditingAddBtn').addEventListener('click', () => bsCancelModal.hide());
    document.getElementById('discardAddChangesBtn').addEventListener('click', function() {
        isFormDirty = false;
        form.reset();
        bsCancelModal.hide();
        bsAddModal.hide();
    });
});
</script>
@endif