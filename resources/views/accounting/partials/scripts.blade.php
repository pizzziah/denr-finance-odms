<script>
document.addEventListener('DOMContentLoaded', function () {

  const showUrlBase   = "{{ url('/accounting/logbook') }}"; 
  const updateUrlBase = "{{ url('/accounting/logbook') }}"; 
  const rowTemplate   = document.getElementById('creditRowTemplate');

  const RETURN_STATUSES = ['Returned to End User', 'Returned to Budget'];

  /* ---------------------------------------------------------------- *
   * Helper: Format datetime for <input type="datetime-local">
   * ---------------------------------------------------------------- */
  function formatDateTimeLocal(dateString) {
    if (!dateString || dateString === '-') return '';
    return dateString.replace(' ', 'T').substring(0, 16);
  }

  /* ---------------------------------------------------------------- *
   * Credit-entry repeater
   * ---------------------------------------------------------------- */
  function addCreditRow(containerId, prefill) {
    const container = document.getElementById(containerId);
    if (!container || !rowTemplate) return;

    const clone = rowTemplate.content.cloneNode(true);
    const wrap  = clone.querySelector('.credit-row');

    if (prefill) {
      wrap.querySelector('[name="credit_uac_codes[]"]').value   = prefill.uac_codes ?? '';
      wrap.querySelector('[name="credit_amounts[]"]').value     = prefill.credit ?? '';
      wrap.querySelector('[name="credit_tax_percent[]"]').value = prefill.tax_percent ?? '';
      wrap.querySelector('[name="credit_tax_remarks[]"]').value = prefill.tax_remarks ?? '';
    }

    container.appendChild(clone);
  }

  document.getElementById('addUacsBtn-add')?.addEventListener('click', () => addCreditRow('addCreditRows'));
  document.getElementById('addUacsBtn-edit')?.addEventListener('click', () => addCreditRow('editCreditRows'));

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.remove-credit-row');
    if (!btn) return;
    btn.closest('.credit-row')?.remove();
    recalcEditCreditTotal();
  });

  document.addEventListener('input', function (e) {
    if (e.target.name === 'credit_amounts[]') recalcEditCreditTotal();
  });

  function recalcEditCreditTotal() {
    const total = Array.from(document.querySelectorAll('#editCreditRows [name="credit_amounts[]"]'))
      .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
    const el = document.getElementById('editCreditTotal');
    if (el) el.textContent = total.toFixed(2);
  }

  /* ---------------------------------------------------------------- *
   * Toggles (Signed & Status)
   * ---------------------------------------------------------------- */
  function wireSignedToggle(select) {
    if (!select) return;
    const scope = select.dataset.scope;
    const signedBy   = document.querySelector(`.signed-by-input[data-scope="${scope}"]`);
    const dateSigned = document.querySelector(`.date-signed-input[data-scope="${scope}"]`);

    function apply() {
      const isYes = select.value === 'Yes';
      [signedBy, dateSigned].forEach(input => {
        if (!input) return;
        input.disabled = !isYes;
        if (isYes) input.setAttribute('required', 'required');
        else { input.removeAttribute('required'); input.value = ''; }
      });
    }
    select.addEventListener('change', apply);
    apply();
  }
  document.querySelectorAll('.signed-select').forEach(wireSignedToggle);

  function wireStatusToggle(select) {
    if (!select) return;
    const scope = select.dataset.scope;
    const wrap = document.querySelector(`.returned-remarks-wrap[data-scope="${scope}"]`);
    function apply() {
      if (wrap) wrap.style.display = RETURN_STATUSES.includes(select.value) ? '' : 'none';
    }
    select.addEventListener('change', apply);
    apply();
  }
  document.querySelectorAll('.status-select').forEach(wireStatusToggle);

  /* ---------------------------------------------------------------- *
   * Edit modal: Fetch transaction and prefill using Self-Healing URLs
   * ---------------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.action-btn[data-action="edit"]');
    if (!btn) return;

    const dbId = btn.dataset.id;
    const dvCode = btn.dataset.dv;
    if (!dbId) return;

    const loading  = document.getElementById('editLoading');
    const formBody = document.getElementById('editFormBody');
    const editCreditRows = document.getElementById('editCreditRows');

    // Reset UI states
    if (loading) {
      loading.style.display  = '';
      loading.innerHTML = `
        <div class="spinner-border text-primary" role="status"></div>
        <p class="text-muted mt-2">Retrieving transaction details...</p>
      `;
    }
    if (formBody) formBody.style.display = 'none';
    if (editCreditRows) editCreditRows.innerHTML = '';

    document.getElementById('editTransactionId').value = dbId;
    document.getElementById('editTransactionLabel').textContent = dvCode;

    // Build URLs using the database numeric ID
    const restfulUrl = `${showUrlBase}/${dbId}`;
    const customUrl  = `${showUrlBase}/${dbId}/show`;

    let finalUpdateUrl = `${updateUrlBase}/${dbId}`; // Standard RESTful update fallback

    fetch(restfulUrl, {
      headers: { 
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json' 
      }
    })
    .then(res => {
      if (res.ok) return res.json();
      
      // Fallback endpoint if RESTful is unavailable
      finalUpdateUrl = `${updateUrlBase}/${dbId}/update`;
      return fetch(customUrl, {
        headers: { 
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json' 
        }
      }).then(res2 => {
        if (!res2.ok) throw new Error('Could not retrieve transaction details from standard or custom routes.');
        return res2.json();
      });
    })
    .then(data => {
      // Correct form submission destination target
      document.getElementById('editRecordForm').action = finalUpdateUrl;

      // Extract raw record properties securely
      const record = data.record || data || {};

      const isBudgetSourced = (record.budget_id !== null && record.budget_id !== undefined && record.budget_id !== '');
      const fieldsToLock = ['edit_payee', 'edit_particulars', 'edit_debit', 'edit_uac_codes', 'edit_obr_no'];


      fieldsToLock.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.readOnly = isBudgetSourced;
          el.style.backgroundColor = isBudgetSourced ? '#e9ecef' : '';
        }
      });

      const safelySet = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.value = value ?? '';
      };

      safelySet('edit_date_received', formatDateTimeLocal(record.date_received));
      safelySet('edit_obr_date', (record.obr_date || '').substring(0, 10));
      safelySet('edit_obr_no', record.obr_no);
      safelySet('edit_ors_no', record.ors_no);
      safelySet('edit_payee', record.payee);
      safelySet('edit_particulars', record.particulars);
      safelySet('edit_particulars_remark', record.particulars_remark);
      safelySet('edit_date_processed', formatDateTimeLocal(record.date_processed));
      safelySet('edit_dv_no', record.dv_no);
      safelySet('edit_debit', record.debit ? parseFloat(record.debit).toFixed(2) : '');
      safelySet('edit_uac_codes', record.uac_codes);

      const debitTotal = document.getElementById('editDebitTotal');
      if (debitTotal) debitTotal.textContent = record.debit ? parseFloat(record.debit).toFixed(2) : '0.00';

      const signedSelect = document.getElementById('edit_signed');
      if (signedSelect) {
        signedSelect.value = (record.signed === 'Yes' || record.signed == 1 || record.signed === true) ? 'Yes' : 'No';
        signedSelect.dispatchEvent(new Event('change'));
      }
      
      safelySet('edit_signed_by_accountant', record.signed_by_accountant);
      safelySet('edit_date_signed', formatDateTimeLocal(record.date_signed));

      const statusSelect = document.getElementById('edit_status');
      if (statusSelect) {
        statusSelect.value = record.status || 'Pending';
        statusSelect.dispatchEvent(new Event('change'));
      }

      safelySet('edit_date_forwarded', formatDateTimeLocal(record.date_forwarded));
      safelySet('edit_returned_remarks', record.returned_remarks);

      // Populate credit items breakdown
      const creditEntries = data.credit_entries || record.credit_entries || [];
      creditEntries.forEach(entry => addCreditRow('editCreditRows', entry));
      recalcEditCreditTotal();

      if (loading) loading.style.display  = 'none';
      if (formBody) formBody.style.display = '';
    })
    .catch((error) => {
      console.error('Data Fetching Error:', error);
      if (loading) {
        loading.innerHTML = `
          <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Failed to load record.</span><br>
          <small class="text-muted">Ensure your controller route mapped for transaction <b>${dvCode}</b> exists.</small>
        `;
      }
    });
  });

  /* ---------------------------------------------------------------- *
   * Delete Action: Populate dynamic confirmation #actionModal
   * ---------------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.action-btn[data-action="delete"]');
    if (!btn) return;

    const dbId = btn.dataset.id;
    const dvCode = btn.dataset.dv;
    if (!dbId) return;

    const actionTitle  = document.getElementById('actionTitle');
    const actionBody   = document.getElementById('actionBody');
    const actionFooter = document.getElementById('actionFooter');

    if (actionTitle) {
      actionTitle.innerHTML = `<i class="bi bi-trash-fill text-danger me-2"></i>Delete Record Confirmation`;
    }
    
    if (actionBody) {
      actionBody.innerHTML = `
        <p>Are you sure you want to permanently delete transaction <strong class="text-danger">${dvCode}</strong>?</p>
        <p class="text-muted small">This will delete all debit and credit split logs associated with this group. This cannot be undone.</p>
      `;
    }

    if (actionFooter) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const deleteUrl = `/accounting/logbook/${dbId}/destroy`;

    actionFooter.innerHTML = `
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
    <form action="${deleteUrl}" method="POST" class="d-inline">
      <input type="hidden" name="_token" value="${csrfToken}">
      <input type="hidden" name="_method" value="DELETE">
      <button type="submit" class="btn btn-danger btn-sm">Yes, Delete</button>
    </form>
    `;
    }
  });

  /* ---------------------------------------------------------------- *
   * View Action: Populate dynamic #actionModal (Bonus)
   * ---------------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.action-btn[data-action="view"]');
    if (!btn) return;

    const dbId = btn.dataset.id;
    const dvCode = btn.dataset.dv;
    if (!dbId) return;

    const actionTitle  = document.getElementById('actionTitle');
    const actionBody   = document.getElementById('actionBody');
    const actionFooter = document.getElementById('actionFooter');

    if (actionTitle) {
      actionTitle.innerHTML = `<i class="bi bi-info-circle-fill text-info me-2"></i>Transaction Details: ${dvCode}`;
    }

    if (actionBody) {
      actionBody.innerHTML = `
        <div class="text-center my-3" id="viewLoading">
          <div class="spinner-border text-info" role="status"></div>
          <p class="text-muted mt-2">Loading details...</p>
        </div>
      `;
    }

    if (actionFooter) {
      actionFooter.innerHTML = `<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>`;
    }

    const restfulUrl = `${showUrlBase}/${dbId}`;
    const customUrl  = `${showUrlBase}/${dbId}/show`;

    fetch(restfulUrl, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => {
      if (res.ok) return res.json();
      return fetch(customUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      }).then(res2 => {
        if (!res2.ok) throw new Error();
        return res2.json();
      });
    })
    .then(data => {
      const record = data.record || data || {};
      const creditEntries = data.credit_entries || record.credit_entries || [];

      let creditRowsHtml = '';
      if (creditEntries.length > 0) {
        creditRowsHtml = creditEntries.map(entry => `
          <tr>
            <td>${entry.uac_codes ?? '-'}</td>
            <td class="text-end">₱${parseFloat(entry.credit ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
            <td>${entry.tax_percent ?? '-'}</td>
            <td>${entry.tax_remarks ?? '-'}</td>
          </tr>
        `).join('');
      } else {
        creditRowsHtml = `<tr><td colspan="4" class="text-center text-muted">No credit breakdown entries logged.</td></tr>`;
      }

      if (actionBody) {
        actionBody.innerHTML = `
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <tbody>
                <tr><th style="width:35%;">Payee</th><td>${record.payee ?? '-'}</td></tr>
                <tr><th>Particulars</th><td>${record.particulars ?? '-'}</td></tr>
                <tr><th>UACS (Debit)</th><td>${record.uac_codes ?? '-'}</td></tr>
                <tr><th>Debit Amount</th><td class="fw-bold text-success">₱${parseFloat(record.debit ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td></tr>
                <tr><th>Status</th><td><span class="badge bg-dark">${record.status ?? 'Pending'}</span></td></tr>
              </tbody>
            </table>
          </div>
          <h6 class="mt-3 fw-bold text-uppercase small text-muted">Credit Split Breakdown</h6>
          <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered mt-1">
              <thead class="table-light">
                <tr>
                  <th>UACS Code</th>
                  <th class="text-end">Credit Amount</th>
                  <th>Tax %</th>
                  <th>Tax Remarks</th>
                </tr>
              </thead>
              <tbody>
                ${creditRowsHtml}
              </tbody>
            </table>
          </div>
        `;
      }
    })
    .catch(() => {
      if (actionBody) {
        actionBody.innerHTML = `<span class="text-danger fw-bold">Could not fetch data.</span>`;
      }
    });
  });

});
</script>