<?php
/**
 * AI abstract class.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * AI class.
 *
 * @since 1.8.0
 */
abstract class AI {

    // phpcs:ignore Generic.Files.LineLength
    const TRANSLATE_SYSTEM_PROMPT = 'You are a language translation AI. User asks you to translate a text to a specific language. Do not include anything in the response other than the translation.';

    // phpcs:ignore Generic.Files.LineLength
    const TRANSLATE_ALL_SYSTEM_PROMPT = '
        You will be given a JSON object. Your task is to translate all values into the [target language], preserving the structure and keeping all keys untouched.
        If the string includes HTML, translate only the visible text and leave tags and attributes untouched.
        Example:
        "description": "Click <button class=\'btn\'>here</button>" → "description": "Κάντε κλικ <button class=\'btn\'>εδώ</button>"
        Output only the full translated JSON object, preserving its structure. Do not include anything else in the response, not even a formatted markdown code block.
    ';

    // phpcs:ignore Generic.Files.LineLength
    const COOKIE_DESCRIPTION_SYSTEM_PROMPT = 'You are an AI tool that generates descriptions for cookies. User sends you a cookie name. Write the description for that cookie. If you have no information about the cookie, just say "N/A" but only if absolutely necessary. Do not include anything in the response other than the description.';

    /**
     * @var string System prompt.
     */
    protected string $system_prompt;

    /**
     * @var string Model to use.
     */
    protected string $model;

    /**
     * AI constructor.
     */
    public function __construct() {
        // Set default values
        $this->system_prompt = $this->get_default_system_prompt();
        $this->model         = $this->get_default_model();
    }

    /**
     * Initialize the AI instance.
     *
     * @param string $api_key API key.
     */
    abstract public function init( string $api_key ): AI;

    /**
     * Return the default system prompt.
     *
     * @return string
     */
    public function get_default_system_prompt(): string {
        return 'You are an AI integration for a WordPress plugin.';
    }

    /**
     * Return the default model.
     *
     * @return string
     */
    abstract public function get_default_model(): string;

    /**
     * Set the system prompt.
     *
     * @param ?string $prompt System prompt.
     *
     * @return $this
     */
    public function set_system_prompt( ?string $prompt ): AI {
        if ( empty( $prompt ) ) {
            $this->system_prompt = $this->get_default_system_prompt();

            return $this; // chainable
        }

        $this->system_prompt = $prompt;

        return $this; // chainable
    }

    /**
     * Select the model to use.
     *
     * @param ?string $model Model to use.
     *
     * @return $this
     */
    public function select_model( ?string $model ): AI {
        if ( empty( $model ) ) {
            $this->model = $this->get_default_model();

            return $this; // chainable
        }

        $this->model = $model;

        return $this; // chainable
    }

    /**
     * Return the models the provider supports.
     *
     * @return array<array<string, string>>
     */
    abstract protected function get_provider_models(): array;

    /**
     * Return the allowed models to filter the list of models returned by the provider.
     *
     * @return string[]
     */
    abstract protected function get_allowed_models(): array;

    /**
     * Return all available models.
     *
     * @return array<array<string, string>>
     */
    public function get_models(): array {
        $provider_models = $this->get_provider_models();
        $allowed_models  = $this->get_allowed_models();

        $models = array();

        foreach ( $provider_models as $model ) {
            if ( in_array( $model['id'], $allowed_models, true ) ) {
                $models[] = $model;
            }
        }

        return $models;
    }

    /**
     * Send a message to the AI.
     *
     * @param string $text Message to send.
     *
     * @return string AI response.
     */
    abstract public function prompt( string $text ): string;

}
