<?php
/**
 * Calculates the royalty fee based on function type and member status
 * Flexible for Condolence meetings and Economically Backward members
 */

if (!function_exists('calculateRoyalty')) {
    function calculateRoyalty($base_amount, $function_type, $is_needy = false) {
        // No royalty for Condolence meetings
        if (strtolower($function_type) == 'condolence') {
            return 0.00;
        }

        // No royalty for economically backward community members
        if ($is_needy) {
            return 0.00;
        }

        // Standard 10% NGO Royalty
        $royalty_rate = 0.10; 
        return $base_amount * $royalty_rate;
    }
}

/**
 * Format Currency specifically for your VMS
 */
if (!function_exists('formatRupee')) {
    function formatRupee($amount) {
        return "₹" . number_format($amount, 2);
    }
}
?>