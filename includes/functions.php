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

    /*
     * We use a hardcoded 'en' language since the UI does not support
     * multiple languages for the cookies list yet.
     *
     * In the future, we will need to update this function to accept
     * a `$language` parameter and return the cookies for the specified
     * language.
     */
    $blocks = $settings['languages']['en']['settings_modal']['blocks'];

    $cookie_categories = array(
        $blocks[1] ?? array(), // Necessary
        $blocks[2] ?? array(), // Analytics
        $blocks[3] ?? array(), // Targeting
        $blocks[4] ?? array(), // Preferences
    );

    return array_map(
        function( $category ) {
            return array(
                'title'       => $category['title'] ?? '',
                'description' => $category['description'] ?? '',
                'cookies'     => $category['cookie_table'] ?? array(),
            );
        },
        $cookie_categories
    );
}
