<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorsController extends Controller
{
    public function updateBusiness(Request $request){

        $validator = Validator::make($request->all(), [
            'identification_mode' => 'required',
            'identification_no' => 'required|digits:10',
            'dob' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'current_location' => 'required',
            'permanent_address' => 'required',
            'state_id' => 'required',
            'lga_id' => 'required',
            'community' => 'required',
            'business_name' => 'required',
            'business_address' => 'required',
            'registration_no' => 'required',
            'business_type' => 'required',
            'business_email' => 'required',
            'business_mobile' => 'required',
            'bank' => 'required',
            'account_no' => 'required|numeric|digits:10',
            'account_name' => 'required',
            'tin' => 'required|digits:11'

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $vendor = Vendor::updateOrCreate(
            ['user_id' => auth()->id()],
            [

                'user_id' => auth()->id(),
                'identification_mode' => $request->identification_mode,
                'identification_no' => $request->identification_no,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'current_location' => $request->current_location,
                'permanent_address' => $request->permanent_address,
                'state_id' => $request->state_id,
                'lga_id' => $request->lga_id,
                'community' => $request->community,
                'business_name' => $request->business_name,
                'business_address' => $request->business_address,
                'registration_no' => $request->registration_no,
                'business_type' => $request->business_type,
                'business_email' => $request->business_email,
                'business_mobile' => $request->business_mobile,
                'bank' => $request->bank,
                'account_no' => $request->account_no,
                'account_name' => $request->account_name,
                'tin' => $request->tin

            ]
        );

        if($vendor){
            $user = User::find(auth()->id());
            $user->profile_completed = 1;
            $user->save();
            return response()->json(['status' => true, 'message' => 'Business information updated', 'data' => ['vendor' => $vendor]], 200);
        }else{
            return response()->json(['status' => false, 'message' => 'Could not update business information, please try again'], 500);
        }
    }
}
