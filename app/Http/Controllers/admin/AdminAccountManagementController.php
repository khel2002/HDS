<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminAccountManagementController extends Controller
{
    public function index()
    {
      $users= User::all();
      
      return view('content.admin.accounts.usermanagement', compact('users'));

    }
}
