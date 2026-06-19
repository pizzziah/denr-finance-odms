@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="container-fluid p-0 m-0">
  <div class="d-flex mb-3">
    <x-button variant="header"
      data-bs-toggle="modal"
      data-bs-target="#addUserModal">
      <i class="bi bi-person-fill-add"></i>
      Add User
    </x-button>
  </div>

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

  <div class="card">
    <h5 class="px-3 pt-3 fw-bold pb-0 m-0">
      Manage Users
    </h5>

    <div class="card-body">
      <table class="table table-bordered align-middle">
        <thead>
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
                <form action="{{ route('admin.users.destroy', $user->id) }}"
                  method="POST">
                  @csrf
                  @method('DELETE')

                  @if($user->is_active === 'active')
                    <x-button variant="alert" type="submit" class="px-2">
                      <i class="bi bi-person-fill-lock"></i>
                    </x-button>
                  @else
                    <x-button variant="success" type="submit" class="px-2">
                      <i class="bi bi-person-fill-check"></i>
                    </x-button>
                  @endif
                </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">No users found.</td>
        </tr>
        @endforelse
      </tbody>
      </table>

      <div class="mt-3">
        {{ $users->links() }}
      </div>
    </div>

    
  </div>
</div>

@include('admin.partials.add-user-modal')
@endsection

@php
  $pageTitle = 'Users';
@endphp