<?php
/**
 * Feedback exception.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Feedback;

use Exception;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Feedback_Exception class.
 *
 * @since 1.1.0
 */
class Feedback_Exception extends Exception {}
