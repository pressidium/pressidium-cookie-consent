<?php
/**
 * Ask for feedback when the plugin is deactivated.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Feedback;

use const Pressidium\WP\CookieConsent\PLUGIN_DIR;
use const Pressidium\WP\CookieConsent\PLUGIN_URL;

use Pressidium\WP\CookieConsent\Hooks\Actions;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Feedback class.
 *
 * @since 1.1.0
 */
class Feedback implements Actions {

    /**
     * @var Feedback_API Instance of `Feedback_API`.
     */
    private Feedback_API $feedback_api;

    /**
     * Feedback constructor.
     *
     * @param Feedback_API $feedback_api Instance of `Feedback_API`.
     */
    public function __construct( Feedback_API $feedback_api ) {
        $this->feedback_api = $feedback_api;
    }

    /**
     * Enqueue the feedback script on wp-admin.
     *
     * @param string $hook_suffix The current admin page.
     *
     * @return void
     */
    public function enqueue_feedback_script( string $hook_suffix ): void {
        if ( $hook_suffix !== 'plugins.php' ) {
            // Not on the plugins page, bail early
            return;
        }

        $assets_file = PLUGIN_DIR . 'public/bundle.feedback.asset.php';

        if ( ! file_exists( $assets_file ) ) {
            // File doesn't exist, bail early
            return;
        }

        $assets = require $assets_file;

        $dependencies = $assets['dependencies'] ?? array();
        $version      = $assets['version'] ?? filemtime( $assets_file );

        wp_enqueue_style(
            'cookie-consent-feedback-admin-style',
            PLUGIN_URL . 'public/bundle.feedback.css',
            array( 'wp-components' ),
            $version
        );

        wp_enqueue_script(
            'cookie-consent-feedback-admin-script',
            PLUGIN_URL . 'public/bundle.feedback.js',
            $dependencies,
            $version,
            true
        );

        wp_localize_script(
            'cookie-consent-feedback-admin-script',
            'pressidiumCCFeedbackDetails',
            array(
                'api' => array(
                    'route' => 'pressidium-cookie-consent/v1/feedback',
                    'nonce' => wp_create_nonce( 'pressidium_cookie_consent_feedback_rest' ),
                ),
            )
        );

        wp_set_script_translations(
            'cookie-consent-feedback-admin-script',
            'pressidium-cookie-consent'
        );
    }

    /**
     * Render an empty element that will be used as a React/wp.element to render the feedback modal.
     *
     * @link https://github.com/WordPress/gutenberg/blob/trunk/packages/element/README.md
     *
     * @return void
     */
    public function render_feedback_modal(): void {
        ?>

        <pressidium-dialog
            id="pressidium-cookie-consent-feedback-dialog"
            title="<?php esc_attr_e( 'Help us improve!', 'pressidium-cookie-consent' ); ?>"
            <?php // phpcs:ignore Generic.Files.LineLength ?>
            description="<?php esc_attr_e( 'We value your feedback. Please let us know why you’re deactivating Pressidium Cookie Consent.', 'pressidium-cookie-consent' ); ?>"
        >
            <div slot="content">
                <div id="pressidium-cookie-consent-feedback-root"></div>
            </div>
        </pressidium-dialog>

        <?php
    }

    /**
     * Send plugin feedback.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function send_plugin_feedback( WP_REST_Request $request ) {
        $nonce   = $request->get_param( 'nonce' );
        $reason  = $request->get_param( 'reason' );
        $comment = $request->get_param( 'comment' );

        // Validate nonce
        if ( ! wp_verify_nonce( $nonce, 'pressidium_cookie_consent_feedback_rest' ) ) {
            return new WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce.', 'pressidium-cookie-consent' ),
                array( 'status' => 403 )
            );
        }

        try {
            $this->feedback_api->send( $reason, $comment );
        } catch ( Feedback_Exception $exception ) {
            return new WP_Error(
                'feedback_exception',
                $exception->getMessage(),
                array( 'status' => 500 )
            );
        }

        $response = array( 'success' => true );

        return rest_ensure_response( $response );
    }

    /**
     * Register REST routes.
     *
     * @return void
     */
    public function register_rest_routes(): void {
        register_rest_route(
            'pressidium-cookie-consent/v1',
            '/feedback',
            array(
                'methods'              => 'POST',
                'callback'             => array( $this, 'send_plugin_feedback' ),
                'args'                 => array(
                    'nonce'   => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'reason'  => array(
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'comment' => array(
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can( 'activate_plugins' );
                },
            )
        );
    }

    /**
     * Return the filters to register.
     *
     * @link https://developer.wordpress.org/reference/hooks/plugin_action_links/
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'admin_enqueue_scripts' => array( 'enqueue_feedback_script' ),
            'admin_footer'          => array( 'render_feedback_modal' ),
            'rest_api_init'         => array( 'register_rest_routes' ),
        );
    }

}
