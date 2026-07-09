@extends('layouts.app')

@section('title', 'Pending Unlock Requests')

@section('content')
<div class="container-fluid mt-4 px-4">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm p-3">
    <div class="px-3 pt-3 pb-3 border-bottom mb-3 d-flex align-items-center justify-content-between">
      <h5 class="fw-bold text-dark m-0">
        Pending Quarter Unlock Requests
      </h5>
      <span class="badge bg-light text-dark border fw-semibold px-3 py-2">
        {{ $pendingUnlocks->count() }} Request(s) Pending
      </span>
    </div>

    <div class="card-body bg-transparent p-0">
      @if(isset($pendingUnlocks) && $pendingUnlocks->count() > 0)
        <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
          <table class="table table-bordered table-hover align-middle">
            <thead>
              <tr>
                <th class="ps-3 py-3">Target Period</th>
                <th class="py-3">Requester</th>
                <th class="py-3">Timestamp</th>
                <th width="360" class="text-center py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pendingUnlocks as $lock)

              <tr>
  <td class="ps-3">
    <span class="fw-semibold text-dark">
      Year {{ $lock->year }} — Quarter {{ $lock->quarter }}
    </span>
  </td>
  <td>
    <div class="d-flex align-items-center">
      <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
        <i class="bi bi-person-fill"></i>
      </div>
      <div>
        <span class="text-dark fw-medium d-block small">
          {{ auth()->user() && auth()->user()->department === 'Accounting' ? auth()->user()->email : ($currentUser->email ?? 'accounting@system.local') }}
        </span>
        <span class="text-muted tiny d-block font-monospace" style="font-size: 0.70rem;">Accounting Dept Context</span>
      </div>
    </div>
  </td>
  <td>
    <div class="text-dark mb-0 small fw-medium">
      {{ $lock->updated_at ? \Carbon\Carbon::parse($lock->updated_at)->setTimezone('Asia/Manila')->format('M d, Y') : \Carbon\Carbon::parse($lock->created_at)->setTimezone('Asia/Manila')->format('M d, Y') }}
    </div>
    <div class="text-muted tiny text-xs" style="font-size: 0.75rem;">
      {{ $lock->updated_at ? \Carbon\Carbon::parse($lock->updated_at)->setTimezone('Asia/Manila')->format('h:i A') : \Carbon\Carbon::parse($lock->created_at)->setTimezone('Asia/Manila')->format('h:i A') }}
    </div>
  </td>
              
                  <td>
                    <div class="d-flex gap-2 justify-content-center align-items-center">
                      <form action="{{ route('admin.unlock-quarter', $lock->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm">
                          <i class="bi bi-unlock-fill me-1"></i> Grant Unlock
                        </button>
                      </form>

                      <button type="button" class="btn btn-sm btn-outline-danger px-3" data-bs-toggle="modal" data-bs-target="#denyUnlockModal{{ $lock->id }}">
                        <i class="bi bi-x-lg me-1"></i> Deny
                      </button>
                    </div>
                  </td>
                </tr>

                <div class="modal fade" id="denyUnlockModal{{ $lock->id }}" tabindex="-1" data-bs-backdrop="false" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0">
                      <div class="modal-header bg-danger text-white py-3">
                        <h5 class="modal-title fs-6 fw-bold m-0">
                          <i class="bi bi-exclamation-triangle-fill me-2"></i>Deny Unlock Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      
                      <div class="modal-body text-dark p-4">
                        Are you sure you want to deny the administrative unlock request for 
                        <strong class="text-danger">Year {{ $lock->year }}, Quarter {{ $lock->quarter }}</strong>? This action locks down further entries.
                      </div>
                      
                      <div class="modal-footer bg-light border-0 py-2 px-3">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                          Cancel
                        </button>
                        
                        <form action="{{ route('admin.unlock-quarter.deny', $lock->id) }}" method="POST" class="m-0">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger px-3">
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
        <div class="text-center py-5 my-3 text-muted">
          <i class="bi bi-check-circle text-success fs-1 d-block mb-3 opacity-75"></i>
          <h6 class="fw-bold text-dark">All caught up!</h6>
          <p class="small text-secondary mb-0">No pending quarter administrative unlock requests found.</p>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clearBackdrop = () => {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    };

    document.addEventListener('hidden.bs.modal', clearBackdrop);
    document.addEventListener('shown.bs.modal', function() {
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    });
});
</script>
@endsection

@php
  $pageTitle = 'Unlock Requests';
@endphp