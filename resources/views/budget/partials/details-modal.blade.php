{{-- DETAILS MODAL --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="fw-bold">Budget Record Details</h4>
                <button class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>
            <div class="px-4 mt-2">
                <div class="d-flex justify-content-between align-items-center mt-2">
                        <div>
                            <h5 class="fw-bold mb-0" id="transactionTitle"></h5>
                            <small class="text-muted" id="transactionSubtitle"></small>
                        </div>

                        <div class="d-flex gap-2">

                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="detailsEditBtn">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button"
                            class="btn btn-outline-danger btn-sm"
                            id="detailsDeleteBtn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
            <hr>
            <div class="modal-body" id="detailsBody"></div>

            <div class="modal-footer">
                <button class="btn btn-primary" onclick="printDetails()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button class="btn btn-success"
                        data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>