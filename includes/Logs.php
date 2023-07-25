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
        return sprintf(
            "Pressidium Cookie Consent v%s\nPHP v%s\nWordPress v%s\nInstallation @ %s\n",
            VERSION,
            phpversion(),
            get_bloginfo( 'version' ),
            get_bloginfo( 'url' ),
        );
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
