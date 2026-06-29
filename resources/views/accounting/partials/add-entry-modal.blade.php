@if(!$isLocked)
<div class="modal fade" id="addSummaryModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('accounting.quarterly-summary.store') }}" onsubmit="return confirm('Please confirm execution: Are you sure you want to write this row entry into the active Quarter {{ $selectedQuarter }} database log?')">
        @csrf
        <input type="hidden" name="target_quarter" value="{{ $selectedQuarter }}">
        
        <div class="modal-header">
          <h5 class="fw-bold mb-0" id="addSummaryModalLabel">Add Quarterly Summary Entry</h5>
        </div>
        
        <div class="modal-body">
          {{-- DATE INPUT (Datepicker) --}}
          <div class="mb-3">
            <label class="fw-bold">Date</label>
            <input type="date" name="emds_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
          </div>

          {{-- PARTICULARS INPUT --}}
          <div class="mb-3">
            <label class="fw-bold">Particulars</label>
            <textarea name="particulars" class="form-control" rows="2" placeholder="Enter ledger context..." required></textarea>
          </div>

          {{-- DV NUMBER INPUT --}}
          <div class="mb-3">
            <label class="fw-bold">DV No.</label>
            <input type="text" name="dv_no" class="form-control" placeholder="Enter DV tracking code...">
          </div>

          {{-- TRANSACTION TYPE SELECTOR --}}
          <div class="mb-3">
            <label class="fw-bold d-block mb-1">Transaction Type</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_received" value="received" checked required>
              <label class="form-check-label fw-semibold text-success" for="type_received">NCA/NTA Received</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_downloaded" value="downloaded">
              <label class="form-check-label fw-semibold text-danger" for="type_downloaded">NCA/NTA Downloaded</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="transaction_type" id="type_adjustment" value="adjustment">
              <label class="form-check-label fw-semibold text-warning" for="type_adjustment">Adjustment</label>
            </div>
          </div>

          {{-- AMOUNT INPUT WITH LIVE VISUAL FORMATTER --}}
          <div class="mb-3">
            <label class="fw-bold">Amount</label>
            <div class="input-group">
              <span class="input-group-text bg-white">₱</span>
              <input type="number" name="amount" id="amount_input" step="0.01" class="form-control font-monospace" placeholder="0.00" required>
            </div>
            <div class="mt-1 small font-monospace text-muted">
              Live Preview: <span id="amount_preview" class="fw-bold text-dark">₱0.00</span>
            </div>
          </div>

          {{-- ADA CHECK NO INPUT --}}
          <div class="mb-3">
            <label class="fw-bold">ADA Check No.</label>
            <input type="text" name="ada_check_no" class="form-control font-monospace" placeholder="Enter tracking instrument id...">
          </div>

          {{-- REMARKS INPUT --}}
          <div class="mb-3">
            <label class="fw-bold">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Optional audit tracking metadata updates..."></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <x-button type="button" variant="secondary" data-bs-dismiss="modal">
            Cancel
          </x-button>
          <x-button type="submit" variant="primary">Save</x-button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('amount_input');
    const amountPreview = document.getElementById('amount_preview');

    if (amountInput && amountPreview) {
      amountInput.addEventListener('input', function() {
        const val = parseFloat(this.value);
        if (!isNaN(val)) {
          amountPreview.textContent = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
          }).format(val);
        } else {
          amountPreview.textContent = '₱0.00';
        }
      });
    }
  });
</script>
@endif