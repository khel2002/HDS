<?php

namespace App\Http\Controllers\admin\accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GuestDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GuestAccountManagementController extends Controller
{
  public function index()
  {

    $guests = GuestDetail::with('user')
      ->whereHas('user', function ($query) {
        $query->where('role_id', 1);
      })
      ->get();

    $totalUsers     = $guests->count();
    $activeUsers    = $guests->where('STATUS', 'active')->count();
    $inactiveUsers  = $guests->where('STATUS', 'inactive')->count();
    $suspendedUsers = $guests->where('STATUS', 'suspended')->count();
    // dd($guests);
    return view('content.admin.guest-accounts.guestmanagement', compact(
      'guests',
      'totalUsers',
      'activeUsers',
      'inactiveUsers',
      'suspendedUsers'
    ));
  }
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:8',
      'first_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'last_name' => 'required|string|max:255',
      'contact_number' => 'required|string|max:20',
      'dob' => 'required|date',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => $validator->errors()->first()
      ], 422);
    }

    DB::beginTransaction();
    try {
      // Create user account
      $user = User::create([
        'role_id' => 1,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'STATUS' => 'active',
      ]);

      // Create guest details
      $guestDetail = GuestDetail::create([
        'user_id' => $user->user_id,
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'contact_number' => $request->contact_number,
        'dob' => $request->dob,
        'arrival_date' => null,
        'departure_date' => null,
      ]);

      DB::commit();

      $guestDetail->load('user');

      return response()->json([
        'success' => true,
        'message' => 'Guest user created successfully',
        'guest' => $guestDetail
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to create guest user: ' . $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    $guestDetail = GuestDetail::where('guest_details_id', $id)->with('user')->firstOrFail();

    if ($guestDetail->user->role_id != 1) {
      return response()->json([
        'success' => false,
        'message' => 'User is not a guest'
      ], 403);
    }

    $validator = Validator::make($request->all(), [
      'email' => 'required|email|unique:users,email,' . $guestDetail->user_id . ',user_id',
      'password' => 'nullable|min:8',
      'first_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'last_name' => 'required|string|max:255',
      'contact_number' => 'required|string|max:20',
      'dob' => 'required|date',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => $validator->errors()->first()
      ], 422);
    }

    DB::beginTransaction();
    try {
      $user = $guestDetail->user;
      $user->email = $request->email;
      $user->first_name = $request->first_name;
      $user->middle_name = $request->middle_name;
      $user->last_name = $request->last_name;

      if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
      }

      $user->save();

      $guestDetail->first_name = $request->first_name;
      $guestDetail->middle_name = $request->middle_name;
      $guestDetail->last_name = $request->last_name;
      $guestDetail->contact_number = $request->contact_number;
      $guestDetail->dob = $request->dob;
      $guestDetail->save();

      DB::commit();

      $guestDetail->load('user');

      return response()->json([
        'success' => true,
        'message' => 'Guest user updated successfully',
        'guest' => $guestDetail
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to update guest user: ' . $e->getMessage()
      ], 500);
    }
  }

  public function updateStatus(Request $request, $id)
  {
    $guestDetail = GuestDetail::where('guest_details_id', $id)->with('user')->firstOrFail();

    if ($guestDetail->user->role_id != 1) {
      return response()->json([
        'success' => false,
        'message' => 'User is not a guest'
      ], 403);
    }

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
      $user = $guestDetail->user;
      $oldStatus = strtolower($user->STATUS);
      $user->STATUS = $request->status;
      $user->save();

      return response()->json([
        'success'    => true,
        'message'    => 'Guest user status updated successfully',
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
    DB::beginTransaction();
    try {
      $guestDetail = GuestDetail::where('guest_details_id', $id)->with('user')->firstOrFail();

      if ($guestDetail->user->role_id != 1) {
        return response()->json([
          'success' => false,
          'message' => 'User is not a guest'
        ], 403);
      }

      if ((string) auth()->user()->user_id === (string) $guestDetail->user_id) {
        return response()->json([
          'success' => false,
          'message' => 'You cannot delete your own account'
        ], 403);
      }

      $user = $guestDetail->user;
      $guestDetail->delete();
      $user->delete();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Guest user deleted successfully'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Guest user not found'
      ], 404);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete guest user: ' . $e->getMessage()
      ], 500);
    }
  }
}
