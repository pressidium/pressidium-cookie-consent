<?php
/**
 * API.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Feedback;

use Pressidium\WP\CookieConsent\Utils\String_Utils;
use WP_Error;

/**
 * Class API.
 *
 * @since 1.1.0
 */
abstract class API {

    /**
     * Return the base URL for this API.
     *
     * @return string
     */
    abstract protected function get_api_base_url(): string;

    /**
     * Return the API URL for the base URL and route
     *
     * @param string $route Route to use.
     *
     * @return string
     */
    private function get_api_url( string $route ): string {
        return sprintf(
            '%s/%s',
            String_Utils::untrailing_slash_it( $this->get_api_base_url() ),
            String_Utils::unleading_slash_it( $route )
        );
    }

    /**
     * Send a POST request to this API.
     *
     * @param string $route   Route to send the request to.
     * @param ?array $data    Optional. Data to send to the API.
     * @param ?array $headers Optional. Headers to send to the API.
     * @param ?int   $timeout Optional. Timeout for the request in seconds. Defaults to `30` seconds.
     *
     * @return array|WP_Error
     */
    public function post(
        string $route,
        ?array $data = array(),
        ?array $headers = array(),
        ?int $timeout = 30
    ) {
        $url = $this->get_api_url( $route );

        return wp_remote_post(
            $url,
            array(
                'timeout'     => $timeout,
                'headers'     => array_merge( array( 'Content-Type' => 'application/json' ), $headers ),
                'body'        => wp_json_encode( $data ),
                'data_format' => 'body',
            )
        );
    }

}
