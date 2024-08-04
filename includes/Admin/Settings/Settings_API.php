<?php
/**
 * Settings API.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Admin\Settings;

use Pressidium\WP\CookieConsent\Database\Exporter;
use const Pressidium\WP\CookieConsent\VERSION;

use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Settings;
use Pressidium\WP\CookieConsent\Migrator;
use Pressidium\WP\CookieConsent\Emoji;
use Pressidium\WP\CookieConsent\Logs;
use Pressidium\WP\CookieConsent\Geo_Locator;
use Pressidium\WP\CookieConsent\Database\Tables\Consents_Table;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Settings_API class.
 *
 * @since 1.0.0
 */
class Settings_API implements Actions {

    /**
     * @var string REST route namespace.
     */
    const REST_NAMESPACE = 'pressidium-cookie-consent/v1';

    /**
     * @var Settings Instance of `Settings`.
     */
    private Settings $settings;

    /**
     * @var Logger Instance of `Logger`.
     */
    private Logger $logger;

    /**
     * @var Logs Instance of `Logs`.
     */
    private Logs $logs;

    /**
     * @var Geo_Locator Instance of `Geo_Locator`.
     */
    private Geo_Locator $geo_locator;

    /**
     * @var Consents_Table Instance of `Consents_Table`.
     */
    private Consents_Table $consents_table;

    /**
     * @var Exporter Instance of `Exporter`.
     */
    private Exporter $exporter;

    /**
     * Settings_API constructor.
     *
     * @param Settings    $settings
     * @param Logger      $logger
     * @param Logs        $logs
     * @param Geo_Locator $geo_locator
     * @param Exporter    $exporter
     */
    public function __construct(
        Settings $settings,
        Logger $logger,
        Logs $logs,
        Geo_Locator $geo_locator,
        Consents_Table $consents_table,
        Exporter $exporter
    ) {
        $this->settings       = $settings;
        $this->logger         = $logger;
        $this->logs           = $logs;
        $this->geo_locator    = $geo_locator;
        $this->consents_table = $consents_table;
        $this->exporter       = $exporter;
    }

    /**
     * Return the settings schema to sanitize the request.
     *
     * @link https://developer.wordpress.org/reference/functions/rest_sanitize_value_from_schema/
     *
     * @return array
     */
    private function get_settings_schema(): array {
        return array(
            'type' => 'object',
            'required' => array(
                'autorun',
                'force_consent',
                'autoclear_cookies',
                'page_scripts',
                'hide_from_bots',
                'reconsent',
                'delay',
                'cookie_expiration',
                'cookie_path',
                'cookie_domain',
                'auto_language',
                'languages',
                'gui_options',
                'pressidium_options',
            ),
            'properties' => array(
                'autorun' => array(
                    'type' => 'boolean',
                ),
                'force_consent' => array(
                    'type' => 'boolean',
                ),
                'autoclear_cookies' => array(
                    'type' => 'boolean',
                ),
                'page_scripts' => array(
                    'type' => 'boolean',
                ),
                'hide_from_bots' => array(
                    'type' => 'boolean',
                ),
                'reconsent' => array(
                    'type' => 'boolean',
                ),
                'delay' => array(
                    'type' => 'integer',
                ),
                'cookie_expiration' => array(
                    'type' => 'integer',
                ),
                'cookie_path' => array(
                    'type' => 'string',
                ),
                'cookie_domain' => array(
                    'type' => 'string',
                ),
                'auto_language' => array(
                    'type' => 'string',
                ),
                'languages' => array(
                    'type' => 'object',
                    'properties' => array(
                        '[a-zA-Z]' => array(
                            'type' => 'object',
                            'required' => array(
                                'consent_modal',
                                'settings_modal',
                            ),
                            'properties' => array(
                                'consent_modal' => array(
                                    'type' => 'object',
                                    'required' => array(
                                        'title',
                                        'description',
                                        'primary_btn',
                                        'secondary_btn',
                                    ),
                                    'properties' => array(
                                        'title' => array(
                                            'type' => 'string',
                                        ),
                                        'description' => array(
                                            'type' => 'string',
                                        ),
                                        'primary_btn' => array(
                                            'type' => 'object',
                                            'required' => array(
                                                'text',
                                                'role',
                                            ),
                                            'properties' => array(
                                                'text' => array(
                                                    'type' => 'string',
                                                ),
                                                'role' => array(
                                                    'type' => 'string',
                                                ),
                                            ),
                                        ),
                                        'secondary_btn' => array(
                                            'type' => 'object',
                                            'required' => array(
                                                'text',
                                                'role',
                                            ),
                                            'properties' => array(
                                                'text' => array(
                                                    'type' => 'string',
                                                ),
                                                'role' => array(
                                                    'type' => 'string',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                                'settings_modal' => array(
                                    'type' => 'object',
                                    'required' => array(
                                        'title',
                                        'save_settings_btn',
                                        'accept_all_btn',
                                        'reject_all_btn',
                                        'close_btn_label',
                                        'cookie_table_headers',
                                        'blocks',
                                    ),
                                    'properties' => array(
                                        'title' => array(
                                            'type' => 'string',
                                        ),
                                        'save_settings_btn' => array(
                                            'type' => 'string',
                                        ),
                                        'accept_all_btn' => array(
                                            'type' => 'string',
                                        ),
                                        'reject_all_btn' => array(
                                            'type' => 'string',
                                        ),
                                        'close_btn_label' => array(
                                            'type' => 'string',
                                        ),
                                        'cookie_table_headers' => array(
                                            'type' => 'array',
                                            'items' => array(
                                                'type' => 'object',
                                                'required' => array(
                                                    'name',
                                                    'domain',
                                                    'expiration',
                                                    'path',
                                                    'description',
                                                ),
                                                'properties' => array(
                                                    'name' => array(
                                                        'type' => 'string',
                                                    ),
                                                    'domain' => array(
                                                        'type' => 'string',
                                                    ),
                                                    'expiration' => array(
                                                        'type' => 'string',
                                                    ),
                                                    'path' => array(
                                                        'type' => 'string',
                                                    ),
                                                    'description' => array(
                                                        'type' => 'string',
                                                    ),
                                                ),
                                            ),
                                        ),
                                        'blocks' => array(
                                            'type' => 'array',
                                            'items' => array(
                                                'type' => 'object',
                                                'required' => array(
                                                    'title',
                                                    'description',
                                                ),
                                                'properties' => array(
                                                    'title' => array(
                                                        'type' => 'string',
                                                    ),
                                                    'description' => array(
                                                        'type' => 'string',
                                                    ),
                                                    'toggle' => array(
                                                        'type' => 'object',
                                                        'required' => array(
                                                            'value',
                                                            'enabled',
                                                            'readonly',
                                                        ),
                                                        'properties' => array(
                                                            'value' => array(
                                                                'type' => 'string',
                                                            ),
                                                            'enabled' => array(
                                                                'type' => 'boolean',
                                                            ),
                                                            'readonly' => array(
                                                                'type' => 'boolean',
                                                            ),
                                                        ),
                                                    ),
                                                    'cookie_table' => array(
                                                        'type' => 'array',
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'gui_options' => array(
                    'type' => 'object',
                    'required' => array(
                        'consent_modal',
                        'settings_modal',
                    ),
                    'properties' => array(
                        'consent_modal' => array(
                            'type' => 'object',
                            'required' => array(
                                'layout',
                                'position',
                                'transition',
                                'swap_buttons',
                            ),
                            'properties' => array(
                                'layout' => array(
                                    'type' => 'string',
                                ),
                                'position' => array(
                                    'type' => 'string',
                                ),
                                'transition' => array(
                                    'type' => 'string',
                                ),
                                'swap_buttons' => array(
                                    'type' => 'boolean',
                                ),
                            ),
                        ),
                        'settings_modal' => array(
                            'type' => 'object',
                            'required' => array(
                                'layout',
                                'position',
                                'transition',
                            ),
                            'properties' => array(
                                'layout' => array(
                                    'type' => 'string',
                                ),
                                'position' => array(
                                    'type' => 'string',
                                ),
                                'transition' => array(
                                    'type' => 'string',
                                ),
                            ),
                        ),
                    ),
                ),
                'pressidium_options' => array(
                    'type' => 'object',
                    'required' => array(
                        'primary_btn_role',
                        'secondary_btn_role',
                        'cookie_table',
                        'font',
                        'floating_button',
                        'colors',
                        'gcm',
                    ),
                    'properties' => array(
                        'primary_btn_role' => array(
                            'type' => 'string',
                        ),
                        'secondary_btn_role' => array(
                            'type' => 'string',
                        ),
                        'cookie_table' => array(
                            'type' => 'object',
                            'required' => array(
                                'necessary',
                                'analytics',
                                'targeting',
                                'preferences',
                            ),
                            'properties' => array(
                                'necessary' => array(
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'object',
                                        'required' => array(
                                            'name',
                                            'domain',
                                            'expiration',
                                            'path',
                                            'description',
                                            'is_regex',
                                        ),
                                        'properties' => array(
                                            'name' => array(
                                                'type' => 'string',
                                            ),
                                            'domain' => array(
                                                'type' => 'string',
                                            ),
                                            'expiration' => array(
                                                'type' => 'string',
                                            ),
                                            'path' => array(
                                                'type' => 'string',
                                            ),
                                            'description' => array(
                                                'type' => 'string',
                                            ),
                                            'is_regex' => array(
                                                'type' => 'boolean',
                                            ),
                                        ),
                                    ),
                                ),
                                'analytics' => array(
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'object',
                                        'required' => array(
                                            'name',
                                            'domain',
                                            'expiration',
                                            'path',
                                            'description',
                                            'is_regex',
                                        ),
                                        'properties' => array(
                                            'name' => array(
                                                'type' => 'string',
                                            ),
                                            'domain' => array(
                                                'type' => 'string',
                                            ),
                                            'expiration' => array(
                                                'type' => 'string',
                                            ),
                                            'path' => array(
                                                'type' => 'string',
                                            ),
                                            'description' => array(
                                                'type' => 'string',
                                            ),
                                            'is_regex' => array(
                                                'type' => 'boolean',
                                            ),
                                        ),
                                    ),
                                ),
                                'targeting' => array(
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'object',
                                        'required' => array(
                                            'name',
                                            'domain',
                                            'expiration',
                                            'path',
                                            'description',
                                            'is_regex',
                                        ),
                                        'properties' => array(
                                            'name' => array(
                                                'type' => 'string',
                                            ),
                                            'domain' => array(
                                                'type' => 'string',
                                            ),
                                            'expiration' => array(
                                                'type' => 'string',
                                            ),
                                            'path' => array(
                                                'type' => 'string',
                                            ),
                                            'description' => array(
                                                'type' => 'string',
                                            ),
                                            'is_regex' => array(
                                                'type' => 'boolean',
                                            ),
                                        ),
                                    ),
                                ),
                                'preferences' => array(
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'object',
                                        'required' => array(
                                            'name',
                                            'domain',
                                            'expiration',
                                            'path',
                                            'description',
                                            'is_regex',
                                        ),
                                        'properties' => array(
                                            'name' => array(
                                                'type' => 'string',
                                            ),
                                            'domain' => array(
                                                'type' => 'string',
                                            ),
                                            'expiration' => array(
                                                'type' => 'string',
                                            ),
                                            'path' => array(
                                                'type' => 'string',
                                            ),
                                            'description' => array(
                                                'type' => 'string',
                                            ),
                                            'is_regex' => array(
                                                'type' => 'boolean',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'font' => array(
                            'type' => 'object',
                            'required' => array(
                                'name',
                                'slug',
                                'family',
                            ),
                        ),
                        'floating_button' => array(
                            'type' => 'object',
                            'required' => array(
                                'enabled',
                                'size',
                                'position',
                                'icon',
                                'transition',
                            ),
                        ),
                        'colors' => array(
                            'type' => 'object',
                            'required' => array(
                                'bg',
                                'text',
                                'btn-primary-bg',
                                'btn-primary-text',
                                'btn-primary-hover-bg',
                                'btn-primary-hover-text',
                                'btn-secondary-bg',
                                'btn-secondary-text',
                                'btn-secondary-hover-bg',
                                'btn-secondary-hover-text',
                                'toggle-bg-off',
                                'toggle-bg-on',
                                'toggle-bg-readonly',
                                'toggle-knob-bg',
                                'toggle-knob-icon-color',
                                'cookie-category-block-bg',
                                'cookie-category-block-bg-hover',
                                'section-border',
                                'block-text',
                                'cookie-table-border',
                                'overlay-bg',
                                'webkit-scrollbar-bg',
                                'webkit-scrollbar-bg-hover',
                                'btn-floating-bg',
                                'btn-floating-icon',
                                'btn-floating-hover-bg',
                                'btn-floating-hover-icon',
                            ),
                            'properties' => array(
                                'bg' => array(
                                    'type' => 'string',
                                ),
                                'text' => array(
                                    'type' => 'string',
                                ),
                                'btn-primary-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-primary-text' => array(
                                    'type' => 'string',
                                ),
                                'btn-primary-hover-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-primary-hover-text' => array(
                                    'type' => 'string',
                                ),
                                'btn-secondary-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-secondary-text' => array(
                                    'type' => 'string',
                                ),
                                'btn-secondary-hover-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-secondary-hover-text' => array(
                                    'type' => 'string',
                                ),
                                'toggle-bg-off' => array(
                                    'type' => 'string',
                                ),
                                'toggle-bg-on' => array(
                                    'type' => 'string',
                                ),
                                'toggle-bg-readonly' => array(
                                    'type' => 'string',
                                ),
                                'toggle-knob-bg' => array(
                                    'type' => 'string',
                                ),
                                'toggle-knob-icon-color' => array(
                                    'type' => 'string',
                                ),
                                'cookie-category-block-bg' => array(
                                    'type' => 'string',
                                ),
                                'cookie-category-block-bg-hover' => array(
                                    'type' => 'string',
                                ),
                                'section-border' => array(
                                    'type' => 'string',
                                ),
                                'block-text' => array(
                                    'type' => 'string',
                                ),
                                'cookie-table-border' => array(
                                    'type' => 'string',
                                ),
                                'overlay-bg' => array(
                                    'type' => 'string',
                                ),
                                'webkit-scrollbar-bg' => array(
                                    'type' => 'string',
                                ),
                                'webkit-scrollbar-bg-hover' => array(
                                    'type' => 'string',
                                ),
                                'btn-floating-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-floating-icon' => array(
                                    'type' => 'string',
                                ),
                                'btn-floating-hover-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-floating-hover-icon' => array(
                                    'type' => 'string',
                                ),
                            ),
                        ),
                        'record_consents' => array(
                            'type' => 'boolean',
                        ),
                        'hide_empty_categories' => array(
                            'type' => 'boolean',
                        ),
                        'gcm' => array(
                            'type' => 'object',
                            'required' => array(
                                'enabled',
                                'implementation',
                                'ads_data_redaction',
                                'url_passthrough',
                                'regions',
                            ),
                            'properties' => array(
                                'enabled' => array(
                                    'type' => 'boolean',
                                ),
                                'implementation' => array(
                                    'type' => 'string',
                                ),
                                'ads_data_redaction' => array(
                                    'type' => 'boolean',
                                ),
                                'url_passthrough' => array(
                                    'type' => 'boolean',
                                ),
                                'regions' => array(
                                    'type' => 'object',
                                    'properties' => array(
                                        '[a-zA-Z]' => array(
                                            'type' => 'object',
                                            'properties' => array(
                                                'ad_storage' => array(
                                                    'type' => 'boolean'
                                                ),
                                                'ad_user_data' => array(
                                                    'type' => 'boolean'
                                                ),
                                                'ad_personalization' => array(
                                                    'type' => 'boolean'
                                                ),
                                                'analytics_storage' => array(
                                                    'type' => 'boolean'
                                                ),
                                                'functionality_storage' => array(
                                                    'type' => 'boolean'
                                                ),
                                                'personalization_storage' => array(
                                                    'type' => 'boolean'
                                                ),
                                                'security_storage' => array(
                                                    'type' => 'boolean'
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Return whether the cookie tables have changed.
     *
     * @param array $prev_settings Previous settings.
     * @param array $new_settings  New settings.
     *
     * @return bool
     */
    private function are_cookie_tables_changed( array $prev_settings, array $new_settings ): bool {
        if ( empty( $prev_settings ) ) {
            return true;
        }

        $prev_cookie_table = $prev_settings['pressidium_options']['cookie_table'];
        $new_cookie_table = $new_settings['pressidium_options']['cookie_table'];

        $cookie_categories = array( 'necessary', 'analytics', 'targeting', 'preferences' );

        foreach ( $cookie_categories as $category ) {
            if ( $prev_cookie_table[ $category ] != $new_cookie_table[ $category ] ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the revision to set in the settings.
     *
     * @param array $new_settings New settings.
     *
     * @return int
     */
    private function maybe_increment_revision( array $new_settings ): int {
        $prev_settings = Emoji::decode_array( $this->settings->get() );
        $revision      = $prev_settings['revision'] ?? 1;

        if ( $this->are_cookie_tables_changed( $prev_settings, $new_settings ) ) {
            $revision += 1;
        }

        return $revision;
    }

    /**
     * Migrate the given settings to the latest version, if necessary.
     *
     * @param array $settings Settings to migrate.
     *
     * @return array
     */
    private function maybe_migrate( array $settings ): array {
        $migrator          = new Migrator( $settings );
        $mirgated_settings = $migrator->maybe_migrate();

        return $mirgated_settings;
    }

    /**
     * Update settings.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_settings( WP_REST_Request $request ) {
        $settings = $request->get_param( 'settings' );
        $nonce    = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Updating settings failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $settings = $this->maybe_migrate( $settings );

        $settings['revision'] = $this->maybe_increment_revision( $settings );
        $settings['version']  = VERSION;

        $set_successfully = $this->settings->set( $settings );

        $response = array( 'success' => false );

        if ( ! $set_successfully ) {
            $this->logger->error( 'Could not update settings on the database' );

            return rest_ensure_response( $response );
        }

        $response['success'] = true;
        $response['data']    = $settings;

        $this->logger->info( 'Updated settings successfully' );

        return rest_ensure_response( $response );
    }

    /**
     * Return current settings.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_settings( WP_REST_Request $request ) {
        $response = array( 'success' => false );
        $settings = Emoji::decode_array( $this->settings->get() );

        if ( empty( $settings ) ) {
            $this->logger->error( 'Could not retrieve settings from the database' );

            return rest_ensure_response( $response );
        }

        $response['success'] = true;
        $response['data']    = $this->maybe_migrate( $settings );

        return rest_ensure_response( $response );
    }

    /**
     * Delete ALL settings.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete_settings( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Deleting settings failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $deleted_successfully = $this->settings->remove();
        $response             = array( 'success' => $deleted_successfully );

        if ( ! $deleted_successfully ) {
            $this->logger->error( 'Could not delete settings from the database' );
        }

        $this->logger->info( 'Reset settings successfully' );
        return rest_ensure_response( $response );
    }

    /**
     * Delete ALL consent records.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete_consent_records( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Deleting consent records failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $deleted_successfully = $this->consents_table->clear();
        $response             = array( 'success' => $deleted_successfully );

        if ( ! $deleted_successfully ) {
            $this->logger->error( 'Could not delete consent records from the database' );
        }

        $this->logger->info( 'Consent records were deleted successfully' );
        return rest_ensure_response( $response );
    }

    /**
     * Return logs.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_logs( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Retrieving logs failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array(
            'success' => true,
            'data'    => $this->logs->get_logs(),
        );

        return rest_ensure_response( $response );
    }

    /**
     * Delete logs.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete_logs( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Deleting logs failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $deleted_successfully = $this->logs->clear();
        $response             = array( 'success' => $deleted_successfully );

        if ( ! $deleted_successfully ) {
            $this->logger->error( 'Could not clear the log file' );
        }

        $this->logger->info( 'Cleared logs successfully' );
        return rest_ensure_response( $response );
    }

    /**
     * Update consent record for a user.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_consent( WP_REST_Request $request ) {
        $settings = $this->settings->get();

        if ( ! $settings['pressidium_options']['record_consents'] ) {
            $this->logger->warning( 'Attempted to update a consent record while recording was disabled' );

            return new WP_Error(
                'recording_is_disabled',
                __( 'Consent recording is disabled.', 'pressidium-cookie-consent' ),
                array( 'status' => 400 )
            );
        }

        $consent_date        = $request->get_param( 'consent_date' );
        $uuid                = $request->get_param( 'uuid' );
        $url                 = $request->get_param( 'url' );
        $user_agent          = $request->get_param( 'user_agent' );
        $necessary_consent   = $request->get_param( 'necessary_consent' );
        $analytics_consent   = $request->get_param( 'analytics_consent' );
        $targeting_consent   = $request->get_param( 'targeting_consent' );
        $preferences_consent = $request->get_param( 'preferences_consent' );

        $ip_address = $_SERVER['REMOTE_ADDR'];

        $cookie_consent = new Consent_Record();
        $cookie_consent->set_id( $uuid )
            ->set_date( $consent_date )
            ->set_url( $url )
            ->set_geo_location( $this->geo_locator->maybe_get_country_code( $ip_address ) )
            ->set_ip_address( $ip_address )
            ->set_user_agent( $user_agent )
            ->set_necessary_consent( $necessary_consent )
            ->set_analytics_consent( $analytics_consent )
            ->set_targeting_consent( $targeting_consent )
            ->set_preferences_consent( $preferences_consent );

        $updated_successfully = false;

        try {
            $updated_successfully = $this->consents_table->set_consent_record( $cookie_consent );
        } catch ( Exception $exception ) {
            $this->logger->log_exception( $exception );
        }

        $response = array( 'success' => $updated_successfully );

        return rest_ensure_response( $response );
    }

    /**
     * Return (paginated) consent records.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_consent_records( WP_REST_Request $request ) {
        $nonce    = $request->get_param( 'nonce' );
        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' ) ?? 10;

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Retrieving consent records failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $number_of_rows = $this->consents_table->get_total_number_of_rows();
        $rows           = $this->consents_table->get_rows( $page, $per_page );

        $data = array(
            'success' => true,
            'data'    => array_map(
                function( $row ) {
                    $row['necessary_consent']   = (bool) $row['necessary_consent'];
                    $row['analytics_consent']   = (bool) $row['analytics_consent'];
                    $row['targeting_consent']   = (bool) $row['targeting_consent'];
                    $row['preferences_consent'] = (bool) $row['preferences_consent'];

                    return $row;
                },
                $rows
            ),
        );

        $headers = array(
            'X-WP-Total'      => $number_of_rows,
            'X-WP-TotalPages' => ceil( $number_of_rows / $per_page ),
        );

        return rest_ensure_response( new WP_REST_Response( $data, 200, $headers ) );
    }

    /**
     * Export consent records to file.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function export_file( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Exporting file failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        return $this->exporter->export( $this->consents_table );
    }

    /**
     * Register REST routes.
     *
     * @return void
     */
    public function register_rest_routes(): void {
        $did_register_routes = register_rest_route(
            self::REST_NAMESPACE,
            '/settings',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => '__return_true',
            )
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/settings',
            array(
                'methods'              => 'POST',
                'callback'             => array( $this, 'update_settings' ),
                'args'                 => array(
                    'nonce'    => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'settings' => array(
                        'type'              => 'object',
                        'required'          => true,
                        'sanitize_callback' => function( $param ) {
                            return rest_sanitize_value_from_schema( $param, $this->get_settings_schema() );
                        },
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/settings',
            array(
                'methods'             => 'DELETE',
                'callback'            => array( $this, 'delete_settings' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/logs',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_logs' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'permission_callback' => function() {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/logs',
            array(
                'methods'             => 'DELETE',
                'callback'            => array( $this, 'delete_logs' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/export',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'export_file' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            ),
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/consent',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_consent' ),
                'args'                => array(
                    'consent_date'      => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'uuid'              => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'url'               => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_url',
                    ),
                    'user_agent'        => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'necessary_consent' => array(
                        'type'              => 'boolean',
                        'required'          => true,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                    'analytics_consent' => array(
                        'type'              => 'boolean',
                        'required'          => true,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                    'targeting_consent' => array(
                        'type'              => 'boolean',
                        'required'          => true,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                    'preferences_consent' => array(
                        'type'              => 'boolean',
                        'required'          => true,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                ),
                'permission_callback' => '__return_true',
            ),
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/consents',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_consent_records' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'page' => array(
                        'type' => 'integer',
                    ),
                    'per_page' => array(
                        'type' => 'integer',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        $did_register_routes = $did_register_routes && register_rest_route(
            self::REST_NAMESPACE,
            '/consents',
            array(
                'methods'             => 'DELETE',
                'callback'            => array( $this, 'delete_consent_records' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );

        if ( ! $did_register_routes ) {
            $this->logger->error( 'Could not register REST route(s)' );
        }
    }

    /**
     * Return the actions to register.
     *
     * @return array
     */
    public function get_actions(): array {
        return array(
            'rest_api_init' => array( 'register_rest_routes' ),
        );
    }

}
