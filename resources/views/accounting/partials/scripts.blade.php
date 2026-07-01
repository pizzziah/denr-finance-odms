{{-- ACTION SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function () {
            let action = this.dataset.action;
            let dv = this.dataset.dv;
            let obr = this.dataset.obr ?? '';
            let payee = this.dataset.payee ?? '';
            let status = this.dataset.status ?? '';
            let title = document.getElementById('actionTitle');
            let body = document.getElementById('actionBody');
            let footer = document.getElementById('actionFooter');
            let entries = this.dataset.entries;
            let amount = this.dataset.amount;
            const safeDv = encodeURIComponent(dv);

            if(action === 'view'){
                title.innerHTML = 'View Transaction';
                body.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>DV No:</strong> ${dv}
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> ${status}
                    </div>
                    <div class="col-md-12 mt-3">
                        <strong>Payee:</strong> ${payee}
                    </div>
                    <div class="col-md-12 mt-3">
                        <strong>Total Accounting Entries:</strong> <span class="badge bg-primary">${entries}</span>
                    </div>
                    <div class="col-md-6 mt-3">
                        <strong>Total Amount:</strong> ₱${Number(amount).toLocaleString(undefined,{
                            minimumFractionDigits:2,
                            maximumFractionDigits:2
                        })}
                    </div>
                    <div class="col-md-12 mt-4">
                        <div class="alert alert-info mb-0">
                            Click <strong>Open Details</strong> to view every UACS, Debit, Credit and Tax entry.
                        </div>
                    </div>
                </div>
                `;

                footer.innerHTML = `
                    <button
                        class="btn btn-primary"
                        onclick="openDetails('${safeDv}')">
                        Open Details
                    </button>
                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Close
                    </button>
                `;
            }

            if (action === 'edit') {
                fetch('/accounting/logbook/' + encodeURIComponent(dv) + '/edit')
                .then(response => response.json())
                .then(data => {

                document.getElementById('editForm').action =
                    '/accounting/logbook/' + encodeURIComponent(dv) + '/update';

                let summary = data.summary;
                let rows = data.details;
                let html = '';

                rows.forEach((row, index) => {
                    html += `
                        <div class="border rounded p-3 mb-3">
                            <input
                                type="hidden"
                                name="rows[${index}][accounting_id]"
                                value="${row.accounting_id}">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>UACS Code</label>
                                    <input
                                        class="form-control"
                                        name="rows[${index}][uac_codes]"
                                        value="${row.uac_codes ?? ''}">
                                </div>
                                <div class="col-md-2">
                                    <label>Debit</label>
                                    <input
                                        class="form-control"
                                        name="rows[${index}][debit]"
                                        value="${row.debit ?? ''}">
                                </div>
                                <div class="col-md-2">
                                    <label>Credit</label>
                                    <input
                                        class="form-control"
                                        name="rows[${index}][credit]"
                                        value="${row.credit ?? ''}">
                                </div>

                                <div class="col-md-2">
                                    <label>Tax %</label>
                                    <input
                                        class="form-control"
                                        name="rows[${index}][tax_percent]"
                                        value="${row.tax_percent ?? ''}">
                                </div>

                                <div class="col-md-3">
                                    <label>Tax Remarks</label>
                                    <input
                                        class="form-control"
                                        name="rows[${index}][tax_remarks]"
                                        value="${row.tax_remarks ?? ''}">
                                </div>
                            </div>
                        </div>
                    `;
                });

                document.getElementById('accountingRows').innerHTML = html;
                // Fill common fields
                const fields = [
                    'ors_no',
                    'obr_no',
                    'dv_no',
                    'payee',
                    'particulars',
                    'particulars_remark',
                    'returned_remarks',
                    'signed_by_accountant',
                    'status',
                    'budget_year',
                    'source_month',
                    'date_received',
                    'date_processed',
                    'obr_date',
                    'date_signed',
                    'date_forwarded'
                ];

                fields.forEach(field => {
                    const input = document.getElementById('edit_' + field);
                    if (input) {
                        input.value = summary[field] ?? '';
                    }
                });

                console.log(rows);

                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById('editModal')
                ).show();
            });
            }

            if(action === 'delete'){
                title.innerHTML = 'Delete Transaction';
                body.innerHTML = `Are you sure you want to delete <strong>${dv}</strong>?`;
                footer.innerHTML = `
                    <form method="POST" action="/accounting/logbook/${dv}/destroy">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">Delete</button>
                    </form>
                `;
            }
        });
    });
});
    // DETAILS SCRIPT
    function openDetails(dv) {

        bootstrap.Modal.getInstance(
            document.getElementById('actionModal')
        ).hide();

        const detailsModal =
            new bootstrap.Modal(
                document.getElementById('detailsModal')
            );

        detailsModal.show();

        document.getElementById('detailsBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success"></div>
            </div>
        `;

        fetch('/accounting/logbook/' + encodeURIComponent(dv) + '/details')
            .then(response => response.json())
            .then(data => {

                let summary = data.summary;
                let rows = data.details;
                let html = `

                <div class="container-fluid">
                    <div class="row">
                        <!-- Section Title -->
                        <div class="col-2 fw-bold fs-4 lh-1">
                            Record<br>Information
                        </div>

                        <!-- Left Column -->
                        <div class="col-5">
                            <div class="row  ">
                                <div class="col-5 fw-bold">Date Received:</div>
                                <div class="col-7">${rows[0].date_received ?? '-'}</div>
                            </div>
                            <div class="row  ">
                                <div class="col-5 fw-bold">OBR Date:</div>
                                <div class="col-7">${rows[0].obr_date ?? '-'}</div>
                            </div>
                            <div class="row  ">
                                <div class="col-5 fw-bold">OBR No:</div>
                                <div class="col-7">${rows[0].obr_no ?? '-'}</div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-5">
                            <div class="row  ">
                                <div class="col-5 fw-bold">Payee:</div>
                                <div class="col-7">${summary.payee ?? '-'}</div>
                            </div>
                            <div class="row  ">
                                <div class="col-5 fw-bold">Particulars:</div>
                                <div class="col-7">${rows[0].particulars ?? '-'}</div>
                            </div>
                            <div class="row  ">
                                <div class="col-5 fw-bold">Remark:</div>
                                <div class="col-7">${rows[0].particulars_remark ?? '-'}</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-1">

                    <div class="row">
                        <div class="col-2 fw-bold fs-4 lh-1">
                            Accounting<br>Processing
                        </div>
                        <div class="col-10">
                            <div class="row">

                                <!-- LEFT COLUMN -->
                                <div class="col-md-4 border-end">
                                    <div class="row  ">
                                        <div class="col-5 fw-bold">Date Processed:</div>
                                        <div class="col-7">${rows[0].date_processed ?? '-'}</div>
                                    </div>
                                    <div class="row  ">
                                        <div class="col-5 fw-bold">DV No:</div>
                                        <div class="col-7">${rows[0].dv_no ?? '-'}</div>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-md-8">
                    `;
                                    rows.forEach(row => {
                                    html += `
                                    <div class="border-bottom mb-3 pb-3">
                                        <div class="row  ">
                                            <div class="col-5 fw-bold">UACS Code:</div>
                                            <div class="col-7">${row.uac_codes ?? '-'}</div>
                                        </div>
                                        <div class="row  ">
                                            <div class="col-5 fw-bold">Debit:</div>
                                            <div class="col-7">
                                                ₱${Number(row.debit ?? 0).toLocaleString(undefined,{
                                                    minimumFractionDigits:2
                                                })}
                                            </div>
                                        </div>
                                        <div class="row  ">
                                            <div class="col-5 fw-bold">Credit:</div>
                                            <div class="col-7">
                                                ₱${Number(row.credit ?? 0).toLocaleString(undefined,{
                                                    minimumFractionDigits:2
                                                })}
                                            </div>
                                        </div>

                                        <div class="row  ">
                                            <div class="col-5 fw-bold">Tax:</div>
                                            <div class="col-7">${row.tax_percent ?? '-'}</div>
                                        </div>
                                        <div class="row  ">
                                            <div class="col-5 fw-bold">Tax Remarks:</div>
                                            <div class="col-7">${row.tax_remarks ?? '-'}</div>
                                        </div>
                                    </div>
                                    `;
                                });
                html += `
                            </div> 
                        </div> 
                    </div> 
                </div> 
                <hr class="my-1">
                `;
                html += `
                        </tbody>
                    </table>
                `;

                html += `
                    <div class="row">
                        <div class="col-2 fw-bold fs-4 lh-1">
                            Signature 
                        </div>
                        <div class="col-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row  ">
                                        <div class="col-5 fw-bold">Signed By:</div>
                                        <div class="col-7">${rows[0].signed_by_accountant ?? '-'}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row  ">
                                        <div class="col-5 fw-bold">Date Signed:</div>
                                        <div class="col-7">${rows[0].date_signed ?? '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                html += `
                    <hr class="my-1">
                    <div class="row">
                        <div class="col-2 fw-bold fs-4 lh-1">
                            Routing<br>Status
                        </div>
                        <div class="col-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row  ">
                                        <div class="col-5 fw-bold">Status:</div>
                                        <div class="col-7">${summary.status ?? '-'}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row  ">
                                        <div class="col-5 fw-bold">Date Forwarded:</div>
                                        <div class="col-7">${rows[0].date_forwarded ?? '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detailsBody').innerHTML = html;
            })
            .catch(() => {

                document.getElementById('detailsBody').innerHTML = `
                    <div class="alert alert-danger">
                        Unable to load transaction details.
                    </div>
                `;
            });
    }

    // PRINT DETAILS
    function printDetails() {
        const content = document.getElementById('detailsBody').innerHTML;
        const printWindow = window.open('', '_blank');

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Budget Transaction</title>
                <link  <hr class="my-1">ef="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body{
                        padding:30px;
                        font-family:Arial,sans-serif;
                        font-size:14px;
                    }
                    h4{
                        text-align:center;
                        margin-bottom:30px;
                    }
                    <hr class="my-1">{
                        margin:20px 0;
                    }
                    .row{
                        margin-bottom:12px;
                    }
                    strong{
                        font-weight:600;
                    }
                    @media print{
                        body{
                            margin:0;
                            padding:20px;
                        }
                        .no-print{
                            display:none;
                        }
                    }
                </style>
            </head>
            <body>
                <h4>Accounting Transaction Details</h4>
                ${content}

            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        },500)
}
</script>