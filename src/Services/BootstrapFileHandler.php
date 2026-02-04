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
        $tld = strtolower($tld);
        if (strpos($tld, '.') === 0) {
            $tld = substr($tld, 1); // Remove leading dot
        }

        // Check custom TLDs first
        $customTlds = $this->getCustomTlds();
        foreach ($customTlds as $customTld) {
            $customTldClean = ltrim(strtolower($customTld['tld']), '.');
            if ($customTldClean === $tld && !empty($customTld['rdap_url'])) {
                return rtrim($customTld['rdap_url'], '/');
            }
        }

        // Check standard DNS data
        $dns_data = $this->getDnsData();
        
        if (!$dns_data || !isset($dns_data['services'])) {
            return null;
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
        $tlds = [];

        // Get TLDs from DNS data
        $dns_data = $this->getDnsData();
        
        if ($dns_data && isset($dns_data['services'])) {
            foreach ($dns_data['services'] as $service) {
                if (isset($service[0])) {
                    $tlds = array_merge($tlds, $service[0]);
                }
            }
        }

        // Add custom TLDs
        $customTlds = $this->getCustomTlds();
        foreach ($customTlds as $customTld) {
            if (!empty($customTld['tld']) && !empty($customTld['rdap_url'])) {
                $tldClean = ltrim(strtolower($customTld['tld']), '.');
                if (!in_array($tldClean, $tlds)) {
                    $tlds[] = $tldClean;
                }
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
        $tld = strtolower($tld);
        if (strpos($tld, '.') === 0) {
            $tld = substr($tld, 1); // Remove leading dot
        }

        // Check custom TLDs first
        $customTlds = $this->getCustomTlds();
        foreach ($customTlds as $customTld) {
            $customTldClean = ltrim(strtolower($customTld['tld']), '.');
            if ($customTldClean === $tld && !empty($customTld['rdap_url'])) {
                return true;
            }
        }

        // Check standard DNS data
        $dns_data = $this->getDnsData();
        
        if (!$dns_data || !isset($dns_data['services'])) {
            return false;
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

        // Validate JSON structure
        if (!$this->validateDnsDataStructure($dns_data)) {
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
     * Update DNS data from IANA with detailed error information
     *
     * @return array Array with 'success' boolean and 'message' string
     */
    public function updateDnsDataWithDetails(): array
    {
        // Download new data from IANA
        $response = \wp_remote_get(self::IANA_DNS_JSON_URL, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'OWH Domain WHOIS RDAP Plugin/1.0.0'
            ]
        ]);

        if (\is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Erro ao conectar com a IANA: ' . $response->get_error_message()
            ];
        }

        $http_code = \wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            return [
                'success' => false,
                'message' => 'Erro HTTP ao acessar IANA. Código: ' . $http_code
            ];
        }

        $body = \wp_remote_retrieve_body($response);
        if (empty($body)) {
            return [
                'success' => false,
                'message' => 'Resposta vazia da IANA'
            ];
        }

        $dns_data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'JSON inválido da IANA: ' . json_last_error_msg()
            ];
        }

        if (empty($dns_data)) {
            return [
                'success' => false,
                'message' => 'Dados vazios recebidos da IANA'
            ];
        }

        // Validate JSON structure
        if (!$this->validateDnsDataStructure($dns_data)) {
            return [
                'success' => false,
                'message' => 'Estrutura JSON inválida: o arquivo da IANA não possui a estrutura esperada (services array com TLDs e URLs RDAP)'
            ];
        }

        // Replace the bundled file directly
        $plugin_dir = dirname(dirname(__DIR__)) . '/';
        $bundled_file = $plugin_dir . 'data/dns.json';

        // Write new data to bundled file
        $result = \file_put_contents($bundled_file, json_encode($dns_data, JSON_PRETTY_PRINT));

        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Erro ao salvar arquivo dns.json. Verifique permissões de escrita'
            ];
        }

        // Update last update date in WordPress options if successful
        \update_option('owh_domain_whois_rdap_last_update', date('d/m/Y H:i:s'));

        return [
            'success' => true,
            'message' => 'Servidores RDAP atualizados com sucesso! Arquivo contém ' . count($dns_data['services']) . ' serviços'
        ];
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

    /**
     * Validate DNS data structure
     *
     * @param array $dns_data
     * @return bool
     */
    private function validateDnsDataStructure(array $dns_data): bool
    {
        // Check if 'services' key exists
        if (!isset($dns_data['services']) || !is_array($dns_data['services'])) {
            return false;
        }

        // Check if services array is not empty
        if (empty($dns_data['services'])) {
            return false;
        }

        // Validate each service entry
        foreach ($dns_data['services'] as $service) {
            // Each service should be an array with at least 2 elements
            if (!is_array($service) || count($service) < 2) {
                return false;
            }

            // First element should be array of TLDs
            if (!is_array($service[0]) || empty($service[0])) {
                return false;
            }

            // Second element should be array of RDAP servers
            if (!is_array($service[1]) || empty($service[1])) {
                return false;
            }

            // Check if TLDs are strings
            foreach ($service[0] as $tld) {
                if (!is_string($tld) || empty(trim($tld))) {
                    return false;
                }
            }

            // Check if RDAP servers are valid URLs
            foreach ($service[1] as $server) {
                if (!is_string($server) || !filter_var($server, FILTER_VALIDATE_URL)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get custom TLDs configuration
     *
     * @return array
     */
    private function getCustomTlds(): array
    {
        $custom_tlds = \get_option('owh_domain_whois_rdap_custom_tlds', []);
        
        // Ensure we always return an array
        if (!is_array($custom_tlds)) {
            return [];
        }
        
        return $custom_tlds;
    }
}
