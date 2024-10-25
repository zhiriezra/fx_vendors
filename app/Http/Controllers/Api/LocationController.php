<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function statesList(){
        $states = State::all();
        return response()->json(['status' =>  true, 'message' => 'List of all states', 'data' => ['states' => $states]], 200);
    }

    public function state($id){
        $state = State::find($id);
        return response()->json(['status' =>  true, 'message' => 'State', 'data' => ['state' => $state]], 200);
    }

    public function lgasList(){
        $lgas = Lga::all();
        return response()->json(['status' =>  true, 'message' => 'List of all LGAs', 'data' => ['lgas' => $lgas]], 200);
    }

    public function lga($id){
        $lga = Lga::find($id);
        return response()->json(['status' =>  true, 'message' => 'List of all LGAs', 'data' => ['lga' => $lga]], 200);
    }
}
