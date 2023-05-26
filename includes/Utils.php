<?php
/**
 * Utilities.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Utils class.
 *
 * @since 1.0.0
 */
class Utils {

    /**
     * Return the domain of this WordPress website.
     *
     * @return string
     */
    public static function get_domain(): string {
        $domain = parse_url( get_site_url(), PHP_URL_HOST );

        if ( ! $domain ) {
            return $_SERVER['HTTP_HOST'];
        }

        return $domain;
    }

    /**
     * Check if a string ends with a given substring.
     *
     * Equivalent to `str_ends_with()` in PHP 8.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for in the `haystack`.
     *
     * @return bool `true` if `haystack` ends with `needle`, `false` otherwise.
     */
    public static function ends_with( string $haystack, string $needle ): bool {
        if ( empty( $needle ) ) {
            return true;
        }

        $length = strlen( $needle );
        return $length <= 0 || substr( $haystack, -$length ) === $needle;
    }


}
