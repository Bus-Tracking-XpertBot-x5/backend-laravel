<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    public function allUsers()
    {
        return \response()->json(
            [
                'users' => User::all()
            ]
        );
    }

    public function me()
    {
        return \response()->json(
            [
                'user' => Auth::user()
            ],
            200
        );
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,passenger,driver,manager'
        ]);

        $otp = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verification_code' => $otp,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        Mail::to($user->email)->send(new EmailVerificationMail($otp));
        return response()->json([
            'message' => 'User created successfully.Verify your email with code sent',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('phone_number', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'User loggedin successfully',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('email_verification_code', $request->otp)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->email_verification_code = null; // Remove OTP after verification
        $user->save();

        return response()->json(['message' => 'Email verified successfully.']);
    }

    public function completeProfile(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'required|string',
            'location' => 'required|json',
        ]);

        $user = Auth::user();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $user->image = $imagePath;
        }

        $user->address = $request->address;
        $user->location = json_decode($request->location, true);
        $user->save();

        return response()->json([
            'message' => 'Profile completed successfully!',
            'user' => $user
        ], 200);
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $status = Password::sendResetLink([
            'email' => $request->only('email')
        ]);

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'message' => 'Password reset link sent to your email.'
            ], 200)
            : response()->json(['message' => 'Error sending password reset link.'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return back()->with('message', "Password reset done successfully!");
        }

        return back()->with('message', "Invalid email or token!");
    }

    public function showResetPasswordForm(Request $request)
    {
        return view(
            'auth.forgetPasswordLink',
            [
                'token' => $request->query('token'),
                'email' => $request->query('email')
            ]
        );
    }
}
