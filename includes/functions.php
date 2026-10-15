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
    $container       = apply_filters( 'pressidium_cookie_consent_container', null );
    $settings_object = $container->get( 'settings' );

    $settings = $settings_object->get();

    $default_value = array(
        'necessary'   => array(),
        'analytics'   => array(),
        'targeting'   => array(),
        'preferences' => array(),
    );

    if ( ! is_array( $settings ) || empty( $settings['pressidiumOptions']['cookieTable'] ) ) {
        return $default_value;
    }

    return $settings['pressidiumOptions']['cookieTable'];
}
