{{-- ADD MODAL --}}
<div class="modal fade" id="addRecordModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:90%;">
    <div class="modal-content" style="height:60vh;">
      
    {{-- HEADER --}}
    <div class="modal-header">
      <h4 class="fw-bold">Add Budget Record</h4>
      <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    
    {{-- ERROR --}}
    <div id="addError" class="alert alert-danger d-none mx-3"></div>
    
    {{-- BODY --}}
    <div class="modal-body">
      <form id="addForm" method="POST" action="{{ route('budget.logbook.store') }}">
        @csrf
          <div class="container-fluid">

            {{-- REQUEST INFORMATION --}}
            <div class="row">
              <div class="col-2 fw-bold fs-5">Request<br>Information</div>
              <div class="col-10">
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date Received <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_received" class="form-control form-control-sm" value="{{ now('Asia/Manila')->format('Y-m-d\TH:i') }}" required>                  
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Due Date</label>
                    <input type="date" id="add_due_date" name="due_date" class="form-control form-control-sm">
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">ORS No.</label>
                    <input type="text" id="add_ors_no" name="ors_no" class="form-control form-control-sm" inputmode="numeric" pattern="[0-9]*">
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Issuing Office <span class="text-danger">*</span></label>
                    <select id="add_issuing_office" name="issuing_office" class="form-select form-select-sm" required>
                      <option value="">Select Office</option>
                      @foreach($issuingOffices as $office)
                        <option value="{{ $office->issuing_office }}">{{ $office->issuing_office }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Classification <span class="text-danger">*</span></label>
                    <select id="add_classifications" name="classification" class="form-select form-select-sm" required>
                      <option value="">Select Classification</option>
                      @foreach($classifications as $classification)
                        <option value="{{ $classification->classifications }}">{{ $classification->classifications }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payee <span class="text-danger">*</span></label>
                    <input type="text" id="add_payee" name="payee" class="form-control form-control-sm" required>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">UACS Code <span class="text-danger">*</span></label>
                    <select id="add_uac_codes" name="uac_codes" class="form-select form-select-sm" required>
                      <option value="">Select UACS Code</option>
                      @foreach($uacs as $uac)
                        <option value="{{ $uac->new_uac }}">
                          {{ $uac->old_uac }} → {{ $uac->new_uac }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="add_amount" name="amount" class="form-control form-control-sm" required>
                  </div> 

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars <span class="text-danger">*</span></label>
                    <textarea id="add_particulars" name="particulars" rows="2" class="form-control form-control-sm" required></textarea>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Particulars Remark</label>
                    <textarea id="add_particulars_remark" name="particulars_remark" rows="2" class="form-control form-control-sm"></textarea>
                  </div>
                                   
                </div>
              </div>
            </div>

            <hr>

            {{-- STATUS --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Status</div>
              <div class="col-10">
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                    <select id="add_status" name="status" class="form-select form-select-sm" required>
                      <option value="Pending" selected>Pending</option>
                      <option value="Processing">Processing</option>
                      <option value="For Review">For Review</option>
                      <option value="For Obligation">For Obligation</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>

      {{-- FOOTER --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addForm" class="btn btn-success">Save Record</button>
      </div>

    </div>
  </div>
</div>