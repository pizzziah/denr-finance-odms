@extends('layouts.app')

@section('title', 'Pending Unlock Requests')

@section('content')
<div class="container-fluid mt-4 px-4">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card glass-card p-3">
    <!-- Header Block -->
    <div class="px-3 pt-3 pb-2 border-bottom mb-3">
      <h5 class="fw-bold text-dark m-0">
        <i class="bi bi-shield-lock-fill me-2 text-warning"></i> Pending Quarter Unlock Requests
      </h5>
    </div>

    <!-- Main Content Area -->
    <div class="card-body bg-transparent">
      @if(isset($pendingUnlocks) && $pendingUnlocks->count() > 0)
        <!-- Scroll bar active area wrapping custom table layout -->
        <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Target Period</th>
                <th>Request Flag</th>
                <th width="200" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pendingUnlocks as $lock)
                <tr>
                  <td>
                    <span class="fw-bold text-dark">
                      Year {{ $lock->year }} — Quarter {{ $lock->quarter }}
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-warning text-dark px-2 py-1">
                      Requires Admin Unlock
                    </span>
                  </td>
                  <td>
                    <div class="d-flex gap-2 justify-content-center align-items-center">
                      <!-- Grant Access form handler direct trigger action -->
                      <form action="{{ route('admin.unlock-quarter', $lock->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning fw-bold px-3">
                          Grant Unlock
                        </button>
                      </form>

                      <!-- Deny Dialog Modal Activation Switch (Excluded Backdrop overlay setup) -->
                      <button type="button" class="btn btn-sm btn-outline-danger px-2" data-bs-toggle="modal" data-bs-backdrop="false" data-bs-target="#denyUnlockModal{{ $lock->id }}">
                        <i class="bi bi-x-lg me-1"></i> Deny
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Standalone Confirmation Overlay Context Modal Contexts -->
                <div class="modal fade" id="denyUnlockModal{{ $lock->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fs-6 fw-bold">Deny Unlock Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      
                      <div class="modal-body text-dark">
                        Are you sure you want to deny the unlock request for 
                        <strong>Year {{ $lock->year }}, Quarter {{ $lock->quarter }}</strong>?
                      </div>
                      
                      <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                          Cancel
                        </button>
                        
                        <form action="{{ route('admin.unlock-quarter.deny', $lock->id) }}" method="POST" class="m-0">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger">
                            Deny Request
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <!-- Empty State display when collections register empty responses -->
        <div class="text-center py-5 text-muted">
          <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
          <h6 class="fw-bold">All caught up!</h6>
          <p class="small mb-0">No pending quarter administrative unlock requests found.</p>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
// Cleans background overlays cleanly upon termination calls 
document.addEventListener('hidden.bs.modal', function () {
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.remove();
    });
});
</script>
@endsection