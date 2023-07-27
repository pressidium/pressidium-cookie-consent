<?php
/**
 * Migrator.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Migrator class.
 *
 * @since 1.1.2
 */
class Migrator {

    /**
     * @var array Settings.
     */
    private array $settings;

    /**
     * Migrator constructor.
     *
     * @param array $settings Settings to migrate.
     */
    public function __construct( array $settings = array() ) {
        $this->settings = $settings;
    }

    /**
     * Migrate settings coming from versions prior to 1.1.2.
     *
     * @return void
     */
    private function migrate_1_1_2(): void {
        foreach ( $this->settings['languages'] as $lang => $lang_settings ) {
            $table = $this->settings['languages'][ $lang ]['settings_modal']['blocks'][1]['cookie_table'] ?? array();

            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][1]['cookie_table'] = $table;
        }

        $necessary_table = $this->settings['pressidium_options']['cookie_table']['necessary'] ?? array();

        $this->settings['pressidium_options']['cookie_table']['necessary'] = $necessary_table;
    }

    /**
     * Migrate settings if necessary.
     *
     * @return array Migrated settings.
     */
    public function maybe_migrate(): array {
        if ( ! isset( $this->settings['version'] ) ) {
            // We do not have a version, so we assume that we are not upgrading from a previous version
            return $this->settings;
        }

        if ( version_compare( $this->settings['version'], '1.1.2', '<' ) ) {
            // We are upgrading from a version prior to 1.1.2, so we need to migrate the settings
            $this->migrate_1_1_2();
        }

        return $this->settings;
    }

}
