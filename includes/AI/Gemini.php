<?php
/**
 * Gemini.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

use Pressidium\WP\CookieConsent\Utils\String_Utils;

use Pressidium\WP\CookieConsent\Dependencies\GuzzleHttp\Client as GuzzleClient;
use Pressidium\WP\CookieConsent\Dependencies\Nyholm\Psr7\Factory\Psr17Factory;

use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Client;
use Pressidium\WP\CookieConsent\Dependencies\GeminiAPI\Resources\Parts\TextPart;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Http\Client\ClientExceptionInterface;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Gemini class.
 *
 * @since 1.8.0
 */
final class Gemini extends AI {

    /**
     * @var ?Client Gemini client.
     */
    private ?Client $client = null;

    /**
     * Initialize the Gemini client.
     *
     * @param string $api_key API key.
     *
     * @return Gemini
     */
    public function init( string $api_key ): Gemini {
        $guzzle       = new GuzzleClient();
        $psr17Factory = new Psr17Factory();

        $this->client = new Client(
            $api_key,
            $guzzle,
            $psr17Factory, // request factory
            $psr17Factory  // stream factory
        );

        return $this; // chainable
    }

    /**
     * Return the default Gemini model.
     *
     * @return string
     */
    public function get_default_model(): string {
        return 'models/gemini-2.0-flash';
    }

    /**
     * Return the models Gemini supports.
     *
     * @throws RuntimeException If an API key is not set, or if there is an error communicating with the Gemini API.
     *
     * @return array<array<string, string>>
     */
    protected function get_provider_models(): array {
        if ( is_null( $this->client ) ) {
            throw new RuntimeException( 'API key not set.' );
        }

        try {
            $response = $this->client->listModels();
        } catch ( ClientExceptionInterface $exception ) {
            throw new RuntimeException( 'Could not communicate with the Gemini API' );
        }

        return array_map(
            function ( $model ) {
                return array(
                    'id'   => $model->name,
                    // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
                    'name' => $model->displayName,
                );
            },
            $response->models
        );
    }

    /**
     * Return the allowed models to filter the list of models returned by the provider.
     *
     * @return string[]
     */
    protected function get_allowed_models(): array {
        return array(
            'models/gemini-1.5-pro',
            'models/gemini-1.5-flash',
            'models/gemini-2.0-flash',
            'models/gemini-2.0-flash-lite',
        );
    }

    /**
     * Send a message to Gemini.
     *
     * @throws RuntimeException If an API key is not set.
     * @throws RuntimeException If there is an error communicating with the Gemini API.
     *
     * @param string $text Message to send.
     *
     * @return string Gemini response.
     */
    public function prompt( string $text ): string {
        if ( is_null( $this->client ) ) {
            throw new RuntimeException( 'API key not set.' );
        }

        try {
            $response = $this->client
                ->withV1BetaVersion()
                ->generativeModel( String_Utils::strip_prefix( 'models/', $this->model ) )
                ->withSystemInstruction( $this->system_prompt )
                ->generateContent(
                    new TextPart( $text )
                );

            return $response->text();
        } catch ( ClientExceptionInterface $exception ) {
            throw new RuntimeException( 'Could not communicate with the Gemini API' );
        }
    }

}
