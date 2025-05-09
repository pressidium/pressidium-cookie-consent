<?php
/**
 * Logs.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use const Pressidium\WP\CookieConsent\VERSION;

use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Logging\File_Logger;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Logs class.
 *
 * @since 1.0.0
 */
class Logs {

    /**
     * @var Logger An instance of `Logger`.
     */
    private Logger $logger;

    /**
     * Logs constructor.
     *
     * @param Logger $logger An instance of `Logger`.
     */
    public function __construct( Logger $logger ) {
        $this->logger = $logger;
    }

    /**
     * Return debug info.
     *
     * @return string
     */
    private function get_debug_info(): string {
        global $wpdb;

        return implode(
            "\n",
            array(
                sprintf( 'Pressidium Cookie Consent v%s', VERSION ),
                sprintf( 'WordPress v%s', get_bloginfo( 'version' ) ),
                sprintf( 'PHP v%s', phpversion() ),
                $wpdb->db_server_info(),
                $wpdb->get_charset_collate(),
                sprintf( 'Logs located @ %s', ( new File_Logger() )->get_logs_path() ),
                sprintf( 'Installation @ %s', get_bloginfo( 'url' ) ),
            )
        ) . "\n";
    }

    /**
     * Return the logs.
     *
     * @return string
     */
    public function get_logs(): string {
        $logs = '';

        try {
            $logs = $this->logger->get_logs();
        } catch ( RuntimeException $exception ) {
            $this->logger->log_exception( $exception );
        }

        // Prepend debug info
        return sprintf( "%s\n%s", $this->get_debug_info(), $logs );
    }

    /**
     * Clear logs.
     *
     * @return bool
     */
    public function clear(): bool {
        try {
            $this->logger->clear();
        } catch ( RuntimeException $exception ) {
            $this->logger->log_exception( $exception );

            return false;
        }

        return true;
    }

}
