<?php
/**
 * Plugin Name: Pressidium Cookie Consent
 * Plugin URI: https://github.com/pressidium/pressidium-cookie-consent/
 * Description: Lightweight, user-friendly and customizable cookie consent banner to help you comply with the EU GDPR cookie law and CCPA regulations.
 * Version: 1.1.0
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
        define( 'Pressidium\WP\CookieConsent\VERSION', '1.1.0' );
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
 * Initialize the plugin.
 *
 * @return void
 */
function init_plugin(): void {
    // Composer autoload
    require_once __DIR__ . '/vendor/autoload.php';

    // Setup plugin constants
    setup_constants();

    // Initialize the plugin
    $plugin = new Plugin();
    $plugin->init();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init_plugin' );
