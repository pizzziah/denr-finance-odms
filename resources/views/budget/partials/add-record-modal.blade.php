{{-- ADD RECORD MODAL --}}
<div class="modal fade" id="addRecordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width:90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="fw-bold">Add Budget Record</h4>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('budget.logbook.store') }}" method="POST">
                    @csrf
                        <div class="container-fluid">

                            {{-- REQUEST INFORMATI --}}
                            <div class="row">

                                <div class="col-2 fw-bold fs-4">
                                    Request<br>Information
                                </div>

                                <div class="col-10">
                                    <div class="row g-2">
                                        
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Date Received
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" name="date_received"class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}"required>
                                        </div>

                                        {{-- Due Date --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Due Date </label>
                                            <input type="date"  name="due_date"class="form-control form-control-sm">
                                        </div>

                                        {{-- ORS --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">ORS No. </label>
                                            <input type="text" name="ors_no" class="form-control form-control-sm" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        </div>

                                        {{-- Status --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Status
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="Pending"> Pending</option>
                                                <option value="Processing">Processing </option>
                                                <option value="For Review">For Review </option>
                                                <option value="For Obligation" selected> For Obligation</option>
                                                <option value="Forwarded to Accounting">Forwarded to Accounting </option>
                                                <option value="Returned">Returned</option> <option value="Paid"> Paid </option>
                                                <option value="Canceled">Canceled</option>
                                            </select>
                                        </div>

                                        {{-- Issuing Office --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Issuing Office
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select name="issuing_office"class="form-select">
                                                <option value="">Select Office</option>
                                                @foreach($issuingOffices as $office)
                                                    <option value="{{ $office->issuing_office }}">
                                                        {{ $office->issuing_office }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Classification --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Classification
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select
                                                name="classification"
                                                class="form-select form-select-sm"
                                                required>

                                                <option value="">
                                                    Select Classification
                                                </option>

                                                @foreach($classifications as $classification)

                                                    <option value="{{ $classification->classifications }}">
                                                        {{ $classification->classifications }}
                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        {{-- Payee --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Payee
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="payee"
                                                maxlength="255"
                                                class="form-control form-control-sm"
                                                required>
                                        </div>

                                        {{-- Particulars --}}
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">
                                                Particulars
                                                <span class="text-danger">*</span>
                                            </label>

                                            <textarea
                                                name="particulars"
                                                rows="2"
                                                class="form-control form-control-sm"
                                                required></textarea>
                                        </div>

                                        {{-- Particular Remark --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Particulars Remark
                                            </label>

                                            <textarea
                                                name="particulars_remark"
                                                rows="2"
                                                class="form-control form-control-sm"></textarea>
                                        </div>

                                        {{-- UAC --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                UAC Code
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select
                                                name="uac_codes"
                                                class="form-select form-select-sm"
                                                required>

                                                <option value="">
                                                    Select UAC
                                                </option>

                                                @foreach($uacs as $uac)

                                                    <option value="{{ $uac->new_uac }}">
                                                        {{ $uac->new_uac }}
                                                        -
                                                        {{ $uac->uac_title }}
                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        {{-- Amount --}}
                                        <div class="col-md-6">

                                            <label class="form-label fw-semibold">
                                                Amount
                                                <span class="text-danger">*</span>
                                            </label>

                                            <div class="input-group input-group-sm">

                                                <span class="input-group-text">
                                                    ₱
                                                </span>

                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    name="amount"
                                                    class="form-control"
                                                    required>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <hr>
                            {{-- ================= REVIEW PROCESSING ================= --}}
                            <div class="row">

                                <div class="col-2 fw-bold fs-4">
                                    Review<br>Processing
                                </div>

                                <div class="col-10">

                                    <div class="row g-2">

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Date Returned
                                            </label>

                                            <input
                                                type="date"
                                                name="date_returned_1"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Remarks
                                            </label>

                                            <input
                                                type="text"
                                                name="remarks_1"
                                                maxlength="255"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Date Received
                                            </label>

                                            <input
                                                type="date"
                                                name="date_received_1"
                                                class="form-control form-control-sm">
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <hr>

                            {{-- ================= OBLIGATION PROCESSING ================= --}}
                            <div class="row">

                                <div class="col-2 fw-bold fs-4">
                                    Obligation<br>Processing
                                </div>

                                <div class="col-10">

                                    <div class="row g-2">

                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                ORS No.
                                            </label>

                                            <input
                                                type="text"
                                                name="ors_no"
                                                class="form-control form-control-sm"
                                                inputmode="numeric"
                                                pattern="[0-9]*"
                                                oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                Date Forwarded
                                            </label>

                                            <input
                                                type="date"
                                                name="date_forwarded_1"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                Date ORS Received
                                            </label>

                                            <input
                                                type="date"
                                                name="date_ors_received"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                Date Returned
                                            </label>

                                            <input
                                                type="date"
                                                name="date_returned_2"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Remarks
                                            </label>

                                            <input
                                                type="text"
                                                name="remarks_2"
                                                maxlength="255"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Date Received
                                            </label>

                                            <input
                                                type="date"
                                                name="date_received_2"
                                                class="form-control form-control-sm">
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <hr>
                            {{-- ================= FORWARDED TO ACCOUNTING ================= --}}
                            <div class="row">

                                <div class="col-2 fw-bold fs-4">
                                    Forwarded<br>to Accounting
                                </div>

                                <div class="col-10">

                                    <div class="row g-2">

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Date Forwarded to Accounting
                                            </label>

                                            <input
                                                type="date"
                                                name="date_forwarded_accounting"
                                                class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Final Remarks
                                            </label>

                                            <input
                                                type="text"
                                                name="final_remarks"
                                                class="form-control form-control-sm">
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <hr>

                            {{-- ================= PROCESSING METRICS ================= --}}
                            <div class="row">

                                <div class="col-2 fw-bold fs-4">
                                    Processing<br>Metrics
                                </div>

                                <div class="col-10">

                                    <div class="row g-2">

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Total Time in Budget
                                            </label>

                                            <input
                                                type="text"
                                                name="total_time_budget"
                                                class="form-control form-control-sm"
                                                value="00:00:00"
                                                readonly>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                Total Time
                                            </label>

                                            <input
                                                type="text"
                                                name="total_time"
                                                class="form-control form-control-sm"
                                                value="00:00:00"
                                                readonly>
                                        </div>

                                    </div>

                                </div>

                            </div>

                    </div> {{-- End modal-body --}}

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="reset"
                            class="btn btn-outline-secondary">
                            Clear Form
                        </button>

                        <button
                            type="submit"
                            class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Save Record
                        </button>

                    </div>

                </form>

        </div>
    </div>
</div>