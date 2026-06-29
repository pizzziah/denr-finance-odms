<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h4 class="modal-title fw-bold">
                        Edit Budget Record
                    </h4>

                    <button class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Record Information --}}
                    <h5 class="fw-bold mb-3">Record Information</h5>

                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">ORS No.</label>
                            <input type="text"
                                   name="ors_no"
                                   id="edit_ors_no"
                                   class="form-control"
                                   readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date Received</label>
                            <input type="date"
                                   name="date_received"
                                   id="edit_date_received"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payee</label>
                            <input type="text"
                                   name="payee"
                                   id="edit_payee"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Issuing Office</label>
                            <input type="text"
                                   name="issuing_office"
                                   id="edit_issuing_office"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Classification</label>
                            <input type="text"
                                   name="classification"
                                   id="edit_classification"
                                   class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Particulars</label>
                            <textarea name="particulars"
                                      id="edit_particulars"
                                      class="form-control"
                                      rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">UACS Code</label>
                            <input type="text"
                                   name="uac_codes"
                                   id="edit_uac_codes"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   id="edit_amount"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Returned --}}
                    <h5 class="fw-bold mb-3">
                        Returned to End User
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label>Date Returned</label>
                            <input type="date"
                                   name="date_returned_1"
                                   id="edit_date_returned_1"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Date Received</label>
                            <input type="date"
                                   name="date_received_1"
                                   id="edit_date_received_1"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Remarks</label>
                            <input type="text"
                                   name="remarks_1"
                                   id="edit_remarks_1"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Forwarded --}}
                    <h5 class="fw-bold mb-3">
                        Forwarded
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label>Date Forwarded</label>
                            <input type="date"
                                   name="date_forwarded_1"
                                   id="edit_date_forwarded_1"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Date ORS Received</label>
                            <input type="date"
                                   name="date_ors_received"
                                   id="edit_date_ors_received"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Remarks</label>
                            <input type="text"
                                   name="remarks_2"
                                   id="edit_remarks_2"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Status --}}
                    <h5 class="fw-bold mb-3">
                        Status
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Status</label>

                            <select name="status"
                                    id="edit_status"
                                    class="form-select">

                                <option>Pending</option>
                                <option>Processing</option>
                                <option>For Review</option>
                                <option>For Obligation</option>
                                <option>Forwarded to Accounting</option>
                                <option>Returned</option>
                                <option>Canceled</option>

                            </select>

                        </div>

                        <div class="col-md-6">
                            <label>Final Remarks</label>

                            <input type="text"
                                   name="final_remarks"
                                   id="edit_final_remarks"
                                   class="form-control">

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary"
                            data-bs-dismiss="modal"
                            type="button">
                        Cancel
                    </button>

                    <button class="btn btn-success">
                        Save Changes
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>