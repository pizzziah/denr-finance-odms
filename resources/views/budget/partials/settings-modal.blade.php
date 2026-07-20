<!-- ===========================
    BUDGET SETTINGS MODAL
=========================== -->

<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-cogs me-2"></i>
                    Budget Settings
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">

                    <li class="nav-item">

                        <button
                            class="nav-link active"
                            data-bs-toggle="tab"
                            data-bs-target="#classificationTab">

                            Classifications

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#officeTab">

                            Issuing Offices

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#archiveTab">

                            Archive

                        </button>

                    </li>

                </ul>

                <div class="tab-content">

                    <!-- ===================================== -->
                    <!-- CLASSIFICATIONS -->
                    <!-- ===================================== -->

                    <div class="tab-pane fade show active" id="classificationTab">

                        <div class="row">

                            <div class="col-md-4">

                                <div class="card">

                                    <div class="card-header">
                                        Add Classification
                                    </div>

                                    <div class="card-body">

                                        <form method="POST"
                                              action="{{ route('budget.classification.store') }}">

                                            @csrf

                                            <div class="mb-3">

                                                <label class="form-label">

                                                    Classification

                                                </label>

                                                <input
                                                    type="text"
                                                    name="classification"
                                                    class="form-control"
                                                    required>

                                            </div>

                                            <button
                                                class="btn btn-success w-100">

                                                <i class="fas fa-plus"></i>

                                                Add

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-8">

                                <div class="card">

                                    <div class="card-header">

                                        Existing Classifications

                                    </div>

                                    <div class="card-body p-0">

                                        <table class="table table-hover table-bordered mb-0">

                                            <thead class="table-light">

                                                <tr>

                                                    <th width="70">

                                                        #

                                                    </th>

                                                    <th>

                                                        Classification

                                                    </th>

                                                    <th width="120">

                                                        Action

                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                            @forelse($classifications as $item)

                                                <tr>

                                                    <td>

                                                        {{ $loop->iteration }}

                                                    </td>

                                                    <td>

                                                        {{ $item->classifications }}

                                                    </td>

                                                    <td>

                                                        <form method="POST"
                                                            action="{{ route('budget.classification.delete',$item->dropdown_id) }}">

                                                            @csrf
                                                            @method('DELETE')

                                                            <button
                                                                class="btn btn-danger btn-sm w-100"
                                                                onclick="return confirm('Delete this classification?')">

                                                                Delete

                                                            </button>

                                                        </form>

                                                    </td>

                                                </tr>

                                            @empty

                                                <tr>

                                                    <td colspan="3" class="text-center">

                                                        No classifications found.

                                                    </td>

                                                </tr>

                                            @endforelse

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- ===================================== -->
                    <!-- ISSUING OFFICE -->
                    <!-- ===================================== -->

                    <div class="tab-pane fade" id="officeTab">

                        <div class="row">

                            <div class="col-md-4">

                                <div class="card">

                                    <div class="card-header">

                                        Add Issuing Office

                                    </div>

                                    <div class="card-body">

                                        <form method="POST"
                                              action="{{ route('budget.office.store') }}">

                                            @csrf

                                            <div class="mb-3">

                                                <label>

                                                    Office Name

                                                </label>

                                                <input
                                                    type="text"
                                                    name="office"
                                                    class="form-control"
                                                    required>

                                            </div>

                                            <button
                                                class="btn btn-success w-100">

                                                Add Office

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-8">

                                <div class="card">

                                    <div class="card-header">

                                        Existing Offices

                                    </div>

                                    <div class="card-body p-0">

                                        <table class="table table-bordered table-hover mb-0">

                                            <thead class="table-light">

                                            <tr>

                                                <th width="70">

                                                    #

                                                </th>

                                                <th>

                                                    Office

                                                </th>

                                                <th width="120">

                                                    Action

                                                </th>

                                            </tr>

                                            </thead>

                                            <tbody>

                                            @forelse($offices as $item)

                                                <tr>

                                                    <td>

                                                        {{ $loop->iteration }}

                                                    </td>

                                                    <td>

                                                        {{ $item->issuing_office }}

                                                    </td>

                                                    <td>

                                                        <form method="POST"
                                                            action="{{ route('budget.office.delete',$item->dropdown_id) }}">

                                                            @csrf
                                                            @method('DELETE')

                                                            <button
                                                                class="btn btn-danger btn-sm w-100"
                                                                onclick="return confirm('Delete this office?')">

                                                                Delete

                                                            </button>

                                                        </form>

                                                    </td>

                                                </tr>

                                            @empty

                                                <tr>

                                                    <td colspan="3" class="text-center">

                                                        No offices found.

                                                    </td>

                                                </tr>

                                            @endforelse

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- ===================================== -->
                    <!-- ARCHIVE -->
                    <!-- ===================================== -->

                    <div class="tab-pane fade" id="archiveTab">

                        <div class="card">

                            <div class="card-header bg-warning">

                                Year-End Archive

                            </div>

                            <div class="card-body">

                                <div class="alert alert-warning">

                                    <strong>Warning!</strong>

                                    Archiving will move all selected year's records
                                    to the Archives section.

                                </div>

                                <form method="POST"
                                      action="{{ route('budget.archive.year') }}">

                                    @csrf

                                    <div class="row">

                                        <div class="col-md-4">

                                            <label>

                                                Select Year

                                            </label>

                                            <select
                                                class="form-select"
                                                name="year"
                                                required>

                                                @for($y = date('Y'); $y >= 2020; $y--)

                                                    <option value="{{ $y }}">

                                                        {{ $y }}

                                                    </option>

                                                @endfor

                                            </select>

                                        </div>

                                    </div>

                                    <button
                                        class="btn btn-warning mt-4"
                                        onclick="return confirm('Archive all records for this year?')">

                                        <i class="fas fa-archive"></i>

                                        Archive Records

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>