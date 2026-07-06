
{{-- FILTER MODAL --}}
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ route('budget.logbook') }}">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2"><i class="bi bi-sort-down"></i>
                        <span>Sort Records</span>
                    </h5>
                    <button type="button"class="btn-close"data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label><strong>Year</strong></label>
                        <select name="year" class="form-select">
                            <option value="all">All</option>
                            <option value="2025" @selected(request('year')=='2025')>2025</option>
                            <option value="2026" @selected(request('year')=='2026')>2026</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label><strong>Month</strong></label>
                        <select name="month" class="form-select">
                            <option value="">All</option>
                            @for($m=1;$m<=12;$m++)
                                <option value="{{ $m }}" @selected(request('month')==$m)>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label><strong>Status</strong></label>
                        <select name="status" class="form-select">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="for_review">For Review</option>
                            <option value="for_obligation">For Obligation</option>
                            <option value="forwarded_to_accounting">Forwarded to Accounting</option>
                            <option value="forwarded_to_cashier">Forwarded to Cashier</option>
                            <option value="paid">Paid</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <input type="hidden" name="search" value="{{ request('search') }}">

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">
                        Apply Filters
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>