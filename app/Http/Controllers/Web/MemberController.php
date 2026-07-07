<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CompanyMembership;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = CompanyMembership::where('company_id', $company->id)
            ->with(['user', 'role']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $members = $query->latest()->paginate(15)->withQueryString();

        return view('contents.property.members.index', compact('members'));
    }

    public function create()
    {
        $company = app('currentCompany');
        $roles = Role::whereNull('company_id')->orWhere('company_id', $company->id)->get();

        return view('contents.property.members.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role_id' => 'nullable|exists:roles,id',
            'title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'is_owner' => 'boolean',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        $existing = CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->withErrors(['email' => 'This user is already an active member.'])->withInput();
        }

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $validated['role_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'department' => $validated['department'] ?? null,
            'is_owner' => $validated['is_owner'] ?? false,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        return redirect('/members')->with('success', 'Member added successfully.');
    }

    public function edit(string $id)
    {
        $company = app('currentCompany');
        $member = CompanyMembership::where('company_id', $company->id)
            ->with(['user', 'role'])
            ->findOrFail($id);

        $roles = Role::whereNull('company_id')->orWhere('company_id', $company->id)->get();

        return view('contents.property.members.edit', compact('member', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $company = app('currentCompany');
        $member = CompanyMembership::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'is_owner' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $member->update($validated);

        if ($validated['status'] === 'inactive' && !$member->left_at) {
            $member->update(['left_at' => now()]);
        }

        return redirect('/members')->with('success', 'Member updated successfully.');
    }

    public function destroy(string $id)
    {
        $company = app('currentCompany');
        $member = CompanyMembership::where('company_id', $company->id)->findOrFail($id);

        $member->update(['status' => 'inactive', 'left_at' => now()]);

        return redirect('/members')->with('success', 'Member removed successfully.');
    }
}
