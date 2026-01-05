<?php

namespace OwhDomainWhoisRdap\Helpers;

/**
 * Domain Validator
 * 
 * Helper class for domain validation and manipulation
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Helpers
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class DomainValidator
{
    /**
     * Validate domain format
     *
     * @param string $domain
     * @return bool
     */
    public static function isValidDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        
        // Remove protocol if present
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        
        // Remove www if present
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Remove path and query parameters
        $domain = strtok($domain, '/');
        $domain = strtok($domain, '?');
        
        // Basic domain regex
        $pattern = '/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*$/';
        
        if (!preg_match($pattern, $domain)) {
            return false;
        }
        
        // Check if it has at least one dot
        if (strpos($domain, '.') === false) {
            return false;
        }
        
        // Check length
        if (strlen($domain) > 253) {
            return false;
        }
        
        return true;
    }

    /**
     * Extract TLD from domain
     *
     * @param string $domain
     * @return string|null
     */
    public static function extractTld(string $domain): ?string
    {
        $domain = strtolower(trim($domain));
        
        // Clean domain
        $domain = self::cleanDomain($domain);
        
        $parts = explode('.', $domain);
        
        if (count($parts) < 2) {
            return null;
        }
        
        // Return the last part as TLD
        return end($parts);
    }

    /**
     * Clean domain name
     *
     * @param string $domain
     * @return string
     */
    public static function cleanDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        
        // Remove protocol
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        
        // Remove www
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Remove path and parameters
        $domain = strtok($domain, '/');
        $domain = strtok($domain, '?');
        $domain = strtok($domain, '#');
        
        return $domain;
    }

    /**
     * Get domain without TLD
     *
     * @param string $domain
     * @return string|null
     */
    public static function getDomainWithoutTld(string $domain): ?string
    {
        $domain = self::cleanDomain($domain);
        $parts = explode('.', $domain);
        
        if (count($parts) < 2) {
            return null;
        }
        
        // Remove the last part (TLD)
        array_pop($parts);
        
        return implode('.', $parts);
    }

    /**
     * Check if domain has valid characters
     *
     * @param string $domain
     * @return bool
     */
    public static function hasValidCharacters(string $domain): bool
    {
        // Allow letters, numbers, hyphens and dots
        return preg_match('/^[a-zA-Z0-9.-]+$/', $domain);
    }

    /**
     * Normalize domain name
     *
     * @param string $domain
     * @return string
     */
    public static function normalize(string $domain): string
    {
        $domain = self::cleanDomain($domain);
        
        // Convert to ASCII if needed (basic IDN support)
        if (function_exists('idn_to_ascii')) {
            $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        }
        
        return $domain;
    }
}
