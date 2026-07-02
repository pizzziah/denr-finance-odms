<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <form action="{{ route('accounting.logbook.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addRecordModalLabel">
                        <i class="bi bi-file-earmark-plus me-2"></i>
                        Add Log Book Record
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        {{-- DV No --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">DV No.</label>
                            <input
                                type="text"
                                class="form-control"
                                name="dv_no"
                                required>
                        </div>

                        {{-- Date Received --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date Received</label>
                            <input
                                type="date"
                                class="form-control"
                                name="date_received">
                        </div>

                        {{-- Date Processed --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date Processed</label>
                            <input
                                type="date"
                                class="form-control"
                                name="date_processed">
                        </div>

                        {{-- OBR Date --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">OBR Date</label>
                            <input
                                type="date"
                                class="form-control"
                                name="obr_date">
                        </div>

                        {{-- OBR No --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">OBR No.</label>
                            <input
                                type="text"
                                class="form-control"
                                name="obr_no">
                        </div>

                        {{-- Payee --}}
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Payee</label>
                            <input
                                type="text"
                                class="form-control"
                                name="payee"
                                required>
                        </div>

                        {{-- Particulars --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Particulars</label>
                            <textarea
                                class="form-control"
                                rows="3"
                                name="particulars"
                                required></textarea>
                        </div>

                        {{-- Remarks --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Particulars Remark</label>
                            <textarea
                                class="form-control"
                                rows="2"
                                name="particulars_remark"></textarea>
                        </div>

                        {{-- Amount --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Amount (₱)</label>
                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="total_debit"
                                required>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>

                            <select
                                class="form-select"
                                name="status">
                                <option value="Pending">Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="Returned">Returned</option>
                                <option value="Forwarded to Cashier">Forwarded to Cashier</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>

                        {{-- Signed --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Signed</label>

                            <select
                                class="form-select"
                                name="signed">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>

                        {{-- Date Signed --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date Signed</label>
                            <input
                                type="date"
                                class="form-control"
                                name="date_signed">
                        </div>

                        {{-- Date Forwarded --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date Forwarded</label>
                            <input
                                type="date"
                                class="form-control"
                                name="date_forwarded">
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Save Record
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>