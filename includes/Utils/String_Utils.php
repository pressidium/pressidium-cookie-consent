<?php
/**
 * String Utilities.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * String_Utils class.
 *
 * @since 1.0.0
 */
class String_Utils {

    /**
     * Check if a string starts with a given substring.
     *
     * Equivalent to `str_starts_with()` in PHP 8
     *
     * @since 1.1.0
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for in the `haystack`.
     *
     * @return bool `true` if `haystack` begins with `needle`, `false` otherwise.
     */
    public static function starts_with( string $haystack, string $needle ): bool {
        if ( empty( $needle ) ) {
            return true;
        }

        return strpos( $haystack, $needle ) === 0;
    }

    /**
     * Check if a string ends with a given substring.
     *
     * Equivalent to `str_ends_with()` in PHP 8.
     *
     * @since 1.0.0
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

    /**
     * Return the given string with the given prefix removed.
     *
     * @since 1.1.0
     *
     * @param string $prefix Prefix to remove from the string.
     * @param string $str    String to remove the prefix from.
     *
     * @return string
     */
    public static function strip_prefix( string $prefix, string $str ): string {
        if ( self::starts_with( $str, $prefix ) ) {
            return substr( $str, strlen( $prefix ) );
        }

        return $str;
    }

    /**
     * Return the given string with the given suffix removed.
     *
     * @since 1.1.0
     *
     * @param string $suffix Suffix to remove from the string.
     * @param string $str    String to remove the suffix from.
     *
     * @return string
     */
    public static function strip_suffix( string $suffix, string $str ): string {
        if ( self::ends_with( $str, $suffix ) && ! empty( $suffix ) ) {
            return substr( $str, 0, -strlen( $suffix ) );
        }

        return $str;
    }


    /**
     * Return the given string with the leading slash removed (if any).
     *
     * @since 1.1.0
     *
     * @param string $string String to remove the leading slash from.
     *
     * @return string String with leading slash removed.
     */
    public static function unleading_slash_it( string $string ): string {
        return self::strip_prefix( '/', $string );
    }

    /**
     * Return the given string with the trailing slash removed (if any).
     *
     * @since 1.1.0
     *
     * @param string $string String to remove the trailing slash from.
     *
     * @return string String with trailing slash removed
     */
    public static function untrailing_slash_it( string $string ): string {
        return self::strip_suffix( '/', $string );
    }

    /**
     * Truncate a string to the given length.
     *
     * @param ?string $string String to truncate.
     * @param int     $length Length to truncate the string to.
     *
     * @return ?string Truncated string.
     */
    public static function truncate( ?string $string, int $length ): ?string {
        if ( is_null( $string ) ) {
            return null;
        }

        return substr( $string, 0, $length );
    }

}
