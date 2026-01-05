<?php

namespace OwhDomainWhoisRdap\Models;

/**
 * Domain Result DTO
 * 
 * Data Transfer Object for domain search results
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Models
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class DomainResult
{
    /**
     * Domain name
     *
     * @var string
     */
    private $domain;

    /**
     * Is domain available
     *
     * @var bool
     */
    private $isAvailable;

    /**
     * Status message
     *
     * @var string
     */
    private $status;

    /**
     * Raw RDAP response
     *
     * @var array|null
     */
    private $rdapData;

    /**
     * Error message if any
     *
     * @var string|null
     */
    private $error;

    /**
     * Constructor
     *
     * @param string $domain
     * @param bool $isAvailable
     * @param string $status
     * @param array|null $rdapData
     * @param string|null $error
     */
    public function __construct(
        string $domain,
        bool $isAvailable,
        string $status,
        ?array $rdapData = null,
        ?string $error = null
    ) {
        $this->domain = $domain;
        $this->isAvailable = $isAvailable;
        $this->status = $status;
        $this->rdapData = $rdapData;
        $this->error = $error;
    }

    /**
     * Get domain name
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Check if domain is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Get status message
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get RDAP data
     *
     * @return array|null
     */
    public function getRdapData(): ?array
    {
        return $this->rdapData;
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Check if there's an error
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return !empty($this->error);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'isAvailable' => $this->isAvailable,
            'status' => $this->status,
            'rdapData' => $this->rdapData,
            'error' => $this->error,
        ];
    }

    /**
     * Create from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['domain'] ?? '',
            $data['isAvailable'] ?? false,
            $data['status'] ?? '',
            $data['rdapData'] ?? null,
            $data['error'] ?? null
        );
    }
}
