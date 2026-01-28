<?php

namespace OwhDomainWhoisRdap\Services;

use OwhDomainWhoisRdap\Models\DomainResult;

/**
 * RDAP Client
 * 
 * Handles HTTP requests to RDAP servers
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class RdapClient
{
    /**
     * Make RDAP query for domain
     *
     * @param string $domain
     * @param string $rdapServer
     * @return array|null
     */
    public function queryDomain(string $domain, string $rdapServer): ?array
    {
        $url = $rdapServer . '/' . $domain;
        $response = \wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/rdap+json,application/json',
                'User-Agent' => 'OWH Domain WHOIS RDAP Plugin/1.0.0'
            ]
        ]);

        if (\is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            // cURL error 56 (SSL EOF) indica domínio disponível/liberado
            if (strpos($error_message, 'cURL error 56') !== false || 
                strpos($error_message, 'SSL_read: error:0A000126') !== false ||
                strpos($error_message, 'unexpected eof') !== false) {
                
                return [
                    'status_code' => 404,
                    'body' => '',
                    'data' => null,
                    'curl_error' => 'Domain available (SSL EOF)'
                ];
            }
            
            return null;
        }

        $status_code = \wp_remote_retrieve_response_code($response);
        $body = \wp_remote_retrieve_body($response);

        $result = [
            'status_code' => $status_code,
            'body' => $body,
            'data' => null
        ];

        // Try to decode JSON if we have a body
        if (!empty($body)) {
            $json_data = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result['data'] = $json_data;
            }
        }

        return $result;
    }

    /**
     * Check if RDAP server is reachable
     *
     * @param string $rdapServer
     * @return bool
     */
    public function isServerReachable(string $rdapServer): bool
    {
        $response = \wp_remote_get($rdapServer . '/help', [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/rdap+json,application/json',
                'User-Agent' => 'OWH Domain WHOIS RDAP Plugin/1.0.0'
            ]
        ]);

        return !\is_wp_error($response) && 
               \wp_remote_retrieve_response_code($response) < 500;
    }
}
