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
        // Migrate log files to the new location
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

        // Google tag gateway defaults
        $default_tag_gateway = array(
            'proxy_enabled' => false,
            'gtag_id'       => '',
        );

        $tag_gateway = $this->settings['pressidium_options']['google_tag_gateway'] ?? $default_tag_gateway;

        $this->settings['pressidium_options']['google_tag_gateway'] = $tag_gateway;

        /*
         * Previously AI translations did not include the toggles in each language,
         * so we need to migrate them from the default language to fix any issues.
         */
        $default_lang = array_key_first( $this->settings['languages'] );

        $necessary_toggle   = $this->settings['languages'][ $default_lang ]['settings_modal']['blocks'][1]['toggle'] ?? array();
        $analytics_toggle   = $this->settings['languages'][ $default_lang ]['settings_modal']['blocks'][2]['toggle'] ?? array();
        $targeting_toggle   = $this->settings['languages'][ $default_lang ]['settings_modal']['blocks'][3]['toggle'] ?? array();
        $preferences_toggle = $this->settings['languages'][ $default_lang ]['settings_modal']['blocks'][4]['toggle'] ?? array();

        foreach ( $this->settings['languages'] as $lang => $lang_settings ) {
            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][1]['toggle'] = $necessary_toggle;
            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][2]['toggle'] = $analytics_toggle;
            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][3]['toggle'] = $targeting_toggle;
            $this->settings['languages'][ $lang ]['settings_modal']['blocks'][4]['toggle'] = $preferences_toggle;
        }
    }

    /**
     * Migrate settings coming from versions prior to 2.0.0.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return void
     */
    private function migrate_2_0_0(): void {
        // Rename top-level options
        $this->settings['autoShow']               = $this->settings['autorun']           ?? true;
        $this->settings['disablePageInteraction'] = $this->settings['force_consent']     ?? false;
        $this->settings['autoClearCookies']       = $this->settings['autoclear_cookies'] ?? false;
        $this->settings['manageScriptTags']       = $this->settings['page_scripts']      ?? false;
        $this->settings['hideFromBots']           = $this->settings['hide_from_bots']    ?? true;

        unset(
            $this->settings['autorun'],
            $this->settings['force_consent'],
            $this->settings['autoclear_cookies'],
            $this->settings['page_scripts'],
            $this->settings['hide_from_bots'],
            $this->settings['delay']
        );

        // Consolidate cookie fields into a `cookie` object
        $this->settings['cookie'] = array(
            'expiresAfterDays' => $this->settings['cookie_expiration'] ?? 182,
            'path'             => $this->settings['cookie_path']       ?? '/',
            'domain'           => $this->settings['cookie_domain']     ?? '',
            'name'             => $this->settings['cookie_name']       ?? 'pressidium_cookie_consent',
        );

        unset(
            $this->settings['cookie_expiration'],
            $this->settings['cookie_path'],
            $this->settings['cookie_domain'],
            $this->settings['cookie_name']
        );

        // Build top-level `categories` object from per-language block toggles
        $default_lang = array_key_first( $this->settings['languages'] ?? array() );
        $blocks       = $this->settings['languages'][ $default_lang ]['settings_modal']['blocks'] ?? array();

        $toggle_map = array();
        foreach ( $blocks as $block ) {
            if ( isset( $block['toggle']['value'] ) ) {
                $toggle_map[ $block['toggle']['value'] ] = $block['toggle'];
            }
        }

        $this->settings['categories'] = array(
            'necessary'   => array(
                'enabled'  => $toggle_map['necessary']['enabled']  ?? true,
                'readOnly' => $toggle_map['necessary']['readonly'] ?? true,
            ),
            'analytics'   => array(
                'enabled'  => $toggle_map['analytics']['enabled']  ?? false,
                'readOnly' => $toggle_map['analytics']['readonly'] ?? false,
            ),
            'targeting'   => array(
                'enabled'  => $toggle_map['targeting']['enabled']  ?? false,
                'readOnly' => $toggle_map['targeting']['readonly'] ?? false,
            ),
            'preferences' => array(
                'enabled'  => $toggle_map['preferences']['enabled']  ?? false,
                'readOnly' => $toggle_map['preferences']['readonly'] ?? false,
            ),
        );

        // Restructure `languages` into a `language` object
        $old_languages        = $this->settings['languages'] ?? array();
        $default_lang         = array_key_first( $old_languages );
        $new_translations     = array();
        $cookie_table_headers = array( 'translations' => array() );

        foreach ( $old_languages as $lang => $lang_settings ) {
            $consent_modal  = $lang_settings['consent_modal']  ?? array();
            $settings_modal = $lang_settings['settings_modal'] ?? array();
            $old_blocks     = $settings_modal['blocks']        ?? array();

            $new_consent_modal = array(
                'title'              => $consent_modal['title']                 ?? '',
                'description'        => $consent_modal['description']           ?? '',
                'acceptAllBtn'       => $consent_modal['primary_btn']['text']   ?? 'Accept all',
                'acceptNecessaryBtn' => $consent_modal['secondary_btn']['text'] ?? 'Accept necessary',
                'showPreferencesBtn' => 'Show preferences',
                'closeIconLabel'     => 'Close',
                'footer'             => '<a href="#link">Privacy Policy</a><a href="#link">Terms and conditions</a>',
                'footerLinks'        => array(
                    array( 'url' => '#link', 'label' => 'Privacy Policy' ),
                    array( 'url' => '#link', 'label' => 'Terms and conditions' ),
                ),
            );

            $new_sections = array();
            foreach ( $old_blocks as $block ) {
                $section = array(
                    'title'       => $block['title']       ?? '',
                    'description' => $block['description'] ?? '',
                );
                if ( isset( $block['toggle']['value'] ) ) {
                    $section['linkedCategory'] = $block['toggle']['value'];
                }
                $new_sections[] = $section;
            }

            $new_preferences_modal = array(
                'title'              => $settings_modal['title']             ?? 'Cookie preferences',
                'savePreferencesBtn' => $settings_modal['save_settings_btn'] ?? 'Save preferences',
                'acceptAllBtn'       => $settings_modal['accept_all_btn']    ?? 'Accept all',
                'acceptNecessaryBtn' => $settings_modal['reject_all_btn']    ?? 'Accept necessary',
                'closeIconLabel'     => $settings_modal['close_btn_label']   ?? 'Close',
                'sections'          => $new_sections,
            );

            $new_translations[ $lang ] = array(
                'consentModal'     => $new_consent_modal,
                'preferencesModal' => $new_preferences_modal,
            );

            $raw_headers  = $settings_modal['cookie_table_headers'] ?? array();
            $flat_headers = array();
            foreach ( $raw_headers as $header_item ) {
                foreach ( $header_item as $key => $value ) {
                    $flat_headers[ $key ] = $value;
                }
            }
            if ( ! empty( $flat_headers ) ) {
                $cookie_table_headers['translations'][ $lang ] = $flat_headers;
            }
        }

        $this->settings['language'] = array(
            'default'      => $default_lang,
            'autoDetect'   => $this->settings['auto_language'] ?? 'browser',
            'translations' => $new_translations,
        );

        unset( $this->settings['languages'], $this->settings['auto_language'] );

        // Rename `gui_options` → `guiOptions`
        $old_gui     = $this->settings['gui_options'] ?? array();
        $old_consent = $old_gui['consent_modal']      ?? array();
        $old_pref    = $old_gui['settings_modal']     ?? array();

        $this->settings['guiOptions'] = array(
            'consentModal'     => array(
                'layout'             => $old_consent['layout']       ?? 'box',
                'position'           => $old_consent['position']     ?? 'bottom right',
                'equalWeightButtons' => false,
                'flipButtons'        => $old_consent['swap_buttons'] ?? false,
            ),
            'preferencesModal' => array(
                'layout'             => $old_pref['layout']   ?? 'box',
                'position'           => $old_pref['position'] ?? 'left',
                'equalWeightButtons' => false,
                'flipButtons'        => false,
            ),
        );

        unset( $this->settings['gui_options'] );

        // Rename `pressidium_options` → `pressidiumOptions`
        $old_opts    = $this->settings['pressidium_options']  ?? array();
        $old_colors  = $old_opts['colors']              ?? array();
        $old_gcm     = $old_opts['gcm']                 ?? array();
        $old_gateway = $old_opts['google_tag_gateway']  ?? array();

        $new_colors = array(
            'bg'                             => $old_colors['bg']                             ?? '#f9faff',
            'primary-color'                  => $old_colors['text']                           ?? '#112954',
            'btn-primary-bg'                 => $old_colors['btn-primary-bg']                 ?? '#3859d0',
            'btn-primary-color'              => $old_colors['btn-primary-text']               ?? '#f9faff',
            'btn-primary-hover-bg'           => $old_colors['btn-primary-hover-bg']           ?? '#1d2e38',
            'btn-primary-hover-color'        => $old_colors['btn-primary-hover-text']         ?? '#f9faff',
            'btn-secondary-bg'               => $old_colors['btn-secondary-bg']               ?? '#dfe7f9',
            'btn-secondary-color'            => $old_colors['btn-secondary-text']             ?? '#112954',
            'btn-secondary-hover-bg'         => $old_colors['btn-secondary-hover-bg']         ?? '#c6d1ea',
            'btn-secondary-hover-color'      => $old_colors['btn-secondary-hover-text']       ?? '#112954',
            'toggle-off-bg'                  => $old_colors['toggle-bg-off']                  ?? '#8fa8d6',
            'toggle-on-knob-bg'              => $old_colors['toggle-bg-on']                   ?? '#3859d0',
            'toggle-readonly-bg'             => $old_colors['toggle-bg-readonly']             ?? '#cbd8f1',
            'toggle-knob-bg'                 => $old_colors['toggle-knob-bg']                 ?? '#fff',
            'toggle-knob-icon-color'         => $old_colors['toggle-knob-icon-color']         ?? '#ecf2fa',
            'cookie-category-block-bg'       => $old_colors['cookie-category-block-bg']       ?? '#ebeff9',
            'cookie-category-block-hover-bg' => $old_colors['cookie-category-block-bg-hover'] ?? '#dbe5f9',
            'separator-border-color'         => $old_colors['section-border']                 ?? '#f1f3f5',
            'block-text'                     => $old_colors['block-text']                     ?? '#112954',
            'cookie-table-border'            => $old_colors['cookie-table-border']            ?? '#e1e7f3',
            'overlay-bg'                     => $old_colors['overlay-bg']                     ?? 'rgba(230, 235, 255, .85)',
            'webkit-scrollbar-bg'            => $old_colors['webkit-scrollbar-bg']            ?? '#ebeff9',
            'webkit-scrollbar-bg-hover'      => $old_colors['webkit-scrollbar-bg-hover']      ?? '#3859d0',
            'btn-floating-bg'                => $old_colors['btn-floating-bg']                ?? '#3859d0',
            'btn-floating-icon'              => $old_colors['btn-floating-icon']              ?? '#f9faff',
            'btn-floating-hover-bg'          => $old_colors['btn-floating-hover-bg']          ?? '#1d2e38',
            'btn-floating-hover-icon'        => $old_colors['btn-floating-hover-icon']        ?? '#f9faff',
        );

        $this->settings['pressidiumOptions'] = array(
            'cookieTable'            => $old_opts['cookie_table'] ?? array(
                'necessary'   => array(),
                'analytics'   => array(),
                'targeting'   => array(),
                'preferences' => array(),
            ),
            'cookieTableHeaders'     => $cookie_table_headers,
            'consentModalCloseIcon'  => true,
            'showConsentModalFooter' => true,
            'blockedScripts'         => $old_opts['blocked_scripts']      ?? array(),
            'font'                   => $old_opts['font']                 ?? array(
                'name'   => 'Default',
                'slug'   => 'default',
                'family' => 'inherit',
            ),
            'floatingButton'         => $old_opts['floating_button']      ?? array(
                'enabled'    => false,
                'size'       => 'sm',
                'position'   => 'left',
                'icon'       => 'pressidium',
                'transition' => 'fade-in-up',
            ),
            'colors'                 => $new_colors,
            'recordConsents'         => $old_opts['record_consents']       ?? true,
            'hideEmptyCategories'    => $old_opts['hide_empty_categories'] ?? false,
            'gcm'                    => array(
                'enabled'          => $old_gcm['enabled']            ?? false,
                'implementation'   => $old_gcm['implementation']     ?? 'gtag',
                'adsDataRedaction' => $old_gcm['ads_data_redaction'] ?? false,
                'urlPassthrough'   => $old_gcm['url_passthrough']    ?? false,
                'regions'          => $old_gcm['regions']            ?? array(),
            ),
            'googleTagGateway'       => array(
                'proxyEnabled' => $old_gateway['proxy_enabled'] ?? false,
                'gtagId'       => $old_gateway['gtag_id']       ?? '',
            ),
            'ai'                     => $old_opts['ai'] ?? array(
                'provider' => 'openai',
                'model'    => 'gpt-3.5-turbo',
            ),
        );

        unset( $this->settings['pressidium_options'] );
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

        if ( version_compare( $this->settings['version'], '2.0.0', '<' ) ) {
            // We are upgrading from a version prior to 2.0.0, so we need to migrate the settings
            $this->migrate_2_0_0();
        }

        return $this->settings;
    }

}
