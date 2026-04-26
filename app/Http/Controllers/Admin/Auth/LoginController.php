<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminPasswordReset;
use App\Services\RateLimiterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.guest')->except('logout');
    }

    public function loginPage()
    {
        $pageTitle = 'Login Page';

        AdminPasswordReset::truncate();

        return view('backend.auth.login', compact('pageTitle'));
    }

    public function login(Request $request, RateLimiterService $rateLimiter)
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $remember = $request->has('remember');
        $rateLimitKey = 'login_attempts:'.$request->ip();

        $agent = new Agent();
        $device = $agent->device();
        $browser = $agent->browser();
        $platform = $agent->platform();

        // Check Rate Limit
        $rateLimitCheck = $rateLimiter->checkRateLimit($rateLimitKey);
        if ($rateLimitCheck) {
            activity()
                ->event('failed_login')
                ->withProperties([
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                    'device' => $device,
                    'browser' => $browser,
                    'platform' => $platform,
                ])
                ->log('Rate limit exceeded for login attempts');

            return redirect()->route('admin.login')->with('error', $rateLimitCheck['message']);
        }

        // Attempt login
        if (Auth::guard('admin')->attempt([$loginField => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $rateLimiter->clearRateLimit($rateLimitKey);

            // Log successful login with device details
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->event('login')
                ->withProperties([
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                    'device' => $device,
                    'browser' => $browser,
                    'platform' => $platform,
                ])
                ->log('Admin logged in successfully');

            // Handle Remember Me
            if ($remember) {
                $cookieEmail = cookie('remember_email', $credentials['email'], 10080);
                $cookieRemember = cookie('remember_me', 'checked', 10080);

                return redirect()->route('admin.home')
                    ->with('success', 'Welcome to OptimoSell Dashboard')
                    ->withCookies([$cookieEmail, $cookieRemember]);
            } else {
                $cookieEmail = cookie('remember_email', '', -1);
                $cookieRemember = cookie('remember_me', '', -1);

                return redirect()->route('admin.home')
                    ->with('success', 'Welcome to OptimoSell Dashboard')
                    ->withCookies([$cookieEmail, $cookieRemember]);
            }
        }

        // Increment Rate Limit on failed login
        $rateLimiter->hitRateLimit($rateLimitKey);

        // Log failed login attempt
        activity()
            ->event('failed_login')
            ->withProperties([
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'device' => $device,
                'browser' => $browser,
                'platform' => $platform,
            ])
            ->log('Failed admin login attempt');

        return redirect()->route('admin.login')->with('error', 'Authentication Failed!');
    }

    public function logout()
    {
        auth()->guard('admin')->logout();

        return redirect()->route('admin.login')->with('success', 'successfully Logged out ');
    }
}
