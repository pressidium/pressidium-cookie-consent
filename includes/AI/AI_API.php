<?php
/**
 * AI API.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Options\Encryption_Exception;
use Pressidium\WP\CookieConsent\Options\Decryption_Exception;
use Pressidium\WP\CookieConsent\Options\Encrypted_Options;
use Pressidium\WP\CookieConsent\Settings;
use Pressidium\WP\CookieConsent\Emoji;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use InvalidArgumentException;
use RuntimeException;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * AI_API class.
 *
 * @since 1.8.0
 */
final class AI_API implements Actions {

    /**
     * @var string REST route namespace.
     */
    const REST_NAMESPACE = 'pressidium-cookie-consent/v1';

    /**
     * @var string API key option key.
     */
    const API_KEY_OPTION_KEY = 'pressidium_cookie_consent_ai_api_key';

    /**
     * @var Logger `Logger` instance.
     */
    private Logger $logger;

    /**
     * @var Encrypted_Options `Encrypted_Options` instance.
     */
    private Encrypted_Options $encrypted_options;

    /**
     * @var Settings `Settings` instance.
     */
    private Settings $settings;

    /**
     * @var ?AI AI instance.
     */
    private ?AI $ai = null;

    /**
     * AI_API constructor.
     *
     * @param Logger            $logger
     * @param Encrypted_Options $encrypted_options
     * @param Settings          $settings
     */
    public function __construct(
        Logger $logger,
        Encrypted_Options $encrypted_options,
        Settings $settings
    ) {
        $this->logger            = $logger;
        $this->encrypted_options = $encrypted_options;
        $this->settings          = $settings;

        $this->ai = $this->init_ai();
    }

    /**
     * Initialize the AI instance.
     *
     * @return ?AI
     */
    private function init_ai(): ?AI {
        try {
            $ai_provider = $this->get_ai_setting( 'provider' ) ?? 'openai';
            $api_key     = $this->encrypted_options->get( self::API_KEY_OPTION_KEY );

            $provider_to_type_map = array(
                'openai' => 'gpt',
                'gemini' => 'gemini',
            );

            $ai_type = $provider_to_type_map[ $ai_provider ] ?? $ai_provider;

            return AI_Factory::create( $ai_type )->init( $api_key );
        } catch ( InvalidArgumentException $exception ) {
            $this->logger->error( 'Could not instantiate AI instance' );
            return null;
        } catch ( Decryption_Exception $exception ) {
            $this->logger->error( 'Could not decrypt API key' );
            return null;
        }
    }

    /**
     * Return the value of the AI setting with the given key.
     *
     * @param string $key Setting key to retrieve.
     *
     * @return mixed Value of the setting, or `null` if not found.
     */
    private function get_ai_setting( string $key ) {
        $settings = Emoji::decode_array( $this->settings->get() );

        $ai_settings = $settings['pressidium_options']['ai'] ?? array();

        return $ai_settings[ $key ] ?? null;
    }

    /**
     * Update an AI setting.
     *
     * @param string $key   Setting key to update.
     * @param mixed  $value New value for the setting.
     *
     * @return bool Whether the setting was updated successfully.
     */
    private function update_ai_setting( string $key, $value ): bool {
        $settings = Emoji::decode_array( $this->settings->get() );

        $ai_settings         = $settings['pressidium_options']['ai'] ?? array();
        $ai_settings[ $key ] = $value;

        $settings['pressidium_options']['ai'] = $ai_settings;

        return $this->settings->set( $settings );
    }

    /**
     * Update the selected AI provider.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_provider( WP_REST_Request $request ) {
        $provider = $request->get_param( 'provider' );
        $nonce    = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Updating AI provider failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $did_update = $this->update_ai_setting( 'provider', $provider );

        if ( ! $did_update ) {
            $this->logger->error( 'Could not update AI provider' );
            return rest_ensure_response( array( 'success' => false ) );
        }

        return rest_ensure_response( array( 'success' => true ) );
    }

    /**
     * Update the AI credentials.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_credentials( WP_REST_Request $request ) {
        $api_key = $request->get_param( 'api_key' );
        $nonce   = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Updating AI credentials failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        try {
            $did_update = $this->encrypted_options->set( self::API_KEY_OPTION_KEY, $api_key );
            $did_update = $did_update && $this->update_ai_setting( 'model', null );

            if ( ! $did_update ) {
                $this->logger->error( 'Could not update AI API key' );
                return rest_ensure_response( $response );
            }
        } catch ( Encryption_Exception $exception ) {
            $this->logger->error( 'Could not encrypt AI API key' );
            return rest_ensure_response( $response );
        }

        return rest_ensure_response( array( 'success' => true ) );
    }

    /**
     * Update the selected AI model.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_model( WP_REST_Request $request ) {
        $model = $request->get_param( 'model' );
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Updating AI model failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        $did_update = $this->update_ai_setting( 'model', $model );

        if ( ! $did_update ) {
            $this->logger->error( 'Could not update AI model' );
            return rest_ensure_response( $response );
        }

        return rest_ensure_response( array( 'success' => true ) );
    }

    /**
     * Return the selected AI provider.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_provider( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Fetching the selected AI provider failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        try {
            $provider = $this->get_ai_setting( 'provider' );
        } catch ( RuntimeException | Exception $exception ) {
            $this->logger->log_exception( $exception );
            return rest_ensure_response( $response );
        }

        $response = array(
            'success'  => true,
            'provider' => $provider,
        );

        return rest_ensure_response( $response );
    }

    /**
     * Return the available AI models.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_models( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Fetching the available AI models failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        if ( is_null( $this->ai ) ) {
            return rest_ensure_response( $response );
        }

        try {
            $models = $this->ai->get_models();
        } catch ( RuntimeException | Exception $exception ) {
            $this->logger->log_exception( $exception );
            return rest_ensure_response( $response );
        }

        $response = array(
            'success' => true,
            'models'  => $models,
        );

        return rest_ensure_response( $response );
    }

    /**
     * Return the selected AI model.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_model( WP_REST_Request $request ) {
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Fetching the selected AI model failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        try {
            $model = $this->get_ai_setting( 'model' );

            if ( empty( $model ) ) {
                // If no model is set, use the default model
                $model = $this->ai->get_default_model();
            }
        } catch ( RuntimeException | Exception $exception ) {
            $this->logger->log_exception( $exception );
            return rest_ensure_response( $response );
        }

        if ( empty( $model ) ) {
            return rest_ensure_response( $response );
        }

        $response = array(
            'success' => true,
            'model'   => $model,
        );

        return rest_ensure_response( $response );
    }

    /**
     * Translate the specified text to the specified language.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function translate( WP_REST_Request $request ) {
        $text  = $request->get_param( 'text' );
        $lang  = $request->get_param( 'lang' );
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Translating failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        if ( is_null( $this->ai ) ) {
            return rest_ensure_response( $response );
        }

        try {
            // Prompt the AI to translate the text
            $translation = $this->ai
                // phpcs:ignore Generic.Files.LineLength
                ->set_system_prompt( AI::TRANSLATE_SYSTEM_PROMPT )
                ->select_model( $this->get_ai_setting( 'model' ) )
                ->prompt( sprintf( 'Target Language: "%s"\nText to translate: "%s"', $lang, $text ) );
        } catch ( RuntimeException | Exception $exception ) {
            $this->logger->log_exception( $exception );
            return rest_ensure_response( $response );
        }

        $response = array(
            'success'     => true,
            'translation' => $translation,
        );

        return rest_ensure_response( $response );
    }

    /**
     * Translate the specified string representation of a JSON object to the specified language.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function translate_all( WP_REST_Request $request ) {
        $json  = $request->get_param( 'json' );
        $lang  = $request->get_param( 'lang' );
        $nonce = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Translating failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        if ( is_null( $this->ai ) ) {
            return rest_ensure_response( $response );
        }

        try {
            // Prompt the AI to translate the text
            $translation = $this->ai
                // phpcs:ignore Generic.Files.LineLength
                ->set_system_prompt( AI::TRANSLATE_ALL_SYSTEM_PROMPT )
                ->select_model( $this->get_ai_setting( 'model' ) )
                ->prompt( sprintf( 'Target Language: "%s"\nJSON to translate:\n"%s"', $lang, $json ) );
        } catch ( RuntimeException | Exception $exception ) {
            $this->logger->log_exception( $exception );
            return rest_ensure_response( $response );
        }

        $response = array(
            'success'     => true,
            'translation' => $translation,
        );

        return rest_ensure_response( $response );
    }

    /**
     * Generate a cookie description for the specified cookie name.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function generate_cookie_description( WP_REST_Request $request ) {
        $cookie_name = $request->get_param( 'cookie_name' );
        $nonce       = $request->get_param( 'nonce' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_rest' ) ) {
            $this->logger->error( 'Generating cookie description failed due to invalid nonce' );

            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        $response = array( 'success' => false );

        if ( is_null( $this->ai ) ) {
            return rest_ensure_response( $response );
        }

        try {
            // Prompt the AI to generate a cookie description
            $description = $this->ai
                // phpcs:ignore Generic.Files.LineLength
                ->set_system_prompt( AI::COOKIE_DESCRIPTION_SYSTEM_PROMPT )
                ->select_model( $this->get_ai_setting( 'model' ) )
                ->prompt( sprintf( 'Cookie name: %s', $cookie_name ) );
        } catch ( RuntimeException | Exception $exception ) {
            $this->logger->log_exception( $exception );
            return rest_ensure_response( $response );
        }

        if ( strtolower( trim( $description ) ) === 'n/a' ) {
            // If the AI doesn't know what the cookie does, return `null`
            $description = null;
        }

        $response = array(
            'success'     => true,
            'description' => $description,
        );

        return rest_ensure_response( $response );
    }

    /**
     * Register REST routes.
     *
     * @return void
     */
    public function register_rest_routes(): void {
        $did_register_routes = register_rest_route(
            self::REST_NAMESPACE,
            '/ai/provider',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_provider' ),
                'args'                => array(
                    'nonce'    => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'provider' => array(
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
            '/ai/credentials',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_credentials' ),
                'args'                => array(
                    'nonce'   => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'api_key' => array(
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
            '/ai/model',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_model' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'model' => array(
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
            '/ai/provider',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_provider' ),
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
            '/ai/models',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_models' ),
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
            '/ai/model',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_model' ),
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
            '/ai/translate',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'translate' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'text'  => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'lang'  => array(
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
            '/ai/translate-all',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'translate_all' ),
                'args'                => array(
                    'nonce' => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'json'  => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'lang'  => array(
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
            '/ai/cookie-description',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'generate_cookie_description' ),
                'args'                => array(
                    'nonce'       => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'cookie_name' => array(
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
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'rest_api_init' => array( 'register_rest_routes' ),
        );
    }

}
