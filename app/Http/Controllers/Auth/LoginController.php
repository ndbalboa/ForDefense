<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Google2FA;
use App\Models\User;
use App\Models\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = $this->throttleKey($request);

        // Check if the user is rate-limited
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many login attempts. Please try again in {$seconds} seconds."
            ], 429);
        }

        // Find the user by username
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment the rate limiter for failed attempts
            RateLimiter::hit($key, 30); // Lock out for 30 seconds after too many attempts
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Reset rate limiter on successful login
        RateLimiter::clear($key);

        // Log the activity
        $this->logActivity($user, 'login', 'User logged in.');

        // Check if 2FA is enabled for the user
        if ($user->google2fa_secret) {
            // Return response indicating 2FA is required
            return response()->json([
                'two_factor_required' => true,
                'two_factor_token' => $user->google2fa_secret, // Send the user's 2FA secret
            ]);
        }

        // If no 2FA is required, issue an access token
        $accessToken = $user->createToken('YourApp')->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'role' => $user->role,
        ]);
    }

    public function verify(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'two_factor_token' => 'required|string',
            'two_factor_code' => 'required|string',
        ]);

        // Find the user by the 2FA token
        $user = User::where('google2fa_secret', $request->two_factor_token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid 2FA token'], 401);
        }

        // Verify the 2FA code using Google2FA
        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($user->google2fa_secret, $request->two_factor_code)) {
            return response()->json(['message' => 'Invalid authentication code'], 401);
        }

        // Log the activity
        $this->logActivity($user, '2FA Verification', 'User verified 2FA code.');

        // If the 2FA code is correct, issue an access token
        $accessToken = $user->createToken('YourApp')->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $request)
    {
        // Log out the user if they are authenticated
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            // Log the activity
            $this->logActivity($user, 'logout', 'User logged out.');

            Auth::guard('web')->logout();
            $request->session()->invalidate();

            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Successfully logged out.'], 200);
    }

    /**
     * Helper function to log activities.
     *
     * @param User $user
     * @param string $action
     * @param string $details
     */
    private function logActivity($user, $action, $details)
    {
        Log::create([
            'user_id' => $user->id,
            'user_full_name' => $user->lastName . ', ' . $user->firstName,  // Storing the full name
            'action' => $action,
            'details' => $details,
        ]);
    }

    /**
     * Generate the rate limiter key for the user.
     *
     * @param Request $request
     * @return string
     */
    private function throttleKey(Request $request)
    {
        return Str::lower($request->username) . '|' . $request->ip();
    }
}
