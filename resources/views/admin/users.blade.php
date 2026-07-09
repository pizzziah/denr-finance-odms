@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="container-fluid mt-4 px-4">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card glass-card p-3">
    <div class="px-3 pt-3 pb-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      
      <div>
        <x-button variant="header" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="bi bi-person-fill-add"></i> Add User
        </x-button>
      </div>
      
      <form action="{{ route('admin.users') }}" method="GET" class="d-flex align-items-center gap-2 m-0 flex-wrap flex-md-nowrap">
        <select name="department" class="form-select form-select-sm" style="min-width: 160px;" onchange="this.form.submit()">
          <option value="">All Sections</option>
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

    <div class="card-body bg-transparent table-responsive" style="max-height: 650px; overflow-y: auto;">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Email</th>
            <th>Section</th>
            <th>Role</th>
            <th style="width: 180px;">Access Scope (For Accounting Only)</th> 
            <th>Status</th>
            <th width="220">Actions</th>
          </tr>
        </thead>
        <tbody>

        @forelse($users as $user)
          @php
            $deptClean = strtolower(str_replace(' ', '', $user->department));
            
            $roleColorStyle = match($deptClean) {
              'systemadministration', 'admin' => 'color: #0B879D; font-weight: bold;',
              'accounting'                    => 'color: var(--primary); font-weight: bold;',
              'budget'                        => 'color: var(--secondary); font-weight: bold;',
              default                         => 'color: text-dark;'
            };
          @endphp
          <tr>
            <td class="fw-bold">{{ $user->email }}</td>
            <td>{{ $user->department === 'Admin' ? 'System Administration' : $user->department }}</td>
            <td>
              <span style="{{ $roleColorStyle }} font-size: 0.85rem;">
                {{ match(strtolower(str_replace(' ', '', $user->role))) {
                  'admin' => 'Admin',
                  'accountant' => 'Accountant',
                  'bookkeeper' => 'Book Keeper',
                  'budget' => 'Budget',
                  default => ucwords($user->role),
                } }}
              </span>
            </td>
            <td class="text-center" style="{{ $user->department !== 'Accounting' ? 'background-color: #e7e7e7;' : '' }}">
              @if($user->department === 'Accounting')
                <span style="font-size: 0.85rem;">
                  {{ $user->permission_level === 'special' ? 'Special' : 'Restricted' }}
                </span>
              @else
                <span style="color: #6c757d; font-weight: bold;">—</span>
              @endif
            </td>
            <td>
              <span style="{{ $user->is_active === 'active' ? 'color: var(--primary);' : 'color: var(--error);' }}">
                {{ ucwords($user->is_active) }}
              </span>
            </td>
            <td>
              <div class="d-flex gap-2 justify-content-center align-items-center">
                <button type="button" class="btn btn-xs btn-outline-primary px-2" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                  <i class="bi bi-pencil-square"></i>
                </button>

                @if(auth()->id() != $user->id && !in_array($user->department, ['System Administration', 'Admin']))
                  <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="m-0">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs {{ $user->is_active === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} px-2">
                       <i class="bi {{ $user->is_active === 'active' ? 'bi-person-fill-lock' : 'bi-person-fill-check' }}"></i>
                    </button>
                  </form>
                  <button type="button" class="btn btn-xs btn-outline-danger px-2" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}">
                    <i class="bi bi-trash3"></i>
                  </button>
                @endif
              </div>
            </td>
          </tr>
          @include('admin.partials.edit-user-modal', ['user' => $user])
          
          <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" data-bs-backdrop="false" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered">
                 <div class="modal-content">
                     <div class="modal-header bg-danger text-white">
                         <h5 class="modal-title fs-6">Remove User Account</h5>
                         <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                     </div>
                     <div class="modal-body text-start">
                         Are you completely sure you want to purge data permissions assigned to <strong>{{ $user->email }}</strong>?
                     </div>
                     <div class="modal-footer">
                         <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                         <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" class="m-0">
                             @csrf @method('DELETE')
                             <button type="submit" class="btn btn-sm btn-danger">Confirm Deletion</button>
                         </form>
                     </div>
                 </div>
             </div>
          </div>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-3">No user records matched query parameters.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('admin.partials.add-user-modal')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addModalEl = document.getElementById('addUserModal');
    if (addModalEl) {
        addModalEl.setAttribute('data-bs-backdrop', 'false'); // Backup structural assurance
        const passwordInput = addModalEl.querySelector('input[name="password"]');
        const saveButton = addModalEl.querySelector('.btn-save-entry') || addModalEl.querySelector('button[type="submit"]');
        const cancelButtons = addModalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        const inputs = addModalEl.querySelectorAll('input, select');
        
        let errorMsg = document.createElement('small');
        errorMsg.className = 'text-danger d-block mt-1 password-error-msg';
        errorMsg.style.display = 'none';
        errorMsg.innerText = 'Password must be at least 8 characters long.';
        
        if (passwordInput) {
            passwordInput.parentNode.appendChild(errorMsg);
            
            passwordInput.addEventListener('input', function() {
                if (passwordInput.value.length > 0 && passwordInput.value.length < 8) {
                    errorMsg.style.display = 'block';
                    if (saveButton) saveButton.disabled = true;
                } else {
                    errorMsg.style.display = 'none';
                    if (saveButton) saveButton.disabled = false;
                }
            });
        }

        cancelButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                let hasValues = false;
                inputs.forEach(input => {
                    if(input.type !== 'submit' && input.type !== 'button' && input.value !== '' && input.name !== '_token') {
                        hasValues = true;
                    }
                });

                if (hasValues) {
                    if (!confirm('You have unsaved changes. Are you sure you want to discard them?')) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }
            });
        });
    }

    // Intercept and break backdrop construction globally
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

@if ($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addUserModal = document.getElementById('addUserModal');

    if (addUserModal) {
        const modal = new bootstrap.Modal(addUserModal);
        modal.show();
    }
});
</script>
@endif
@endsection

@php
  $pageTitle = 'Users';
@endphp