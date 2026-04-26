<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
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

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        $pageTitle = 'Account Recovery';

        AdminPasswordReset::truncate();

        return view('backend.auth.email', compact('pageTitle'));
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

    public function sendResetCodeEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = Admin::where('email', $request->email)->first();
        if (! $user) {
            return back()->with('error', 'Invalid email address!!!');
        }

        $otp = verificationCode(6);

        AdminPasswordReset::create(
            [
                'email' => $user->email,
                'token' => $otp,
                'status' => 0,
            ]
        );

        sendMail('PASSWORD_RESET', [
            'otp' => $otp,
        ], $user);

        $pageTitle = 'Account Recovery';

        return view('backend.auth.code_verify', compact('pageTitle'));
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|array|size:6',
            'code.*' => 'required|numeric',
        ]);

        $code = implode('', $request->code);

        return redirect()->route('admin.password.reset.form', $code)->with('success', 'You can change your password');
    }
}
