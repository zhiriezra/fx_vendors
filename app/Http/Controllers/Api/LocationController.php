<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Unit;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    public function countriesList(){

        $allCountries = Country::all();
        $countries = [];
        foreach($allCountries as $country){
            $countries[] = ['id' => $country->id, 'name' => $country->name, 'active' => $country->active];
        };

        return response()->json(['status' =>  true, 'message' => 'List of all countries', 'data' => ['countries' => $countries]], 200);
    }

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
            $lgas[] = [
                'id' => $lga->id, 
                'name' => $lga->name, 
                'state_id' => $lga->state_id,
                'active' => $lga->active];
        };

        return response()->json(['status' =>  true, 'message' => 'List of all LGAs', 'data' => ['lgas' => $lgas]], 200);
    }

    public function lga($id){
        $lga = Lga::find($id);
        return response()->json([
            'status' =>  true, 
            'message' => 'LGA detail', 
            'data' => [
                'id' => $lga->id, 
                'name' => $lga->name, 
                'state_id' => $lga->state_id,
                'state_name' =>$lga->state->name,
                'active' => $lga->active
                ]
            ], 200);
    }

    public function getBankList(){

        $banks = Bank::all();
        $banks = $banks->map(function ($bank){
            return [
                'id' => $bank->id,
                'name' => $bank->name,
                'code' => $bank->code,
                'country' => $bank->country,
            ];
        });

        return response()->json(['status' => true, 'message' => 'Bank list', 'data' => $banks]);

    }

    public function unitList(){

        $allUnits = Unit::all();
        $units = [];
        foreach($allUnits as $unit){
            $units[] = [
                'id' => $unit->id, 
                'name' => $unit->name,
                'status' => $unit->status
            ];
        };

        return response()->json(['status' =>  true, 'message' => 'List of all Units', 'data' => ['units' => $units]], 200);
    }
}
