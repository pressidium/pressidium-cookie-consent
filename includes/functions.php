<?php
/**
 * Global functions.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Return all cookies.
 *
 * @global
 *
 * @throws RuntimeException If called before the plugin has finished initialising.
 *
 * @return array
 */
function pressidium_cookie_consent_get_cookies(): array {
    $default_value = array(
        'necessary'   => array(),
        'analytics'   => array(),
        'targeting'   => array(),
        'preferences' => array(),
    );

    $container = apply_filters( 'pressidium_cookie_consent_container', null );

    /*
     * The container filter is only registered at the end of `Plugin::init()`, so a theme
     * or another plugin calling this helper on an earlier hook hits `null->get()`. That
     * is a programming error on the caller's side and it should keep failing; what it
     * should not do is fail with `Call to a member function get() on null`, which says
     * nothing about what to do differently. Returning the default instead would be worse
     * still: four empty categories are indistinguishable from a site that has no cookies
     * configured, so the caller would read a wrong answer as a correct one.
     */
    if ( ! is_object( $container ) || ! method_exists( $container, 'get' ) ) {
        throw new RuntimeException(
            'pressidium_cookie_consent_get_cookies() was called before Pressidium Cookie '
            . 'Consent finished initialising. Call it on `init` or later.'
        );
    }

    $settings = $container->get( 'settings' )->get();

    if ( ! is_array( $settings ) || empty( $settings['pressidiumOptions']['cookieTable'] ) ) {
        return $default_value;
    }

    return $settings['pressidiumOptions']['cookieTable'];
}
