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
     * The container filter is only registered at the end of `Plugin::init()`. A theme
     * or another plugin calling this helper on an earlier hook would otherwise hit
     * `null->get()` and fatal, so bail with the default instead.
     */
    if ( ! is_object( $container ) || ! method_exists( $container, 'get' ) ) {
        return $default_value;
    }

    $settings_object = $container->get( 'settings' );

    if ( ! is_object( $settings_object ) || ! method_exists( $settings_object, 'get' ) ) {
        return $default_value;
    }

    $settings = $settings_object->get();

    if ( ! is_array( $settings ) || empty( $settings['pressidiumOptions']['cookieTable'] ) ) {
        return $default_value;
    }

    return $settings['pressidiumOptions']['cookieTable'];
}
