<?php
/**
 * Settings API.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Admin\Settings;

use const Pressidium\WP\CookieConsent\VERSION;

use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Settings;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

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
     * Settings_API constructor.
     *
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
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
                        'colors',
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
                                'analytics',
                                'targeting',
                            ),
                            'properties' => array(
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
                                'btn-secondary-bg',
                                'btn-secondary-text',
                                'btn-secondary-hover-bg',
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
                                'btn-secondary-bg' => array(
                                    'type' => 'string',
                                ),
                                'btn-secondary-text' => array(
                                    'type' => 'string',
                                ),
                                'btn-secondary-hover-bg' => array(
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

        if ( $prev_cookie_table['analytics'] != $new_cookie_table['analytics'] ) {
            return true;
        }

        if ( $prev_cookie_table['targeting'] != $new_cookie_table['targeting'] ) {
            return true;
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
        $prev_settings = $this->settings->get();
        $revision      = $prev_settings['revision'] ?? 1;

        if ( $this->are_cookie_tables_changed( $prev_settings, $new_settings ) ) {
            $revision += 1;
        }

        return $revision;
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
            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $settings['revision'] = $this->maybe_increment_revision( $settings );
        $settings['version']  = VERSION;

        $set_successfully = $this->settings->set( $settings );

        $response = array( 'success' => false );

        if ( $set_successfully ) {
            $response['success'] = true;
            $response['data']    = $settings;
        }

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
        $settings = $this->settings->get();

        if ( ! empty( $settings ) ) {
            $response['success'] = true;
            $response['data']    = $settings;
        }

        return rest_ensure_response( $response );
    }

    /**
     * Register REST routes.
     *
     * @return void
     */
    public function register_rest_routes(): void {
        register_rest_route(
            self::REST_NAMESPACE,
            '/settings',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
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
                'permissions_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );
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
