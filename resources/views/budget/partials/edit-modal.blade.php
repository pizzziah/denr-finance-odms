{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable" style="max-width:90%;">
    <div class="modal-content">

      {{-- HEADER --}}
      <div class="modal-header">
        <h4 class="fw-bold">Edit Budget Record</h4>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- ERROR --}}
      <div id="editError" class="alert alert-danger d-none mx-3"></div>

      {{-- BODY --}}
      <div class="modal-body">
        <form id="editForm" method="POST">
          @csrf
          @method('PUT')

          <div class="container-fluid">

            {{-- ================= REQUEST INFORMATION ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Request<br>Information</div>

              <div class="col-10">
                <div class="row g-2">

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Received</label>
                    <input type="datetime-local" id="edit_date_received" name="date_received"
                           class="form-control form-control-sm" readonly>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">ORS No.</label>
                    <input type="text" id="edit_ors_no" name="ors_no"
                           class="form-control form-control-sm">
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payee</label>
                    <input type="text" id="edit_payee" name="payee"
                           class="form-control form-control-sm">
                  </div>

                  {{-- ISSUING OFFICE (TOMSELECT) --}}
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Issuing Office</label>
                    <select id="edit_issuing_office" name="issuing_office"
                            class="form-select form-select-sm">
                      <option value="">Select Office</option>
                      @foreach($issuingOffices as $office)
                        <option value="{{ $office->issuing_office }}">
                          {{ $office->issuing_office }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  {{-- CLASSIFICATION (TOMSELECT) --}}
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Classification</label>
                    <select id="edit_classifications" name="classification"
                            class="form-select form-select-sm">
                      <option value="">Select Classification</option>
                      @foreach($classifications as $classification)
                        <option value="{{ $classification->classifications }}">
                          {{ $classification->classifications }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars</label>
                    <textarea id="edit_particulars" name="particulars"
                              rows="2" class="form-control form-control-sm"></textarea>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code</label>
                    <select id="edit_uac_codes" name="uac_codes"
                            class="form-select form-select-sm">
                      @foreach($uacs as $uac)
                        <option value="{{ $uac->new_uac }}">
                          {{ $uac->old_uac }} → {{ $uac->new_uac }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Amount</label>
                    <input type="number" step="0.01" id="edit_amount"
                           name="amount" class="form-control form-control-sm">
                  </div>

                </div>
              </div>
            </div>

            <hr>

            {{-- ================= REVIEW PROCESSING ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Review<br>Processing</div>
              
              <div class="col-10">
                <div class="row g-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold text-muted">Review Transactions</div>

                    <button type="button"
                            class="btn btn-sm btn-outline-dark"
                            id="btnAddReviewRow">
                        + Add Review
                    </button>
                    </div>

                    <div id="reviewRows1"></div>
                    <div id="reviewRowsContainer" class="mt-2"></div>

                    <template id="reviewRowTemplate">
                        <div class="border rounded p-2 mb-2 review-row bg-light">
                            <div class="row g-2">

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        Date Returned
                                    </label>
                                    <input
                                        type="datetime-local"
                                        name="review_date_returned[]"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        Remarks
                                    </label>
                                    <input
                                        type="text"
                                        name="review_remarks[]"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">
                                        Date Received
                                    </label>
                                    <input
                                        type="datetime-local"
                                        name="review_date_received[]"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm w-100 btnRemoveReview">
                                        ✕
                                    </button>
                                </div>

                            </div>
                        </div>
                    </template>
              </div>
            </div>
            
            <hr>

            {{-- ================= OBLIGATION PROCESSING ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Obligation<br>Processing</div>

              <div class="col-10">
                <div class="row g-2">

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date Forwarded</label>
                    <input type="datetime-local" id="edit_date_forwarded_1"
                           name="date_forwarded_1" class="form-control form-control-sm">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date ORS Received</label>
                    <input type="datetime-local" id="edit_date_ors_received"
                           name="date_ors_received" class="form-control form-control-sm">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Remarks</label>
                    <input type="text" id="edit_remarks_2"
                           name="remarks_2" class="form-control form-control-sm">
                  </div>

                </div>
              </div>
            </div>

            <hr>

            {{-- ================= RETURNED BY ACCOUNTING ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Returned<br>Accounting</div>

              <div class="col-10">
                <div class="row g-2">

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Date Returned</label>
                    <input type="datetime-local" id="edit_date_returned_2"
                           name="date_returned_2" class="form-control form-control-sm">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Date Received</label>
                    <input type="datetime-local" id="edit_date_received_2"
                           name="date_received_2" class="form-control form-control-sm">
                  </div>

                </div>
              </div>
            </div>

            <hr>

            {{-- ================= FORWARDED TO ACCOUNTING ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Forwarded<br>Accounting</div>

              <div class="col-10">
                <div class="row g-2">

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Date Forwarded</label>
                    <input type="datetime-local" id="edit_date_forwarded_accounting"
                           name="date_forwarded_accounting"
                           class="form-control form-control-sm">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Total Time Budget</label>
                    <input type="text" id="edit_total_time_budget"
                           name="total_time_budget"
                           class="form-control form-control-sm">
                  </div>

                </div>
              </div>
            </div>
        
            <hr>

            {{-- ================= STATUS ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Status</div>

              <div class="col-10">
                <div class="row g-2">

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="edit_status" name="status"
                            class="form-select form-select-sm">
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
                    <label class="form-label small fw-semibold">Total Time</label>
                    <input type="text" id="edit_total_time"
                           name="total_time"
                           class="form-control form-control-sm">
                  </div>

                  <div class="col-12">
                    <label class="form-label small fw-semibold">Final Remarks</label>
                    <textarea id="edit_final_remarks"
                              name="final_remarks"
                              rows="2"
                              class="form-control form-control-sm"></textarea>
                  </div>

                </div>
              </div>
            </div>

          </div>
        </form>
      </div>

      {{-- FOOTER --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="submit" form="editForm" class="btn btn-success">
          Save Changes
        </button>
      </div>

    </div>
  </div>
</div>
