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
     * Migrate settings coming from versions prior to 1.2.0.
     *
     * @return void
     */
    private function migrate_1_2_0(): void {
        $record_consents = $this->settings['pressidium_options']['record_consents'] ?? true;

        $this->settings['pressidium_options']['record_consents'] = $record_consents;
    }

    /**
     * Migrate settings coming from versions prior to 1.3.0.
     *
     * @return void
     */
    private function migrate_1_3_0(): void {
        $colors = $this->settings['pressidium_options']['colors'] ?? array();

        $primary_hover   = $colors['btn-primary-hover-text'] ?? $colors['btn-primary-text'];
        $secondary_hover = $colors['btn-secondary-hover-text'] ?? $colors['btn-secondary-text'];

        $this->settings['pressidium_options']['colors']['btn-primary-hover-text']   = $primary_hover;
        $this->settings['pressidium_options']['colors']['btn-secondary-hover-text'] = $secondary_hover;
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

        if ( version_compare( $this->settings['version'], '1.2.0', '<' ) ) {
            // We are upgrading from a version prior to 1.2.0, so we need to migrate the settings
            $this->migrate_1_2_0();
        }

        if ( version_compare( $this->settings['version'], '1.3.0', '<' ) ) {
            // We are upgrading from a version prior to 1.3.0, so we need to migrate the settings
            $this->migrate_1_3_0();
        }

        return $this->settings;
    }

}
