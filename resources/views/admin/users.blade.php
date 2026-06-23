@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="container-fluid mt-4 px-4">
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card p-3">
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
          <input type="text" name="search" class="form-control fs-6 p-1" style="border-color: #bebebe" placeholder="Search email..." value="{{ request('search') }}">
          <button class="btn" type="submit" style="border-color: #bebebe">
            <i class="bi bi-search"></i>
          </button>
          @if(request('search') || request('department'))
            <a href="{{ route('admin.users') }}" class="btn btn-outline-danger" title="Clear Filters">
              <i class="bi bi-x-circle"></i>
            </a>
          @endif
        </div>
      </form>
    </div>

    <div class="ms-3">
      <x-button variant="header" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-person-fill-add"></i>
      Add User
      </x-button>
    </div>

    <div class="card-body">
      <table class="table table-bordered table-hover align-middle">
        <thead class="sticky-top">
          <tr>
            <th>Email</th>
            <th>Department</th>
            <th>Role</th>
            <th>Status</th>
            <th width="220">Actions</th>
          </tr>
        </thead>

        <tbody>
        @forelse($users as $user)
        <tr>
          <td>{{ $user->email }}</td>
          <td>
            {{ $user->department === 'Admin' ? 'System Administration' : $user->department }}
          </td>
          
          <td> 
            {{ match(strtolower(str_replace(' ', '', $user->role))) {
              'admin' => 'Admin',
              'accountant' => 'Accountant',
              'budget' => 'Budget',
              'bookkeeper' => 'Book Keeper',
              default => ucwords($user->role),
              }
            }}
          </td>
          <td>
            @if($user->is_active === 'active')
              <span class="p-2 rounded" style="background-color: var(--secondary-variant); border: 1px solid var(--primary); color: var(--primary);">
                Active
              </span>
            @else
              <span class="p-2 rounded" style="background-color: #ffe3e3; border: 1px solid var(--error); color: var(--error);">
                Inactive
              </span>
            @endif
          </td>

          <td>
            <div class="d-flex gap-2 justify-content-center align-items-center">
              <x-button variant="edit" as="a"
                href="{{ route('admin.users.edit', $user->id) }}"
                class="px-2">
                  <i class="bi bi-pencil-square"></i>
              </x-button>

              @if(auth()->id() != $user->id && $user->department !== 'System Administration' && $user->department !== 'Admin')
                
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="m-0">
                  @csrf
                  @method('DELETE')

                  @if($user->is_active === 'active')
                    <x-button variant="lock" type="submit" class="px-2" title="Deactivate User">
                      <i class="bi bi-person-fill-lock"></i>
                    </x-button>
                  @else
                    <x-button variant="success" type="submit" class="px-2" title="Reactivate User">
                      <i class="bi bi-person-fill-check"></i>
                    </x-button>
                  @endif
                </form>

                <x-button variant="alert" type="button" class="px-2" 
                          data-bs-toggle="modal" 
                          data-bs-target="#deleteUserModal{{ $user->id }}" 
                          title="Permanently Delete">
                  <i class="bi bi-trash3"></i>
                </x-button>

                <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-start">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold m-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Permanent Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-dark py-3">
                        <p class="mb-1">Are you absolutely sure you want to permanently delete this user account?</p>
                        <strong class="text-danger">{{ $user->email }}</strong>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                          <i class="bi bi-info-circle-fill me-1"></i> This action is irreversible and will erase the account completely.
                        </div>
                      </div>
                      <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.users.forceDelete', $user->id) }}" method="POST" class="m-0">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm fw-bold">Permanently Delete</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center text-muted py-3">No users found matching parameters.</td>
        </tr>
        @endforelse
      </tbody>
      </table>

      <div class="mt-3">
        {{ $users->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>

@include('admin.partials.add-user-modal')
@endsection

@php
  $pageTitle = 'Users';
@endphp