<?php

/**
 * Format a price string/number according to the configured currency
 * Bahrain (BHD), Oman (OMR), Kuwait (KWD) use 3 decimal places.
 * Other currencies default to 2 decimal places.
 *
 * @param float|string|int $amount The amount to format
 * @param string|null $currency_code The currency code (falls back to global $settings if not provided)
 * @param bool $return_float If true, returns a float string without thousands separator (e.g. for DB/API)
 * @return string Formatted price
 */
function format_price($amount, $currency_code = null, $return_float = false) {
    global $settings;
    
    $curr = $currency_code ?? $settings['currency_code'] ?? 'BHD';
    $three_decimals = ['BHD', 'KWD', 'OMR', 'IQD', 'JOD', 'TND', 'LYD'];
    
    $decimals = in_array(strtoupper(trim($curr)), $three_decimals) ? 3 : 2;
    $amount = (float)$amount;
    
    if ($return_float) {
        return number_format($amount, $decimals, '.', '');
    }
    
    return number_format($amount, $decimals);
}
