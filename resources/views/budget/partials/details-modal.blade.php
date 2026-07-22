{{-- DETAILS MODAL --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="max-width: 90%;">
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
            <div class="modal-body">

                <div id="detailsLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-success"></div>
                </div>

                <div id="detailsContent">
                    <div class="container-fluid">
                        {{-- ================= REQUEST INFORMATION ================= --}}
                        <div class="row">
                            <div class="col-2 fw-bold fs-4">Request<br>Information</div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Date Received:</div>
                                    <div class="col-7" id="view_date_received">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Issuing Office:</div>
                                    <div class="col-7" id="view_issuing_office">-</div>
                                </div>
                            </div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Payee:</div>
                                    <div class="col-7" id="view_payee">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Classification:</div>
                                    <div class="col-7" id="view_classification">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Particulars:</div>
                                    <div class="col-7" id="view_particulars">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Due Date:</div>
                                    <div class="col-7" id="view_due_date">-</div>
                                </div>

                                <div class="row">
                                    <div class="col-5 fw-bold">Amount:</div>
                                    <div class="col-7">₱<span id="view_amount">0.00</span></div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= REVIEW PROCESSING ================= --}}
                        <div class="row">
                            <div class="col-2 fw-bold fs-4">Review<br>Processing</div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Status:</div>
                                    <div class="col-7" id="view_status">-</div>
                                </div>
                            </div>

                            <div class="col-5">
                                <div id="view_review_history"></div>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= OBLIGATION PROCESSING ================= --}}
                        <div class="row">
                            <div class="col-2 fw-bold fs-4">Obligation<br>Processing</div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">ORS No:</div>
                                    <div class="col-7" id="view_ors_no">-</div>
                                </div>
                            </div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Date Forwarded:</div>
                                    <div class="col-7" id="view_date_forwarded_1">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Date ORS Received:</div>
                                    <div class="col-7" id="view_date_ors_received">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Date Returned:</div>
                                    <div class="col-7" id="view_date_returned_2">-</div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Remarks:</div>
                                    <div class="col-7" id="view_remarks_2">-</div>
                                </div>

                                <div class="row">
                                    <div class="col-5 fw-bold">Date Received:</div>
                                    <div class="col-7" id="view_date_received_2">-</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= FORWARDED TO ACCOUNTING ================= --}}
                        <div class="row">
                            <div class="col-2 fw-bold fs-4">Forwarded<br>to Accounting</div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Date Forwarded:</div>
                                    <div class="col-7" id="view_date_forwarded_accounting">-</div>
                                </div>
                            </div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Remarks:</div>
                                    <div class="col-7" id="view_final_remarks">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Returned Remarks:</div>
                                    <div class="col-7"><span id="detailReturnedRemarks">-</span></div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- ================= PROCESSING METRICS ================= --}}
                        <div class="row">
                            <div class="col-2 fw-bold fs-4">Processing<br>Metrics</div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Total Time in Budget:</div>
                                    <div class="col-7" id="view_total_time_budget">-</div>
                                </div>
                            </div>

                            <div class="col-5">
                                <div class="row mb-2">
                                    <div class="col-5 fw-bold">Total Time:</div>
                                    <div class="col-7" id="view_total_time">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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