<?php
/**
 * Proxy.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Proxy;

use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Hooks\Filters;

use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Geo_Locator;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Proxy abstract class.
 *
 * @since 1.9.0
 */
abstract class Proxy implements Actions, Filters {

    /**
     * @var Logger $logger `Logger` instance.
     */
    private Logger $logger;

    /**
     * @var Geo_Locator $geo_locator `Geo_Locator` instance.
     */
    private Geo_Locator $geo_locator;

    /**
     * Proxy constructor.
     *
     * @param Logger      $logger      `Logger` instance.
     * @param Geo_Locator $geo_locator `Geo_Locator` instance.
     */
    public function __construct( Logger $logger, Geo_Locator $geo_locator ) {
        $this->logger      = $logger;
        $this->geo_locator = $geo_locator;
    }

    /**
     * Return the route to be proxied.
     *
     * @return string
     */
    abstract protected function get_route(): string;

    /**
     * Return the query var to be used as a flag for the proxy.
     *
     * @return string
     */
    abstract protected function get_flag_var(): string;

    /**
     * Return the query var to be used for the path.
     *
     * @return string
     */
    abstract protected function get_path_var(): string;

    /**
     * Return the upstream URL.
     *
     * @param string $path Path to append to the upstream URL.
     *
     * @return string
     */
    abstract protected function get_upstream_url( string $path ): string;

    /**
     * Return whether the request should be proxied.
     *
     * Override this method in child classes to customize the logic.
     *
     * @return bool
     */
    protected function should_proxy(): bool {
        // Always proxy by default
        return true;
    }

    /**
     * Return the timeout for the proxy request.
     *
     * Override this method in child classes to customize the timeout.
     *
     * @return int Timeout in seconds.
     */
    protected function get_timeout(): int {
        return 10; // seconds
    }

    /**
     * Filter response headers before sending them to the client.
     *
     * Override this method in child classes to customize the response headers.
     *
     * @param array $headers Response headers to filter.
     *
     * @return array
     */
    protected function filter_response_headers( array $headers ): array {
        // By default, do not modify the headers
        return $headers;
    }

    /**
     * Add query vars for the proxy.
     *
     * @param array $query_vars Allowed query variable names.
     *
     * @return array
     */
    public function add_query_vars( array $query_vars ): array {
        // Add our custom query vars
        $query_vars[] = $this->get_flag_var();
        $query_vars[] = $this->get_path_var();

        return $query_vars;
    }

    /**
     * Prevent canonical redirects for our route.
     *
     * This avoids breaking the proxy by returning `301` or `302`
     * responses for trailing slashes or other URL normalizations.
     *
     * @param string $redirect_url  Redirect URL.
     * @param string $requested_url Requested URL.
     *
     * @return string
     */
    public function prevent_redirects_for_route( string $redirect_url, string $requested_url ): string {
        $base = trim( $this->get_route(), '/' );

        $pattern = '/^' . preg_quote( home_url( '/' . $base ), '/' ) . '(\/.*)?$/';

        if ( preg_match( $pattern, $requested_url ) ) {
            // Prevent redirect for our route
            return $requested_url;
        }

        return $redirect_url;
    }

    /**
     * Add rewrite rules for the proxy.
     *
     * @return void
     */
    public function add_rewrite(): void {
        $base = trim( $this->get_route(), '/' );

        // Rewrite base path
        add_rewrite_rule(
            '^' . $base . '/?$',
            'index.php?' . $this->get_flag_var() . '=1&' . $this->get_path_var() . '=',
            'top'
        );

        // Rewrite all sub-paths
        add_rewrite_rule(
            '^' . $base . '/(.*)$',
            'index.php?' . $this->get_flag_var() . '=1&' . $this->get_path_var() . '=$matches[1]',
            'top'
        );
    }

    /**
     * Fail with a specific HTTP status code and message.
     *
     * @param int    $code    HTTP status code.
     * @param string $message HTTP status message.
     *
     * @return void
     */
    private function fail_with_status( int $code, string $message ): void {
        if ( ! headers_sent() ) {
            status_header( $code );
            header( 'Content-Type: text/plain; charset=utf-8' );
        }

        echo $message;
        exit;
    }

    /**
     * Maybe proxy the request.
     *
     * @return void
     */
    public function maybe_proxy(): void {
        if ( ! get_query_var( $this->get_flag_var() ) ) {
            // Not our request, bail early
            return;
        }

        if ( ! $this->should_proxy() ) {
            // Our route, but the proxy is disabled. Return a proper error instead of
            // falling through to WordPress, which would serve HTML and break any
            // `<script>` tag pointing at this path.
            $this->fail_with_status( 503, 'Service Unavailable' );
        }

        // Get the path to proxy
        $path = (string) get_query_var( $this->get_path_var() );

        // Get the upstream URL
        $upstream_url = null;

        try {
            $upstream_url = $this->get_upstream_url( $path );
        } catch ( InvalidArgumentException $exception ) {
            // Invalid upstream URL, log the error and return a `400 Bad Request`
            $this->logger->error( 'Could not proxy request: ' . $exception->getMessage() );
            $this->fail_with_status( 400, 'Bad Request' );
        }

        // Read the raw request body
        $raw_body = file_get_contents( 'php://input' ) ?: '';

        // Build response headers
        $headers = new Headers( $this->geo_locator );
        $headers->set_host( $upstream_url )
                ->set_x_forwarded_for()
                ->set_cookie()
                ->set_geo_location();

        $response = wp_remote_request(
            $upstream_url,
            array(
                'method'    => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'body'      => $raw_body,
                'headers'   => $headers->get_headers(),
                'timeout'   => $this->get_timeout(),
                'blocking'  => true, // wait for the response
                'sslverify' => true,
            )
        );

        if ( is_wp_error( $response ) ) {
            // Upstream request failed, log the error and return a `502 Bad Gateway`
            $this->logger->error( 'Upstream request failed: ' . $response->get_error_message() );
            $this->fail_with_status( 502, 'Bad Gateway' );
        }

        // Mirror status
        $code    = wp_remote_retrieve_response_code( $response );
        $message = wp_remote_retrieve_response_message( $response );

        if ( ! headers_sent() ) {
            status_header( $code, $message );
        }

        // Pass-through safe to forward headers
        $all_response_headers = wp_remote_retrieve_headers( $response );
        $response_headers     = $headers->keep_forwardable_headers(
            $all_response_headers->getAll()
        );

        $response_headers = $this->filter_response_headers( $response_headers );

        foreach ( $response_headers as $header => $value ) {
            if ( ! headers_sent() ) {
                header( sprintf( '%s: %s', $header, $value ) );
            }
        }

        // Output response body
        echo wp_remote_retrieve_body( $response );
        exit;
    }

    /**
     * Return the actions to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'init'              => array( 'add_rewrite' ),
            'template_redirect' => array( 'maybe_proxy' ),
        );
    }

    /**
     * Return the filters to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_filters(): array {
        return array(
            'query_vars'         => array( 'add_query_vars' ),
            'redirect_canonical' => array( 'prevent_redirects_for_route', 10, 2 ),
        );
    }

}
