<?php

namespace OwhDomainWhoisRdap\Services;

/**
 * Cache Manager
 * 
 * Manages transient cache for RDAP responses
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class CacheManager
{
    /**
     * Cache prefix
     */
    private const CACHE_PREFIX = 'owh_domain_';

    /**
     * Get cached domain result
     *
     * @param string $domain
     * @return array|false
     */
    public function get(string $domain)
    {
        $cache_key = $this->generateCacheKey($domain);
        return \get_transient($cache_key);
    }

    /**
     * Set domain result in cache
     *
     * @param string $domain
     * @param array $result
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function set(string $domain, array $result, int $ttl): bool
    {
        $cache_key = $this->generateCacheKey($domain);
        return \set_transient($cache_key, $result, $ttl);
    }

    /**
     * Delete cached domain result
     *
     * @param string $domain
     * @return bool
     */
    public function delete(string $domain): bool
    {
        $cache_key = $this->generateCacheKey($domain);
        return \delete_transient($cache_key);
    }

    /**
     * Clear all domain cache
     *
     * @return void
     */
    public function clearAll(): void
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . self::CACHE_PREFIX . '%'
        ));
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_' . self::CACHE_PREFIX . '%'
        ));
    }

    /**
     * Generate cache key for domain
     *
     * @param string $domain
     * @return string
     */
    private function generateCacheKey(string $domain): string
    {
        return self::CACHE_PREFIX . md5(strtolower($domain));
    }
}
