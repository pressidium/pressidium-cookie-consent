<?php
/**
 * WP Consent API integration.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Integrations;

use Pressidium\WP\CookieConsent\Hooks\Filters;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * WP_Consent_API class.
 *
 * @since 1.8.0
 */
class WP_Consent_API implements Filters {

    /**
     * Return whether the plugin is registered with the WP Consent API.
     *
     * @return bool
     */
    public function is_registered(): bool {
        return true;
    }

    /**
     * Return the consent type.
     *
     * @return string
     */
    public function get_consent_type(): string {
        return 'optin';
    }

    /**
     * Return the filters to register.
     *
     * WP Consent API integration.
     *
     * @link https://github.com/WordPress/wp-consent-level-api
     * @link https://wordpress.org/plugins/wp-consent-api/
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_filters(): array {
        return array(
            // Declare compliance with consent level API
            'wp_consent_api_registered_pressidium-cookie-consent/pressidium-cookie-consent.php' => array( 'is_registered' ),
            // Return the consent type (optin, optout, etc.)
            'wp_get_consent_type' => array( 'get_consent_type' ),
        );
    }

}
