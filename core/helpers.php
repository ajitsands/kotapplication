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

/**
 * Get configured shop shift start/end timings (HH:MM) and metadata.
 */
function getShopShiftConfig($settings = null) {
    if ($settings === null) {
        global $settings;
    }
    if (!is_array($settings) || !isset($settings['shift_start_time'])) {
        if (class_exists('Setting')) {
            $settingModel = new Setting();
            $settings = $settingModel->getSettings();
        }
    }
    $start = trim((string)($settings['shift_start_time'] ?? '15:00'));
    $end = trim((string)($settings['shift_end_time'] ?? '04:00'));
    if (!preg_match('/^\d{2}:\d{2}$/', $start)) $start = '15:00';
    if (!preg_match('/^\d{2}:\d{2}$/', $end)) $end = '04:00';

    list($sh, $sm) = array_map('intval', explode(':', $start));
    list($eh, $em) = array_map('intval', explode(':', $end));
    $startMin = $sh * 60 + $sm;
    $endMin = $eh * 60 + $em;
    $isOvernight = ($endMin <= $startMin);

    $startLabel = date('g:i A', strtotime("2000-01-01 $start:00"));
    $endLabel = date('g:i A', strtotime("2000-01-01 $end:00"));
    // Compact label like "3 PM - 4 AM" when minutes are :00, otherwise "3:30 PM - 4:30 AM"
    $startCompact = ($sm === 0) ? date('g A', strtotime("2000-01-01 $start:00")) : $startLabel;
    $endCompact = ($em === 0) ? date('g A', strtotime("2000-01-01 $end:00")) : $endLabel;

    return [
        'start_time' => $start,
        'end_time' => $end,
        'start_minutes' => $startMin,
        'end_minutes' => $endMin,
        'is_overnight' => $isOvernight,
        'start_label' => $startLabel,
        'end_label' => $endLabel,
        'label' => $startCompact . ' - ' . $endCompact
    ];
}

/**
 * Compute start/end dates & datetimes for 'today' (current shift) or 'yesterday' (previous shift).
 */
function getShopShiftWindow($preset = 'today', $settings = null, $nowTs = null) {
    $cfg = getShopShiftConfig($settings);
    $nowTs = $nowTs ?? time();
    $nowMin = (int)date('G', $nowTs) * 60 + (int)date('i', $nowTs);

    if ($cfg['is_overnight']) {
        $baseStartDate = ($nowMin < $cfg['start_minutes'])
            ? date('Y-m-d', strtotime('-1 day', $nowTs))
            : date('Y-m-d', $nowTs);
        if ($preset === 'yesterday') {
            $startDate = date('Y-m-d', strtotime('-1 day', strtotime($baseStartDate)));
        } else {
            $startDate = $baseStartDate;
        }
        $endDate = date('Y-m-d', strtotime('+1 day', strtotime($startDate)));
    } else {
        $baseStartDate = date('Y-m-d', $nowTs);
        if ($preset === 'yesterday') {
            $startDate = date('Y-m-d', strtotime('-1 day', strtotime($baseStartDate)));
        } else {
            $startDate = $baseStartDate;
        }
        $endDate = $startDate;
    }

    return [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'start_time' => $cfg['start_time'],
        'end_time' => $cfg['end_time'],
        'start_datetime' => $startDate . ' ' . $cfg['start_time'] . ':00',
        'end_datetime' => $endDate . ' ' . $cfg['end_time'] . ':59',
        'biz_date' => $startDate,
        'is_overnight' => $cfg['is_overnight'],
        'label' => $cfg['label']
    ];
}

/**
 * Compute current business date (rolls over at shift_end_time for overnight shifts).
 */
function getShopBusinessDate($settings = null, $nowTs = null) {
    $cfg = getShopShiftConfig($settings);
    $nowTs = $nowTs ?? time();
    if ($cfg['is_overnight']) {
        $nowMin = (int)date('G', $nowTs) * 60 + (int)date('i', $nowTs);
        if ($nowMin < $cfg['end_minutes']) {
            return date('Y-m-d', strtotime('-1 day', $nowTs));
        }
    }
    return date('Y-m-d', $nowTs);
}
