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
     * Normalize the given country code.
     *
     * @param mixed $country_code Country code.
     *
     * @return ?string
     */
    private function normalize_country_code( $country_code ): ?string {
        if ( ! is_string( $country_code ) ) {
            return null;
        }

        $country_code = strtoupper( trim( $country_code ) );

        /*
         * The `CF-IPCountry` header contains an ISO-3166-1 alpha-2 code.
         * Cloudflare also uses the following special country codes:
         * - `XX`: Used for clients without country code data.
         * - `T1`: Used for clients using the Tor network.
         */
        if ( $country_code === 'XX' || $country_code === 'T1' ) {
            return null;
        }

        // If the country code is not a valid ISO-3166-1 alpha-2 code, return `null`
        if ( ! preg_match( '/^[A-Z]{2}$/', $country_code ) ) {
            return null;
        }

        return $country_code;
    }

    /**
     * Return the IP address of the current request used for geolocation.
     *
     * @return string
     */
    public function get_ip_address(): string {
        /**
         * Filters the IP address used for geolocation.
         *
         * @since 2.0.0
         *
         * @param string $ip_address The IP address.
         *
         * @return string
         */
        return (string) apply_filters(
            'pressidium_cookie_consent_geo_ip_address',
            $_SERVER['REMOTE_ADDR'] ?? ''
        );
    }

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
     * @return ?string Country code if found, `null` otherwise.
     */
    public function maybe_get_country_code( string $ip_address ): ?string {
        $server_keys = array(
            'MM_COUNTRY_CODE',
            'GEOIP_COUNTRY_CODE',
            'HTTP_CF_IPCOUNTRY',
            'HTTP_X_COUNTRY_CODE',
        );

        /**
         * Filters the keys used to detect the country code from the `$_SERVER` superglobal for geolocation.
         *
         * Indexed array of server keys. The first key found in the `$_SERVER` superglobal
         * will be used to determine the country code.
         *
         * Request headers appear as `HTTP_` followed by the header name in uppercase,
         * with hyphens replaced by underscores (e.g. `X-Country-Code` → `HTTP_X_COUNTRY_CODE`).
         *
         * @since 2.0.0
         *
         * @param string[] $server_keys Indexed array of server keys.
         *
         * @return string[]
         */
        $server_keys = apply_filters( 'pressidium_cookie_consent_geo_server_keys', $server_keys );

        $country_code = null;

        foreach ( $server_keys as $server_key ) {
            if ( empty( $_SERVER[ $server_key ] ) ) {
                continue;
            }

            // Short-circuit when we find a country code
            $country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $server_key ] ) ) );
            break;
        }

        /**
         * Filters the country code for geolocation.
         *
         * @since 2.0.0
         *
         * @param ?string $country_code Country code or `null` if we couldn't detect a country.
         * @param string  $ip_address   IP address.
         *
         * @return ?string
         */
        $country_code = apply_filters( 'pressidium_cookie_consent_geo_country_code', $country_code, $ip_address );

        return $this->normalize_country_code( $country_code );
    }

}
