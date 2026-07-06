<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="fw-bold">Edit Budget Record</h4>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div id="editError"
                class="alert alert-danger d-none">
            </div>
            <div class="modal-body">

                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')

                   <div class="modal-body">

                    {{-- Record Information --}}
                    <h5 class="fw-bold mb-3">Record Information</h5>

                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label"><strong>ORS No.</strong></label>
                            <input type="text"
                                   id="edit_ors_no"
                                   name="ors_no"
                                   class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label"><strong>Date Received</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_received"
                                   name="date_received"
                                   class="form-control"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Payee</strong></label>
                            <input type="text"
                                   id="edit_payee"
                                   name="payee"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Issuing Office</strong></label>
                           <select id="edit_issuing_office"
                                    name="issuing_office"
                                    class="form-select">
                                <option value="">Select Office</option>
                                @foreach($issuingOffices as $office)
                                    <option value="{{ $office->issuing_office }}">
                                        {{ $office->issuing_office }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Classification</strong></label>
                            <select
                                id="edit_classifications"
                                name="classification"
                                class="form-select">
                                <option value="">Select Classification</option>
                                @foreach($classifications as $classification)
                                    <option value="{{ $classification->classifications }}">
                                        {{ $classification->classifications }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label"><strong>Particulars</strong></label>
                            <textarea class="form-control"
                                      rows="3"
                                      id="edit_particulars"
                                      name="particulars"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>UACS Code</strong></label>
                            <select id="edit_uac_codes" name="uac_codes" class="form-select">
                                @foreach($uacs as $uac)
                                    <option value="{{ $uac->new_uac }}">
                                        {{ $uac->old_uac }} → {{ $uac->new_uac }} | {{ $uac->uac_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Amount</strong></label>
                            <input type="number"
                                   step="0.01"
                                   id="edit_amount"
                                   name="amount"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Returned to End User --}}
                    <h5 class="fw-bold mb-3">Returned to End User</h5>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label"><strong>Date Returned</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_returned_1"
                                   name="date_returned_1"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Date Received</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_received_1"
                                   name="date_received_1"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Remarks</strong></label>
                            <input type="text"
                                   id="edit_remarks_1"
                                   name="remarks_1"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Forwarded --}}
                    <h5 class="fw-bold mb-3">Forwarded</h5>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label"><strong>Date Forwarded</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_forwarded_1"
                                   name="date_forwarded_1"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Date ORS Received</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_ors_received"
                                   name="date_ors_received"
                                   class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Remarks</strong></label>
                            <input type="text"
                                   id="edit_remarks_2"
                                   name="remarks_2"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Returned by Accounting --}}
                    <h5 class="fw-bold mb-3">Returned by Accounting</h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label"><strong>Date Returned</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_returned_2"
                                   name="date_returned_2"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Date Received</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_received_2"
                                   name="date_received_2"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Forwarded to Accounting --}}
                    <h5 class="fw-bold mb-3">Forwarded to Accounting</h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label"><strong>Date Forwarded to Accounting</strong></label>
                            <input type="datetime-local"
                                   id="edit_date_forwarded_accounting"
                                   name="date_forwarded_accounting"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Total Time in Budget</strong></label>
                            <input type="text"
                                   id="edit_total_time_budget"
                                   name="total_time_budget"
                                   class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- Status --}}
                    <h5 class="fw-bold mb-3">Status</h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label"><strong>Status</strong></label>

                            <select id="edit_status"
                                    name="status"
                                    class="form-select">

                                <option value="Pending">Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="For Review">For Review</option>
                                <option value="For Obligation">For Obligation</option>
                                <option value="Forwarded to Accounting">Forwarded to Accounting</option>
                                <option value="Returned">Returned</option>
                                <option value="Canceled">Canceled</option>

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><strong>Total Time</strong></label>
                            <input type="text"
                                   id="edit_total_time"
                                   name="total_time"
                                   class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label"><strong>Final Remarks</strong></label>
                            <textarea class="form-control"
                                      rows="3"
                                      id="edit_final_remarks"
                                      name="final_remarks"></textarea>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" class="btn btn-success">
                    Save Changes
                </button>
            </div>
        </form>
        </div>
    </div>
</div>