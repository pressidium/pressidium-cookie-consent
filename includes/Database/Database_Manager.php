<?php
/**
 * Database manager.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

use Pressidium\WP\CookieConsent\Options\Options;
use Pressidium\WP\CookieConsent\Logging\Logger;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Database_Manager class.
 *
 * @since 1.2.0
 */
class Database_Manager {

    /**
     * @var string Key for the table versions option.
     */
    const TABLE_VERSIONS_OPTIONS_KEY = 'pressidium_cookie_consent_table_versions';

    /**
     * @var Options An instance of the `Options` class.
     */
    private Options $options;

    /**
     * @var Logger An instance of the `Logger` class.
     */
    private Logger $logger;

    /**
     * @var Table[] Array of registered tables.
     */
    private array $tables;

    /**
     * Database_Manager constructor.
     *
     * @param Options $options An instance of the `Options` class.
     * @param Logger  $logger  An instance of the `Logger` class.
     */
    public function __construct( Options $options, Logger $logger ) {
        $this->options = $options;
        $this->logger  = $logger;

        $this->tables = array();
    }

    /**
     * Register the given table.
     *
     * @param Table $table Table to register.
     *
     * @return void
     */
    public function register_table( Table $table ): void {
        $this->tables[] = $table;
    }

    /**
     * Create the database tables.
     *
     * @return bool
     */
    public function create_tables(): bool {
        $table_versions = array();

        foreach ( $this->tables as $table ) {
            try {
                if ( $table->exists() ) {
                    // Table already exists, no need to create it
                    continue;
                }

                $table->create();

                $table_versions[ $table->get_table_slug() ] = $table->get_version();
            } catch ( Exception $exception ) {
                $this->logger->error( 'Database table(s) could not be created.' );

                return false;
            }
        }

        $this->options->set( self::TABLE_VERSIONS_OPTIONS_KEY, $table_versions );
        $this->logger->info( 'Database table(s) created successfully.' );

        return true;
    }

    /**
     * Upgrade the database tables depending on the current database version.
     *
     * Will return `false` if there was an error while upgrading, or there was nothing to upgrade.
     *
     * @return bool
     */
    public function maybe_upgrade_tables(): bool {
        $table_versions = $this->options->get( self::TABLE_VERSIONS_OPTIONS_KEY );
        $did_upgrade    = false;

        foreach ( $this->tables as $table ) {
            try {
                $new_version     = $table->get_version();
                $current_version = $table_versions[ $table->get_table_slug() ] ?? null;

                if ( $current_version && version_compare( $new_version, $current_version, '<=' ) ) {
                    // Table is already up to date, no need to upgrade it
                    continue;
                }

                $table->create();

                $table_versions[ $table->get_table_slug() ] = $new_version;
                $did_upgrade                                = true;

                $this->logger->info( "Database table {$table->get_table_slug()} upgraded" );
            } catch ( Exception $exception ) {
                $this->logger->error( 'Database table(s) could not be upgraded.' );

                return false;
            }
        }

        if ( $did_upgrade ) {
            $this->options->set( self::TABLE_VERSIONS_OPTIONS_KEY, $table_versions );
        }

        return $did_upgrade;
    }

}
