{{-- DETAILS / VIEW RECORD MODAL --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 80%;">
    <div class="modal-content">

      {{-- MODAL HEADER --}}
      <div class="modal-header text-white" style="background-color: var(--primary);">
        <h5 class="fw-bold">Accounting Record Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- SUB HEADER / ACTIONS --}}
      <div class="px-4">
        <div class="d-flex justify-content-between align-items-center mt-2">
          <div>
            <h5 class="fw-bold mb-0" id="transactionTitle">-</h5>
            <small class="text-muted" id="transactionSubtitle">-</small>
          </div>

          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="detailsEditBtn">
              <i class="bi bi-pencil"></i> Edit
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="detailsDeleteBtn">
              <i class="bi bi-trash"></i> Delete
            </button>
          </div>
        </div>
      </div>

      <hr>

      {{-- MODAL BODY --}}
      <div class="modal-body">

        {{-- LOADING SPINNER --}}
        <div id="detailsLoading" class="text-center py-5 d-none">
          <div class="spinner-border text-primary"></div>
        </div>

        {{-- ERROR CONTAINER --}}
        <div id="modalError" class="alert alert-danger d-none text-center m-0">
          Could not fetch data. Please try again.
        </div>

        <div id="detailsContent">
          <div class="container-fluid">

            {{-- ================= BASIC INFORMATION ================= --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Basic<br>Information
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Date Received:</div>
                  <div class="col-7" id="view_date_received">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5 fw-bold">OBR Date:</div>
                  <div class="col-7" id="view_obr_date">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5 fw-bold">OBR No.:</div>
                  <div class="col-7" id="view_obr_no">-</div>
                </div>
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Payee:</div>
                  <div class="col-7" id="view_payee">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Particulars:</div>
                  <div class="col-7" id="view_particulars">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Particulars Remark:</div>
                  <div class="col-7" id="view_particulars_remark">-</div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= REVIEW PROCESS ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Review<br>Processing</div>
                <div class="col-5">
                    <div id="reviewHistoryContainer"></div>
                </div>
            </div>
            
            <hr>

            {{-- ================= DEBIT ENTRY ================= --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Debit<br>Entry
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Date Processed:</div>
                  <div class="col-7" id="view_date_processed">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5 fw-bold">DV No.:</div>
                  <div class="col-7" id="view_dv_no">-</div>
                </div>
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">UACS Code:</div>
                  <div class="col-7" id="view_uac_codes">-</div>
                </div>
                <div class="row">
                  <div class="col-5 fw-bold">Debit Amount:</div>
                  <div class="col-7">₱<span id="view_debit">0.00</span></div>
                </div>
                <div class="mt-3">
                    <h6 class="fw-bold text-success">
                        Additional Debit Entries
                    </h6>
                    <div id="view-debit-entries"
                        class="row g-2">
                    </div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= CREDIT ENTRIES ================= --}}
            <div class="row">
              <div class="col-2 fw-bold fs-4">Credit<br>Entries</div>

              <div class="col-10">
                {{-- Dynamic Cards Container --}}
                <div id="view-credit-entries" class="row g-2">
                  <!-- Dynamic read-only cards injected via JavaScript -->
                </div>

                {{-- Financial Totals Summary Bar --}}
                <div class="d-flex justify-content-between align-items-center bg-light p-2 border rounded mt-3">
                  <span class="small fw-bold text-muted">Total Debit: ₱<span id="viewDebitTotal">0.00</span></span>
                  <span class="small fw-bold text-success">Total Credit: ₱<span id="viewCreditTotal">0.00</span></span>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= SIGN-OFF ================= --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Sign-off
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Signed:</div>
                  <div class="col-7" id="view_signed">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Date Signed:</div>
                  <div class="col-7" id="view_date_signed">-</div>
                </div>
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Signed By Accountant:</div>
                  <div class="col-7" id="view_signed_by_accountant">-</div>
                </div>
              </div>
            </div>

            <hr>

            {{-- ================= STATUS & FORWARDING ================= --}}
            <div class="row py-1">
              <div class="col-md-2 fw-bold fs-5 border-end-md pe-md-4 mb-3 mb-md-0" style="color: var(--primary);">
                Status &<br>Forwarding
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Status:</div>
                  <div class="col-7" id="view_status">-</div>
                </div>
              </div>
              <div class="col-5">
                <div class="row mb-2">
                  <div class="col-5 fw-bold">Date Forwarded:</div>
                  <div class="col-7" id="view_date_forwarded">-</div>
                </div>
                <div class="row mb-2 returned-remarks-wrap" id="view_returned_remarks_wrap" style="display: none;">
                  <div class="col-5 fw-bold text-danger">Returned Remarks:</div>
                  <div class="col-7 text-danger" id="view_returned_remarks">-</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      {{-- FOOTER --}}
      <div class="modal-footer">
        <button class="btn btn-primary" id="printDetailsBtn">
          <i class="bi bi-printer"></i> Print
        </button>
        <button class="btn btn-success" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const printBtn = document.getElementById('printDetailsBtn');
  if (!printBtn) return;

  printBtn.addEventListener('click', () => {
    if (!window.currentPrintRecord) {
      alert('No record available to print.');
      return;
    }

    const { record, creditEntries, totalDebit } = window.currentPrintRecord;
    let rows = '';

    rows += `
      <tr>
        <td style="background-color:#F0FFE6; color:#044709; font-weight:bold; text-align:center;"> ${record.dv_no ?? '-'}</td>
        <td>${record.payee ?? '-'}</td>
        <td>${record.particulars ?? '-'}</td>
        <td>${record.uac_codes ?? '-'}</td>
        <td style="text-align:right;">
          ${Number(totalDebit).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          })}
        </td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      `;

    creditEntries.forEach(entry => {
    rows += `
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>${entry.uac_codes ?? '-'}</td>
        <td></td>
        <td style="text-align:right;">
          ${Number(entry.credit || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          })}
        </td>
        <td style="text-align:center;">
          ${entry.tax_percent ? entry.tax_percent + '%' : ''}
        </td>
        <td>
          ${entry.tax_remarks ?? ''}
        </td>
      </tr>
    `;
  });

  const printWindow = window.open('', '_blank', 'width=1000,height=700');

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Accounting Record</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 25px;
        }
        h2 {
          text-align: center;
          margin-bottom: 20px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
        }
        th, td {
          border: 1px solid #000;
          padding: 8px;
          font-size: 12px;
        }
        th {
          background: #efefef;
        }
        th:nth-child(5),
        th:nth-child(6),
        td:nth-child(5),
        td:nth-child(6) {
          text-align: right;
        }
        th:nth-child(7),
        td:nth-child(7) {
          text-align: center;
        }
      </style>
    </head>
    <body>
      <h3>Accounting Record</h3>
      <table>
        <thead>
          <tr>
            <th>DV No.</th>
            <th>PAYEE</th>
            <th>PARTICULARS</th>
            <th>UACS CODE</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>% Tax</th>
            <th>Tax Remarks</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </body>
    </html>
  `);

  printWindow.document.close();

  printWindow.onload = () => {
    setTimeout(() => {
    printWindow.focus();
    printWindow.print();
    }, 300);
  };

  printWindow.onafterprint = () => {
    printWindow.close();
    };
  });
});
</script>