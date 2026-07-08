<?php

namespace App\Domains\Shareholder\Services;

use App\Models\Company;
use App\Models\Shareholder;
use App\Models\User;

class ShareholderService
{
    public function __construct(
        private readonly CapTableService $capTable = new CapTableService(),
    ) {}

    public function listForCompany(Company $company): mixed
    {
        return $company->shareholders()
            ->with('user:id,uuid,name,email')
            ->latest()
            ->paginate(20);
    }

    public function create(Company $company, array $data): Shareholder
    {
        if (! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        $investmentAmount = (float) ($data['investment_amount'] ?? 0);
        unset($data['investment_amount'], $data['shares_owned'], $data['ownership_percentage']);

        // Mirror the investment onto the legacy amount column for display.
        $data['share_amount'] = $investmentAmount;

        $shareholder = $company->shareholders()->create($data);

        // Issue shares for the investment (prices shares, records the immutable
        // transaction, credits the ledger and recalculates ownership).
        if ($investmentAmount > 0) {
            $this->capTable->issueShares(
                shareholder: $shareholder,
                investmentAmount: $investmentAmount,
                notes: 'Initial investment',
            );
        } else {
            $this->capTable->recalculateOwnerships($company->id);
        }

        return $shareholder->fresh('user');
    }

    public function update(Shareholder $shareholder, array $data): Shareholder
    {
        if (array_key_exists('user_id', $data) && ! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        // Shares & ownership are system-generated — never editable here.
        unset($data['investment_amount'], $data['shares_owned'], $data['ownership_percentage'], $data['share_amount']);

        $shareholder->update($data);

        // Status may have toggled active/inactive → refresh ownership percentages.
        $this->capTable->recalculateOwnerships($shareholder->company_id);

        return $shareholder->fresh('user');
    }

    public function delete(Shareholder $shareholder): void
    {
        $companyId = $shareholder->company_id;

        // Buy back the holder's full position at the current price before removing.
        if ((float) $shareholder->shares_owned > 0) {
            $this->capTable->buybackShares(
                shareholder: $shareholder,
                shares: (float) $shareholder->shares_owned,
                notes: 'Reversal: shareholder ' . $shareholder->name . ' removed',
            );
        }

        $shareholder->delete();

        // Shares removed → recalculate remaining ownership.
        $this->capTable->recalculateOwnerships($companyId);
    }
}
