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
