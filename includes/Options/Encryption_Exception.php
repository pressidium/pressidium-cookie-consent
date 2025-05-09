<?php
/**
 * Encryption exception.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Options;

use Exception;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Encryption_Exception class.
 *
 * @since 1.1.0
 */
class Encryption_Exception extends Exception {}
