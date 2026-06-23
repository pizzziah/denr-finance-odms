<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller {
  /**
  * Display all users with search and department filtering applied.
  */
  public function index(Request $request) {
    // Instantiating query scope builder
    $query = AdminUser::query();

    // Target Specific Department Scope via dropdown
    if ($request->filled('department')) {
      $dept = $request->input('department');
      if ($dept === 'System Administration') {
        $query->whereIn('department', ['System Administration', 'Admin']);
      } else {
        $query->where('department', $dept);
      }
    }

    // Apply Email Search string matching queries
    if ($request->filled('search')) {
      $search = $request->input('search');
      $query->where('email', 'LIKE', "%{$search}%");
    }

    // Apply global sorting scope pipeline before rendering pages
    $users = $query->latest()->paginate(10);

    return view('admin.users', compact('users'));
  }

  /**
    * Store a new user.
  */
  public function store(Request $request) {
    $request->validate([
      'department' => 'required',
      'role' => 'required',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:8'
    ]);

    AdminUser::create([
      'department' => $request->department,
      'role' => $request->role,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'is_active' => 'active'
    ]);

    return redirect()
      ->route('admin.users')
      ->with('success', 'User added successfully.');
  }

  /**
  * Show edit form.
  */
  public function edit(string $id) {
    $user = AdminUser::findOrFail($id);

    return view('admin.edit-user', compact('user'));
  }

  /**
  * Update user.
  */
  public function update(Request $request, string $id) {
    $user = AdminUser::findOrFail($id);

    $request->validate([
      'department' => 'required',
      'role' => 'required',
      'email' => 'required|email',
    ]);

    $user->update([
      'email' => $request->email,
      'department' => $request->department,
      'role' => $request->role,
    ]);

    return redirect()
      ->route('admin.users')
      ->with('success', 'User updated successfully.');
  }

  /**
  * Deactivate user.
  */
  public function destroy(string $id) {
    $user = AdminUser::findOrFail($id);

    $newStatus = $user->is_active === 'active'
      ? 'inactive'
      : 'active';

    $user->update([
      'is_active' => $newStatus
    ]);

    return redirect()
      ->route('admin.users')
      ->with(
        'success',
        $newStatus === 'active'
          ? 'User reactivated successfully.'
          : 'User deactivated successfully.'
    );
  }

  /**
   * Permanently delete a user.
  */
  public function forceDelete(string $id) {
    $user = AdminUser::findOrFail($id);

    if (auth()->id() == $user->id || $user->department === 'System Administration' || $user->department === 'Admin') {
      return redirect()
        ->route('admin.users')
        ->with('error', 'Action denied. This account cannot be permanently deleted.');
      }

      $user->delete();

      return redirect()
        ->route('admin.users')
        ->with('success', 'User has been permanently deleted.');
  }
}