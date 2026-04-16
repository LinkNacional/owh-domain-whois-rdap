<?php

namespace OwhDomainWhoisRdap\Services;

/**
 * TLD Validator Service
 * 
 * Validates Top Level Domains using IANA official list
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class TldValidator
{
    /**
     * Bootstrap File Handler
     *
     * @var BootstrapFileHandler
     */
    private $bootstrapHandler;

    /**
     * Constructor
     *
     * @param BootstrapFileHandler $bootstrapHandler
     */
    public function __construct(BootstrapFileHandler $bootstrapHandler)
    {
        $this->bootstrapHandler = $bootstrapHandler;
    }

    /**
     * Validate if TLD is officially supported
     *
     * @param string $tld
     * @return bool
     */
    public function isValid(string $tld): bool
    {
        return $this->bootstrapHandler->isValidTld($tld);
    }

    /**
     * Extract TLD from domain
     *
     * @param string $domain
     * @return string|null
     */
    public function extractTld(string $domain): ?string
    {
        $parts = explode('.', $domain);
        
        if (count($parts) < 2) {
            return null;
        }

        return strtolower(end($parts));
    }

    /**
     * Validate domain TLD
     *
     * @param string $domain
     * @return bool
     */
    public function validateDomainTld(string $domain): bool
    {
        $tld = $this->extractTld($domain);
        
        if (!$tld) {
            return false;
        }

        return $this->isValid($tld);
    }

    /**
     * Get all supported TLDs
     *
     * @return array
     */
    public function getSupportedTlds(): array
    {
        return $this->bootstrapHandler->getSupportedTlds();
    }
}
