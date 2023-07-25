<?php
/**
 * Feedback API.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Feedback;

use const Pressidium\WP\CookieConsent\PLUGIN_FILE;
use const Pressidium\WP\CookieConsent\VERSION;

use Pressidium\WP\CookieConsent\Logging\Logger;

use WP_Error;
use Exception;

/**
 * Class Feedback_API.
 *
 * @since 1.1.0
 */
class Feedback_API extends API {

    /**
     * @var Logger Instance of `Logger`.
     */
    private Logger $logger;

    /**
     * Feedback_API constructor.
     *
     * @param Logger $logger Instance of `Logger`.
     */
    public function __construct( Logger $logger ) {
        $this->logger = $logger;
    }

    /**
     * Return the base URL for this API.
     *
     * @return string
     */
    protected function get_api_base_url(): string {
        return 'https://feedback-api.pressidium.com/api/v1/';
    }

    /**
     * Whether the response is an error.
     *
     * @param array|WP_Error $response Response to check.
     *
     * @return bool
     */
    private function is_error( $response ): bool {
        return is_wp_error( $response ) || intval( wp_remote_retrieve_response_code( $response ) ) > 299;
    }

    /**
     * Return the error message from the given response.
     *
     * @param array|WP_Error $response Response to check.
     *
     * @return string
     */
    private function get_error_message( $response ): string {
        try {
            if ( is_wp_error( $response ) ) {
                return $response->get_error_message();
            }

            $body = json_decode( wp_remote_retrieve_body( $response ), true );

            $error_message = $body['error'] ?? __( 'Unknown error', 'pressidium-cookie-consent' );

            $this->logger->error( 'Feedback API error: ' . $error_message );

            return $error_message;
        } catch ( Exception $exception ) {
            $this->logger->log_exception( $exception );

            return __( 'Unknown error', 'pressidium-cookie-consent' );
        }
    }

    /**
     * Send feedback to the Feedback API.
     *
     * @throws Feedback_Exception If the API returns an error.
     *
     * @param string      $reason  The reason for the deactivation of the plugin.
     * @param string|null $comment Optional. An additional comment about the deactivation of the plugin.
     *
     * @return void
     */
    public function send( string $reason, ?string $comment ): void {
        $plugin_slug = plugin_basename( PLUGIN_FILE );

        $payload = array(
            'reason'         => $reason,
            'comment'        => $comment,
            'plugin_name'    => $plugin_slug,
            'plugin_version' => VERSION,
            'php_version'    => phpversion(),
            'wp_version'     => get_bloginfo( 'version' ),
        );

        $response = $this->post( 'feedback', $payload );

        if ( $this->is_error( $response ) ) {
            $error_message = $this->get_error_message( $response );
            throw new Feedback_Exception( $error_message );
        }

        $this->logger->info( 'Submitted feedback successfully' );
    }

}
