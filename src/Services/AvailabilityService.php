<?php

namespace OwhDomainWhoisRdap\Services;

use OwhDomainWhoisRdap\Models\DomainResult;
use OwhDomainWhoisRdap\Helpers\DomainValidator;

/**
 * Availability Service
 * 
 * Analyzes RDAP response and determines domain availability
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class AvailabilityService
{
    /**
     * RDAP Client
     *
     * @var RdapClient
     */
    private $rdapClient;

    /**
     * Bootstrap File Handler
     *
     * @var BootstrapFileHandler
     */
    private $bootstrapHandler;

    /**
     * Cache Manager
     *
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * Settings Manager
     *
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * Constructor
     *
     * @param RdapClient $rdapClient
     * @param BootstrapFileHandler $bootstrapHandler
     * @param CacheManager $cacheManager
     * @param SettingsManager $settingsManager
     */
    public function __construct(
        RdapClient $rdapClient,
        BootstrapFileHandler $bootstrapHandler,
        CacheManager $cacheManager,
        SettingsManager $settingsManager
    ) {
        $this->rdapClient = $rdapClient;
        $this->bootstrapHandler = $bootstrapHandler;
        $this->cacheManager = $cacheManager;
        $this->settingsManager = $settingsManager;
    }

    /**
     * Check domain availability using universal RDAP.org endpoint or custom TLD configuration
     *
     * @param string $domain
     * @return DomainResult
     */
    public function checkAvailability(string $domain): DomainResult
    {
        // Validate domain format
        if (!DomainValidator::isValidDomain($domain)) {
            return new DomainResult(
                $domain,
                false,
                'Formato de domínio inválido',
                null,
                'Formato de domínio inválido'
            );
        }

        $domain = strtolower(trim($domain));

        // Extract TLD from domain
        $tld = $this->extractTld($domain);
        if (!$tld) {
            return new DomainResult(
                $domain,
                false,
                'TLD não encontrado',
                null,
                'Não foi possível extrair a TLD do domínio'
            );
        }

        // Check for custom TLD configuration first
        $customRdapUrl = $this->settingsManager->getCustomRdapUrl($tld);
        if ($customRdapUrl) {
            // Use custom RDAP URL for this TLD
            $rdapServer = $customRdapUrl;
        } else {
            // Validate TLD using IANA list
            if (!$this->bootstrapHandler->isValidTld($tld)) {
                return new DomainResult(
                    $domain,
                    false,
                    'TLD não suportado',
                    null,
                    sprintf('A extensão "%s" não está na lista oficial da IANA', $tld)
                );
            }
            
            // Use universal RDAP.org endpoint for standard TLDs
            $rdapServer = 'https://rdap.org/domain/';
        }

        // Query RDAP server (custom or universal)
        $rdapResponse = $this->rdapClient->queryDomain($domain, $rdapServer);
        if (!$rdapResponse) {
            return new DomainResult(
                $domain,
                false,
                'Erro na consulta RDAP',
                null,
                'Falha ao conectar com o servidor RDAP'
            );
        }

        // Analyze response
        $result = $this->analyzeRdapResponse($domain, $rdapResponse);

        // Cache result
        $this->cacheResult($result);

        return $result;
    }

    /**
     * Analyze RDAP response to determine availability
     *
     * @param string $domain
     * @param array $rdapResponse
     * @return DomainResult
     */
    private function analyzeRdapResponse(string $domain, array $rdapResponse): DomainResult
    {
        $statusCode = $rdapResponse['status_code'];
        $data = $rdapResponse['data'];

        // 404 OR cURL error 56 means domain is AVAILABLE (not registered)
        if ($statusCode === 404 || isset($rdapResponse['curl_error'])) {
            return new DomainResult(
                $domain,
                true,
                'Domínio disponível',
                $data
            );
        }

        // 200 means domain is REGISTERED (not available)
        if ($statusCode === 200) {
            return new DomainResult(
                $domain,
                false,
                'Domínio registrado',
                $data
            );
        }

        // Other status codes indicate errors
        return new DomainResult(
            $domain,
            false,
            'Erro na consulta',
            $data,
            "Resposta HTTP: {$statusCode}"
        );
    }

    /**
     * Check if domain is available based on RDAP data
     *
     * @param array $data
     * @return bool
     */
    private function isDomainAvailable(array $data): bool
    {
        // Check status array for availability indicators
        if (isset($data['status'])) {
            $statuses = (array) $data['status'];
            
            // Common status values that indicate unavailability
            $unavailableStatuses = [
                'active',
                'ok',
                'client delete prohibited',
                'client transfer prohibited',
                'client update prohibited'
            ];

            foreach ($statuses as $status) {
                if (in_array(strtolower($status), $unavailableStatuses)) {
                    return false;
                }
            }
        }

        // Check for entities (registrant, registrar, etc.)
        if (isset($data['entities']) && !empty($data['entities'])) {
            return false;
        }

        // Check for name servers
        if (isset($data['nameservers']) && !empty($data['nameservers'])) {
            return false;
        }

        // If we have detailed data but no clear indicators, assume registered
        return false;
    }

    /**
     * Cache the result
     *
     * @param DomainResult $result
     * @return void
     */
    private function cacheResult(DomainResult $result): void
    {
        $cacheTime = $result->isAvailable() ?
            $this->settingsManager->getAvailableCacheTime() :
            $this->settingsManager->getUnavailableCacheTime();

        $this->cacheManager->set(
            $result->getDomain(),
            $result->toArray(),
            $cacheTime
        );
    }

    /**
     * Extract TLD from domain
     *
     * @param string $domain
     * @return string|null
     */
    private function extractTld(string $domain): ?string
    {
        $dotPosition = strpos($domain, '.');
        
        if ($dotPosition === false) {
            return null;
        }

        return substr($domain, $dotPosition + 1);
    }
}
