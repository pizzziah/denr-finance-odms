<div class="modal fade" id="editSummaryModal{{ $rowId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
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
            <label class="fw-bold">Date Processed</label>
            @php 
              try {
                $formattedProcessedDate = !empty($record->date_processed) ? \Carbon\Carbon::createFromFormat('n/j/Y', $record->date_processed)->format('Y-m-d') : '';
              } catch(\Exception $e) {
                $formattedProcessedDate = ''; 
              }
            @endphp
            <input type="date" name="date_processed" class="form-control" value="{{ $formattedProcessedDate }}" required>
          </div>

          <div class="mb-3">
            <label class="fw-bold">DV Number</label>
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
            <div class="form-check">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_adjustment_{{ $rowId }}" value="adjustment" @checked($txType === 'adjustment') required>
              <label class="form-check-label" style="color: #7909FF;" for="type_adjustment_{{ $rowId }}">Adjustment</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_received_{{ $rowId }}" value="received" @checked($txType === 'received')>
              <label class="form-check-label" style="color: #9D6B0B;" for="type_received_{{ $rowId }}">NCA/NTA Received</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_downloaded_{{ $rowId }}" value="downloaded" @checked($txType === 'downloaded')>
              <label class="form-check-label" style="color: var(--error)" for="type_downloaded_{{ $rowId }}">NCA/NTA Downloaded</label>
            </div>
          </div>

          <hr class="my-3">

          <div class="mb-3">
            <label class="fw-bold">EMDS Date</label>
            @php 
              try {
                $formattedDate = !empty($record->emds_date) ? \Carbon\Carbon::createFromFormat('n/j/Y', $record->emds_date)->format('Y-m-d') : '';
              } catch(\Exception $e) {
                $formattedDate = ''; 
              }
            @endphp
            <input type="date" name="emds_date" class="form-control" value="{{ $formattedDate }}">
          </div>

          <div class="mb-3">
            <label class="fw-bold">ADA Check No.</label>
            <input type="text" name="ada_no" class="form-control" value="{{ $record->ada_no }}" placeholder="Enter tracking instrument id...">
          </div>

          <div class="mb-3">
            <label class="fw-bold">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Optional updates...">{{ $record->remarks }}</textarea>
          </div>
        </div>
        
        <div class="modal-footer">
  <button type="button" class="btn btn-secondary" id="cancelEditBtn_{{ $rowId }}">Cancel</button>
  <button type="submit" class="btn btn-primary">Save</button>
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
    const mainModalEl = document.getElementById('editSummaryModal{{ $rowId }}');
    const cancelModalEl = document.getElementById('editCancelConfirmModal_{{ $rowId }}');
    const form = document.getElementById('editSummaryForm_{{ $rowId }}');
    
    const editAmountInput = document.getElementById('amount_input_{{ $rowId }}');
    const editAmountPreview = document.getElementById('amount_preview_{{ $rowId }}');
    
    // We will save the original string version of the form data here
    let originalFormDataString = "";

    // Function to convert form inputs into a simple URL-encoded string for clean tracking
    function getFormSnapshot() {
        return new URLSearchParams(new FormData(form)).toString();
    }

    // Capture the initial state as soon as the modal is fully visible to the user
    mainModalEl.addEventListener('shown.bs.modal', function () {
        originalFormDataString = getFormSnapshot();
        console.log("Original snapshot captured!");
    });

    // Live Amount Preview formatting
    if (editAmountInput && editAmountPreview) {
      editAmountInput.addEventListener('input', function() {
        const val = parseFloat(this.value);
        editAmountPreview.textContent = !isNaN(val) ? new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val) : '₱0.00';
      });
    }

    // Handle the Cancel button click
    const cancelBtn = document.getElementById('cancelEditBtn_{{ $rowId }}');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Take a fresh snapshot of the current state right now
            const currentFormDataString = getFormSnapshot();
            
            // If the text strings do not match, a user changed something!
            const isFormDirty = (originalFormDataString !== currentFormDataString);
            
            console.log("Cancel button clicked.");
            console.log("Original Form State:", originalFormDataString);
            console.log("Current Form State: ", currentFormDataString);
            console.log("Is form dirty?:", isFormDirty);

            const bsCancelModal = bootstrap.Modal.getOrCreateInstance(cancelModalEl);
            const bsEditModal = bootstrap.Modal.getOrCreateInstance(mainModalEl);

            if (isFormDirty) {
                console.log("Changes detected. Showing confirmation pop-up...");
                bsCancelModal.show();
            } else {
                console.log("No changes detected. Safely closing main modal.");
                bsEditModal.hide();
            }
        });
    }

    // "Keep Editing" button logic
    document.getElementById('keepEditingEditBtn_{{ $rowId }}').addEventListener('click', () => {
        const bsCancelModal = bootstrap.Modal.getOrCreateInstance(cancelModalEl);
        bsCancelModal.hide();
    });
    
    // "Discard" button logic
    document.getElementById('discardEditChangesBtn_{{ $rowId }}').addEventListener('click', function() {
        const bsCancelModal = bootstrap.Modal.getOrCreateInstance(cancelModalEl);
        const bsEditModal = bootstrap.Modal.getOrCreateInstance(mainModalEl);
        
        bsCancelModal.hide();
        bsEditModal.hide();
        form.reset(); 
    });
});
</script>