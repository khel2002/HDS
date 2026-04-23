<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    // Role IDs — must match your roles table
    const ROLE_GUEST       = 1;
    const ROLE_STAFF       = 2;
    const ROLE_ADMIN       = 3;
    const ROLE_SUPER_ADMIN = 4;

    public function index(Request $request)
    {
        $query = User::with('roleModel');

        if ($request->filled('role')) {
            // role filter expects role_name string e.g. 'admin'
            $roleId = Role::where('role_name', $request->role)->value('role_id');
            if ($roleId) $query->where('role_id', $roleId);
        }

        if ($request->filled('status')) {
            $query->where('STATUS', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();
        $roles = Role::all();

        $stats = [
            'total'       => User::count(),
            'super_admin' => User::where('role_id', self::ROLE_SUPER_ADMIN)->count(),
            'admin'       => User::where('role_id', self::ROLE_ADMIN)->count(),
            'staff'       => User::where('role_id', self::ROLE_STAFF)->count(),
            'guest'       => User::where('role_id', self::ROLE_GUEST)->count(),
            'active'      => User::where('STATUS', 'active')->count(),
            'inactive'    => User::where('STATUS', 'inactive')->count(),
        ];

        return view('content.super-admin.users.index', compact('users', 'stats', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'role_id'    => 'required|exists:roles,role_id',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'role_id'    => $request->role_id,
            'STATUS'     => $request->status ?? 'active',
            'password'   => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::with('roleModel')->findOrFail($id);
        return response()->json([
            'user_id'    => $user->user_id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'role_id'    => $user->role_id,
            'role_name'  => $user->role,
            'status'     => $user->status,
            'created_at' => $user->created_at,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => ['required', 'email', Rule::unique('users', 'email')->ignore($id, 'user_id')],
            'role_id'    => 'required|exists:roles,role_id',
            'status'     => 'required|in:active,inactive',
            'password'   => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'role_id'    => $request->role_id,
            'STATUS'     => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $user->update(['STATUS' => $request->status]);

        return redirect()->back()->with('success', 'User status updated.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->user_id === auth()->user()->user_id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}