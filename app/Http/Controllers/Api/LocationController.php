<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function statesList(){

        $allStates = State::all();
        $states = [];
        foreach($allStates as $state){
            $states[] = ['id' => $state->id, 'name' => $state->name, 'active' => $state->active];
        };

        return response()->json(['status' =>  true, 'message' => 'List of all states', 'data' => ['states' => $states]], 200);
    }

    public function state($id){
        $state = State::find($id);
        $lgas = Lga::where('state_id', $state->id)->get()->map(function ($lga){
            return [
                'id' => $lga->id,
                'name' => $lga->name,
                'active' => $lga->active
            ];
        });

        $data = [
            'state' => [
                'id' => $state->id,
                'name' => $state->name,
                'active' => $state->active
            ],

            'lgas' => $lgas
        ];
        return response()->json(['status' =>  true, 'message' => 'State detail', 'data' => $data ], 200);
    }

    public function lgasList(){

        $allLgas = Lga::all();
        $lgas = [];
        foreach($allLgas as $lga){
            $lgas[] = ['id' => $lga->id, 'name' => $lga->name, 'active' => $lga->active];
        };

        return response()->json(['status' =>  true, 'message' => 'List of all LGAs', 'data' => ['lgas' => $lgas]], 200);
    }

    public function lga($id){
        $lga = Lga::find($id);
        return response()->json(['status' =>  true, 'message' => 'LGA detail', 'data' => ['id' => $lga->id, 'name' => $lga->name, 'active' => $lga->active]], 200);
    }
}
