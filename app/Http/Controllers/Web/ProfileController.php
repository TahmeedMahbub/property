<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('contents.profile', [
            'user' => Auth::user(),
            'company' => app('currentCompany'),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:p_users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return back()->with('success', t('msg.profile_updated'));
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => t('auth.current_password_wrong')]);
        }

        $user->password = $request->password;
        $user->save();

        return back()->with('success', t('msg.password_changed'));
    }

    public function updateCompany(Request $request)
    {
        $company = app('currentCompany');

        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
            'company_website' => 'nullable|url|max:255',
        ]);

        $company->name = $request->company_name;
        $company->email = $request->company_email;
        $company->phone = $request->company_phone;
        $company->address = $request->company_address;
        $company->website = $request->company_website;
        $company->save();

        return back()->with(['success' => t('msg.company_updated'), 'active_tab' => 'company']);
    }
}
