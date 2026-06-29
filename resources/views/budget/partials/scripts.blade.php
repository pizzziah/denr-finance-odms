<script>
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.action-btn').forEach(button => {

        button.addEventListener('click', function () {

            const action = this.dataset.action;
            const ors = this.dataset.ors;
            const payee = this.dataset.payee ?? '';
            const status = this.dataset.status ?? '';

            const title = document.getElementById('actionTitle');
            const body = document.getElementById('actionBody');
            const footer = document.getElementById('actionFooter');

            switch(action){

                case 'view':
                    openView(title, body, footer, ors, payee, status);
                    break;

                case 'edit':
                    openEdit(title, body, footer, payee, status);
                    break;

                case 'delete':
                    openDelete(title, body, footer, payee);
                    break;

            }

        });

    });

});


function openView(title, body, footer, ors, payee, status){

    title.innerHTML = 'View Transaction';

    body.innerHTML = `
        <p><strong>ORS No.</strong> ${ors}</p>
        <p><strong>Payee</strong> ${payee}</p>
        <p><strong>Status</strong> ${status}</p>
    `;

    footer.innerHTML = `
        <button class="btn btn-secondary"
                data-bs-dismiss="modal">
            Close
        </button>
    `;

}


function openEdit(title, body, footer, payee, status){

    title.innerHTML = 'Edit Status';

    body.innerHTML = `
        <form
            id="editForm"
            method="POST"
            action="/budget/logbook/${payee}/update">

            @csrf
            @method('PUT')

            <label class="form-label">
                Status
            </label>

            <select
                name="status"
                class="form-select">

                <option value="Pending"
                    ${status==='Pending' ? 'selected':''}>
                    Pending
                </option>

                <option value="Processing"
                    ${status==='Processing' ? 'selected':''}>
                    Processing
                </option>

                <option value="For Review"
                    ${status==='For Review' ? 'selected':''}>
                    For Review
                </option>

                <option value="For Obligation"
                    ${status==='For Obligation' ? 'selected':''}>
                    For Obligation
                </option>

                <option value="Forwarded to Accounting"
                    ${status==='Forwarded to Accounting' ? 'selected':''}>
                    Forwarded to Accounting
                </option>

                <option value="Returned"
                    ${status==='Returned' ? 'selected':''}>
                    Returned
                </option>

                <option value="Canceled"
                    ${status==='Canceled' ? 'selected':''}>
                    Canceled
                </option>

            </select>

        </form>
    `;

    footer.innerHTML = `
        <button
            form="editForm"
            class="btn btn-success">
            Save Changes
        </button>
    `;

}


function openDelete(title, body, footer, payee){

    title.innerHTML = 'Delete Record';

    body.innerHTML = `
        Are you sure you want to delete
        <strong>${payee}</strong>?
    `;

    footer.innerHTML = `
        <form
            method="POST"
            action="/budget/logbook/${payee}/destroy">

            @csrf
            @method('DELETE')

            <button
                class="btn btn-danger">
                Delete
            </button>

        </form>

        <button
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Cancel
        </button>
    `;

}
function openBudgetDetails(ors){

    const modal = new bootstrap.Modal(
        document.getElementById('detailsModal')
    );

    modal.show();

    document.getElementById('detailsBody').innerHTML =
        '<div class="text-center p-5"><div class="spinner-border"></div></div>';

    fetch('/budget/logbook/' + encodeURIComponent(ors) + '/details')
        .then(res => res.json())
        .then(data => {

            let row = data;

            let html = `
            <div class="row">

                <div class="col-md-4">
                    <strong>ORS No.</strong><br>
                    ${row.ors_no}
                </div>

                <div class="col-md-4">
                    <strong>Date Received</strong><br>
                    ${row.date_received ?? '-'}
                </div>

                <div class="col-md-4">
                    <strong>Status</strong><br>
                    ${row.status}
                </div>

                <div class="col-md-6 mt-3">
                    <strong>Payee</strong><br>
                    ${row.payee}
                </div>

                <div class="col-md-6 mt-3">
                    <strong>Issuing Office</strong><br>
                    ${row.issuing_office}
                </div>

                <div class="col-md-12 mt-3">
                    <strong>Particulars</strong><br>
                    ${row.particulars}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Classification</strong><br>
                    ${row.classification}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>UACS Code</strong><br>
                    ${row.uac_codes}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Amount</strong><br>
                    ₱${Number(row.amount).toLocaleString()}
                </div>

               <div class="col-md-4 mt-3">
                    <strong>Date Returned</strong><br>
                    ${row.date_returned_1 ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Returned Remarks</strong><br>
                    ${row.remarks_1 ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Date Received Again</strong><br>
                    ${row.date_received_1 ?? '-'}
                </div>

                <hr class="my-4">

                <div class="col-md-4 mt-3">
                    <strong>Date Forwarded</strong><br>
                    ${row.date_forwarded_1 ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Date ORS Received</strong><br>
                    ${row.date_ors_received ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Forwarded Remarks</strong><br>
                    ${row.remarks_2 ?? '-'}
                </div>

                <hr class="my-4">

                <div class="col-md-4 mt-3">
                    <strong>Returned by Accounting</strong><br>
                    ${row.date_returned_2 ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Date Received from Accounting</strong><br>
                    ${row.date_received_2 ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Date Forwarded to Accounting</strong><br>
                    ${row.date_forwarded_accounting ?? '-'}
                </div>

                <hr class="my-4">

                <div class="col-md-4 mt-3">
                    <strong>Total Time in Budget</strong><br>
                    ${row.total_time_budget ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Total Time</strong><br>
                    ${row.total_time ?? '-'}
                </div>

                <div class="col-md-4 mt-3">
                    <strong>Final Remarks</strong><br>
                    ${row.final_remarks ?? '-'}
                </div>

            </div>
            `;

            document.getElementById('detailsBody').innerHTML = html;
        });
}
</script>