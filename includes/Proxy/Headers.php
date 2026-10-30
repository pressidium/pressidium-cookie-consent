<?php
/**
 * Headers to be forwarded by the proxy.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Proxy;

use Pressidium\WP\CookieConsent\Geo_Locator;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Headers class.
 *
 * @since 1.9.0
 */
class Headers {

    /**
     * @var Geo_Locator $geo_locator `Geo_Locator` instance.
     */
    private Geo_Locator $geo_locator;

    /**
     * @var array<string, string> $headers Associative array of all forwardable headers in the request.
     */
    private array $headers;

    /**
     * Headers constructor.
     *
     * @param Geo_Locator $geo_locator `Geo_Locator` instance.
     */
    public function __construct( Geo_Locator $geo_locator ) {
        $this->geo_locator = $geo_locator;

        // Initialize with all forwardable headers in the request
        $this->headers = $this->keep_forwardable_headers( $this->get_request_headers() );
    }

    /**
     * Return all request headers.
     *
     * @return array<string, string> Associative array of all request headers.
     */
    private function get_request_headers(): array {
        $headers = array();

        if ( function_exists( 'getallheaders' ) ) {
            $headers = getallheaders();
        }

        if ( empty( $headers ) ) {
            return array();
        }

        return $headers;
    }

    /**
     * Return all hop-by-hop headers listed in the `Connection` header.
     *
     * All hop-by-hop headers must be listed in the `Connection` header field, so that
     * the first proxy knows it has to consume them and not forward them further.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Connection
     *
     * @param array<string, string> $headers All request headers.
     *
     * @return string[]
     */
    private function get_hop_by_hop_headers_in_connection( array $headers ): array {
        $connection_tokens = array();

        /*
         * Iterate over all headers to find the `Connection` header
         * We need to handle it like this as headers are case-insensitive.
         */
        foreach ( $headers as $header => $value ) {
            if ( strtolower( $header ) !== 'connection' ) {
                // Not the `Connection` header, skip
                continue;
            }

            // Extract any hop-by-hop headers listed in the `Connection` header
            foreach ( explode( ',', $value ) as $token ) {
                $token = strtolower( trim( $token ) );

                if ( ! empty( $token ) ) {
                    $connection_tokens[] = $token;
                }
            }
        }

        return $connection_tokens;
    }

    /**
     * Return the standard hop-by-hop headers.
     *
     * @return string[]
     */
    private function get_standard_hop_by_hop_headers(): array {
        return array(
            'keep-alive',
            'transfer-encoding',
            'te',
            'connection',
            'trailer',
            'upgrade',
            'proxy-authorization',
            'proxy-authenticate',
        );
    }

    /**
     * Return only the headers that are safe to be forwarded further.
     *
     * Hop-by-hop headers are meaningful only for a single transport-level connection,
     * and must not be transmitted by proxies.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers#hop-by-hop_headers
     *
     * @return array<string, string> Safe headers suitable for forwarding further.
     */
    public function keep_forwardable_headers( array $headers ): array {
        $hop_by_hop_headers = array_merge(
            $this->get_hop_by_hop_headers_in_connection( $headers ),
            $this->get_standard_hop_by_hop_headers()
        );

        $forwardable_headers = array();

        foreach ( $headers as $header => $value ) {
            $normalized_header = strtolower( $header );

            if ( in_array( $normalized_header, $hop_by_hop_headers, true ) ) {
                // Hop-by-hop header, skip
                continue;
            }

            $forwardable_headers[ $header ] = $value;
        }

        return $forwardable_headers;
    }

    /**
     * Set the `Host` header based on the upstream URL.
     *
     * @param string $upstream_url Upstream URL.
     *
     * @return Headers
     */
    public function set_host( string $upstream_url ): Headers {
        $parsed_url = parse_url( $upstream_url );

        if ( $parsed_url && ! empty( $parsed_url['host'] ) ) {
            $this->headers['Host'] = $parsed_url['host'];
        }

        return $this; // chainable
    }

    /**
     * Set (or append) an IP address to the `X-Forwarded-For` header.
     *
     * @return Headers
     */
    public function set_x_forwarded_for(): Headers {
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $this->headers['X-Forwarded-For'] = $_SERVER['REMOTE_ADDR'];
        }

        return $this; // chainable
    }

    /**
     * Set the `Cookie` header (if there are cookies in the request) to be forwarded explicitly.
     *
     * @return Headers
     */
    public function set_cookie(): Headers {
        if ( ! empty( $_SERVER['HTTP_COOKIE'] ) ) {
            $this->headers['Cookie'] = $_SERVER['HTTP_COOKIE'];
        }

        return $this; // chainable
    }

    /**
     * Set the `X-Forwarded-Country` header based on the geographic location of the client IP address.
     *
     * @return Headers
     */
    public function set_geo_location(): Headers {
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip_address   = $this->geo_locator->get_ip_address();
            $country_code = $this->geo_locator->maybe_get_country_code( $ip_address );

            if ( $country_code ) {
                $this->headers['X-Forwarded-Country'] = $country_code;
            }
        }

        return $this; // chainable
    }

    /**
     * Return an associative array of all headers to be forwarded.
     *
     * @return array<string, string>
     */
    public function get_headers(): array {
        return $this->headers;
    }

}
