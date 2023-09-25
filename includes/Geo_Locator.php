<?php
/**
 * Geo_Locator.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Geo_Locator class.
 *
 * @since 1.2.0
 */
class Geo_Locator {

    /**
     * Return the geographic location (if it can be found) based on the given IP address.
     *
     * Based on the `WC_Geolocation::get_country_code_from_headers()` method of WooCommerce.
     *
     * @link https://woocommerce.github.io/code-reference/classes/WC-Geolocation.html#method_get_country_code_from_headers
     * @link https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-geolocation.html#source-view.235
     *
     * @param string $ip_address IP address.
     *
     * @return string|null Country code if found, `null` otherwise.
     */
    public function maybe_get_country_code( string $ip_address ): ?string {
        $headers = array(
            'MM_COUNTRY_CODE',
            'GEOIP_COUNTRY_CODE',
            'HTTP_CF_IPCOUNTRY',
            'HTTP_X_COUNTRY_CODE',
        );

        foreach ( $headers as $header ) {
            if ( empty( $_SERVER[ $header ] ) ) {
                continue;
            }

            return strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
        }

        return null;
    }

}
