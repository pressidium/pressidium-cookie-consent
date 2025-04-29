<?php
/**
 * GPT.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

use Pressidium\WP\CookieConsent\Dependencies\Orhanerday\OpenAi\OpenAi;

use RuntimeException;
use Exception;

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
     * @var ?OpenAi OpenAI client.
     */
    private ?OpenAi $client = null;

    /**
     * Initialize GPT client.
     *
     * @param string $api_key API key.
     *
     * @return GPT
     */
    public function init( string $api_key ): GPT {
        $this->client = new OpenAi( $api_key );

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
     * @throws RuntimeException If an API key is not set.
     * @throws RuntimeException If the response from the OpenAI API could not be decoded.
     * @throws RuntimeException If the response from the OpenAI API is invalid.
     * @throws RuntimeException If no models are found in the response from the OpenAI API.
     *
     * @return array<array<string, string>>
     */
    protected function get_provider_models(): array {
        if ( is_null( $this->client ) ) {
            throw new RuntimeException( 'API key not set.' );
        }

        $response      = $this->client->listModels();
        $response_data = json_decode( $response );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new RuntimeException( 'Could not decode the response from the OpenAI API' );
        }

        if ( ! is_object( $response_data ) || ! isset( $response_data->data ) || ! is_array( $response_data->data ) ) {
            throw new RuntimeException( 'Invalid response from the OpenAI API' );
        }

        $models = array();

        foreach ( $response_data->data as $model ) {
            if ( isset( $model->id ) && is_string( $model->id ) ) {
                $models[] = array(
                    'id'   => $model->id,
                    'name' => $model->id,
                );
            }
        }

        if ( empty( $models ) ) {
            throw new RuntimeException( 'No models found in the response from the OpenAI API' );
        }

        return $models;
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
            'gpt-4.1',
            'gpt-4.5-preview',
        );
    }

    /**
     * Send a message to GPT.
     *
     * @throws RuntimeException If an API key is not set.
     *
     * @param string $text Message to send.
     *
     * @return string GPT response.
     */
    public function prompt( string $text ): string {
        if ( is_null( $this->client ) ) {
            throw new RuntimeException( 'API key not set.' );
        }

        try {
            $response = $this->client->chat(
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
        } catch ( Exception $exception ) {
            throw new RuntimeException( $exception->getMessage() );
        }

        $result = json_decode( $response );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new RuntimeException( 'Could not decode the response from the OpenAI API' );
        }

        if ( ! is_object( $result ) || ! isset( $result->choices ) || ! is_array( $result->choices ) ) {
            throw new RuntimeException( 'Unexpected response structure from the OpenAI API' );
        }

        if ( ! isset( $result->choices[0]->message->content ) ) {
            throw new RuntimeException( 'Invalid response from the OpenAI API' );
        }

        return $result->choices[0]->message->content;
    }

}
