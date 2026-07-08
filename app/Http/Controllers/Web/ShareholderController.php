<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shareholder;
use App\Services\JournalService;
use Illuminate\Http\Request;

class ShareholderController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = Shareholder::where('company_id', $company->id)->with('user');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $shareholders = $query->latest()->paginate(15)->withQueryString();

        return view('contents.property.shareholders.index', compact('shareholders'));
    }

    public function create()
    {
        return view('contents.property.shareholders.create');
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'share_percentage' => 'nullable|numeric|min:0|max:100',
            'share_amount' => 'nullable|numeric|min:0',
            'share_type' => 'nullable|string|in:equity,preferred,common',
            'acquired_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['company_id'] = $company->id;

        $shareholder = Shareholder::create($validated);

        // Record share capital as money-in (credit)
        JournalService::syncReference(
            companyId: $company->id,
            reference: $shareholder,
            targetCredit: (float) ($shareholder->share_amount ?? 0),
            category: 'shareholder_investment',
            remarks: 'Share capital from ' . $shareholder->name,
        );

        return redirect('/shareholders')->with('success', 'Shareholder added successfully.');
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $shareholder = Shareholder::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        return view('contents.property.shareholders.edit', compact('shareholder'));
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $shareholder = Shareholder::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'share_percentage' => 'nullable|numeric|min:0|max:100',
            'share_amount' => 'nullable|numeric|min:0',
            'share_type' => 'nullable|string|in:equity,preferred,common',
            'acquired_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $shareholder->update($validated);

        // Keep the ledger in sync with the (possibly changed) share amount
        JournalService::syncReference(
            companyId: $company->id,
            reference: $shareholder,
            targetCredit: (float) ($shareholder->share_amount ?? 0),
            category: 'shareholder_investment',
            remarks: 'Share capital adjustment for ' . $shareholder->name,
        );

        return redirect('/shareholders')->with('success', 'Shareholder updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $shareholder = Shareholder::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        // Reverse the share capital contribution before deleting
        JournalService::reverseReference(
            companyId: $company->id,
            reference: $shareholder,
            category: 'shareholder_investment',
            remarks: 'Reversal: shareholder ' . $shareholder->name . ' removed',
        );

        $shareholder->delete();

        return redirect('/shareholders')->with('success', 'Shareholder deleted successfully.');
    }
}
