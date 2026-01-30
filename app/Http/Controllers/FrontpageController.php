<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontpageController extends Controller
{
  public function index()
  {
    return view('frontpages.landingpage');
  }
  public function sampleLanding()
  {
    return view('frontpages.landingpage-sample');
  }
}
