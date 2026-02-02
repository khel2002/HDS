<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use Exception;
use Crypt;

class FrontpageController extends Controller
{
  public function index()
  {

      try {
        //Desktop
        $roomsFirst = Room::orderBy('room_id')->take(3)->get();
        $roomsSecond = Room::orderBy('room_id')->skip(3)->take(3)->get();

        //Mobile
        $roomsMobFirst =Room::orderBy('room_id')->take(1)->get();
        $roomsMobSecond = Room::orderBy('room_id')->skip(1)->take(5)->get();


        return view('frontpages.landingpage', compact('roomsFirst','roomsSecond','roomsMobFirst','roomsMobSecond'));

    } catch (\Exception $e) {
        $rooms = 'link or blankimagepath';
        dd($e);
    }

  }
  public function sampleLanding()
  {
    return view('frontpages.landingpage-sample');
  }
}
