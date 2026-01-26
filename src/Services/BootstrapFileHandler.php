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
     * Update DNS data from IANA - Replace local file completely
     *
     * @return bool
     */
    public function updateDnsData(): bool
    {
        // Download new data from IANA
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

        if (json_last_error() !== JSON_ERROR_NONE || empty($dns_data)) {
            return false;
        }

        // Replace the bundled file directly
        $plugin_dir = dirname(dirname(__DIR__)) . '/';
        $bundled_file = $plugin_dir . 'data/dns.json';

        // Write new data to bundled file
        $result = \file_put_contents($bundled_file, json_encode($dns_data, JSON_PRETTY_PRINT));

        // Update last update date in WordPress options if successful
        if ($result !== false) {
            \update_option('owh_domain_whois_rdap_last_update', date('d/m/Y H:i:s'));
        }

        return $result !== false;
    }

    /**
     * Get last update information
     *
     * @return array
     */
    public function getLastUpdateInfo(): array
    {
        // Always check bundled file
        $plugin_dir = dirname(dirname(__DIR__)) . '/';
        $bundled_file = $plugin_dir . 'data/dns.json';
        
        $info = [
            'has_file' => \file_exists($bundled_file),
            'last_modified' => null,
            'file_size' => 0,
            'from_bundled' => true,
            'date' => 'Não disponível',
            'source' => 'Arquivo bundled do plugin'
        ];

        if ($info['has_file']) {
            $info['last_modified'] = \filemtime($bundled_file);
            $info['file_size'] = \filesize($bundled_file);
            $info['date'] = gmdate('d/m/Y H:i:s', $info['last_modified']);
            
            // Se arquivo foi modificado recentemente, provavelmente foi atualizado
            if (time() - $info['last_modified'] < 300) { // 5 minutos
                $info['source'] = 'Atualizado da IANA';
            }
        }

        return $info;
    }

    /**
     * Get DNS data (from local files only - no cache)
     *
     * @return array|null
     */
    private function getDnsData(): ?array
    {
        // Always try to load from plugin's bundled file
        $bundled_data = $this->loadBundledDnsData();
        if ($bundled_data) {
            return $bundled_data;
        }

        // If bundled file is empty/corrupt, fallback to test data
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
        $plugin_upload_dir = $upload_dir['basedir'] . '/owh-domain-whois-rdap';
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
        $file_path = $upload_dir['basedir'] . '/owh-domain-whois-rdap/dns.json';

        if (!\file_exists($file_path)) {
            return null;
        }

        $content = \file_get_contents($file_path);
        $dns_data = json_decode($content, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $dns_data : null;
    }

    /**
     * Load bundled DNS data from plugin directory
     *
     * @return array|null
     */
    private function loadBundledDnsData(): ?array
    {
        // Get plugin directory path
        $plugin_dir = dirname(dirname(__DIR__)) . '/';
        $file_path = $plugin_dir . 'data/dns.json';

        if (!\file_exists($file_path)) {
            return null;
        }

        $content = \file_get_contents($file_path);
        $dns_data = json_decode($content, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $dns_data : null;
    }

    /**
     * Copy bundled file to uploads directory (for backup)
     *
     * @return bool
     */
    private function copyBundledToUploads(): bool
    {
        $plugin_dir = dirname(dirname(__DIR__)) . '/';
        $bundled_file = $plugin_dir . 'data/dns.json';

        if (!\file_exists($bundled_file)) {
            return false;
        }

        $upload_dir = \wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/owh-domain-whois-rdap';
        $target_file = $plugin_upload_dir . '/dns.json';

        if (!\file_exists($plugin_upload_dir)) {
            \wp_mkdir_p($plugin_upload_dir);
        }

        return \copy($bundled_file, $target_file);
    }
}
