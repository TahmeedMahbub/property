<?php

namespace App\Http\Controllers\Web;

use App\Domains\Shareholder\Services\CapTableService;
use App\Http\Controllers\Controller;
use App\Models\Shareholder;
use App\Models\ShareTransaction;
use Illuminate\Http\Request;

class ShareholderController extends Controller
{
    public function __construct(
        private readonly CapTableService $capTable = new CapTableService(),
    ) {}
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
            'investment_amount' => 'required|numeric|min:0',
            'share_type' => 'nullable|string|in:equity,preferred,common',
            'acquired_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // The holder record starts with zero shares; the cap table issues them.
        $shareholder = Shareholder::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'share_amount' => $validated['investment_amount'],
            'share_type' => $validated['share_type'] ?? null,
            'acquired_at' => $validated['acquired_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        // Issue shares for the investment. The service prices the shares, records the
        // immutable transaction, credits the ledger and recalculates ownership.
        if ((float) $validated['investment_amount'] > 0) {
            $this->capTable->issueShares(
                shareholder: $shareholder,
                investmentAmount: (float) $validated['investment_amount'],
                notes: 'Initial investment',
            );
        } else {
            $this->capTable->recalculateOwnerships($company->id);
        }

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

        // Only profile fields are editable. Shares & ownership are system-generated
        // and can only change through cap-table transactions (issue/transfer/buyback).
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'share_type' => 'nullable|string|in:equity,preferred,common',
            'acquired_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $shareholder->update($validated);

        // Status may have toggled active/inactive → refresh ownership percentages.
        $this->capTable->recalculateOwnerships($company->id);

        return redirect('/shareholders')->with('success', 'Shareholder updated successfully.');
    }

    /**
     * Manage Investment submodule — dedicated page to deposit or withdraw for a holder.
     */
    public function investment(string $uuid)
    {
        $company = app('currentCompany');
        $shareholder = Shareholder::where('company_id', $company->id)
            ->where('uuid', $uuid)->with('company.metrics')->firstOrFail();

        $transactions = $shareholder->shareTransactions()
            ->whereIn('type', ['issue', 'buyback'])
            ->with('user')
            ->latest()
            ->paginate(10, ['*'], 'txn');

        return view('contents.property.shareholders.investment', compact('shareholder', 'transactions'));
    }

    /**
     * Company-wide investments overview: share price, totals and the full
     * deposit/withdraw history across every shareholder.
     */
    public function investments()
    {
        $company = app('currentCompany')->loadMissing('metrics');

        $shareholdersCount = Shareholder::where('company_id', $company->id)->count();

        $transactions = ShareTransaction::forCompany($company->id)
            ->whereIn('type', ['issue', 'buyback'])
            ->with(['shareholder', 'user'])
            ->latest()
            ->paginate(15, ['*'], 'txn');

        return view('contents.property.shareholders.investments', compact('company', 'shareholdersCount', 'transactions'));
    }

    /**
     * Deposit or withdraw money for a shareholder from the Manage Investment page.
     * A single entry point: `action` decides whether shares are issued or bought back.
     */
    public function transact(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $shareholder = Shareholder::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'action' => 'required|in:deposit,withdraw',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = (float) $validated['amount'];

        if ($validated['action'] === 'deposit') {
            // Issues new shares at the current price and dilutes other holders.
            $this->capTable->issueShares(
                shareholder: $shareholder,
                investmentAmount: $amount,
                notes: $validated['notes'] ?? 'Additional deposit',
            );

            $shareholder->share_amount = (float) $shareholder->share_amount + $amount;
            $shareholder->save();

            $message = 'Deposit recorded. New shares issued and ownership updated.';
        } else {
            // Buys back the equivalent shares at the current price.
            try {
                $this->capTable->withdrawAmount(
                    shareholder: $shareholder,
                    amount: $amount,
                    notes: $validated['notes'] ?? 'Withdrawal',
                );
            } catch (\InvalidArgumentException $e) {
                return redirect("/shareholders/{$shareholder->uuid}/investment")
                    ->withErrors(['amount' => $e->getMessage()]);
            }

            $shareholder->share_amount = max(0, (float) $shareholder->share_amount - $amount);
            $shareholder->save();

            $message = 'Withdrawal recorded. Shares bought back and ownership updated.';
        }

        return redirect("/shareholders/{$shareholder->uuid}/investment")
            ->with('success', $message);
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $shareholder = Shareholder::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        // Buy back the holder's full position at the current price. This removes their
        // shares from the cap table and posts the matching money-out to the ledger.
        if ((float) $shareholder->shares_owned > 0) {
            $this->capTable->buybackShares(
                shareholder: $shareholder,
                shares: (float) $shareholder->shares_owned,
                notes: 'Reversal: shareholder ' . $shareholder->name . ' removed',
            );
        }

        $shareholder->delete();

        // Shares removed → recalculate remaining ownership percentages.
        $this->capTable->recalculateOwnerships($company->id);

        return redirect('/shareholders')->with('success', 'Shareholder deleted successfully.');
    }
}
