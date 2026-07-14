<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUser;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; 

class AdminUserController extends Controller {
  public function unlockRequests() {
    $pendingUnlocks = DB::table('odms_admin_quarter_locks')
      ->where('requires_admin_unlock', true)
      ->latest('updated_at')
      ->get();

    return view('admin.unlock-requests', compact('pendingUnlocks'));
}
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
    $pendingUnlocks = DB::table('odms_admin_quarter_locks')->where('requires_admin_unlock', true)->get();

    return view('admin.users', compact('users', 'pendingUnlocks'));
  }

  public function store(Request $request) {
    $request->validate([
      'department' => 'required|string',
      'role' => 'required|string',
      'email' => 'required|email|unique:odms_admin_users,email',
      'password' => 'required|min:8',
      'permission_level' => 'required_if:department,Accounting|nullable|in:restricted,special',
    ], [
      'email.unique' => 'This email address is already registered.',
      'email.email'  => 'Please enter a valid email address.',
      'password.min' => 'Password must be at least 8 characters long.',
    ]);

    AdminUser::create([
      'department' => $request->department,
      'role' => $request->role,
      'email' => $request->email,
      'password' => Hash::make($request->password),        
      'permission_level' => $request->department === 'Accounting' ? $request->permission_level : null,
      'is_active' => 'active',
    ]);

    return redirect()->route('admin.users')->with('success', 'User added successfully.');
  }

  public function update(Request $request, string $id) {
    $user = AdminUser::findOrFail($id);

    $assignedRole = match ($request->input('department')) {
      'System Administration', 'Admin' => 'Admin',
      'Budget'                         => 'Budget',
      'Accounting'                     => $user->role === 'Budget' || $user->role === 'Admin' ? 'Accountant' : $user->role, 
      default                          => 'Book Keeper',
    };

    $request->merge(['role' => $request->input('role', $assignedRole)]);

    $request->validate([
      'department'       => 'required|string',
      'role'             => 'required|string',
      'email'            => [
        'required',
        'email',
        Rule::unique('odms_admin_users', 'email')->ignore($user->id),
      ],
      'permission_level' => 'required_if:department,Accounting|nullable|in:restricted,special',
      'password'         => 'nullable|string|min:8', // Safe field updating validation rules
    ]);

    $data = [
      'email'            => $request->email,
      'department'       => $request->department,
      'role'             => $request->role,
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
    DB::table('odms_admin_quarter_locks')
      ->where('id', $id)
      ->update([
        'status' => 'open',
        'requires_admin_unlock' => false,
        'updated_at' => Carbon::now(),
      ]);

      $lock = DB::table('odms_admin_quarter_locks')
        ->where('id', $id)
        ->first();

      Notification::create([
          'title'       => 'Unlock Request Approved',
          'message'     => "Your request to unlock Year {$lock->year}, Quarter {$lock->quarter} has been approved.",
          'target_role' => 'accountant',
          'type'        => 'unlock_approved',
          'priority'    => 'Medium',
          'related_id'  => $lock->id,
          'is_read'     => 0,
      ]);

    return redirect()->back()->with('success', 'Quarter access granted and unlocked successfully.');
  }

  public function denyUnlockQuarter($id) {
    DB::table('odms_admin_quarter_locks')
      ->where('id', $id)
      ->update([
        'requires_admin_unlock' => false,
        'updated_at' => Carbon::now(),
      ]);

      $lock = DB::table('odms_admin_quarter_locks')
        ->where('id', $id)
        ->first();

      Notification::create([
          'title'       => 'Unlock Request Denied',
          'message'     => "Your request to unlock Year {$lock->year}, Quarter {$lock->quarter} has been denied.",
          'target_role' => 'accountant',
          'type'        => 'unlock_denied',
          'priority'    => 'Medium',
          'related_id'  => $lock->id,
          'is_read'     => 0,
      ]);

    return redirect()->back()->with('success', 'Unlock request denied. Ledger access remains locked.');
  }
  public function checkEmail(Request $request)
{
    $exists = AdminUser::where('email', $request->email)->exists();

    return response()->json([
        'exists' => $exists
    ]);
}
}