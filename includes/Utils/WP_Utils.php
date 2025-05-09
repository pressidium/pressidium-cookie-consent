<?php
/**
 * WordPress Utilities.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * WP_Utils class.
 *
 * @since 1.8.0
 */
class WP_Utils {

    /**
     * Return the domain of this WordPress website.
     *
     * @since 1.1.0
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
     * Whether the current request is a Ninja Forms preview.
     *
     * @since 1.9.0
     *
     * @link https://wordpress.org/plugins/ninja-forms/
     *
     * @return bool
     */
    public static function is_ninja_forms_preview(): bool {
        return isset( $_GET['nf_preview_form'] ) && isset( $_GET['nf_iframe'] );
    }

}
