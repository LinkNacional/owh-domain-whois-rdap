<?php

namespace OwhDomainWhoisRdap\Services;

/**
 * Service Container for Dependency Injection
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class ServiceContainer
{
    /**
     * Container for services
     *
     * @var array
     */
    private $services = [];

    /**
     * Container for singletons
     *
     * @var array
     */
    private $singletons = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerServices();
    }

    /**
     * Register all services
     *
     * @return void
     */
    private function registerServices()
    {
        // Register SettingsManager
        $this->bind('SettingsManager', function () {
            return new SettingsManager();
        }, true);

        // Register CacheManager
        $this->bind('CacheManager', function () {
            return new CacheManager();
        }, true);

        // Register BootstrapFileHandler
        $this->bind('BootstrapFileHandler', function () {
            return new BootstrapFileHandler();
        }, true);

        // Register RdapClient
        $this->bind('RdapClient', function () {
            return new RdapClient();
        });

        // Register AvailabilityService
        $this->bind('AvailabilityService', function () {
            return new AvailabilityService(
                $this->get('RdapClient'),
                $this->get('BootstrapFileHandler'),
                $this->get('CacheManager'),
                $this->get('SettingsManager')
            );
        });
    }

    /**
     * Bind a service to the container
     *
     * @param string $name
     * @param callable $resolver
     * @param bool $singleton
     * @return void
     */
    public function bind(string $name, callable $resolver, bool $singleton = false)
    {
        $this->services[$name] = [
            'resolver' => $resolver,
            'singleton' => $singleton
        ];
    }

    /**
     * Get a service from the container
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \Exception("Service {$name} not found in container");
        }

        $service = $this->services[$name];

        if ($service['singleton']) {
            if (!isset($this->singletons[$name])) {
                $this->singletons[$name] = $service['resolver']();
            }
            return $this->singletons[$name];
        }

        return $service['resolver']();
    }

    /**
     * Check if service exists
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
}
