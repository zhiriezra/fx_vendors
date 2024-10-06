<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'nullable|email|unique:users,email,except,id',
            'phone' => 'required|digits:11|unique:users,phone,except,id',
            'password' => 'required|min:4',
        ]);

        $user = User::create([
            'user_type_id' => 2,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),

        ]);

        return response()->json(['message' => 'Successfully created user!', 'status' => true, 'user' => $user, 'token' => $user->createToken('auth-token')->plainTextToken], 201);
    }

    public function loginEmail(Request $request){

        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        // Look for user
        $user = User::where(['email' => $request->email, 'user_type_id' => 2])->first();

        if($user){
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user->tokens()->delete(); // Delete old tokens
                return response()->json(['user' => $user,'token' => $user->createToken('auth-token')->plainTextToken]);
            }else{
                return response()->json(['status'=> false, 'message' => 'Invalid Email address or Password', 'status_code' => 401], 401);
            }
        }else{
            return response()->json(['status'=> false, 'message' => 'Error logging in, user not found or not registered as a vendor', 'status_code' => 404], 404);
        }
    }

    public function login(Request $request){
        $request->validate([
            'phone' => 'required|digits:11|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();

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
        return response()->json(['message' => 'authenticated user', 'user' => $user], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Successfully logged out']);
    }

}
