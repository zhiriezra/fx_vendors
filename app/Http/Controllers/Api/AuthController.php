<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
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
            'email' => 'nullable|email',
            'phone' => 'required|digits:11|unique:users,phone,except,id',
        ]);

        User::create([
            'user_type_id' => 2,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone
        ]);

        return response()->json(['message' => 'Successfully created user!', 'status_code' => 201], 201);
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
        $user = User::with('employmentBackground', 'educationBackground')->get();
        return response()->json(['message' => 'authenticated user', 'user' => $user], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

}
