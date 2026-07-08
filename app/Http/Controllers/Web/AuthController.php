<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$field => $login, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'login' => t('auth.invalid_credentials'),
        ])->onlyInput('login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:p_users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
            ]);

            $company = Company::create([
                'name' => $request->company_name,
                'status' => 'active',
            ]);

            CompanyMembership::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'is_owner' => true,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $this->sendVerificationCode($user);

        return redirect()->route('verification.notice');
    }

    public function showVerifyEmail()
    {
        if (Auth::user()->email_verified_at) {
            return redirect('/dashboard');
        }

        return view('auth.verify-email');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $user = Auth::user();

        $record = DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'No verification code found. Please request a new one.']);
        }

        if (now()->greaterThan($record->expires_at)) {
            DB::table('email_verification_codes')->where('user_id', $user->id)->delete();
            return back()->withErrors(['code' => 'Code has expired. Please request a new one.']);
        }

        if ($record->attempts >= 5) {
            DB::table('email_verification_codes')->where('user_id', $user->id)->delete();
            return back()->withErrors(['code' => 'Too many attempts. Please request a new code.']);
        }

        if ($record->code !== $request->code) {
            DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->increment('attempts');

            $remaining = 5 - ($record->attempts + 1);
            return back()->withErrors(['code' => "Invalid code. {$remaining} attempts remaining."]);
        }

        // Code is valid
        $user->email_verified_at = now();
        $user->save();

        DB::table('email_verification_codes')->where('user_id', $user->id)->delete();

        return redirect('/dashboard')->with('status', 'Email verified successfully!');
    }

    public function resendCode()
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            return redirect('/dashboard');
        }

        $this->sendVerificationCode($user);

        return back()->with('status', 'verification-code-sent');
    }

    protected function sendVerificationCode(User $user): void
    {
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('email_verification_codes')->where('user_id', $user->id)->delete();

        DB::table('email_verification_codes')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        Mail::raw("Your verification code is: {$code}\n\nThis code expires in 15 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject(config('app.name') . ' - Email Verification Code');
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
