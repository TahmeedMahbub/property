<?php

// Global helper functions

if (! function_exists('t')) {
    /**
     * Retrieve a translated string from config/translations.php.
     *
     * Usage: t('brand.name') → returns the value for the current locale ('bn' or 'en').
     * Falls back to 'en', then returns the key itself if not found.
     */
    function t(string $key, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $segments = explode('.', $key);
        $value = config('translations');

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $key;
            }
            $value = $value[$segment];
        }

        if (is_array($value)) {
            return $value[$locale] ?? $value['en'] ?? $value['bn'] ?? $key;
        }

        return (string) $value;
    }
}

if (! function_exists('journal')) {
    /**
     * Record a journal entry and update company running balance.
     *
     * @param  int          $companyId    Company ID
     * @param  string       $type         'credit' (increase) or 'debit' (decrease)
     * @param  float|int    $amount       Positive amount
     * @param  string|null  $category     e.g. investment, sale, purchase, expense, refund, adjustment
     * @param  string|null  $remarks      Free-text note
     * @param  Model|null   $reference    Related model (polymorphic)
     * @param  int|null     $userId       Who performed this (defaults to auth user)
     * @return \App\Models\Journal
     */
    function journal(
        int $companyId,
        string $type,
        float|int $amount,
        ?string $category = null,
        ?string $remarks = null,
        ?object $reference = null,
        ?int $userId = null
    ): \App\Models\Journal {
        return \App\Services\JournalService::record(
            companyId: $companyId,
            type: $type,
            amount: $amount,
            category: $category,
            remarks: $remarks,
            reference: $reference,
            userId: $userId,
        );
    }
}

if (! function_exists('companyBalance')) {
    /**
     * Get the current balance for a company.
     */
    function companyBalance(int $companyId): float
    {
        return \App\Services\JournalService::balance($companyId);
    }
}
