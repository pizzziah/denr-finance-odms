<script>
document.addEventListener('DOMContentLoaded', () => {

    // ==========================
    // COMMON FUNCTION
    // ==========================
    async function getRecord(budget_id) {
        const response = await fetch(`/budget/logbook/${encodeURIComponent(budget_id)}/details`);

        if (!response.ok) {
            throw new Error('Unable to load record.');
        }

        return await response.json();
    }

    // ==========================
    // VIEW BUTTON
    // ==========================
    document.querySelectorAll('.view-btn').forEach(button => {

        button.addEventListener('click', async function () {

            const budget_id = this.dataset.budgetId;

            const modal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById('detailsModal')
            );

            modal.show();

            document.getElementById('detailsBody').innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border"></div>
                </div>
            `;

            try{

                const row = await getRecord(budget_id);

                document.getElementById('detailsBody').innerHTML = `
                <div class="row">

                    <div class="col-md-4 mb-3">
                        <strong>ORS No:</strong> ${row.ors_no ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Date Received:</strong> ${row.date_received ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Status:</strong> ${row.status ?? '-'}
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Payee:</strong> ${row.payee ?? '-'}
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Issuing Office:</strong> ${row.issuing_office ?? '-'}
                    </div>

                    <div class="col-md-12 mb-3">
                        <strong>Particulars:</strong> ${row.particulars ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Classification:</strong> ${row.classification ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>UACS Code:</strong> ${row.uac_codes ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Amount:</strong> ₱${parseFloat(row.amount ?? 0).toLocaleString('en-PH',{
                            minimumFractionDigits:2,
                            maximumFractionDigits:2
                        })}
                    </div>

                    <hr>

                    <div class="col-md-4 mb-3">
                        <strong>Date Returned:</strong> ${row.date_returned_1 ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Returned Remarks:</strong> ${row.remarks_1 ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Date Received:</strong> ${row.date_received_1 ?? '-'}
                    </div>

                    <hr>

                    <div class="col-md-4 mb-3">
                        <strong>Date Forwarded:</strong> ${row.date_forwarded_1 ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Date ORS Received:</strong> ${row.date_ors_received ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Forwarded Remarks:</strong> ${row.remarks_2 ?? '-'}
                    </div>

                    <hr>

                    <div class="col-md-4 mb-3">
                        <strong>Returned by Accounting:</strong> ${row.date_returned_2 ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Date Received from Accounting:</strong> ${row.date_received_2 ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Date Forwarded to Accounting:</strong> ${row.date_forwarded_accounting ?? '-'}
                    </div>

                    <hr>

                    <div class="col-md-4 mb-3">
                        <strong>Total Time in Budget:</strong> ${row.total_time_budget ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Total Time:</strong>${row.total_time ?? '-'}
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong>Final Remarks:</strong> ${row.final_remarks ?? '-'}
                    </div>

                </div>`;
            }
            catch(error){
                document.getElementById('detailsBody').innerHTML =
                    '<div class="alert alert-danger">Unable to load record.</div>';
            }

        });

    });

    // ==========================
    // EDIT BUTTON
    // ==========================
    document.querySelectorAll('.edit-btn').forEach(button => {

        button.addEventListener('click', async function () {

            const budget_id = this.dataset.budgetId;

            try{

                const row = await getRecord(budget_id);

                document.getElementById('editForm').action =
                    `/budget/logbook/${encodeURIComponent(budget_id)}/update`;

                [
                    'ors_no',
                    'date_received',
                    'payee',
                    'issuing_office',
                    'classification',
                    'particulars',
                    'uac_codes',
                    'amount',
                    'date_returned_1',
                    'date_received_1',
                    'remarks_1',
                    'date_forwarded_1',
                    'date_ors_received',
                    'remarks_2',
                    'status',
                    'final_remarks'
                ].forEach(field => {

                    const input = document.getElementById('edit_' + field);

                    if(input){
                        input.value = row[field] ?? '';
                    }

                });

                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById('editModal')
                ).show();

            }catch(error){

                alert('Unable to load record.');

            }

        });

    });

    // ==========================
    // DELETE BUTTON
    // ==========================
    document.querySelectorAll('.delete-btn').forEach(button => {

        button.addEventListener('click', function(){

            const budget_id = this.dataset.budgetId;
            const payee = this.dataset.payee;

            document.getElementById('actionTitle').textContent = 'Delete Record';

            document.getElementById('actionBody').innerHTML =
                `Are you sure you want to delete <strong>${budget_id}</strong>?`;

            document.getElementById('actionFooter').innerHTML = `
                <form method="POST" action="/budget/logbook/${encodeURIComponent(budget_id)}/destroy">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>

                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
            `;

            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('actionModal')
            ).show();

        });

    });

});
function printDetails() {

    const content = document.getElementById('detailsBody').innerHTML;

    const printWindow = window.open('', '_blank');

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Budget Transaction</title>

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

                hr{
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

    },500);

}
</script>