{{-- ADD RECORD MODAL --}}
<x-modal-layout
  id="addRecordModal"
  title="Add New Accounting Record"
  icon="bi bi-file-earmark-plus"
  maxWidth="80%"
  formId="addRecordForm"
  :action="route('accounting.logbook.store')"
>
  <div class="container-fluid">
    {{-- SECTION 1: BASIC INFORMATION --}}
    <x-modal-section-row title="Basic<br>Information" titleColor="var(--primary)">
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Date Received <span class="text-danger">*</span></label>
          <input type="datetime-local" name="date_received" id="add_date_received" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">OBR Date</label>
          <input type="date" name="obr_date" id="add_obr_date" class="form-control form-control-sm">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">OBR No.</label>
          <input type="text" name="obr_no" id="add_obr_no" class="form-control form-control-sm">
        </div>
        <div class="col-md-8">
          <label class="form-label small fw-semibold">Payee <span class="text-danger">*</span></label>
          <input type="text" name="payee" id="add_payee" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Particulars <span class="text-danger">*</span></label>
          <textarea name="particulars" id="add_particulars" rows="2" class="form-control form-control-sm" required></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Particulars Remark</label>
          <textarea name="particulars_remark" id="add_particulars_remark" rows="2" class="form-control form-control-sm"></textarea>
        </div>
      </div>
    </x-modal-section-row>

    <hr class="my-4">

    {{-- SECTION 2: DEBIT ENTRY --}}
    <x-modal-section-row title="Debit<br>Entry" titleColor="var(--primary)">
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Date Processed</label>
          <input type="datetime-local" name="date_processed" id="add_date_processed" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">DV No.</label>
          <input type="text" name="dv_no" id="add_dv_no" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 small">
          <label class="form-label  fw-semibold">UACS Code <span class="text-danger">*</span></label>
          <select name="uac_codes" id="add_uac_codes" class="form-select small">
            <option value="" class="small">Select UACS Code</option>
            @foreach($uacs as $u)
              <option class="small" value="{{ $u->uac_codes }}">
                {{ $u->uac_codes }} - {{ $u->classification }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Debit Amount <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <span class="input-group-text">₱</span>
            <input type="number" step="0.01" name="debit" id="add_debit" class="form-control form-control-sm" required>
          </div>
        </div>
      </div>
    </x-modal-section-row>

    <hr class="my-4">

    {{-- SECTION 3: CREDIT ENTRIES --}}
    <x-modal-section-row title="Credit<br>Entries" titleColor="var(--primary)">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="small fw-bold text-muted text-uppercase">UACS / Breakdown</span>
        <button type="button" class="btn btn-sm btn-outline-dark" id="addUacsBtn-add">
          <i class="bi bi-plus-circle me-1"></i>Add UACS
        </button>
      </div>

      {{-- Row Container --}}
      <div id="addCreditRows"></div>
    </x-modal-section-row>

    <hr class="my-4">

    {{-- SECTION 4: SIGN-OFF --}}
    <x-modal-section-row title="Sign-off" titleColor="var(--primary)">
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Signed</label>
          <select name="signed" id="add_signed" class="form-select form-select-sm signed-select" data-scope="add">
            <option value="No">No</option>
            <option value="Yes">Yes</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Signed By Accountant</label>
          <input type="text" name="signed_by_accountant" id="add_signed_by_accountant" class="form-control form-control-sm signed-by-input" data-scope="add" disabled>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Date Signed</label>
          <input type="datetime-local" name="date_signed" id="add_date_signed" class="form-control form-control-sm date-signed-input" data-scope="add" disabled>
        </div>
      </div>
    </x-modal-section-row>

    <hr class="my-4">

    {{-- SECTION 5: STATUS --}}
    <x-modal-section-row title="Status &<br>Forwarding" titleColor="var(--primary)">
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
          <select name="status" id="add_status" class="form-select form-select-sm status-select" data-scope="add" required>
            <option value="Pending">Pending</option>
            <option value="Processing">Processing</option>
            <option value="Returned to End User">Returned to End User</option>
            <option value="Returned to Budget">Returned to Budget</option>
            <option value="Paid">Paid</option>
            <option value="Forwarded to Cashier">Forwarded to Cashier</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Date Forwarded</label>
          <input type="datetime-local" name="date_forwarded" id="add_date_forwarded" class="form-control form-control-sm">
        </div>
        <div class="col-md-12 returned-remarks-wrap" data-scope="add" style="display: none;">
          <label class="form-label small fw-semibold text-danger">Returned Remarks</label>
          <textarea name="returned_remarks" id="add_returned_remarks" rows="2" class="form-control form-control-sm border-danger"></textarea>
        </div>
      </div>
    </x-modal-section-row>
  </div>
</x-modal-layout>

{{-- Template row cloned by JS for both Add and Edit credit-entry repeaters --}}
<template id="creditRowTemplate">
  <div class="row g-2 align-items-end credit-row mb-2 border-bottom pb-2">
    <div class="col-md-4">
      <label class="form-label small mb-1">UACS Code</label>
      <select name="credit_uac_codes[]" class="form-select form-select-sm add-credit-uacs">
        <option value="">Select UACS Code</option>
        @foreach($uacs as $u)
          <option value="{{ $u->uac_codes }}">
            {{ $u->uac_codes }} - {{ $u->classification }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Credit</label>
      <input type="number" step="0.01" name="credit_amounts[]" class="form-control form-control-sm" placeholder="0.00">
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1">Tax %</label>
      <input type="text" name="credit_tax_percent[]" class="form-control form-control-sm" placeholder="5.00%">
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1">Tax Remarks</label>
      <input type="text" name="credit_tax_remarks[]" class="form-control form-control-sm">
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-sm btn-outline-danger remove-credit-row" title="Remove">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  </div>
</template>

<template id="debitRowTemplate">
  <div class="row g-2 align-items-end debit-row mb-2 border-bottom pb-2">

    <div class="col-md-6">
      <label class="form-label small mb-1">UACS Code</label>

      <select
        name="debit_uac_codes[]"
        class="form-select form-select-sm add-debit-uacs">

        <option value="">Select UACS Code</option>

        @foreach($uacs as $u)
          <option value="{{ $u->uac_codes }}">
            {{ $u->uac_codes }} - {{ $u->classification }}
          </option>
        @endforeach

      </select>
    </div>

    <div class="col-md-5">
      <label class="form-label small mb-1">Amount</label>

      <input
        type="number"
        step="0.01"
        name="debit_amounts[]"
        class="form-control form-control-sm"
        placeholder="0.00">
    </div>

    <div class="col-md-1">
      <button
        type="button"
        class="btn btn-sm btn-outline-danger remove-debit-row"
        title="Remove">

        <i class="bi bi-trash"></i>

      </button>
    </div>

  </div>
</template>