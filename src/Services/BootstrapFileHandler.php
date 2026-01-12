<?php

namespace OwhDomainWhoisRdap\Services;

/**
 * Bootstrap File Handler
 * 
 * Manages the IANA DNS.json file for RDAP server endpoints
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class BootstrapFileHandler
{
    /**
     * IANA DNS JSON URL
     */
    private const IANA_DNS_JSON_URL = 'https://data.iana.org/rdap/dns.json';

    /**
     * Cache key for DNS data
     */
    private const DNS_CACHE_KEY = 'lknaci_owh_domain_rdap_dns_json';

    /**
     * Cache duration (24 hours)
     */
    private const CACHE_DURATION = 86400;

    /**
     * Get RDAP server for TLD
     *
     * @param string $tld
     * @return string|null
     */
    public function getRdapServerForTld(string $tld): ?string
    {
        $dns_data = $this->getDnsData();
        
        if (!$dns_data || !isset($dns_data['services'])) {
            return null;
        }

        $tld = strtolower($tld);
        if (strpos($tld, '.') === 0) {
            $tld = substr($tld, 1); // Remove leading dot
        }

        foreach ($dns_data['services'] as $service) {
            if (isset($service[0]) && isset($service[1])) {
                $tlds = $service[0];
                $servers = $service[1];

                if (in_array($tld, $tlds) && !empty($servers[0])) {
                    return rtrim($servers[0], '/');
                }
            }
        }

        return null;
    }

    /**
     * Get all supported TLDs
     *
     * @return array
     */
    public function getSupportedTlds(): array
    {
        $dns_data = $this->getDnsData();
        
        if (!$dns_data || !isset($dns_data['services'])) {
            return [];
        }

        $tlds = [];
        foreach ($dns_data['services'] as $service) {
            if (isset($service[0])) {
                $tlds = array_merge($tlds, $service[0]);
            }
        }

        return array_unique($tlds);
    }

    /**
     * Check if TLD is valid/supported
     *
     * @param string $tld
     * @return bool
     */
    public function isValidTld(string $tld): bool
    {
        $dns_data = $this->getDnsData();
        
        if (!$dns_data || !isset($dns_data['services'])) {
            return false;
        }

        $tld = strtolower($tld);
        if (strpos($tld, '.') === 0) {
            $tld = substr($tld, 1); // Remove leading dot
        }

        foreach ($dns_data['services'] as $service) {
            if (isset($service[0])) {
                if (in_array($tld, $service[0])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update DNS data from IANA
     *
     * @return bool
     */
    public function updateDnsData(): bool
    {
        $response = \wp_remote_get(self::IANA_DNS_JSON_URL, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'OWH Domain WHOIS RDAP Plugin/1.0.0'
            ]
        ]);

        if (\is_wp_error($response)) {
            return false;
        }

        $body = \wp_remote_retrieve_body($response);
        $dns_data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Cache the data
        \set_transient(self::DNS_CACHE_KEY, $dns_data, self::CACHE_DURATION);

        // Also save to file as backup
        $this->saveDnsDataToFile($dns_data);

        return true;
    }

    /**
     * Get DNS data (from cache or IANA)
     *
     * @return array|null
     */
    private function getDnsData(): ?array
    {
        // Try to get from cache first
        $dns_data = \get_transient(self::DNS_CACHE_KEY);
        
        if ($dns_data !== false) {
            return $dns_data;
        }

        // Try to update from IANA first
        if ($this->updateDnsData()) {
            return \get_transient(self::DNS_CACHE_KEY);
        }

        // Fallback to test data if IANA fails
        $test_data = [
            'services' => [
                [
                    ['com'],
                    ['https://rdap.verisign.com/com/v1/']
                ],
                [
                    ['net'],
                    ['https://rdap.verisign.com/net/v1/']
                ],
                [
                    ['org'],
                    ['https://rdap.publicinterestregistry.org/rdap/']
                ],
                [
                    ['info'],
                    ['https://rdap.afilias.info/rdap/v1/']
                ],
                [
                    ['biz'],
                    ['https://rdap.afilias.info/rdap/v1/']
                ]
            ]
        ];
        
        return $test_data;
        
        // Código original comentado para teste
        /*
        // Try to get from cache first
        $dns_data = \get_transient(self::DNS_CACHE_KEY);
        
        if ($dns_data !== false) {
            return $dns_data;
        }

        // Try to load from file backup
        $dns_data = $this->loadDnsDataFromFile();
        
        if ($dns_data) {
            // Cache it
            \set_transient(self::DNS_CACHE_KEY, $dns_data, self::CACHE_DURATION);
            return $dns_data;
        }

        // Try to update from IANA
        if ($this->updateDnsData()) {
            return \get_transient(self::DNS_CACHE_KEY);
        }

        return null;
        */
    }

    /**
     * Save DNS data to file
     *
     * @param array $dns_data
     * @return void
     */
    private function saveDnsDataToFile(array $dns_data): void
    {
        $upload_dir = \wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/lknaci-owh-domain-whois-rdap';
        $file_path = $plugin_upload_dir . '/dns.json';

        if (!\file_exists($plugin_upload_dir)) {
            \wp_mkdir_p($plugin_upload_dir);
        }

        \file_put_contents($file_path, json_encode($dns_data, JSON_PRETTY_PRINT));
    }

    /**
     * Load DNS data from file
     *
     * @return array|null
     */
    private function loadDnsDataFromFile(): ?array
    {
        $upload_dir = \wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/lknaci-owh-domain-whois-rdap/dns.json';

        if (!\file_exists($file_path)) {
            return null;
        }

        $content = \file_get_contents($file_path);
        $dns_data = json_decode($content, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $dns_data : null;
    }
}
