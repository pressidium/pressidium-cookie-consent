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

    return $settings['pressidium_options']['cookie_table'];
}
