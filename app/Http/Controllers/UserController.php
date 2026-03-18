<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            /** @var \App\Models\User */
            $user = Auth::user();

            if (!$user->emailVerified) {
                Auth::logout();
                return response()->json(['message' => 'Please verify your email before logging in.'], 403);
            }

            return response()->json([
                'message' => 'Login successful.',
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    public function me(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        /** @var \App\Models\User */
        $user = Auth::user();

        return response()->json([
            'user'  => $user,
            'roles' => $user->getRoleNames(),
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|max:255',
        ]);

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uid = '';
        for ($i = 0; $i < 27; $i++) {
            $uid .= $characters[rand(0, strlen($characters) - 1)];
        }

        $user = new User();
        $user->full_name     = $request->full_name;
        $user->email         = $request->email;
        $user->password      = $request->password;
        $user->uid           = $uid;
        $user->kalaka_id     = substr($request->email, 2, 4) . '_' . substr($uid, -4);
        $user->emailVerified = false;
        $user->save();

        $user->syncRoles(['User']);

        $token       = Str::random(64);
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $verifyUrl   = "{$frontendUrl}/VerifyEmail?token={$token}&email=" . urlencode($user->email);

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::raw(
            "Welcome to Kalaka!\n\nClick the link below to verify your email address:\n\n{$verifyUrl}\n\nThis link expires in 24 hours.\n\nIf you did not create an account, you can ignore this email.",
            function ($mail) use ($user) {
                $mail->to($user->email)->subject('Verify Your Email – Kalaka');
            }
        );

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
        ], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $record = DB::table('email_verification_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 422);
        }

        if (now()->diffInHours($record->created_at) > 24) {
            DB::table('email_verification_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Verification link has expired. Please register again.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->emailVerified = true;
        $user->save();

        DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Email verified successfully!',
            'user'    => $user,
            'roles'   => $user->getRoleNames(),
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Don't reveal whether the email is registered
        if (!$user) {
            return response()->json(['message' => 'If that email is registered, a reset link has been sent.'], 200);
        }

        $token       = Str::random(64);
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $resetUrl    = "{$frontendUrl}/ResetPassword?token={$token}&email=" . urlencode($request->email);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::raw(
            "You requested a password reset for your Kalaka account.\n\nClick the link below to set a new password:\n\n{$resetUrl}\n\nThis link expires in 60 minutes.\n\nIf you did not request this, you can ignore this email.",
            function ($mail) use ($request) {
                $mail->to($request->email)->subject('Password Reset Request – Kalaka');
            }
        );

        return response()->json(['message' => 'If that email is registered, a reset link has been sent.'], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 422);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Reset link has expired. Please request a new one.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = $request->password;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully. You can now log in.'], 200);
    }

    public function getUsers(Request $request)
    {
        $sortKey       = $request->input('sort_key', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort direction. Use "asc" or "desc".'], 400);
        }

        $users = User::select('*')->get();

        $users->transform(function ($user) {
            $user->role = $user->getRoleNames()->first();
            unset($user->roles);
            unset($user->role_names);
            $user->email_verified_at = $user->email_verified_at ? $user->email_verified_at->format('Y-m-d') : null;
            return $user;
        });

        $users = $users->sortBy([[$sortKey, $sortDirection]]);

        return response()->json($users, 200);
    }

    public function getUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->role = $user->getRoleNames()->first();
        unset($user->roles);
        $user->email_verified_at = $user->email_verified_at ? $user->email_verified_at->format('Y-m-d') : null;

        return response()->json([
            'message' => 'User retrieved successfully.',
            'user'    => $user
        ], 200);
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'password'  => 'required|string|max:255',
        ]);

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uid = '';
        for ($i = 0; $i < 27; $i++) {
            $uid .= $characters[rand(0, strlen($characters) - 1)];
        }

        $user = new User();
        $user->full_name     = $request->full_name;
        $user->email         = $request->email;
        $user->password      = $request->password;
        $user->uid           = $uid;
        $user->kalaka_id     = substr($request->email, 2, 4) . '_' . substr($uid, -4);
        $user->emailVerified = true; // Admin-created users are pre-verified
        $user->save();

        $user->syncRoles(['User']);

        return response()->json([
            'message' => 'User created successfully!',
            'user'    => $user,
        ], 201);
    }

    public function editUser(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->full_name = $request->full_name;
        $user->email     = $request->email;
        $user->save();

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => $user,
            'role'    => $request->role,
        ], 200);
    }

    public function removeUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User removed successfully.'], 200);
    }

    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:Admin,User',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'Role updated successfully.',
            'role'    => $request->role,
        ], 200);
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = $request->new_password;
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }
}
