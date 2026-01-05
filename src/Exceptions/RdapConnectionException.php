<?php

namespace OwhDomainWhoisRdap\Exceptions;

/**
 * RDAP Connection Exception
 * 
 * Exception thrown when RDAP connection fails
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Exceptions
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class RdapConnectionException extends \Exception
{
    /**
     * RDAP server URL
     *
     * @var string
     */
    private $rdapServer;

    /**
     * Domain that was being queried
     *
     * @var string
     */
    private $domain;

    /**
     * Constructor
     *
     * @param string $message
     * @param string $rdapServer
     * @param string $domain
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = "",
        string $rdapServer = "",
        string $domain = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->rdapServer = $rdapServer;
        $this->domain = $domain;
    }

    /**
     * Get RDAP server URL
     *
     * @return string
     */
    public function getRdapServer(): string
    {
        return $this->rdapServer;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get full error context
     *
     * @return array
     */
    public function getContext(): array
    {
        return [
            'message' => $this->getMessage(),
            'rdap_server' => $this->rdapServer,
            'domain' => $this->domain,
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}
