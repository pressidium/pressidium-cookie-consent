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
     * @var string Name of the option holding the random suffix of the log file.
     */
    const LOG_SUFFIX_OPTION = 'pressidium_cookie_consent_log_suffix';

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
     * Return the log file name, which carries an unguessable suffix.
     *
     * Blocking the directory is not enough on its own, because `.htaccess` is only
     * read by Apache. On nginx it is ignored entirely, `web.config` applies to IIS,
     * and `index.php` only hides a directory listing rather than a known file name.
     * A predictable `error.log` under a predictable directory is therefore reachable
     * over HTTP on a large share of installs, and the consent log contains visitor
     * IP addresses and user agents.
     *
     * Adding a random suffix to the file name closes that regardless of the web
     * server, and without asking the site owner to configure anything. The suffix is
     * generated once and stored, so the path stays stable across requests.
     *
     * @since 2.0.0
     *
     * @return string
     */
    private function get_log_filename(): string {
        $suffix = get_option( self::LOG_SUFFIX_OPTION );

        if ( is_string( $suffix ) && preg_match( '/^[a-f0-9]{16}$/', $suffix ) ) {
            return 'error-' . $suffix . '.log';
        }

        $suffix = substr(
            hash( 'sha256', wp_generate_password( 32, false, false ) . wp_salt( 'nonce' ) ),
            0,
            16
        );

        /*
         * `add_option()` rather than `update_option()`: if two requests reach this
         * at the same time, the first one wins and both end up using the same name.
         */
        if ( ! add_option( self::LOG_SUFFIX_OPTION, $suffix, '', false ) ) {
            $stored = get_option( self::LOG_SUFFIX_OPTION );

            if ( is_string( $stored ) && $stored !== '' ) {
                $suffix = $stored;
            }
        }

        return 'error-' . $suffix . '.log';
    }

    /**
     * Drop the usual guard files into the logs directory.
     *
     * None of these is sufficient on its own, which is why the log file name is
     * unguessable as well, but together they cover the common server setups:
     * `.htaccess` for Apache (both the 2.2 and the 2.4 directive, since
     * `Deny from all` alone is a no-op on 2.4 without `mod_access_compat`),
     * `web.config` for IIS, and an empty `index.php` so a directory listing cannot
     * enumerate the files.
     *
     * Failures here are deliberately not fatal. A missing guard file is a reason to
     * carry on, not to take the site down, so this reports whether every guard is in
     * place rather than throwing, and the caller uses that to decide whether the
     * setup may be marked as done.
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
     * Move an existing `error.log` to the current, suffixed file name.
     *
     * Installs created before the suffix existed keep their history in a file whose
     * name is public knowledge. Moving it preserves the entries and retires the
     * predictable name in one step.
     *
     * @since 2.0.0
     *
     * @param string $log_dir  Path to the logs directory.
     * @param string $log_path Path to the current log file.
     *
     * @return bool Whether the predictable file name is no longer in use.
     */
    private function maybe_move_legacy_log( string $log_dir, string $log_path ): bool {
        $legacy_path = trailingslashit( $log_dir ) . 'error.log';

        if ( $legacy_path === $log_path || ! file_exists( $legacy_path ) ) {
            // Nothing to retire
            return true;
        }

        if ( file_exists( $log_path ) ) {
            /*
             * Both names are in use, which `get_log_filename()` does not produce on
             * its own, so the log path is filtered. Appending one file to the other
             * is not ours to decide, so leave both alone and stop retrying.
             */
            return true;
        }

        return rename( $legacy_path, $log_path );
    }

    /**
     * Run the one-time setup of the logs directory.
     *
     * Writing the guard files and retiring a legacy log file are setup tasks rather
     * than logging tasks, so they do not belong on the path taken by every entry. An
     * option records that they have run, which leaves the common case at a single
     * option read instead of several filesystem calls per entry.
     *
     * The marker holds a revision and the directory it applies to rather than a
     * boolean, so a later release that adds another guard file can re-run the setup by
     * bumping the constant, and a site that repoints
     * `pressidium_cookie_consent_logs_path` at a directory that already exists has it
     * set up as well.
     *
     * The marker is written only once the work has actually succeeded. Both steps
     * report whether they did, so a failure (a read-only filesystem, a directory we
     * may create but not write into) is retried on the next entry instead of being
     * recorded as done.
     *
     * A directory created in this very request is set up regardless of the marker:
     * it carries no guard files whatever the option says, which is also the case when
     * the directory is removed from under an install that had been set up already.
     *
     * @since 2.0.0
     *
     * @param string $log_dir      Path to the logs directory.
     * @param string $log_path     Path to the current log file.
     * @param bool   $is_fresh_dir Whether the directory was created in this request.
     *
     * @return void
     */
    private function maybe_set_up_logs_directory( string $log_dir, string $log_path, bool $is_fresh_dir ): void {
        $marker = self::LOGS_SETUP_REVISION . ':' . md5( $log_dir );

        if ( ! $is_fresh_dir && get_option( self::LOGS_SETUP_OPTION ) === $marker ) {
            return;
        }

        $did_protect = $this->protect_logs_directory( $log_dir );
        $did_retire  = $this->maybe_move_legacy_log( $log_dir, $log_path );

        if ( ! $did_protect || ! $did_retire ) {
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
        $log_path    = trailingslashit( $uploads_dir ) . 'pressidium-cookie-consent/logs/' . $this->get_log_filename();

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

        $this->maybe_set_up_logs_directory( $log_dir, $log_path, $is_fresh_dir );

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
