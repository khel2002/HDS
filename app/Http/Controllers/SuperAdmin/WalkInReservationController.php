<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalkInReservationController extends Controller
{
    public function index()
    {
      return view ('content.super-admin.reservation.walk-in');
    }
}
