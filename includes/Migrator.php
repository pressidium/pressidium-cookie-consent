<?php
/**
 * Migrator.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use Pressidium\WP\CookieConsent\Logging\File_Logger;

use WP_Filesystem_Direct;

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
     * Migrate settings coming from versions prior to 1.5.0.
     *
     * @return void
     */
    private function migrate_1_5_0(): void {
        // Hide empty categories
        $hide_empty_categories = $this->settings['pressidium_options']['hide_empty_categories'] ?? false;

        $this->settings['pressidium_options']['hide_empty_categories'] = $hide_empty_categories;

        // Font
        $default_font = array(
            'name'   => 'Default',
            'slug'   => 'default',
            'family' => 'inherit',
        );

        $font = $this->settings['pressidium_options']['font'] ?? $default_font;

        $this->settings['pressidium_options']['font'] = $font;

        // Floating button
        $default_floating_button = array(
            'enabled'    => true,
            'size'       => 'sm',
            'position'   => 'left',
            'icon'       => 'pressidium',
            'transition' => 'fade-in-up',
        );

        $floating_button = $this->settings['pressidium_options']['floating_button'] ?? $default_floating_button;

        $this->settings['pressidium_options']['floating_button'] = $floating_button;

        // Floating button colors
        $colors = $this->settings['pressidium_options']['colors'] ?? array();

        $btn_bg         = $colors['btn-floating-bg'] ?? $colors['btn-primary-bg'];
        $btn_icon       = $colors['btn-floating-icon'] ?? $colors['btn-primary-text'];
        $btn_hover_bg   = $colors['btn-floating-hover-bg'] ?? $colors['btn-primary-hover-bg'];
        $btn_hover_icon = $colors['btn-floating-hover-icon'] ?? $colors['btn-primary-hover-text'];

        $this->settings['pressidium_options']['colors']['btn-floating-bg']         = $btn_bg;
        $this->settings['pressidium_options']['colors']['btn-floating-icon']       = $btn_icon;
        $this->settings['pressidium_options']['colors']['btn-floating-hover-bg']   = $btn_hover_bg;
        $this->settings['pressidium_options']['colors']['btn-floating-hover-icon'] = $btn_hover_icon;
    }

    /**
     * Migrate settings coming from versions prior to 1.7.0.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return void
     */
    private function migrate_1_7_0(): void {
        $lang_codes_mapping = array(
            'be'    => 'bel',
            'bg'    => 'bg-BG',
            'bn'    => 'bn-BD',
            'cs'    => 'cs-CZ',
            'da'    => 'da-DK',
            'en-ZA' => 'en-SA',
            'gl'    => 'gl-ES',
            'gu'    => 'gu-IN',
            'he'    => 'he-IL',
            'hi'    => 'hi-IN',
            'hu'    => 'hu-HU',
            'id'    => 'id-ID',
            'is'    => 'is-IS',
            'it'    => 'it-IT',
            'ka'    => 'ka-GE',
            'kl'    => 'kal',
            'ko'    => 'ko-KR',
            'ky'    => 'ky-KY',
            'ln'    => 'lin',
            'lt'    => 'lt-LT',
            'mg'    => 'mg-MG',
            'mi'    => 'mri',
            'ml'    => 'ml-IN',
            'ms'    => 'ms-MY',
            'mt'    => 'mlt',
            'my'    => 'my-MM',
            'nl'    => 'nl-NL',
            'pa'    => 'pa-IN',
            'pl'    => 'pl-PL',
            'ro'    => 'ro-RO',
            'ru'    => 'ru-RU',
            'rw'    => 'kin',
            'sd'    => 'sd-PK',
            'sk'    => 'sk-SK',
            'sl'    => 'sl-SI',
            'sn'    => 'sna',
            'so'    => 'so-SO',
            'sr'    => 'sr-RS',
            'su'    => 'su-ID',
            'sv'    => 'sv-SE',
            'ta'    => 'ta-IN',
            'tk'    => 'tuk',
            'tr'    => 'tr-TR',
            'uz'    => 'uz-UZ',
            'xh'    => 'xho',
            'yo'    => 'yor',
        );

        foreach ( $this->settings['languages'] as $lang => $lang_settings ) {
            if ( ! array_key_exists( $lang, $lang_codes_mapping ) ) {
                continue;
            }

            $this->settings['languages'][ $lang_codes_mapping[ $lang ] ] = $lang_settings;
            unset( $this->settings['languages'][ $lang ] );
        }
    }

    /**
     * Migrate settings coming from versions prior to 1.8.0.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return void
     */
    private function migrate_1_8_0(): void {
        $default_ai = array(
            'provider' => 'openai',
            'model'    => 'gpt-3.5-turbo',
        );

        $ai = $this->settings['pressidium_options']['ai'] ?? $default_ai;

        $this->settings['pressidium_options']['ai'] = $ai;
    }

    /**
     * Migrate settings coming from versions prior to 1.9.0.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return void
     */
    private function migrate_1_9_0(): void {
        $previous_logs_path = PLUGIN_DIR . 'logs/error.log';
        $new_logs_path      = ( new File_Logger() )->get_logs_path();

        if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        }

        $filesystem = new WP_Filesystem_Direct( null );

        if ( $filesystem->exists( $previous_logs_path ) && ! $filesystem->exists( $new_logs_path ) ) {
            // Move the logs to the new location
            $filesystem->move( $previous_logs_path, $new_logs_path );
        }

        $previous_logs_dir = dirname( $previous_logs_path );

        if ( $filesystem->exists( $previous_logs_dir ) ) {
            // Delete the old logs directory
            $filesystem->rmdir( $previous_logs_dir, true );
        }
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

        if ( version_compare( $this->settings['version'], '1.5.0', '<' ) ) {
            // We are upgrading from a version prior to 1.5.0, so we need to migrate the settings
            $this->migrate_1_5_0();
        }

        if ( version_compare( $this->settings['version'], '1.7.0', '<' ) ) {
            // We are upgrading from a version prior to 1.7.0, so we need to migrate the settings
            $this->migrate_1_7_0();
        }

        if ( version_compare( $this->settings['version'], '1.8.0', '<' ) ) {
            // We are upgrading from a version prior to 1.8.0, so we need to migrate the settings
            $this->migrate_1_8_0();
        }

        if ( version_compare( $this->settings['version'], '1.9.0', '<' ) ) {
            // We are upgrading from a version prior to 1.9.0, so we need to migrate the settings
            $this->migrate_1_9_0();
        }

        return $this->settings;
    }

}
