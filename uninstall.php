<?php
/**
 * Uninstall the plugin.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Clean up before uninstalling this plugin
delete_option( 'pressidium_cookie_consent_settings' );
delete_option( 'pressidium_cookie_consent_table_versions' );
delete_option( 'pressidium_cookie_consent_ai_api_key' );

// Delete the custom cookie consents table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pressidium_cookie_consents" );
