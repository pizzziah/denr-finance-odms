@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="container-fluid mt-4 px-4">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(isset($pendingUnlocks) && $pendingUnlocks->count() > 0)
     <div class="card border-0 shadow-sm border-start border-warning border-3 mb-4 p-3 bg-white">
        <h6 class="fw-bold text-warning mb-2"><i class="bi bi-shield-lock-fill"></i> Pending Quarter Unlock Requests</h6>
        <div class="d-flex flex-wrap gap-2">
           @foreach($pendingUnlocks as $lock)
              <div class="p-2 border rounded d-flex align-items-center gap-3 bg-light">
                 <span class="small font-monospace text-dark"><strong>Year {{ $lock->year }} - Q{{ $lock->quarter }}</strong></span>
                 <div class="d-flex align-items-center gap-2">

    <form action="{{ route('admin.unlock-quarter', $lock->id) }}"
          method="POST"
          class="m-0">
        @csrf
        <button type="submit"
                class="btn btn-xs btn-warning py-0 fw-bold px-2">
            Grant Unlock
        </button>
    </form>

    <button
        type="button"
        class="btn btn-xs btn-outline-danger py-0 px-2"
        data-bs-toggle="modal"
        data-bs-target="#denyUnlockModal{{ $lock->id }}">
        <i class="bi bi-x-lg"></i>
    </button>

</div>
              </div>
           @endforeach

           <div class="modal fade"
     id="denyUnlockModal{{ $lock->id }}"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    Deny Unlock Request
                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                Are you sure you want to deny the unlock request for

                <strong>
                    Year {{ $lock->year }},
                    Quarter {{ $lock->quarter }}
                </strong>?

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <form action="{{ route('admin.unlock-quarter.deny', $lock->id) }}"
                      method="POST">

                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger">
                        Deny Request
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>
        </div>
     </div>
  @endif

  <div class="card glass-card p-3">
    <div class="px-3 pt-3 pb-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <h5 class="fw-bold m-0">Manage Users</h5>
      
      <form action="{{ route('admin.users') }}" method="GET" class="d-flex align-items-center gap-2 m-0 flex-wrap flex-md-nowrap">
        <select name="department" class="form-select form-select-sm" style="min-width: 160px;" onchange="this.form.submit()">
          <option value="">All Departments</option>
          <option value="Accounting" {{ request('department') === 'Accounting' ? 'selected' : '' }}>Accounting</option>
          <option value="Budget" {{ request('department') === 'Budget' ? 'selected' : '' }}>Budget</option>
          <option value="System Administration" {{ request('department') === 'System Administration' || request('department') === 'Admin' ? 'selected' : '' }}>System Administration</option>
        </select>

        <div class="input-group input-group-sm" style="min-width: 260px;">
          <input type="text" name="search" class="form-control p-1" placeholder="Search email..." value="{{ request('search') }}" style="border-color:#bebebe;">
          <button class="btn btn-dark" type="submit" style="border-color:#bebebe;"><i class="bi bi-search"></i></button>  
          @if(request('search') || request('department'))
            <a href="{{ route('admin.users') }}" class="btn btn-outline-danger" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
          @endif
        </div>
      </form>
    </div>

    <div class="ms-3">
      <x-button variant="header" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-fill-add"></i> Add User</x-button>
    </div>

    <div class="card-body bg-transparent">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Email</th>
            <th>Department</th>
            <th>Role</th>
            <th>Accounting Access Scope</th> <th>Status</th>
            <th width="220">Actions</th>
          </tr>
        </thead>
        <tbody>


@forelse($users as $user)
<tr>
  <td>{{ $user->email }}</td>
  <td>{{ $user->department === 'Admin' ? 'System Administration' : $user->department }}</td>
  <td>
    {{ match(strtolower(str_replace(' ', '', $user->role))) {
      'admin' => 'Admin',
      'accountant' => 'Accountant',
      'bookkeeper' => 'Book Keeper',
      'budget' => 'Budget',
      default => ucwords($user->role),
    } }}
  </td>
  <td>
    @if($user->department === 'Accounting')
      <span class="badge {{ $user->permission_level === 'special' ? 'bg-purple style-override text-dark border border-dark' : 'bg-light text-muted border' }} px-2 py-1">
        {{ $user->permission_level === 'special' ? '⚡ Special' : '🔒 Restricted' }}
      </span>
    @else
      <span class="text-muted font-monospace small">-</span>
    @endif
  </td>
          <td>
            <span class="p-2 rounded small" style="{{ $user->is_active === 'active' ? 'background-color: var(--secondary-variant); border: 1px solid var(--primary); color: var(--primary);' : 'background-color: #ffe3e3; border: 1px solid var(--error); color: var(--error);' }}">
              {{ ucwords($user->is_active) }}
            </span>
          </td>
          <td>
            <div class="d-flex gap-2 justify-content-center align-items-center">
              <button type="button" class="btn btn-xs btn-outline-primary px-2" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}"><i class="bi bi-pencil-square"></i></button>

              @if(auth()->id() != $user->id && !in_array($user->department, ['System Administration', 'Admin']))
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="m-0">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-xs {{ $user->is_active === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} px-2">
                     <i class="bi {{ $user->is_active === 'active' ? 'bi-person-fill-lock' : 'bi-person-fill-check' }}"></i>
                  </button>
                </form>
                <button type="button" class="btn btn-xs btn-outline-danger px-2" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}"><i class="bi bi-trash3"></i></button>
              @endif
            </div>
          </td>
        </tr>
        @include('admin.partials.edit-user-modal', ['user' => $user])
        @empty
        <tr><td colspan="5" class="text-center text-muted py-3">No user records matched query parameters.</td></tr>
        @endforelse
      </tbody>
      </table>
      <div class="mt-3">{{ $users->withQueryString()->links() }}</div>
    </div>
  </div>
</div>
@include('admin.partials.add-user-modal')
@endsection

@php 
  $pageTitle = 'Users';
@endphp