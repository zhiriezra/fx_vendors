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
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Cache;
use App\Services\PushNotificationService;
use App\Mail\SendOtpMail;

class AuthController extends Controller
{

    use ApiResponder;
    protected $otpService;
    protected $pushNotificationService;

    public function __construct(OTPService $otpService, PushNotificationService $pushNotificationService)
    {
        $this->otpService = $otpService;
        $this->pushNotificationService = $pushNotificationService;
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
                'firstname' => 'New',
                'lastname' => 'User',
                'phone' => rand(7000000000, 9999999999), // Generate random Nigerian phone number
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_completed' => 0,
                'active' => 0,
                'user_type_id' => 2
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            $title = 'Welcome to FarmEx';
            $body = 'Complete your profile and start supporting your farmers!';
            $data = [
                'type' => 'single',
                'user_id' => $user->id
            ];

            $this->pushNotificationService->sendToUser($user, $title, $body, $data);

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
            'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15|unique:users,phone,' . request()->user()->id,
            'nin' => 'nullable|digits:11',
            'bvn' => 'nullable|digits:11',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 'Validation failed', 422);
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

            $vendor = Vendor::updateOrCreate([
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
            $error = app()->isProduction() ? 'Bio update failed' : $e->getMessage();
            return $this->error($error, 'An unexpected error occurred', 500);
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

            return $this->success([
                'user' => $responseData], 'Business updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 'Business update failed', 500);
        }
    }

    public function updateBusinessPartial(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'business_email' => 'nullable|email',
            'business_mobile' => 'nullable|string',
            'business_name' => 'nullable|string',
            'business_address' => 'nullable|string',
            'registration_no' => 'nullable|string',
            'tin' => 'nullable|string',
            'business_type' => 'nullable',
            'bank' => 'nullable',
            'account_no' => 'nullable|string',
            'account_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $vendor = $user->vendor;

            // Update only the fields provided in the request
            $vendor->update($request->only([
                'business_email',
                'business_mobile',
                'business_name',
                'business_address',
                'registration_no',
                'tin',
                'business_type',
                'bank',
                'account_no',
                'account_name',
            ]));

            DB::commit();

            // Fetch the updated vendor details
            $updatedVendor = $vendor->fresh(); // Refresh the model to get the latest data

            // Return success response with updated user details
            return $this->success([
                'vendor' => $updatedVendor,
                'message' => 'Business details updated successfully'
            ], 'Success');

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
        ],
        [
            'email.exists' => 'Email does not exist on this platform',
        ]);

        if($validator->fails()){
            return $this->validation($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = User::where('email', $request->email)->first();
        // Check if the user is a Vendor
        if ($user->user_type_id !== 2) {
            return $this->error(null, 'You are not Registered as a Vendor', 403); // Forbidden
        }
        
        if($user){
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $otp = $user->generateTwoFactorCode();
                try {
                    Mail::to($request->email)->send(new SendOtpMail($otp));
                    return $this->success(['user_id' => $user->id, 'otp' => $otp], '2FA code sent', 200);

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Login failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return $this->error($e->getMessage(), 'Server error: Unable to send 2FA code. Please try again later.', 500);
                }
            }else{
                return $this->error(null, 'Invalid email or password', 401);
            }
        }else{
            return $this->error(null, 'User not found', 404);
        }
        
    }

    public function verify2FA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|numeric|digits:5'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        try {
            $user = User::where('id', $request->user_id)->first();

            // Check if OTP matches and is not expired
            if ($user->otp !== $request->otp || $user->otp_expires_at < now()) {
                return $this->error(null, 'Invalid or expired OTP', 400);
            }

            // Clear the OTP
            $user->update([
                'otp' => null,
                'otp_expires_at' => null
            ]);

            // Generate a unique token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success(['token' => $token], 'OTP verified successfully', 200);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error verifying OTP', 500);
        }
    }
    
    public function getUser(Request $request)
    {
        $user = User::with('vendor')->where('id', $request->user()->id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Transform profile_image and signature to include the full URL
        $fullProfileImage = $user->profile_image ? url($user->profile_image) : null;
        $fullSignature = $user->signature ? url($user->signature) : null;

        $responseData = [
            'id' => $user->id,
            'user_type_id' => $user->user_type_id,
            'user_type' => $user->usertype->name,
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'phone' => $user->phone,
            'email' => $user->email,
            'fcm_token' => $user->fcm_token,
            'company_id' => $user->company_id,
            'active' => $user->active,
            'profile_completed' => $user->profile_completed,
            'profile_image' => $fullProfileImage, // Full URL for profile image
            'signature' => $fullSignature,       // Full URL for signature
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'vendor' => $user->vendor ? [
                'id' => $user->vendor->id,
                'dob' => $user->vendor->dob,
                'gender' => $user->vendor->gender,
                'marital_status' => $user->vendor->marital_status,
                'current_location' => $user->vendor->current_location,
                'permanent_address' => $user->vendor->permanent_address,
                'country_id' => $user->vendor->state->country_id,
                'country' => $user->vendor->state->country->name,
                'state_id' => $user->vendor->state_id,
                'state' => $user->vendor->state->name,
                'lga_id' => $user->vendor->lga_id,
                'lga' => $user->vendor->lga->name,
                'community' => $user->vendor->community,
                'business_name' => $user->vendor->business_name,
                'nin' => $user->vendor->nin,
                'bvn' => $user->vendor->bvn,
                'tin' => $user->vendor->tin,
                'business_email' => $user->vendor->business_email,
                'business_mobile' => $user->vendor->business_mobile,
                'business_address' => $user->vendor->business_address,
                'registration_no' => $user->vendor->registration_no,
                'business_type' => $user->vendor->business_type,
                'bank' => $user->vendor->bank,
                'account_no' => $user->vendor->account_no,
                'account_name' => $user->vendor->account_name,
                'status' => $user->vendor->status,
            ] : null,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Authenticated user',
            'data' => [
                'user' => $responseData,
            ],
        ], 200);
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

            $fullImagePath = Storage::url($imagePath);

            // Store image path or URL in the database if needed
            $user = User::find($request->user()->id);
            $user->profile_image =$fullImagePath;
            $user->save();

            return response()->json([
                'status'=> true,
                'message' => 'Profile image uploaded successfully',
                'data' => [
                'profile_image' => url($fullImagePath)
                ]
            ], 200);
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

            $fullImagePath = Storage::url($imagePath);

            // Store image path or URL in the database if needed
            $user = User::find($request->user()->id);
            $user->signature = $fullImagePath;
            $user->profile_completed = 1;
            $user->save();

            return response()->json([
                'status'=> true,
                'message' => 'Signature uploaded successfully',
                'data' => [
                'profile_image' => url($fullImagePath)
                ]
            ], 200);
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

    public function sendForgotPasswordOTP(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'channel' => 'required|in:email,sms'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $user = User::where('email', $request->email)->first();

             // Check if the user is an Vendor
            if ($user->user_type_id !== 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not Registered as a Vendor',
                    'errors' => null
                ], 403); // Forbidden
            }

            $otp = rand(10000, 99999); // Generate a 5-digit OTP

            // Store OTP in the database with expiration time
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(5) // OTP expires after 5 minutes
            ]);

            // Email channel
            if ($request->channel === 'email') {

                Mail::to($user->email)->send(new ForgotPasswordMail($otp)
                );
            } elseif ($request->channel === 'sms') {
                // Send OTP via SMS
                $this->sendSms($user->phone, "Your OTP is: $otp");
            }

            return response()->json([
                'status' => true,
                'message' => "OTP has been sent to your {$request->channel}",
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error sending OTP: ' . $e->getMessage(),
                'errors' => null
            ], 500);
        }
    }

    public function verifyForgotPasswordOTP(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric|digits:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Check if OTP matches and is not expired
            if ($user->otp !== $request->otp || $user->otp_expires_at < now()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                    'errors' => null
                ], 400);
            }

            // Clear the OTP
            $user->update([
                'otp' => null,
                'otp_expires_at' => null
            ]);

            // Generate a unique token
            $token = bin2hex(random_bytes(32)); // Generate a secure random token

            // Cache the token for 5 minutes
            Cache::put("password_reset_token:{$user->email}", $token, now()->addMinutes(5));

            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully',
                'data' => [
                    'token' => $token
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error verifying OTP: ' . $e->getMessage(),
                'errors' => null
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $user = User::where('email', $request->email)->first();

            // Get the cached token
            $cachedToken = Cache::get("password_reset_token:{$user->email}");

            // Validate the token
            if (!$cachedToken || $cachedToken !== $request->token) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired token',
                    'errors' => null
                ], 403);
            }

            // Update the user's password
            $user->update([
                'password' => bcrypt($request->password)
            ]);

            // Clear the cached token
            Cache::forget("password_reset_token:{$user->email}");

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error resetting password: ' . $e->getMessage(),
                'errors' => null
            ], 500);
        }
    }
}
