<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

/**
 * Class PayPalUserAgent
 * PayPalUserAgent generates User Agent for curl requests
 *
 * @package PayPal\Core
 */
class User_Agent
{
    /**
     * Returns the value of the User-Agent header
     * Add environment values and php version numbers
     */
    public static function get_value(): string
    {
        $feature_list = ['platform-ver=' . PHP_VERSION, 'bit=' . self::_get_php_bit(), 'os=' . str_replace(' ', '_', php_uname('s') . ' ' . php_uname('r')), 'machine=' . php_uname('m')];
        if (defined('OPENSSL_VERSION_TEXT')) {
            $openssl_version = explode(' ', OPENSSL_VERSION_TEXT);
            $feature_list[] = 'crypto-lib-ver=' . $openssl_version[1];
        }
        if (function_exists('curl_version')) {
            $curl_version = curl_version();
            $feature_list[] = 'curl=' . $curl_version['version'];
        }
        return sprintf('PayPalSDK/%s %s (%s)', 'Checkout-PHP-SDK', Version::VERSION, implode('; ', $feature_list));
    }
    /**
     * Gets PHP Bit version
     */
    private static function _get_php_bit(): string
    {
        return match (PHP_INT_SIZE) {
            4 => '32',
            8 => '64',
            default => PHP_INT_SIZE,
        };
    }
}