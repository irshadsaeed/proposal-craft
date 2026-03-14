<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUsersController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminUser::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $admins = $query->latest()->paginate(20);

        return view('admin-dashboard.admin-users-view', compact('admins'));
    }

    public function show(AdminUser $user)
    {
        return view('admin-dashboard.admin-users-detail', compact('user'));
    }

    public function update(Request $request, AdminUser $user)
    {
        $request->validate([
            'role' => 'required|in:super_admin,admin,editor',
            'is_active' => 'required|boolean'
        ]);

        $user->update([
            'role' => $request->role,
            'is_active' => $request->is_active
        ]);

        AdminActivityLog::log('admin.updated', $user, [
            'role' => $request->role,
            'is_active' => $request->is_active
        ]);

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('success', "{$user->name}'s account updated successfully.");
    }

    public function suspend(AdminUser $user)
    {
        $user->update(['is_active' => false]);
        AdminActivityLog::log('admin.suspended', $user);
        return response()->json(['ok' => true, 'message' => "{$user->name} suspended."]);
    }

    public function unsuspend(AdminUser $user)
    {
        $user->update(['is_active' => true]);
        AdminActivityLog::log('admin.unsuspended', $user);
        return response()->json(['ok' => true, 'message' => "{$user->name} reactivated."]);
    }

    public function destroy(AdminUser $user)
    {
        if ($user->id === auth('admin')->id()) {
            return response()->json(['ok' => false, 'message' => 'Cannot delete yourself.'], 403);
        }
        $name = $user->name;
        AdminActivityLog::log('admin.deleted', $user, ['email' => $user->email]);
        $user->delete();
        return response()->json(['ok' => true, 'message' => "{$name} deleted."]);
    }
}
