<script>
// Helper to format date strings into ISO-like formats suitable for datetime-local inputs
const formatToDateTimeLocal = (dateStr) => {
  if (!dateStr || dateStr === '-') return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.getFullYear() + '-' +
         String(d.getMonth() + 1).padStart(2, '0') + '-' +
         String(d.getDate()).padStart(2, '0') + 'T' +
         String(d.getHours()).padStart(2, '0') + ':' +
         String(d.getMinutes()).padStart(2, '0');
};

document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.action-btn');
    if (!btn) return;

    let { action, dv, status = '', payee = '', entries, amount } = btn.dataset;
    let title = document.getElementById('actionTitle'),
        body = document.getElementById('actionBody'),
        footer = document.getElementById('actionFooter');
    const safeDv = encodeURIComponent(dv);

    if (action === 'view') {
      title.innerHTML = 'View Transaction';
      body.innerHTML = `
        <div class="row">
          <div class="col-md-6"><strong>DV No:</strong> ${dv}</div>
          <div class="col-md-6"><strong>Status:</strong> ${status}</div>
          <div class="col-md-12 mt-3"><strong>Payee:</strong> ${payee}</div>
          <div class="col-md-12 mt-3"><strong>Total Accounting Entries:</strong> <span class="badge bg-primary">${entries}</span></div>
          <div class="col-md-6 mt-3"><strong>Total Amount:</strong> ₱${Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
          <div class="col-md-12 mt-4"><div class="alert alert-info mb-0">Click <strong>Open Details</strong> to view every UACS, Debit, Credit and Tax entry.</div></div>
        </div>`;
      footer.innerHTML = `<button class="btn btn-primary" onclick="openDetails('${safeDv}')">Open Details</button><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`;
    }

    if (action === 'edit') openAccountingEditModal(dv);

    if (action === 'delete') {
      title.innerHTML = 'Delete Transaction';
      body.innerHTML = `Are you sure you want to delete <strong>${dv}</strong>?`;
      footer.innerHTML = `<form method="POST" action="/accounting/logbook/${dv}/destroy">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>`;
    }
  });

// Add-record form: submit via fetch, append row on success, no full page reload
  const addForm = document.getElementById('addForm');
  if (addForm) {
    addForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const submitBtn = addForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      // FIX: Let FormData naturally process dynamic inputs to avoid duplications/scrambled arrays
      const fd = new FormData(addForm);

      fetch(addForm.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: fd,
      })
        .then(async res => {
          const data = await res.json();
          if (!res.ok) throw data;
          return data;
        })
        .then(data => {
          appendNewRecordToTable(data.record);
          bootstrap.Modal.getInstance(document.getElementById('addRecordModal')).hide();
          addForm.reset();
          
          // Clear dynamically added rows from UI
          const container = document.getElementById('addAccountingRows');
          if(container) container.innerHTML = '';
          
          showTopAlert('success', data.message || 'Record saved successfully.');
        })
        .catch(err => {
          const msg = err?.error || (err?.errors ? Object.values(err.errors).flat().join(' ') : 'Unable to save record.');
          showTopAlert('danger', msg);
        })
        .finally(() => { if (submitBtn) submitBtn.disabled = false; });
    });
  }

  const params = new URLSearchParams(window.location.search);
  const addDebit = document.getElementById('add_total_debit');
  const addBtn = document.getElementById('add_btn_add_uacs');

  if (addDebit && addBtn) {
    addDebit.addEventListener('input', function () {
      addBtn.disabled = Number(this.value || 0) <= 0;
    });
  }
const editDebit = document.getElementById('edit_total_debit');
const editBtn = document.getElementById('edit_btn_add_uacs');

if (editDebit && editBtn) {
  editDebit.addEventListener('input', function () {
    editBtn.disabled = Number(this.value || 0) <= 0;
  });
}


  const id = params.get('view');
  if (id) {
    const btn = document.querySelector(`.action-btn[data-action="view"][data-dv="${id}"]`);
    if (btn) { btn.click(); history.replaceState({}, '', window.location.pathname); }
  }
  if (addBtn) {
  addBtn.addEventListener('click', addAddUacsRow);
}

if (editBtn) {
  editBtn.addEventListener('click', addEditUacsRow);
}
document.addEventListener('click', function(e) {
  if (e.target.closest('.remove-uacs')) {
    e.target.closest('.uacs-row').remove();
  }

  if (e.target.closest('.remove-edit-uacs')) {
    e.target.closest('.edit-uacs-row').remove();
  }
});
});

function addEditUacsRow() {
  const template = document.getElementById('editUacsRowTemplate');
  const container = document.getElementById('editAccountingRows');
  if (!template || !container) return;
  container.appendChild(template.content.cloneNode(true));
}

function showTopAlert(type, message) {
  const container = document.querySelector('.container-fluid.mt-3');
  if (!container) return;
  const div = document.createElement('div');
  div.className = `alert alert-${type} alert-dismissible fade show`;
  div.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
  container.prepend(div);
}

function appendNewRecordToTable(record) {
  const tbody = document.querySelector('#accountingTable tbody');
  if (!tbody) return;

  const emptyRow = tbody.querySelector('.empty-row-placeholder');
  if (emptyRow) emptyRow.remove();

  const statusStyles = {
    'Pending':              'background-color:#FFEECC;color:#9D6B0B;',
    'Processing':           'background-color:#FFDEC5;color:#BB400D;',
    'Returned to End User': 'background-color:#EFDFFF;color:#7909FF;',
    'Returned to Budget':   'background-color:#EBFEFF;color:#0B879D;',
    'Paid':                 'background-color:#DEF5C4;color:var(--secondary);',
    'Forwarded to Cashier': 'background-color:var(--secondary-variant);color:var(--primary);',
  };
  const style = statusStyles[record.status] || 'background-color:#F8F9FA;color:#6C757D;';
  const amount = Number(record.total_credit  ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const received = record.date_received ? new Date(record.date_received).toLocaleString() : '-';

  const tr = document.createElement('tr');
  tr.id = 'row-' + record.transaction_id;
  tr.innerHTML = `
    <td>${received}</td>
    <td style="color:#9D6B0B;background-color:#FFEECC"><strong>${record.dv_no ?? '-'}</strong></td>
    <td>-</td>
    <td>-</td>
    <td style="color:var(--primary);background-color:var(--secondary-variant)"><strong>-</strong></td>
    <td><strong>${record.payee ?? '-'}</strong></td>
    <td><strong>-</strong></td>
    <td><i>-</i></td>
    <td class="fw-bold">₱${amount}</td>
    <td><span class="badge fw-bold" style="${style};font-size:1em;">${record.status ?? '-'}</span></td>
    <td class="text-center"><span class="badge fw-bold" style="background-color:#BCC3F6;color:#271ECE;font-size:1em;">${record.total_entries ?? 1} Entries</span></td>
    <td><span class="text-muted">-</span></td>
    <td>-</td>
    <td>-</td>
    <td>
      <div class="d-flex gap-1 justify-content-center">
        <button type="button" class="btn btn-sm btn-outline-info action-btn" data-action="view"
          data-dv="${record.transaction_id}" data-entries="${record.total_entries ?? 1}"
          data-amount="${record.total_credit  ?? 0}" data-payee="${record.payee ?? ''}"
          data-status="${record.status ?? ''}" data-bs-toggle="modal" data-bs-target="#actionModal">
          <i class="bi bi-eye"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary action-btn" data-action="edit"
          data-dv="${record.transaction_id}" data-status="${record.status ?? ''}"
          data-bs-toggle="modal" data-bs-target="#editRecordModal">
          <i class="bi bi-pencil"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger action-btn" data-action="delete"
          data-dv="${record.transaction_id}" data-bs-toggle="modal" data-bs-target="#actionModal">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </td>
  `;
  tbody.prepend(tr);
  tr.classList.add('table-warning');

setTimeout(function() {
  tr.classList.remove('table-warning');
},3000);
}

function openDetails(dv) {
  bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
  new bootstrap.Modal(document.getElementById('detailsModal')).show();
  document.getElementById('detailsBody').innerHTML = `<div class="text-center py-5"><div class="spinner-border text-success"></div></div>`;

  fetch('/accounting/logbook/' + encodeURIComponent(dv) + '/details')
    .then(res => res.json())
    .then(data => {
      let { summary, details: rows } = data;
      let html = `
        <div class="container-fluid">
          <div class="row">
            <div class="col-2 fw-bold fs-4 lh-1">Record<br>Information</div>
            <div class="col-5">
              <div class="row"><div class="col-5 fw-bold">Date Received:</div><div class="col-7">${rows[0].date_received ?? '-'}</div></div>
              <div class="row"><div class="col-5 fw-bold">OBR Date:</div><div class="col-7">${rows[0].obr_date ?? '-'}</div></div>
              <div class="row"><div class="col-5 fw-bold">OBR No:</div><div class="col-7">${summary.ors_no ?? rows[0].obr_no ?? '-'}</div></div>
            </div>
            <div class="col-5">
              <div class="row"><div class="col-5 fw-bold">Payee:</div><div class="col-7">${summary.payee ?? '-'}</div></div>
              <div class="row"><div class="col-5 fw-bold">Particulars:</div><div class="col-7">${rows[0].particulars ?? '-'}</div></div>
              <div class="row"><div class="col-5 fw-bold">Remark:</div><div class="col-7">${rows[0].particulars_remark ?? '-'}</div></div>
            </div>
          </div>
          <hr class="my-1">
          <div class="row">
            <div class="col-2 fw-bold fs-4 lh-1">Accounting<br>Processing</div>
            <div class="col-10"><div class="row"><div class="col-md-4 border-end"><div class="row"><div class="col-5 fw-bold">Date Processed:</div><div class="col-7">${rows[0].date_processed ?? '-'}</div></div><div class="row"><div class="col-5 fw-bold">DV No:</div><div class="col-7">${rows[0].dv_no ?? '-'}</div></div></div><div class="col-md-8">`;

      rows.forEach(row => {
        html += `
          <div class="border-bottom mb-3 pb-3">
            <div class="row"><div class="col-5 fw-bold">UACS Code:</div><div class="col-7">${row.uac_codes ?? '-'}</div></div>
            <div class="row"><div class="col-5 fw-bold">Debit:</div><div class="col-7">₱${Number(row.debit ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</div></div>
            <div class="row"><div class="col-5 fw-bold">Credit:</div><div class="col-7">₱${Number(row.credit ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</div></div>
            <div class="row"><div class="col-5 fw-bold">Tax:</div><div class="col-7">${row.tax_percent ?? '-'}</div></div>
            <div class="row"><div class="col-5 fw-bold">Tax Remarks:</div><div class="col-7">${row.tax_remarks ?? '-'}</div></div>
          </div>`;
      });

      html += `</div></div></div></div><hr class="my-1"><div class="row"><div class="col-2 fw-bold fs-4 lh-1">Signature</div><div class="col-10"><div class="row"><div class="col-md-6"><div class="row"><div class="col-5 fw-bold">Signed By:</div><div class="col-7">${rows[0].signed_by_accountant ?? '-'}</div></div></div><div class="col-md-6"><div class="row"><div class="col-5 fw-bold">Date Signed:</div><div class="col-7">${rows[0].date_signed ?? '-'}</div></div></div></div></div></div><hr class="my-1"><div class="row"><div class="col-2 fw-bold fs-4 lh-1">Routing<br>Status</div><div class="col-10"><div class="row"><div class="col-md-6"><div class="row"><div class="col-5 fw-bold">Status:</div><div class="col-7">${summary.status ?? '-'}</div></div></div><div class="col-md-6"><div class="row"><div class="col-5 fw-bold">Date Forwarded:</div><div class="col-7">${rows[0].date_forwarded ?? '-'}</div></div></div></div></div></div></div>`;

      document.getElementById('detailsBody').innerHTML = html;
      document.getElementById('transactionTitle').textContent = rows[0].dv_no ?? '-';
      document.getElementById('transactionSubtitle').textContent = summary.payee ?? '';

      ['detailsEditBtn', 'detailsDeleteBtn'].forEach(id => {
        let el = document.getElementById(id);
        if (el) el.onclick = () => id === 'detailsEditBtn' ? openAccountingEditModal(dv) : deleteAccountingRecord(dv);
      });
    })
    .catch(() => document.getElementById('detailsBody').innerHTML = `<div class="alert alert-danger">Unable to load transaction details.</div>`);
}

async function openAccountingEditModal(dv) {
  try {
    const response = await fetch('/accounting/logbook/' + encodeURIComponent(dv) + '/edit');
    if (!response.ok) throw new Error();
    const { summary, details: rows } = await response.json();

    document.getElementById('editForm').action = '/accounting/logbook/' + encodeURIComponent(dv) + '/update';

    // Explicitly update status element dropdown/input selections cleanly
    const statusInput = document.getElementById('edit_status');
    if (statusInput) statusInput.value = summary.status ?? '';

    ['dv_no', 'payee', 'particulars', 'particulars_remark', 'signed_by_accountant', 'budget_year'].forEach(f => {
      let input = document.getElementById('edit_' + f);
      if (input) input.value = summary[f] ?? '';
    });

    // Parse date values cleanly with time segments included
    ['date_received', 'date_processed', 'obr_date', 'date_signed', 'date_forwarded'].forEach(f => {
      let input = document.getElementById('edit_' + f);
      if (input) input.value = formatToDateTimeLocal(summary[f] ?? rows[0]?.[f]);
    });

    // Populate dynamic logic where budget's "ORS No" maps onto the disabled Edit OBR No input container element
    let obrInput = document.getElementById('edit_obr_no');
    if (obrInput) {
      obrInput.value = summary.ors_no ?? summary.obr_no ?? '';
      obrInput.readOnly = true;
    }

    if (rows?.length > 0) {
      let topUacs = document.getElementById('edit_uacs_code'),
          topDebit = document.getElementById('edit_total_debit'),
          topId = document.getElementById('edit_accounting_id');
      if (topUacs) topUacs.value = rows[0].uac_codes ?? '';
      if (topDebit) topDebit.value = rows[0].debit ?? '';
      if (topId) topId.value = rows[0].accounting_id ?? '';
    }

    let html = '';
    for (let i = 1; i < rows.length; i++) {
      const container = document.getElementById('editAccountingRows');
      container.innerHTML = '';

      rows.slice(1).forEach(row => {
        const clone = document.getElementById('editUacsRowTemplate').content.cloneNode(true);

        clone.querySelector('[name="rows[][uac_codes]"]').value = row.uac_codes ?? '';
        clone.querySelector('[name="rows[][credit]"]').value = row.credit ?? '';
        clone.querySelector('[name="rows[][tax_percent]"]').value = row.tax_percent ?? '';
        clone.querySelector('[name="rows[][tax_remarks]"]').value = row.tax_remarks ?? '';

        container.appendChild(clone);
      });
    }
    document.getElementById('editAccountingRows').innerHTML = html;
    bootstrap.Modal.getInstance(document.getElementById('detailsModal')).hide();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editRecordModal')).show();
  } catch (e) { console.error("Error opening edit modal: ", e); }
}
function addAddUacsRow() {
  const template = document.getElementById('uacsRowTemplate');
  const container = document.getElementById('addAccountingRows');
  if (!template || !container) return;
  container.appendChild(template.content.cloneNode(true));
}
function printDetails() {
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`<!DOCTYPE html><html><head><title>Budget Transaction</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{padding:30px;font-family:Arial,sans-serif;font-size:14px;}h4{text-align:center;margin-bottom:30px;}.row{margin-bottom:12px;}strong{font-weight:600;}@media print{body{margin:0;padding:20px;}.no-print{display:none;}}</style></head><body><h4>Accounting Transaction Details</h4>${document.getElementById('detailsBody').innerHTML}</body></html>`);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
}
</script>