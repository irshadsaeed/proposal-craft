<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class ClientUsersController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientUser::withCount('proposals');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->get('plan')) {
            $query->where('plan_slug', $plan);
        }

        if ($status = $request->get('status')) {
            $query->where('is_active', $status === 'active');
        }

        $users = $query->latest()->paginate(20);

        return view('admin-dashboard.client-users-view', compact('users'));
    }

    public function show(ClientUser $user)
    {
        $user->load(['proposals' => fn($q) => $q->latest()->take(5)]);
        $user->loadCount('proposals');
        return view('admin-dashboard.client-users-detail', compact('user'));
    }

    public function suspend(ClientUser $user)
    {
        $user->update(['is_active' => false]);
        AdminActivityLog::log('user.suspended', $user);
        return response()->json(['ok' => true, 'message' => "{$user->name} suspended."]);
    }

    public function update(Request $request, ClientUser $user)
    {
        $request->validate(['plan_slug' => 'required|in:free,pro,agency']);
        $user->update(['plan_slug' => $request->plan_slug]);
        return redirect()->route('admin.users.show', $user->id)
            ->with('success', "{$user->name}'s plan updated to " . ucfirst($request->plan_slug) . ".");
    }

    public function unsuspend(ClientUser $user)
    {
        $user->update(['is_active' => true]);
        AdminActivityLog::log('user.unsuspended', $user);
        return response()->json(['ok' => true, 'message' => "{$user->name} reactivated."]);
    }

    public function destroy(ClientUser $user)
    {
        $name = $user->name;
        AdminActivityLog::log('user.deleted', $user, ['email' => $user->email]);
        $user->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => "{$name} has been deleted."]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "{$name} has been deleted.");
    }
}
