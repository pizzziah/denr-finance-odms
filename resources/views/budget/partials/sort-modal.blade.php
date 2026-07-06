{{-- SORT MODAL --}}
<div class="modal fade" id="sortModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ route('budget.logbook') }}">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Sort Records
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            <strong>Sort By</strong>
                        </label>

                        <select name="sort" class="form-select">

                            <option value="latest"
                                @selected(request('sort')=='latest')>
                                Latest Date
                            </option>

                            <option value="oldest"
                                @selected(request('sort')=='oldest')>
                                Oldest Date
                            </option>

                            <option value="ors_asc"
                                @selected(request('sort')=='ors_asc')>
                                ORS Number (Ascending)
                            </option>

                            <option value="ors_desc"
                                @selected(request('sort')=='ors_desc')>
                                ORS Number (Descending)
                            </option>

                        </select>
                    </div>

                    {{-- Preserve current filters --}}
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Apply Sort
                    </button>

                </div>

            </div>

        </form>
    </div>
</div>