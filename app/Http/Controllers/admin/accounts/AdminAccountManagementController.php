<?php

namespace App\Http\Controllers\admin\accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AdminAccountManagementController extends Controller
{
  public function index()
  {
    $users = User::where('role_id', '!=', 1)
        ->with('role')
        ->orderBy('created_at', 'desc')
        ->get();

    $totalUsers     = $users->count();
    $activeUsers    = $users->where('STATUS', 'active')->count();
    $inactiveUsers  = $users->where('STATUS', 'inactive')->count();
    $suspendedUsers = $users->where('STATUS', 'suspended')->count();


    return view('content.admin.admin-accounts.usermanagement', compact(
      'users',
      'totalUsers',
      'activeUsers',
      'inactiveUsers',
      'suspendedUsers'
    ));
  }

  public function store(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'role_id' => 'required|exists:roles,role_id',
      'email' => 'required|email|unique:users,email',
      'password' => 'nullable|min:8',
      'first_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'last_name' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => $validator->errors()->first()
      ], 422);
    }

    try {
      $user = User::create([
        'role_id' => $request->role_id,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'STATUS' => 'active', // Default status
      ]);

      return response()->json([
        'success' => true,
        'message' => 'User created successfully',
        'user' => $user
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to create user: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update the specified user
   */
  public function update(Request $request, $id)
  {
    $user = User::where('user_id', $id)->firstOrFail();

    $validator = Validator::make($request->all(), [
      'role_id' => 'required|exists:roles,role_id',
      'email' => 'required|email|unique:users,email,' . $id . ',user_id',
      'password' => 'nullable|min:8',
      'first_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'last_name' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => $validator->errors()->first()
      ], 422);
    }

    try {
      $user->role_id = $request->role_id;
      $user->email = $request->email;
      $user->first_name = $request->first_name;
      $user->middle_name = $request->middle_name;
      $user->last_name = $request->last_name;

      // Only update password if provided
      if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
      }

      $user->save();

      return response()->json([
        'success' => true,
        'message' => 'User updated successfully',
        'user' => $user
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update user: ' . $e->getMessage()
      ], 500);
    }
  }
  public function updateStatus(Request $request, $id)
  {
    $user = User::where('user_id', $id)->firstOrFail();

    $validator = Validator::make($request->all(), [
      'status' => 'required|in:active,inactive,suspended',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => $validator->errors()->first()
      ], 422);
    }

    try {
      $oldStatus   = strtolower($user->STATUS);
      $user->STATUS = $request->status;
      $user->save();

      return response()->json([
        'success'    => true,
        'message'    => 'User status updated successfully',
        'old_status' => $oldStatus,
        'new_status' => $request->status,
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update status: ' . $e->getMessage()
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $user = User::where('user_id', $id)->firstOrFail();

      // Safe comparison — cast both to string to avoid type mismatch
      if ((string) auth()->user()->user_id === (string) $id) {
        return response()->json([
          'success' => false,
          'message' => 'You cannot delete your own account'
        ], 403);
      }

      $deletedStatus = strtolower($user->STATUS);
      $user->delete();

      return response()->json([
        'success' => true,
        'message' => 'User deleted successfully'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      return response()->json([
        'success' => false,
        'message' => 'User not found'
      ], 404);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete user: ' . $e->getMessage()
      ], 500);
    }
  }
}
