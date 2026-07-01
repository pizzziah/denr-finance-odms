<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller {
  
  public function index(Request $request) {
    $query = AdminUser::query();

    if ($request->filled('department')) {
      $dept = $request->input('department');
      if ($dept === 'System Administration') {
        $query->whereIn('department', ['System Administration', 'Admin']);
      } else {
        $query->where('department', $dept);
      }
    }

    if ($request->filled('search')) {
      $query->where('email', 'LIKE', "%{$request->input('search')}%");
    }

    $users = $query->latest()->paginate(10);
    $pendingUnlocks = DB::table('quarter_locks')->where('requires_admin_unlock', true)->get();

    return view('admin.users', compact('users', 'pendingUnlocks'));
  }

  public function store(Request $request) {
    $request->validate([
      'department' => 'required|string',
      'role' => 'required|string',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:8',
      'permission_level' => 'required_if:department,Accounting|nullable|in:restricted,special'
    ]);

    AdminUser::create([
      'department' => $request->department,
      'role' => $request->role,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'permission_level' => $request->department === 'Accounting' ? $request->permission_level : null,
      'is_active' => 'active'
    ]);

    return redirect()->route('admin.users')->with('success', 'User added successfully.');
  }

  public function update(Request $request, string $id) {
    $user = AdminUser::findOrFail($id);

    $request->validate([
      'department' => 'required|string',
      'role' => 'required|string',
      'email' => 'required|email',
      'permission_level' => 'required_if:department,Accounting|nullable|in:restricted,special'
    ]);

    $data = [
      'email' => $request->email,
      'department' => $request->department,
      'role' => $request->role,
      'permission_level' => $request->department === 'Accounting' ? $request->permission_level : null,
    ];

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);
    return redirect()->route('admin.users')->with('success', 'User updated successfully.');
  }

  public function destroy(string $id) {
    $user = AdminUser::findOrFail($id);
    $newStatus = $user->is_active === 'active' ? 'inactive' : 'active';
    $user->update(['is_active' => $newStatus]);
    return redirect()->route('admin.users')->with('success', $newStatus === 'active' ? 'User reactivated.' : 'User deactivated.');
  }

  public function forceDelete(string $id) {
    $user = AdminUser::findOrFail($id);
    if (auth()->id() == $user->id || in_array($user->department, ['System Administration', 'Admin'])) {
       return redirect()->route('admin.users')->with('error', 'Action denied.');
    }
    $user->delete();
    return redirect()->route('admin.users')->with('success', 'Account permanently removed.');
  }

  public function administrativeUnlockQuarter(Request $request, $id) {
    DB::table('quarter_locks')->where('id', $id)->update(['status' => 'open', 'requires_admin_unlock' => false]);
    return redirect()->back()->with('success', 'Quarter unlocked successfully.');
  }

  public function denyUnlockQuarter($id)
{
    $request = UnlockQuarterRequest::findOrFail($id);

    $request->delete();

    return back()->with(
        'success',
        'Unlock request denied.'
    );
}
}