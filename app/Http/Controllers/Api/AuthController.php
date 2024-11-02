<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PDO;

class AuthController extends Controller
{

    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function signup(Request $request){

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,except,id',
            'phone' => 'required|digits:11|unique:users,phone,except,id',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' =>  $validator->errors()->first()], 422);
        }

        $user = User::create([
            'user_type_id' => 2,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),

        ]);

        if($user){
            return response()->json(['status' => true, 'message' => 'Successfully created user!', 'data' => ['user' => $user, 'token' => $user->createToken('auth-token')->plainTextToken]], 200);
        }else{
            return response()->json(['status' => false, 'message' => 'Something went wrong, please try again'], 500);

        }

    }

    public function loginEmail(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Look for user
        $user = User::where(['email' => $request->email, 'user_type_id' => 2])->first();

        if($user){
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])){

                $userDetails = [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_completed' => $user->profile_completed,
                    'signature' => $user->signature,
                    'profile_image' => $user->profile_image,
                    'active' => $user->active,
                ];

                $user->tokens()->delete(); // Delete old tokens

                return response()->json(['status' => true, 'message' => 'Successfully Logged in!', 'data' => ['user' => $userDetails, 'token' => $user->createToken('auth-token')->plainTextToken]], 200);
            }else{
                return response()->json(['status'=> false, 'message' => 'Invalid Email address or Password'], 401);
            }
        }else{
            return response()->json(['status'=> false, 'message' => 'User does not exist'], 404);
        }
    }

    public function login(Request $request){
        $request->validate([
            'phone' => 'required|digits:11|exists:users,phone',
        ]);

        $user = User::select('firstname','lastname', 'phone', 'email', 'profile_completed', 'signature')->where('phone', $request->phone)->first();

        $otp = $this->otpService->generateOTP($user);
        return response()->json(['status'=> true, 'message' => 'OTP send successfully', 'status_code' => 200, 'data' => $otp], 200);
    }

    public function verifyOTP(Request $request){
        $request->validate([
            'phone' => 'required|digits:11|exists:users,phone',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$this->otpService->verifyOTP($user, $request->otp)) {
            throw ValidationException::withMessages([
                'otp' => ['The provided OTP is incorrect or has expired.'],
            ]);
        }

        $user->tokens()->delete(); // Delete old tokens
        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken
        ]);
    }

    public function getUser(Request $request){
        $user = User::with('vendor')->where('id', $request->user()->id)->first();
        return response()->json(['status' => true, 'message' => 'authenticated user', 'data' => ['user' => $user]], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Successfully logged out', 200]);
    }

    public function uploadProfileImage(Request $request){
        // Validate the request
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max size
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->file('profile_image')) {

            $image = $request->file('profile_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('vendor_images', $imageName, 'public');

            // Store image path or URL in the database if needed
            $user = User::find($request->user()->id);
            $user->profile_image = env('APP_URL').'/'.$imagePath;
            $user->save();

            return response()->json(['status'=> true, 'message' => 'Profile image uploaded successfully', 'data' => ['profile_image' => env('APP_URL').Storage::url($imagePath)] ], 200);
        }

    }

    public function uploadSignature(Request $request){
        // Validate the request
        $validator = Validator::make($request->all(), [
            'signature' => 'required|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max size
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->file('signature')) {

            $image = $request->file('signature');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('vendor_signatures', $imageName, 'public');

            // Store image path or URL in the database if needed
            $user = User::find($request->user()->id);
            $user->signature = env('APP_URL').'/'.$imagePath;
            $user->profile_completed = 1;
            $user->save();

            return response()->json(['status'=> true, 'message' => 'Signature uploaded successfully', 'data' => ['profile_image' => env('APP_URL').Storage::url($imagePath)] ], 200);
        }

    }

    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        if($status == Password::RESET_LINK_SENT){
            return response()->json(['status' => true, 'message' => __($status)], 200);
        }

        return response()->json(['status' => false, 'message' => trans($status)], 200);

    }

    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'The current password is incorrect.'], 422);

            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['status' => true, 'message' => 'Password updated successfully.'], 200);

    }
}
