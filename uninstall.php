<?php
/**
 * Uninstall plugin.
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
