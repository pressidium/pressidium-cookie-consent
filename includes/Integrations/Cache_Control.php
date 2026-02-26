<?php
/**
 * Cache control integration.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Integrations;

use Pressidium\WP\CookieConsent\Hooks\Filters;

use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Cache_Control class.
 *
 * Prevents caching of the plugin's REST API responses.
 *
 * LiteSpeed Cache (and QUIC.cloud CDN) caches GET REST API responses,
 * which can cause stale settings to be returned after saving.
 *
 * @since 1.10.0
 */
class Cache_Control implements Filters {

    /**
     * @var string REST API namespace.
     */
    private const REST_NAMESPACE = '/pressidium-cookie-consent/v1';

    /**
     * Set no-cache headers on the REST API response.
     *
     * @param WP_REST_Response $result  Result to send to the client.
     * @param WP_REST_Server   $server  The REST server instance.
     * @param WP_REST_Request  $request The request that generated the response.
     *
     * @return WP_REST_Response
     */
    public function set_nocache_headers(
        WP_REST_Response $result,
        WP_REST_Server $server,
        WP_REST_Request $request
    ): WP_REST_Response {
        $route = $request->get_route();

        if ( ! str_starts_with( $route, self::REST_NAMESPACE ) ) {
            return $result;
        }

        do_action( 'litespeed_control_set_nocache', 'pressidium cookie consent REST API response' );

        $result->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );

        return $result;
    }

    /**
     * Return the filters to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_filters(): array {
        return array(
            'rest_post_dispatch' => array( 'set_nocache_headers', 10, 3 ),
        );
    }

}
