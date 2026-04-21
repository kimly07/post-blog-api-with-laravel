<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
// use Carbon\Carbon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

use function Symfony\Component\Clock\now;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->is_verify) {
                return response()->json([
                    'message' => 'User already exists and verified',
                    'user' => $user->email
                ], 400);
            }
        }

        $otp = rand(1000, 9999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_verify' => false
        ]);

        Cache::put('otp_' . $request->email, $otp, Carbon::now()->addMinutes(10));
        Mail::to($request->email)->send(new SendOtpMail($otp));

        return response()->json([
            'message' => 'OTP sent to email',
            'otp' => $otp
        ], 200);

        Cache::put('otp_' . $request->email, $otp, Carbon::now()->addMinutes(10));
        Mail::to($request->email)->send(new SendOtpMail($otp));

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $storedOtp = Cache::get('otp_' . $request->email);

        if (!$storedOtp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired or not found'
            ], 400);
        }

        if ($storedOtp != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 401);
        }

        $user->is_verify = true;
        $user->save();

        Cache::forget('otp_' . $request->email);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (is_null($user->is_verify) || !$user->is_verify) {
            return response()->json(['message' => 'Email is not yet verify'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'role' => $user->role
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
