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
   * Pay Confirmation Action: Intercept and display styled Modal layout
   * ---------------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.action-btn[data-action="pay-confirm"]');
    if (!btn) return;

    const dvCode = btn.dataset.dv;
    const actionUrl = btn.dataset.url;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const actionTitle  = document.getElementById('actionTitle');
    const actionBody   = document.getElementById('actionBody');
    const actionFooter = document.getElementById('actionFooter');

    if (actionTitle) {
      actionTitle.innerHTML = `<i class="bi bi-check2-circle text-success me-2"></i>Disbursement Confirmation`;
    }
    
    if (actionBody) {
      actionBody.innerHTML = `
        <div class="p-2 text-center">
          <i class="bi bi-cash-coin text-success mb-2" style="font-size: 2.5rem;"></i>
          <p class="mb-1">Are you sure you want to mark DV No. <strong class="text-primary">${dvCode}</strong> as fully <strong>Paid</strong>?</p>
          <p class="text-muted small">Confirming shifts this workflow pipeline record directly into the Archives registry logs.</p>
        </div>
      `;
    }

    if (actionFooter) {
      actionFooter.innerHTML = `
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <form action="${actionUrl}" method="POST" class="d-inline">
          <input type="hidden" name="_token" value="${csrfToken}">
          <input type="hidden" name="_method" value="PUT">
          <button type="submit" class="btn btn-success btn-sm fw-bold">Confirm Payment</button>
        </form>
      `;
    }
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
    // Listens for elements targeting either .view-details-btn or standard view action targets
    const btn = e.target.closest('.view-details-btn') || e.target.closest('.action-btn[data-action="view"]');
    if (!btn) return;

    const dbId = btn.dataset.id;
    if (!dbId) return;

    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const errorMsg = document.getElementById('modalError');
    const creditContainer = document.getElementById('view-credit-entries');

    // Reset UI Visibility elements inside the details layout framework
    if (loading) loading.classList.remove('d-none');
    if (content) content.classList.add('d-none');
    if (errorMsg) errorMsg.classList.add('d-none');
    if (creditContainer) creditContainer.innerHTML = '';

    // Utilize self-healing routing paths mapping definitions matching your controller framework definitions
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
        if (!res2.ok) throw new Error('Data endpoint execution failed.');
        return res2.json();
      });
    })
    .then(data => {
      const record = data.record || data || {};
      const creditEntries = data.credit_entries || record.credit_entries || [];

      // Safely inject metadata elements into layout DOM targets
      const safelyText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '-';
      };

      safelyText('view-txn-id', data.transaction_id);
      safelyText('view-dv-no', record.dv_no);
      safelyText('view-obr-no', record.obr_no);
      safelyText('view-payee', record.payee);
      safelyText('view-particulars', record.particulars);

      // Total aggregated calculations updates injection
      const totalDebit = parseFloat(data.total_debit || record.debit || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
      safelyText('view-debit', totalDebit);

      // Handle custom style classes decoration matching dynamic statuses parameters variations
      const statusEl = document.getElementById('view-status');
      if (statusEl) {
        const status = (record.status || 'Pending').trim();
        statusEl.textContent = status;
        
        let badgeClass = 'bg-secondary text-white';
        if (status === 'Paid') badgeClass = 'bg-success text-white';
        if (status === 'Forwarded to Cashier') badgeClass = 'bg-primary text-white';
        if (status === 'Pending') badgeClass = 'bg-warning text-dark';
        if (status === 'Cancelled') badgeClass = 'bg-danger text-white';
        
        statusEl.className = `badge ${badgeClass}`;
      }

      // Populate credit items matrix loop arrays mapping definitions rows
      if (creditContainer) {
        if (creditEntries.length > 0) {
          creditContainer.innerHTML = creditEntries.map(entry => `
            <tr>
              <td><code>${entry.uac_codes ?? '-'}</code></td>
              <td class="text-end fw-bold">₱${parseFloat(entry.credit ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
              <td class="text-center">${entry.tax_percent ? entry.tax_percent + '%' : '-'}</td>
              <td>${entry.tax_remarks ?? '-'}</td>
            </tr>
          `).join('');
        } else {
          creditContainer.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-2">No credit allocations split for this layout row.</td></tr>`;
        }
      }

      // Display metrics state changes toggling completion handles
      if (loading) loading.classList.add('d-none');
      if (content) content.classList.remove('d-none');
    })
    .catch((error) => {
      console.error('View details parsing crash context:', error);
      if (loading) loading.classList.add('d-none');
      if (errorMsg) errorMsg.classList.remove('d-none');
    });
  });

  const viewId = "{{ $view ?? '' }}";
  if (viewId) {
      const viewButton = document.querySelector(
          `.action-btn[data-action="view"][data-id="${viewId}"]`
      );
      if (viewButton) {
          // Scroll to the highlighted row
          viewButton.closest("tr")?.scrollIntoView({
              behavior: "smooth",
              block: "center"
          });

          // Open the View modal
          setTimeout(() => {
              viewButton.click();
          }, 500);
      }
  }

    new TomSelect(newRow.querySelector('.add-credit-uacs'), {
      create: false,
      searchField: ['text']
  });
});
</script>