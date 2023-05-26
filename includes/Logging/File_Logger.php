<?php
/**
 * File logger.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Logging;

use Exception;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * File_Logger class.
 *
 * @since 1.0.0
 */
class File_Logger implements Logger {

    /**
     * Log the given exception to a log file.
     *
     * @param Exception $exception Exception to log.
     *
     * @return void
     */
    public function log_exception( Exception $exception ): void {
        $destination = \Pressidium\WP\CookieConsent\PLUGIN_DIR . '/logs/error.log';

        error_log(
            print_r( $exception, true ),
            3, // append to file
            $destination
        );
    }

}
