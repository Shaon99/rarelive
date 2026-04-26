<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest');
    }

    public function showResetForm(Request $request, $token)
    {
        $pageTitle = 'Password Reset';

        $resetToken = AdminPasswordReset::where('token', $token)->where('status', 0)->first();

        if (! $resetToken) {
            return redirect()->route('admin.password.reset')->with('error', 'Invalid code, please send again!');
        }

        $email = $resetToken->email;

        return view('backend.auth.reset', compact('pageTitle', 'email', 'token'));
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:5',
        ]);

        $reset = AdminPasswordReset::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        $user = Admin::where('email', $reset->email)->first();
        if ($reset->status == 1) {
            return redirect()->route('admin.login')->with('error', 'Invalid code, please send again!');
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $reset->status = 1;
        $reset->save();

        return redirect()->route('admin.login');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('admins');
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
