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
     * Migrate settings coming from versions prior to 1.4.0.
     *
     * @return void
     */
    private function migrate_1_4_0(): void {
        // Preferences cookies
        foreach ( $this->settings['languages'] as $lang => $lang_settings ) {
            if ( count( $this->settings['languages'][ $lang ]['settings_modal']['blocks'] ) >= 6 ) {
                // Preferences block exists, so we do not need to migrate
                continue;
            }

            $more_info_block = $this->settings['languages'][ $lang ]['settings_modal']['blocks'][4];

            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][5] = $more_info_block;
            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][4] = array(
                'title'        => '',
                'description'  => '',
                'toggle'       => array(
                    'value'    => 'preferences',
                    'enabled'  => false,
                    'readonly' => false,
                ),
                'cookie_table' => array(),
            );
        }

        if ( isset( $this->settings['languages']['en'] ) ) {
            $default_title       = 'Functionality and Preferences cookies';
            $default_description = 'These cookies allow us to provide enhanced functionality and personalization by storing user preferences.';

            $current_title       = $this->settings['languages']['en']['settings_modal']['blocks'][4]['title'];
            $current_description = $this->settings['languages']['en']['settings_modal']['blocks'][4]['description'];

            $this->settings['languages']['en']['settings_modal']['blocks'][4]['title']       = empty( $current_title ) ? $default_title : $current_title;
            $this->settings['languages']['en']['settings_modal']['blocks'][4]['description'] = empty( $current_description ) ? $default_description : $current_description;
        }

        $preferences_cookie_table = $this->settings['pressidium_options']['cookie_table']['preferences'] ?? array();

        $this->settings['pressidium_options']['cookie_table']['preferences'] = $preferences_cookie_table;

        // GCM
        $default_gcm = array(
            'enabled'            => false,
            'implementation'     => 'gtag',
            'ads_data_redaction' => false,
            'url_passthrough'    => false,
            'regions'            => array(),
        );

        $gcm = $this->settings['pressidium_options']['gcm'] ?? $default_gcm;

        $this->settings['pressidium_options']['gcm'] = $gcm;
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

        if ( version_compare( $this->settings['version'], '1.4.0', '<' ) ) {
            // We are upgrading from a version prior to 1.4.0, so we need to migrate the settings
            $this->migrate_1_4_0();
        }

        return $this->settings;
    }

}
