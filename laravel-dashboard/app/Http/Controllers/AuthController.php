<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        // if (!str_contains($request->path(), 'cpo')) {
        //     return redirect()->route('cpo.login');
        // }
        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            // Check if user has valid role (1 = admin, 2 = partner)
            if (in_array($user->id_role, [1, 2])) {
                return redirect()->route('cpo.dashboard');
            }
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Email not found.',
                    'errors' => ['email' => ['Email not found.']]
                ], 422);
            }
            return back()->withErrors([
                'email' => 'Email not found.',
            ])->withInput($request->only('email'));
        }

        // Check if user has valid role (1 = admin, 2 = partner)
        if (!in_array($user->id_role, [1, 2])) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => "You don't have access to login.",
                    'errors' => ['email' => ["You don't have access to login."]]
                ], 403);
            }
            return back()->withErrors([
                'email' => "You don't have access to login.",
            ])->withInput($request->only('email'));
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Wrong Password.',
                    'errors' => ['password' => ['Wrong Password.']]
                ], 422);
            }
            return back()->withErrors([
                'password' => 'Wrong Password.',
            ])->withInput($request->only('email'));
        }

        // Login user with remember me functionality
        Auth::login($user, $request->boolean('remember'));

        // Redirect to dashboard
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Login successful.',
                'redirect' => route('cpo.dashboard')
            ], 200);
        }

        return redirect()->route('cpo.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // return redirect()->route('cpo.login');
        return redirect()->route('zora.login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 6 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'User not authenticated.',
                ], 401);
            }
            return redirect()->route('cpo.login');
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Current password is incorrect.',
                    'errors' => ['current_password' => ['Current password is incorrect.']]
                ], 422);
            }
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ])->withInput();
        }

        // Update password using User model
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Password changed successfully.',
            ], 200);
        }

        return redirect()->route('cpo.change-password')->with('success', 'Password changed successfully.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user exists and has valid role (1 = admin, 2 = partner)
        if (!$user || !in_array($user->id_role, [1, 2])) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'We can\'t find a user with that email address.',
                    'errors' => ['email' => ['We can\'t find a user with that email address.']]
                ], 422);
            }
            return back()->withErrors([
                'email' => 'We can\'t find a user with that email address.',
            ])->withInput($request->only('email'));
        }

        // Generate password reset token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        // Generate reset URL
        $resetUrl = route('cpo.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);

        // Send email
        try {
            Mail::send('emails.reset-password', [
                'resetUrl' => $resetUrl,
                'user' => $user
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Reset Your Password - Zora CPO');
            });

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'We have emailed your password reset link!'
                ], 200);
            }

            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Failed to send email. Please try again later.',
                    'errors' => ['email' => ['Failed to send email. Please try again later.']]
                ], 500);
            }
            return back()->withErrors([
                'email' => 'Failed to send email. Please try again later.',
            ])->withInput($request->only('email'));
        }
    }

    public function showResetPassword(Request $request, $token = null)
    {
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('cpo.forgot-password')
                ->with('error', 'Invalid reset link.');
        }

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('cpo.forgot-password')
                ->with('error', 'Invalid or expired reset link.');
        }

        // Check if token is valid (within 60 minutes)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('cpo.forgot-password')
                ->with('error', 'This password reset link has expired.');
        }

        // Verify token hash
        if (!Hash::check($token, $resetRecord->token)) {
            return redirect()->route('cpo.forgot-password')
                ->with('error', 'Invalid reset link.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Invalid or expired reset link.',
                    'errors' => ['token' => ['Invalid or expired reset link.']]
                ], 422);
            }
            return back()->withErrors([
                'email' => 'Invalid or expired reset link.',
            ])->withInput($request->only('email'));
        }

        // Check if token is valid (within 60 minutes)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'This password reset link has expired.',
                    'errors' => ['token' => ['This password reset link has expired.']]
                ], 422);
            }
            return back()->withErrors([
                'email' => 'This password reset link has expired.',
            ])->withInput($request->only('email'));
        }

        // Verify token hash
        if (!Hash::check($request->token, $resetRecord->token)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Invalid reset link.',
                    'errors' => ['token' => ['Invalid reset link.']]
                ], 422);
            }
            return back()->withErrors([
                'email' => 'Invalid reset link.',
            ])->withInput($request->only('email'));
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'User not found.',
                    'errors' => ['email' => ['User not found.']]
                ], 422);
            }
            return back()->withErrors([
                'email' => 'User not found.',
            ])->withInput($request->only('email'));
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Your password has been reset successfully.',
                'redirect' => route('cpo.login')
            ], 200);
        }

        return redirect()->route('cpo.login')->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }
}
