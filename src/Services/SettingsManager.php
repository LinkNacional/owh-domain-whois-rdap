<?php

namespace OwhDomainWhoisRdap\Services;

/**
 * Settings Manager
 * 
 * Centralizes access to plugin options stored in wp_options table
 *
 * @since      1.0.0
 * @package    OwhDomainWhoisRdap
 * @subpackage OwhDomainWhoisRdap/Services
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class SettingsManager
{
    /**
     * Option prefix
     */
    private const OPTION_PREFIX = 'lknaci_owh_domain_whois_rdap_';

    /**
     * Get option value
     *
     * @param string $option_name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $option_name, $default = null)
    {
        $wp_option_name = self::OPTION_PREFIX . $option_name;
        return \get_option($wp_option_name, $default);
    }

    /**
     * Set option value
     *
     * @param string $option_name
     * @param mixed $value
     * @return bool
     */
    public function set(string $option_name, $value): bool
    {
        $wp_option_name = self::OPTION_PREFIX . $option_name;
        return \update_option($wp_option_name, $value);
    }

    /**
     * Delete option
     *
     * @param string $option_name
     * @return bool
     */
    public function delete(string $option_name): bool
    {
        $wp_option_name = self::OPTION_PREFIX . $option_name;
        return \delete_option($wp_option_name);
    }

    /**
     * Check if search is enabled
     *
     * @return bool
     */
    public function isSearchEnabled(): bool
    {
        // Nova opção baseada no Figma
        return (bool) \get_option('owh_rdap_enable_search', false);
    }

    /**
     * Get results page ID
     *
     * @return int
     */
    public function getResultsPageId(): int
    {
        // Nova opção baseada no Figma
        return (int) \get_option('owh_rdap_results_page', 0);
    }

    /**
     * Get available domain text
     *
     * @return string
     */
    public function getAvailableText(): string
    {
        return $this->get('available_text', 'Domínio disponível!');
    }

    /**
     * Get unavailable domain text
     *
     * @return string
     */
    public function getUnavailableText(): string
    {
        return $this->get('unavailable_text', 'Domínio não disponível');
    }

    /**
     * Get placeholder text
     *
     * @return string
     */
    public function getPlaceholderText(): string
    {
        return $this->get('placeholder_text', 'Digite o nome do domínio...');
    }

    /**
     * Get loading image URL
     *
     * @return string
     */
    public function getLoadingImage(): string
    {
        return $this->get('loading_image', '');
    }

    /**
     * Get buy button text
     *
     * @return string
     */
    public function getBuyButtonText(): string
    {
        return $this->get('buy_button_text', 'Comprar Domínio');
    }

    /**
     * Get buy button icon class
     *
     * @return string
     */
    public function getBuyButtonIcon(): string
    {
        return $this->get('buy_button_icon', 'dashicons-cart');
    }

    /**
     * Get buy button URL
     *
     * @return string
     */
    public function getBuyButtonUrl(): string
    {
        return $this->get('buy_button_url', '');
    }

    /**
     * Should buy button open in new tab
     *
     * @return bool
     */
    public function shouldBuyButtonOpenInNewTab(): bool
    {
        return (bool) $this->get('buy_button_new_tab', true);
    }

    /**
     * Get available domain cache time (in seconds)
     *
     * @return int
     */
    public function getAvailableCacheTime(): int
    {
        return (int) $this->get('available_cache_time', 3600);
    }

    /**
     * Get unavailable domain cache time (in seconds)
     *
     * @return int
     */
    public function getUnavailableCacheTime(): int
    {
        return (int) $this->get('unavailable_cache_time', 86400);
    }

    /**
     * Get WHOIS details page ID
     *
     * @return int
     */
    public function getWhoisDetailsPageId(): int
    {
        // Use direct WordPress option name since this is a standalone page setting
        return (int) \get_option('owh_rdap_whois_details_page', 0);
    }

    /**
     * Check if WHOIS details link should be shown for unavailable domains
     *
     * @return bool
     */
    public function shouldShowWhoisDetailsLink(): bool
    {
        return $this->getWhoisDetailsPageId() > 0;
    }
}
