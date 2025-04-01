<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PDO;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    use ApiResponder;
    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        try {
            $user = User::create([
                'firstname' => 'temp_name',
                'lastname' => 'temp_name',
                'phone' => rand(7000000000, 9999999999), // Generate random Nigerian phone number
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_completed' => 0,
                'active' => 0,
                'user_type_id' => 2
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token
            ], 'Account has been successfully registered');

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Registration failed', 500);
        }

    }

    public function updateBio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'middlename' => 'nullable|string',
            'lastname' => 'required|string',
            'gender' => 'required|in:male,female',
            'business_name' => 'nullable|string',
            'marital_status' =>'nullable|string',
            'dob' => 'nullable|string',
            'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15',
            'nin' => 'nullable|string',
            'bvn' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        if(auth()->user()->user_type_id != 2){
            return $this->error(null, 'You are not authorized to update this profile', 401);
        }

        try {
            $user = User::find(auth()->id());

            DB::beginTransaction();
            $user->update([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'middlename' => $request->middlename,
                'phone' => $request->phone,
            ]);

            $vendor = Vendor ::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'gender' => $request->gender,
                'business_name' => $request->business_name,
                'marital_status' => $request->marital_status,
                'dob' => $request->dob,
                'nin' => $request->nin,
                'bvn' => $request->bvn,
            ]);

            DB::commit();

            return $this->success(['user' => $user, 'vendor' => $vendor], 'Bio updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 'Bio update failed', 500);
        }
    }

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'lga_id' => 'required|exists:lgas,id',
            'permanent_address' => 'required|string',
            'current_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        if(auth()->user()->user_type_id != 2){
            return $this->error(null, 'You are not authorized to update this profile', 401);
        }

        try {

            DB::beginTransaction();

            $user = auth()->user();

            $vendor = $user->vendor->update([
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'lga_id' => $request->lga_id,
                'permanent_address' => $request->permanent_address,
                'current_location' => $request->current_address,
            ]);

            DB::commit();

            return $this->success(['vendor' => $vendor], 'Location updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 'Location update failed', 500);
        }
    }

    public function updateBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_email' => 'required|email',
            'business_mobile' => 'required|string',
            'business_name' => 'required|string',
            'business_address' => 'required|string',
            'registration_no' => 'required|string',
            'tin' => 'required|string',
            'business_type' => 'required',
            'bank' => 'required',
            'account_no' => 'required|string',
            'account_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        try {
            DB::beginTransaction();


            $user = auth()->user();

            $user->vendor->update([
                'business_email' => $request->business_email,
                'business_mobile' => $request->business_mobile,
                'business_name' => $request->business_name,
                'business_address' => $request->business_address,
                'registration_no' => $request->registration_no,
                'tin' => $request->tin,
                'business_type' => $request->business_type,
                'bank' => $request->bank,
                'account_no' => $request->account_no,
                'account_name' => $request->account_name,
            ]);

            $user->profile_completed = 1;
            $user->save();

            DB::commit();

            $responseData = [
                'profile_completed' => $user->profile_completed,
                'profile_image' => $user->profile_image,
                'signature' => $user->signature,
                'active' => $user->active,
            ];

            return $this->success(['user' => $responseData], 'Business updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 'Business update failed', 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        if($validator->fails())
        {
            return $this->validation($validator->errors(), 'Password is Incorrect', 422);
        }

        // Look for user
        $user = User::where(['email' => $request->email, 'user_type_id' => 2])->first();

        if($user)
        {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user->tokens()->delete(); // Delete old tokens
                return $this->success(['token' => $user->createToken('auth-token')->plainTextToken], 'Success', 200);
            }else{
                return $this->error(null, 'Invalid Email address or Password', 401);
            }
        }else{
            return $this->error(null, 'Error logging in, user not found or not a Vendor', 404);
        }
    }


    public function getUser(Request $request)
    {
        $user = User::with('vendor')->where('id', $request->user()->id)->first();
        return response()->json(['status' => true, 'message' => 'authenticated user', 'data' => ['user' => $user]], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Successfully logged out', 200]);
    }

    public function delete(Request $request)
    {
        $request->user()->delete();
        return $this->success(null, 'Account deleted successfully', 200);
    }

    public function uploadProfileImage(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max size
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->file('profile_image')) {

            $image = $request->file('profile_image');
            $imageName = time() . '_' . preg_replace('/\s+/', '_',$image->getClientOriginalName());
            $imagePath = $image->storeAs('vendor_images', $imageName, 'public');

            // Store image path or URL in the database if needed
            $user = User::find($request->user()->id);
            $user->profile_image = env('APP_URL').Storage::url($imagePath);
            $user->save();

            return response()->json(['status'=> true, 'message' => 'Profile image uploaded successfully', 'data' => ['profile_image' => env('APP_URL').Storage::url($imagePath)]], 200);
        }

    }

    public function uploadSignature(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'signature' => 'required|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max size
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->file('signature')) {

            $image = $request->file('signature');
            $imageName = time() . '_' . preg_replace('/\s+/', '_',$image->getClientOriginalName());
            $imagePath = $image->storeAs('vendor_signatures', $imageName, 'public');

            // Store image path or URL in the database if needed
            $user = User::find($request->user()->id);
            $user->signature = env('APP_URL').Storage::url($imagePath);
            $user->profile_completed = 1;
            $user->save();

            return response()->json(['status'=> true, 'message' => 'Signature uploaded successfully', 'data' => ['profile_image' => env('APP_URL').Storage::url($imagePath)] ], 200);
        }

    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error(null, 'The current password is incorrect', 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->success(null, 'Password updated successfully', 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Generate OTP
            $otp = $this->otpService->generateOTP($user->id);

            // Send OTP via email
            // Note: Implement your email sending logic here

            return $this->success([
                'message' => 'OTP has been sent to your email',
                'user_id' => $user->id
            ], 'Password reset OTP sent successfully');

        } catch (\Exception $e) {
            return $this->error(null, 'Error sending password reset OTP: ' . $e->getMessage(), 500);
        }

    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|string',
            'new_password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        try {
            // Verify OTP
            if (!$this->otpService->verifyOTP($request->user_id, $request->otp)) {
                return $this->error(null, 'Invalid or expired OTP', 422);
            }

            // Update password
            $user = User::find($request->user_id);
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Invalidate OTP after successful password reset
            $this->otpService->invalidateOTP($request->user_id);

            return $this->success(null, 'Password reset successfully');

        } catch (\Exception $e) {
            return $this->error(null, 'Error resetting password: ' . $e->getMessage(), 500);
        }
    }

    public function forgotPasswordEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if($validator->fails()){
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $user = User::where('email', $request->email)->first();

        if($user){
            $otp = $this->otpService->generateOTP($user->id);

            // Send OTP via email
            // Note: Implement your email sending logic here
        }else{
            return $this->error(null, 'User not found', 404);
        }
    }
}
