<?php
/**
 * Plugin Name: Pressidium Cookie Consent
 * Plugin URI: https://github.com/pressidium/pressidium-cookie-consent/
 * Description: Lightweight, user-friendly and customizable cookie consent banner to help you comply with the EU GDPR cookie law and CCPA regulations.
 * Version: 1.7.1
 * Author: Pressidium
 * Author URI: https://pressidium.com/
 * Text Domain: pressidium-cookie-consent
 * Domain Path: /languages
 * License: GPLv2
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Setup plugin constants.
 *
 * @return void
 */
function setup_constants(): void {
    if ( ! defined( 'Pressidium\WP\CookieConsent\VERSION' ) ) {
        define( 'Pressidium\WP\CookieConsent\VERSION', '1.7.1' );
    }

    if ( ! defined( 'Pressidium\WP\CookieConsent\PLUGIN_DIR' ) ) {
        define( 'Pressidium\WP\CookieConsent\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

    if ( ! defined( 'Pressidium\WP\CookieConsent\PLUGIN_URL' ) ) {
        define( 'Pressidium\WP\CookieConsent\PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    if ( ! defined( 'Pressidium\WP\CookieConsent\PLUGIN_FILE' ) ) {
        define( 'Pressidium\WP\CookieConsent\PLUGIN_FILE', __FILE__ );
    }
}

/**
 * Set an option when the plugin is activated.
 *
 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
 *
 * @return void
 */
function activate_plugin(): void {
    add_option( 'pressidium_cookie_consent_activated', true );
}

/**
 * Whether the plugin was just activated.
 *
 * @return bool
 */
function is_activated(): bool {
    $just_activated = is_admin() && get_option( 'pressidium_cookie_consent_activated' );

    if ( $just_activated ) {
        delete_option( 'pressidium_cookie_consent_activated' );

        return true;
    }

    return false;
}

/**
 * Initialize the plugin.
 *
 * @link https://developer.wordpress.org/reference/hooks/plugins_loaded/
 *
 * @return void
 */
function init_plugin(): void {
    // Composer autoload
    require_once __DIR__ . '/vendor/autoload.php';

    // Setup plugin constants
    setup_constants();

    // Instantiate the `Plugin` object
    $plugin = new Plugin();

    if ( is_activated() ) {
        // Mark the plugin as activated
        $plugin->mark_as_activated();
    }

    // Initialize the plugin
    $plugin->init();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate_plugin' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\init_plugin' );
