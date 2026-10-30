<?php
/**
 * File logger.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Logging;

use const Pressidium\WP\CookieConsent\PLUGIN_DIR;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Log\LogLevel;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Log\InvalidArgumentException;

use RuntimeException;
use Exception;
use Throwable;

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
     * Log a message with a level of an emergency — system is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency( $message, array $context = array() ): void {
        $this->log( LogLevel::CRITICAL, $message, $context );
    }

    /**
     * Log a message with a level of an alert — actions must be taken immediately.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert( $message, array $context = array() ): void {
        $this->log( LogLevel::ALERT, $message, $context );
    }

    /**
     * Log a message with a level of a critical condition.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical( $message, array $context = array() ): void {
        $this->log( LogLevel::CRITICAL, $message, $context );
    }

    /**
     * Log a message with a level of an error — runtime errors that do not require immediate action.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error( $message, array $context = array() ): void {
        $this->log( LogLevel::ERROR, $message, $context );
    }

    /**
     * Log a message with a level of warning — exceptional occurrences that are not errors.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning( $message, array $context = array() ): void {
        $this->log( LogLevel::WARNING, $message, $context );
    }

    /**
     * Log a message with a level of notice — normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice( $message, array $context = array() ): void {
        $this->log( LogLevel::NOTICE, $message, $context );
    }

    /**
     * Log a message with a level of info — interesting events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info( $message, array $context = array() ): void {
        $this->log( LogLevel::INFO, $message, $context );
    }

    /**
     * Log a message with a level of debug — detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug( $message, array $context = array() ): void {
        $this->log( LogLevel::DEBUG, $message, $context );
    }

    /**
     * Return the path to the `logs/` directory.
     *
     * @return string
     */
    public function get_logs_path(): string {
        $uploads_dir = wp_get_upload_dir()['basedir'];
        $log_path    = trailingslashit( $uploads_dir ) . 'pressidium-cookie-consent/logs/error.log';

        /**
         * Filters the path to the log file.
         *
         * @since 1.9.0
         *
         * @param string $log_path Path to the log file.
         *
         * @return string
         */
        $log_path = apply_filters( 'pressidium_cookie_consent_logs_path', $log_path );

        $log_dir       = dirname( $log_path );
        $htaccess_path = trailingslashit( $log_dir ) . '.htaccess';

        if ( ! file_exists( $log_dir ) ) {
            // Attempt to create the logs directory if it doesn't exist
            $did_create = wp_mkdir_p( $log_dir );

            if ( ! $did_create ) {
                throw new RuntimeException( 'Could not create logs directory: ' . $log_dir );
            }
        }

        if ( ! wp_is_writable( $log_dir ) ) {
            // Directory is not writable
            throw new RuntimeException( 'Logs directory is not writable: ' . $log_dir );
        }

        if ( ! file_exists( $htaccess_path ) ) {
            // Create a `.htaccess` file to prevent direct access
            $htaccess_content = "Deny from all\n";
            $did_write        = file_put_contents( $htaccess_path, $htaccess_content );

            if ( ! $did_write ) {
                throw new RuntimeException( 'Could not create .htaccess file: ' . $htaccess_path );
            }
        }

        // No need to check if the file exists, it will be created when we log the first message
        return $log_path;
    }

    /**
     * Log a message with an arbitrary level.
     *
     * Logging is a side channel and must never be able to break the request, so
     * nothing is allowed to escape this method. If the entry cannot be written (an
     * unwritable uploads directory, a read-only filesystem, a full disk), the
     * message goes to PHP's error log and execution carries on.
     *
     * @param mixed  $level   Log level.
     * @param string $message Log message.
     * @param array  $context Any extraneous information that does not fit well in a string.
     *
     * @return void
     */
    public function log( $level, $message, array $context = array() ): void {
        if ( ! isset( self::LEVELS[ $level ] ) ) {
            // Degrade to `error` rather than throwing on an unknown level.
            $level = LogLevel::ERROR;
        }

        if ( empty( $message ) ) {
            // Nothing meaningful to record.
            return;
        }

        try {
            $destination = $this->get_logs_path();
            $did_write   = file_put_contents( $destination, $message . PHP_EOL, FILE_APPEND );

            if ( $did_write === false ) {
                throw new RuntimeException( 'Could not write to log file: ' . $destination );
            }
        } catch ( Throwable $throwable ) {
            /*
             * `Throwable` rather than `Exception`: a failure in here can also arrive
             * as an `Error`, and the whole point of this method is that nothing gets
             * out of it.
             */
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( 'Pressidium Cookie Consent [' . $level . ']: ' . $message );
        }
    }

    /**
     * Log the given exception to a log file.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @param Exception $exception Exception to log.
     *
     * @return void
     */
    public function log_exception( Exception $exception ): void {
        $this->error( $exception->getMessage(), array( 'exception' => $exception ) );
    }

    /**
     * Read the log file and return its contents.
     *
     * @throws RuntimeException If the log file could not be read.
     *
     * @return string
     */
    public function get_logs(): string {
        $source = $this->get_logs_path();

        if ( ! file_exists( $source ) ) {
            // File does not exist, so there are no logs
            return '';
        }

        $logs = file_get_contents( $source );

        if ( $logs === false ) {
            throw new RuntimeException( 'Could not read log file: ' . $source );
        }

        return $logs;
    }

    /**
     * Clear logs.
     *
     * @throws RuntimeException If the log file could not be cleared.
     *
     * @return void
     */
    public function clear(): void {
        $destination = $this->get_logs_path();
        $did_clear   = file_put_contents( $destination, '' );

        if ( $did_clear === false ) {
            throw new RuntimeException( 'Could not clear log file: ' . $destination );
        }
    }

}
