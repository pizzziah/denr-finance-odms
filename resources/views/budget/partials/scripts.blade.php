<script>
document.addEventListener('DOMContentLoaded', () => {

    const $ = (id) => document.getElementById(id);

    // ===================== FORMAT DATETIME =====================
    function formatDateTime(value) {
        if (!value) return '';
        return value.replace(' ', 'T').substring(0, 16);
    }

    // ===================== TOMSELECT INIT =====================
    function initTomSelect(context = document) {

        const config = {
            create: false,
            allowEmptyOption: true,
            placeholder: "Search..."
        };

        const selectors = [
            '#edit_issuing_office',
            '#edit_classifications',
            '#edit_uac_codes',

            'select[name="issuing_office"]',
            'select[name="classification"]',
            'select[name="uac_codes"]'
        ];

        selectors.forEach(sel => {
            const el = context.querySelector(sel);
            if (!el) return;

            // destroy old instance if exists
            if (el.tomselect) {
                el.tomselect.destroy();
            }

            new TomSelect(el, config);
        });
    }

    // ===================== GET RECORD =====================
    async function getRecord(id) {
        const res = await fetch(`/budget/logbook/${encodeURIComponent(id)}/details`);
        if (!res.ok) throw new Error('Unable to load record.');
        return await res.json();
    }

    // ===================== VIEW =====================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.view-btn');
        if (!btn) return;

        const id = btn.dataset.budgetId;
        const modal = bootstrap.Modal.getOrCreateInstance($('detailsModal'));

        modal.show();

        $('detailsBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success"></div>
            </div>
        `;

        try {
            const row = await getRecord(id);

            $('transactionTitle').textContent = row.ors_no ?? '-';
            $('transactionSubtitle').textContent = row.payee ?? '-';

            $('detailsEditBtn').onclick = () => {
                modal.hide();
                openEditModal(id);
            };

            $('detailsBody').innerHTML = `
            <div class="container-fluid">

                <div class="row">
                    <div class="col-2 fw-bold fs-4">Request<br>Information</div>

                    <div class="col-5">
                        <div>Date Received: ${row.date_received ?? '-'}</div>
                        <div>Issuing Office: ${row.issuing_office ?? '-'}</div>
                    </div>

                    <div class="col-5">
                        <div>Payee: ${row.payee ?? '-'}</div>
                        <div>Classification: ${row.classification ?? '-'}</div>
                        <div>Particulars: ${row.particulars ?? '-'}</div>
                        <div>Remark: ${row.particulars_remark ?? '-'}</div>
                        <div>Due Date: ${row.due_date ?? '-'}</div>
                        <div>
                            Amount: ₱${Number(row.amount ?? 0).toLocaleString(undefined,{
                                minimumFractionDigits:2,
                                maximumFractionDigits:2
                            })}
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-2 fw-bold fs-4">Review<br>Processing</div>

                    <div class="col-5">
                        <div>Status: ${row.status ?? '-'}</div>
                    </div>

                    <div class="col-5">
                        <div>Date Returned: ${row.date_returned_1 ?? '-'}</div>
                        <div>Remarks: ${row.remarks_1 ?? '-'}</div>
                        <div>Date Received: ${row.date_received_1 ?? '-'}</div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-2 fw-bold fs-4">Obligation<br>Processing</div>

                    <div class="col-5">
                        <div>ORS No: ${row.ors_no ?? '-'}</div>
                    </div>

                    <div class="col-5">
                        <div>Date Forwarded: ${row.date_forwarded_1 ?? '-'}</div>
                        <div>Date ORS Received: ${row.date_ors_received ?? '-'}</div>
                        <div>Date Returned: ${row.date_returned_2 ?? '-'}</div>
                        <div>Remarks: ${row.remarks_2 ?? '-'}</div>
                        <div>Date Received: ${row.date_received_2 ?? '-'}</div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-2 fw-bold fs-4">Forwarded<br>to Accounting</div>

                    <div class="col-5">
                        <div>Date Forwarded: ${row.date_forwarded_accounting ?? '-'}</div>
                    </div>

                    <div class="col-5">
                        <div>Remarks: ${row.final_remarks ?? '-'}</div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-2 fw-bold fs-4">Processing<br>Metrics</div>

                    <div class="col-5">
                        <div>Total Time in Budget: ${row.total_time_budget ?? '-'}</div>
                    </div>

                    <div class="col-5">
                        <div>Total Time: ${row.total_time ?? '-'}</div>
                    </div>
                </div>

            </div>
            `;
        } catch (err) {
            $('detailsBody').innerHTML = `
                <div class="alert alert-danger">
                    Unable to load record.
                </div>
            `;
        }
    });

    // ===================== OPEN EDIT MODAL =====================
    window.openEditModal = async (id) => {
        try {

            const response = await getRecord(id);

            const row = response.budget;
            const reviews = response.reviews ?? [];

            $('editForm').action =
                `/budget/logbook/${encodeURIComponent(id)}/update`;

            const fields = [
                'ors_no','date_received','payee','particulars','amount',
                'date_returned_1','date_received_1','remarks_1',
                'date_forwarded_1','date_ors_received','remarks_2',
                'date_returned_2','date_received_2',
                'date_forwarded_accounting','status',
                'total_time_budget','total_time','final_remarks'
            ];

            fields.forEach(f => {

                const el = $('edit_' + f);

                if (!el) return;

                if (el.type === 'datetime-local') {
                    el.value = formatDateTime(row[f]);
                }
                else if (el.type === 'date') {
                    el.value = row[f]?.substring(0,10) ?? '';
                }
                else {
                    el.value = row[f] ?? '';
                }

            });

            // ===========================
            // LOAD REVIEW HISTORY
            // ===========================

            const container = $('reviewRowsContainer');

            container.innerHTML = '';

            reviews.forEach(review => {

                const clone = document
                    .getElementById('reviewRowTemplate')
                    .content
                    .cloneNode(true);

                clone.querySelector('[name="review_date_returned[]"]').value =
                    formatDateTime(review.date_returned);

                clone.querySelector('[name="review_date_received[]"]').value =
                    formatDateTime(review.date_received);

                clone.querySelector('[name="review_remarks[]"]').value =
                    review.remarks ?? '';

                container.appendChild(clone);

            });

            initTomSelect(document);

            bootstrap.Modal
                .getOrCreateInstance($('editModal'))
                .show();

        }
        catch (e) {
            alert(e.message);
        }
    };
    // ===================== EDIT BUTTON =====================
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.edit-btn');
        if (btn) openEditModal(btn.dataset.budgetId);
    });

    // ===================== ORS VALIDATION =====================
    $('editForm')?.addEventListener('submit', (e) => {
        const ors = $('edit_ors_no');
        const err = $('editError');

        err?.classList.add('d-none');

        if (ors.value.trim() && !/^\d+$/.test(ors.value.trim())) {
            e.preventDefault();
            err.innerHTML = 'ORS No. must be numeric.';
            err.classList.remove('d-none');
            ors.focus();
        }
    });

    // ===================== DELETE =====================
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-btn');
        if (!btn) return;

        const id = btn.dataset.budgetId;

        $('actionTitle').textContent = 'Delete Record';
        $('actionBody').innerHTML = `Delete <strong>${id}</strong>?`;

        $('actionFooter').innerHTML = `
            <form method="POST" action="/budget/logbook/${encodeURIComponent(id)}/destroy">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        `;

        bootstrap.Modal.getOrCreateInstance($('actionModal')).show();
    });

    // ===================== ADD MODAL INIT =====================
    document.getElementById('addRecordModal')
    ?.addEventListener('shown.bs.modal', function () {
        initTomSelect(this);
    });

    // ===================== INITIAL LOAD =====================
    initTomSelect(document);

});

document.addEventListener('DOMContentLoaded', function () {

    const container = document.getElementById('reviewRowsContainer');
    const template = document.getElementById('reviewRowTemplate');
    const addBtn = document.getElementById('btnAddReviewRow');

    // ADD ROW
    addBtn.addEventListener('click', function () {
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    });

    // REMOVE ROW (event delegation)
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('btnRemoveReview')) {
            e.target.closest('.review-row').remove();
        }
    });

});
</script>