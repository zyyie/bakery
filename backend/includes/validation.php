<?php
/**
 * Validation helper functions
 */

/**
 * Check if mobile number is valid
 * Valid formats: digits only, may include spaces, dashes, parentheses, + prefix
 * Should be at least 7 digits and at most 15 digits after cleaning
 */
function isValidMobileNumber($mobileNumber) {
    if(empty($mobileNumber)) {
        return false;
    }
    
    // Remove whitespace
    $cleaned = preg_replace('/\s+/', '', $mobileNumber);
    
    // Check if it looks like an email (has @ symbol)
    if(strpos($cleaned, '@') !== false) {
        return false;
    }
    
    // Remove common phone number characters (+, -, (, ), spaces)
    $digitsOnly = preg_replace('/[\s\-\+\(\)]/', '', $cleaned);
    
    // Should contain only digits
    if(!preg_match('/^\d+$/', $digitsOnly)) {
        return false;
    }
    
    // Should be between 7 and 15 digits (international standard)
    $length = strlen($digitsOnly);
    if($length < 7 || $length > 15) {
        return false;
    }
    
    return true;
}

/**
 * Check if user has a valid mobile number
 */
function userHasValidMobileNumber($userID) {
    $query = "SELECT mobileNumber FROM users WHERE userID = ?";
    $result = executePreparedQuery($query, "i", [$userID]);
    
    if($result && $user = $result->fetch_assoc()) {
        return isValidMobileNumber($user['mobileNumber']);
    }
    
    return false;
}

