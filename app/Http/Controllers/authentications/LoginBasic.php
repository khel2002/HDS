<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class LoginBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }
  public function login(Request $request)
  {
    $response = Http::withToken(config('services.hrmis_api.token'))
            ->post(config('services.hrmis_api.url'), [
                'email' => $email,
            ]);
  }
}
