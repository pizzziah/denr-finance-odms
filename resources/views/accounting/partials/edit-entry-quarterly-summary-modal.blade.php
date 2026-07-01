<div class="modal fade" id="editSummaryModal{{ $rowId }}" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog text-start">
    <div class="modal-content">
      <form id="editSummaryForm_{{ $rowId }}" method="POST" action="{{ route('accounting.quarterly-summary.update', ['id' => $rowId]) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="target_quarter" value="{{ $selectedQuarter }}">
        
        <div class="modal-header">
          <h5 class="fw-bold mb-0">Modify Ledger Entry</h5>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label class="fw-bold">EMDS Date</label>
            @php 
              try {
                // Issue #1 Fix: Parse custom slash formats precisely using createFromFormat
                $formattedDate = !empty($record->emds_date) ? \Carbon\Carbon::createFromFormat('n/j/Y', $record->emds_date)->format('Y-m-d') : '';
              } catch(\Exception $e) {
                $formattedDate = ''; 
              }
            @endphp
            <input type="date" name="emds_date" class="form-control" value="{{ $formattedDate }}" required>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Date Processed</label>
            @php 
              try {
                // Issue #1 Fix: Explicit slash extraction fallback rules applied
                $formattedProcessedDate = !empty($record->date_processed) ? \Carbon\Carbon::createFromFormat('n/j/Y', $record->date_processed)->format('Y-m-d') : '';
              } catch(\Exception $e) {
                $formattedProcessedDate = ''; 
              }
            @endphp
            <input type="date" name="date_processed" class="form-control" value="{{ $formattedProcessedDate }}" required>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Particulars</label>
            <textarea name="particulars" class="form-control" rows="2" required>{{ $record->particulars }}</textarea>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Amount</label>
            <div class="input-group">
              <span class="input-group-text bg-white">₱</span>
              <input type="number" name="amount" id="amount_input_{{ $rowId }}" step="0.01" class="form-control font-monospace" value="{{ $rawAmount }}" required>
            </div>
            <div class="mt-1 small font-monospace text-muted">
              Live Preview: <span id="amount_preview_{{ $rowId }}" class="fw-bold text-dark">₱{{ number_format((float)$rawAmount, 2) }}</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="fw-bold d-block mb-1">Transaction Type</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_received_{{ $rowId }}" value="received" @checked($txType === 'received') required>
              <label class="form-check-label fw-semibold text-success" for="type_received_{{ $rowId }}">NCA/NTA Received</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_downloaded_{{ $rowId }}" value="downloaded" @checked($txType === 'downloaded')>
              <label class="form-check-label fw-semibold text-danger" for="type_downloaded_{{ $rowId }}">NCA/NTA Downloaded</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_adjustment_{{ $rowId }}" value="adjustment" @checked($txType === 'adjustment')>
              <label class="form-check-label fw-semibold text-warning" for="type_adjustment_{{ $rowId }}">Adjustment</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="fw-bold">ADA Check No.</label>
            <input type="text" name="ada_no" class="form-control font-monospace" value="{{ $record->ada_no }}" placeholder="Enter tracking instrument id...">
          </div>

          <div class="mb-3">
            <label class="fw-bold">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Optional updates...">{{ $record->remarks }}</textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelEditBtn_{{ $rowId }}">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editCancelConfirmModal_{{ $rowId }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center p-4">
        <div class="text-warning mb-3"><i class="bi bi-exclamation-circle-fill display-5"></i></div>
        <h5 class="fw-bold">Discard Changes?</h5>
        <p class="small text-muted mb-4">You have modified this record. Discard edits?</p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-sm btn-light border w-100" id="keepEditingEditBtn_{{ $rowId }}">Keep</button>
          <button type="button" class="btn btn-sm btn-warning w-100" id="discardEditChangesBtn_{{ $rowId }}">Discard</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editAmountInput = document.getElementById('amount_input_{{ $rowId }}');
    const editAmountPreview = document.getElementById('amount_preview_{{ $rowId }}');
    const form = document.getElementById('editSummaryForm_{{ $rowId }}');
    
    let isFormDirty = false;
    form.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('change', () => isFormDirty = true);
        input.addEventListener('input', () => isFormDirty = true);
    });

    if (editAmountInput && editAmountPreview) {
      editAmountInput.addEventListener('input', function() {
        const val = parseFloat(this.value);
        editAmountPreview.textContent = !isNaN(val) ? new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val) : '₱0.00';
      });
    }

    const bsEditModal = new bootstrap.Modal(document.getElementById('editSummaryModal{{ $rowId }}'));
    const bsCancelModal = new bootstrap.Modal(document.getElementById('editCancelConfirmModal_{{ $rowId }}'));

    document.getElementById('cancelEditBtn_{{ $rowId }}').addEventListener('click', function() {
        if (isFormDirty) {
            bsCancelModal.show();
        } else {
            bsEditModal.hide();
        }
    });

    document.getElementById('keepEditingEditBtn_{{ $rowId }}').addEventListener('click', () => bsCancelModal.hide());
    document.getElementById('discardEditChangesBtn_{{ $rowId }}').addEventListener('click', function() {
        isFormDirty = false;
        bsCancelModal.hide();
        bsEditModal.hide();
    });
});
</script>