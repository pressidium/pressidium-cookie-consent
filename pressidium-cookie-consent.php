<?php
/**
 * Plugin Name: Pressidium Cookie Consent
 * Plugin URI: https://github.com/pressidium/pressidium-cookie-consent/
 * Description: Lightweight, user-friendly and customizable cookie consent banner to help you comply with the EU GDPR cookie law and CCPA regulations.
 * Version: 1.8.0
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
        define( 'Pressidium\WP\CookieConsent\VERSION', '1.8.0' );
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

    if ( ! defined( 'Pressidium\WP\CookieConsent\MINIMUM_WP_VERSION' ) ) {
        define( 'Pressidium\WP\CookieConsent\MINIMUM_WP_VERSION', '6.0' );
    }

    if ( ! defined( 'Pressidium\WP\CookieConsent\MINIMUM_PHP_VERSION' ) ) {
        define( 'Pressidium\WP\CookieConsent\MINIMUM_PHP_VERSION', '8.1' );
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
 * Display an admin notice if the plugin does not meet the minimum WordPress version.
 *
 * @return void
 */
function admin_notice_minimum_wp_version(): void {
    $message = sprintf(
        /* translators: 1: Plugin name, 2: WordPress version */
        esc_html__( '%1$s requires WordPress version %2$s or greater.', 'pressidium-cookie-consent' ),
        '<strong>' . esc_html__( 'Pressidium Cookie Consent', 'pressidium-cookie-consent' ) . '</strong>',
        '<strong>' . esc_html( MINIMUM_WP_VERSION ) . '</strong>'
    );

    printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
}

/**
 * Display an admin notice if the plugin does not meet the minimum PHP version.
 *
 * @return void
 */
function admin_notice_minimum_php_version(): void {
    $message = sprintf(
        /* translators: 1: Plugin name, 2: PHP version */
        esc_html__( '%1$s requires PHP version %2$s or greater.', 'pressidium-cookie-consent' ),
        '<strong>' . esc_html__( 'Pressidium Cookie Consent', 'pressidium-cookie-consent' ) . '</strong>',
        '<strong>' . esc_html( MINIMUM_PHP_VERSION ) . '</strong>'
    );

    printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
}

/**
 * Whether the plugin meets the minimum PHP version requirements.
 *
 * @return bool
 */
function meets_wp_version_requirements(): bool {
    $wp_version = get_bloginfo( 'version' );

    return version_compare( $wp_version, MINIMUM_WP_VERSION, '>=' );
}

/**
 * Whether the plugin meets the minimum PHP version requirements.
 *
 * @return bool
 */
function meets_php_version_requirements(): bool {
    return version_compare( PHP_VERSION, MINIMUM_PHP_VERSION, '>=' );
}

/**
 * Check if the plugin is compatible with the current environment.
 *
 * @return void
 */
function meets_version_requirements(): bool {
    $requirements_met = true;

    // Check if it meets the minimum WordPress version
    if ( ! meets_wp_version_requirements() ) {
        add_action( 'admin_notices', __NAMESPACE__ . '\admin_notice_minimum_wp_version' );
        $requirements_met = false;
    }

    // Check if it meets the minimum PHP version
    if ( ! meets_php_version_requirements() ) {
        add_action( 'admin_notices', __NAMESPACE__ . '\admin_notice_minimum_php_version' );
        $requirements_met = false;
    }

    return $requirements_met;
}

/**
 * Initialize the plugin.
 *
 * @link https://developer.wordpress.org/reference/hooks/plugins_loaded/
 *
 * @return void
 */
function init_plugin(): void {
    // Setup plugin constants
    setup_constants();

    if ( ! meets_version_requirements() ) {
        // Minimum version requirements not met, bail early
        return;
    }

    // Composer autoload
    require_once __DIR__ . '/vendor/autoload.php';

    // Global functions
    require_once __DIR__ . '/includes/functions.php';

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
