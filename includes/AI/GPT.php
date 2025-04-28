<?php
/**
 * GPT.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

use Pressidium\WP\CookieConsent\Dependencies\OpenAI;
use Pressidium\WP\CookieConsent\Dependencies\OpenAI\Client;

use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * GPT class.
 *
 * @since 1.8.0
 */
final class GPT extends AI {

    /**
     * @var ?Client OpenAI client.
     */
    private ?Client $client = null;

    /**
     * Initialize GPT client.
     *
     * @param string $api_key API key.
     *
     * @return GPT
     */
    public function init( string $api_key ): GPT {
        $this->client = OpenAI::client( $api_key );

        return $this; // chainable
    }

    /**
     * Return the default GPT model.
     *
     * @return string
     */
    public function get_default_model(): string {
        return 'gpt-4o';
    }

    /**
     * Return the models OpenAI supports.
     *
     * @throws RuntimeException If API key is not set.
     *
     * @return array<array<string, string>>
     */
    protected function get_provider_models(): array {
        if ( is_null( $this->client ) ) {
            throw new RuntimeException( 'API key not set.' );
        }

        $response = $this->client->models()->list();

        return array_map(
            function ( $model ) {
                return array(
                    'id'   => $model->id,
                    'name' => $model->id,
                );
            },
            $response->data
        );
    }

    /**
     * Return the allowed models to filter the list of models returned by the provider.
     *
     * @return string[]
     */
    protected function get_allowed_models(): array {
        return array(
            'gpt-3.5-turbo',
            'gpt-4',
            'gpt-4-turbo',
            'gpt-4o',
            'gpt-4o-mini',
        );
    }

    /**
     * Send a message to GPT.
     *
     * @throws RuntimeException If API key is not set.
     *
     * @param string $text Message to send.
     *
     * @return string GPT response.
     */
    public function prompt( string $text ): string {
        if ( is_null( $this->client ) ) {
            throw new RuntimeException( 'API key not set.' );
        }

        $result = $this->client->chat()->create(
            array(
                'model'    => $this->model,
                'messages' => array(
                    array(
                        'role'    => 'system',
                        'content' => $this->system_prompt,
                    ),
                    array(
                        'role'    => 'user',
                        'content' => $text,
                    ),
                ),
            )
        );

        return $result->choices[0]->message->content;
    }

}
