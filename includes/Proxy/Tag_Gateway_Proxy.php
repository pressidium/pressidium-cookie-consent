<?php
/**
 * Proxy to route traffic to Google tag gateway.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Proxy;

use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Geo_Locator;
use Pressidium\WP\CookieConsent\Settings;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Tag_Gateway_Proxy class.
 *
 * @link http://developers.google.com/tag-platform/tag-manager/gateway/setup-guide
 *
 * @since 1.9.0
 */
final class Tag_Gateway_Proxy extends Proxy {

    /**
     * @var array Settings.
     */
    private array $settings;

    /**
     * Tag_Gateway_Proxy constructor.
     *
     * @param Logger      $logger          `Logger` instance.
     * @param Geo_Locator $geo_locator     `Geo_Locator` instance.
     * @param Settings    $settings_object `Settings` instance.
     */
    public function __construct( Logger $logger, Geo_Locator $geo_locator, Settings $settings_object ) {
        $this->settings = $settings_object->get();

        parent::__construct( $logger, $geo_locator );
    }

    /**
     * Return the Google tag gateway host.
     *
     * @return string
     */
    private function get_tag_gateway_host(): string {
        $gtag_id          = $this->settings['pressidiumOptions']['googleTagGateway']['gtagId'] ?? '';
        $tag_gateway_host = sprintf( '%s.fps.goog', $gtag_id );

        return apply_filters( 'pressidium_cookie_consent_tag_gateway_host', $tag_gateway_host );
    }

    /**
     * Return the route to be proxied.
     *
     * @return string
     */
    protected function get_route(): string {
        // Will route all traffic to `/pressidium-cookie-consent-metrics/*` to Google tag gateway.
        return '/pressidium-cookie-consent-metrics';
    }

    /**
     * Return the query var to be used as a flag for the proxy.
     *
     * @return string
     */
    protected function get_flag_var(): string {
        return 'pressidium_gtg_metrics';
    }

    /**
     * Return the query var to be used for the path.
     *
     * @return string
     */
    protected function get_path_var(): string {
        return 'pressidium_gtg_path';
    }

    /**
     * Return whether the request should be proxied.
     *
     * @return bool
     */
    protected function should_proxy(): bool {
        return $this->settings['pressidiumOptions']['googleTagGateway']['proxyEnabled'] ?? false;
    }

    /**
     * Filter response headers before sending them to the client.
     *
     * @param array $headers Response headers to filter.
     *
     * @return array
     */
    protected function filter_response_headers( array $headers ): array {
        // Remove encoding headers to avoid issues with compressed responses
        $headers_to_remove = array(
            'content-encoding',
            'accept-encoding',
        );

        foreach ( $headers as $key => $value ) {
            if ( in_array( strtolower( $key ), $headers_to_remove, true ) ) {
                unset( $headers[ $key ] );
            }
        }

        return $headers;
    }

    /**
     * Return the query string (if any).
     *
     * @return string
     */
    private function get_query_string(): string {
        if ( empty( $_SERVER['QUERY_STRING'] ) ) {
            return '';
        }

        return '?' . $_SERVER['QUERY_STRING'];
    }

    /**
     * Proxy the request.
     *
     * @param string $path Path to proxy.
     *
     * @return void
     */
    protected function get_upstream_url( string $path ): string {
        $tag_gateway_host = $this->get_tag_gateway_host();

        // Validate the Google tag gateway host format
        if ( ! preg_match( '/^[a-zA-Z0-9]+-[a-zA-Z0-9]+\.fps\.goog$/', $tag_gateway_host ) ) {
            throw new InvalidArgumentException( 'Invalid Google tag gateway host' );
        }

        return sprintf(
            'https://%s%s/%s%s',
            $tag_gateway_host,
            $this->get_route(),
            ltrim( $path, '/' ),
            $this->get_query_string()
        );
    }

}
