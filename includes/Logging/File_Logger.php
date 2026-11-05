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
     * @var string Name of the option recording that the logs directory has been set up.
     */
    const LOGS_SETUP_OPTION = 'pressidium_cookie_consent_logs_setup';

    /**
     * @var int Revision of the one-time logs directory setup.
     */
    const LOGS_SETUP_REVISION = 1;

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
     * Drop the usual guard files into the logs directory.
     *
     * The `.htaccess` carried only `Deny from all`, which is Apache 2.2 syntax and a no-op
     * on 2.4 without `mod_access_compat`. Both directives are written now, next to an
     * empty `index.php` so a directory listing cannot enumerate the files, and a
     * `web.config` for IIS.
     *
     * None of these is sufficient on its own and none of them applies to nginx, so a
     * missing guard file is a reason to carry on rather than to take the site down. This
     * reports whether every guard is in place instead of throwing, and the caller uses
     * that to decide whether the setup may be marked as done.
     *
     * @since 2.0.0
     *
     * @param string $log_dir Path to the logs directory.
     *
     * @return bool Whether every guard file is in place.
     */
    private function protect_logs_directory( string $log_dir ): bool {
        $htaccess = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n";

        $web_config = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<configuration>\n"
            . "    <system.webServer>\n"
            . "        <authorization>\n"
            . '            <deny users="*" />' . "\n"
            . "        </authorization>\n"
            . "    </system.webServer>\n"
            . "</configuration>\n";

        $guards = array(
            '.htaccess'  => $htaccess,
            'index.php'  => "<?php\n// Silence is golden.\n",
            'web.config' => $web_config,
        );

        $all_in_place = true;

        foreach ( $guards as $filename => $contents ) {
            $path = trailingslashit( $log_dir ) . $filename;

            if ( file_exists( $path ) ) {
                if ( $filename !== '.htaccess' ) {
                    continue;
                }

                // Upgrade an existing `.htaccess` that only carries the 2.2 directive.
                $current = file_get_contents( $path );

                if ( is_string( $current ) && strpos( $current, 'Require all denied' ) !== false ) {
                    continue;
                }
            }

            if ( file_put_contents( $path, $contents ) === false ) {
                $all_in_place = false;
            }
        }

        return $all_in_place;
    }

    /**
     * Run the one-time setup of the logs directory.
     *
     * Writing the guard files is a setup task rather than a logging task, so it does not
     * belong on the path taken by every entry. Reaching the log already meant a
     * `file_exists()` on the `.htaccess` every time; doing that for three files and
     * re-reading one of them would have made it worse. An option records that the work has
     * run, which leaves the common case at a single option read and no filesystem calls.
     *
     * The marker holds a revision and the directory it applies to rather than a boolean,
     * so a later release that adds another guard file can re-run the setup by bumping the
     * constant, and a site that repoints `pressidium_cookie_consent_logs_path` at a
     * directory that already exists has that one set up as well.
     *
     * The marker is written only once the work has actually succeeded, so a failure on a
     * read-only filesystem is retried on the next entry rather than recorded as done.
     *
     * A directory created in this very request is set up regardless of the marker: it
     * carries no guard files whatever the option says, which is also the case when the
     * directory is removed from under an install that had been set up already.
     *
     * @since 2.0.0
     *
     * @param string $log_dir      Path to the logs directory.
     * @param bool   $is_fresh_dir Whether the directory was created in this request.
     *
     * @return void
     */
    private function maybe_set_up_logs_directory( string $log_dir, bool $is_fresh_dir ): void {
        $marker = self::LOGS_SETUP_REVISION . ':' . md5( $log_dir );

        if ( ! $is_fresh_dir && get_option( self::LOGS_SETUP_OPTION ) === $marker ) {
            return;
        }

        if ( ! $this->protect_logs_directory( $log_dir ) ) {
            // Leave the marker alone, so the next entry tries again
            return;
        }

        update_option( self::LOGS_SETUP_OPTION, $marker, false );
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

        $log_dir      = dirname( $log_path );
        $is_fresh_dir = false;

        if ( ! file_exists( $log_dir ) ) {
            // Attempt to create the logs directory if it doesn't exist
            $did_create = wp_mkdir_p( $log_dir );

            if ( ! $did_create ) {
                throw new RuntimeException( 'Could not create logs directory: ' . $log_dir );
            }

            $is_fresh_dir = true;
        }

        if ( ! wp_is_writable( $log_dir ) ) {
            // Directory is not writable
            throw new RuntimeException( 'Logs directory is not writable: ' . $log_dir );
        }

        $this->maybe_set_up_logs_directory( $log_dir, $is_fresh_dir );

        // No need to check if the file exists, it will be created when we log the first message
        return $log_path;
    }

    /**
     * Log a message with an arbitrary level.
     *
     * @throws InvalidArgumentException If the log level is invalid.
     * @throws InvalidArgumentException If the message is empty.
     * @throws RuntimeException         If the log file could not be written to.
     *
     * @param mixed  $level   Log level.
     * @param string $message Log message.
     * @param array  $context Any extraneous information that does not fit well in a string.
     *
     * @return void
     */
    public function log( $level, $message, array $context = array() ): void {
        $destination = $this->get_logs_path();

        if ( ! isset( self::LEVELS[ $level ] ) ) {
            throw new InvalidArgumentException( 'Invalid log level: ' . $level );
        }

        if ( empty( $message ) ) {
            throw new InvalidArgumentException( 'Empty message' );
        }

        $did_write = file_put_contents( $destination, $message . PHP_EOL, FILE_APPEND );

        if ( ! $did_write ) {
            throw new RuntimeException( 'Could not write to log file: ' . $destination );
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
