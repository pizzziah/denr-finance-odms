{{-- DETAILS MODAL --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="fw-bold">Budget Record Details</h4>

                <button class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="detailsBody">

            </div>

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