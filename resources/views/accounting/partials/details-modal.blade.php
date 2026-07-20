<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="detailsModalLabel"><i class="bi bi-eye"></i> Transaction Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        {{-- Loading Spinner --}}
        <div id="modalLoading" class="text-center my-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        {{-- Error Container --}}
        <div id="modalError" class="alert alert-danger d-none text-center m-0">
          Could not fetch data. Please try again.
        </div>

        {{-- Dynamic Content Area --}}
        <div id="modalContent" class="d-none">
          <div class="row g-3">
            <div class="col-md-6">
              <strong>Transaction ID:</strong> <span id="view-txn-id" class="text-muted"></span>
            </div>
            <div class="col-md-6">
              <strong>DV No:</strong> <span id="view-dv-no" class="fw-bold"></span>
            </div>
            <div class="col-md-6">
              <strong>OBR No:</strong> <span id="view-obr-no"></span>
            </div>
            <div class="col-md-6">
              <strong>Payee:</strong> <span id="view-payee"></span>
            </div>
            <div class="col-md-12">
              <strong>Particulars:</strong>
              <p id="view-particulars" class="border p-2 bg-light rounded m-0 whitespace-pre-wrap"></p>
            </div>
            <div class="col-md-6">
              <strong>Total Debit:</strong> ₱<span id="view-debit" class="fw-bold"></span>
            </div>
            <div class="col-md-6">
              <strong>Status:</strong> <span id="view-status" class="badge"></span>
            </div>
          </div>

          <hr>
          <h5 class="mb-2">Credit Entries Breakdown</h5>
          <div class="table-responsive">
            <table class="table table-sm table-bordered m-0">
              <thead class="table-light">
                <tr>
                  <th>UACS Code</th>
                  <th class="text-end">Credit Amount</th>
                  <th class="text-center">Tax (%)</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody id="view-credit-entries">
                <!-- Dynamic rows injected here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>