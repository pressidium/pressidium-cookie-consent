<?php
/**
 * Shortcode abstract class.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Shortcode abstract class.
 *
 * @since 1.8.0
 */
abstract class Shortcode {

    /**
     * Return the default values for the shortcode attributes.
     *
     * Override this method in the child class to provide
     * default values for the shortcode attributes.
     *
     * @return array<string, mixed>
     */
    public function get_default_attributes(): array {
        return array();
    }

    /**
     * Return the shortcode tag (without square brackets).
     *
     * @return string
     */
    abstract public function get_tag(): string;

    /**
     * Return the shortcode output.
     *
     * This method should return the output of the shortcode.
     * It should *never* produce an output of any kind.
     *
     * @see https://developer.wordpress.org/reference/functions/add_shortcode/
     *
     * @param array<string, mixed> $atts    Shortcode attributes (with default values).
     * @param string               $content Shortcode content.
     *
     * @return string
     */
    abstract public function get_output( array $atts = array(), string $content = '' ): string;

    /**
     * Callback method for `add_shortcode()`.
     *
     * @param array<string, mixed> $atts    Shortcode attributes.
     * @param string               $content Shortcode content.
     *
     * @return string
     */
    public function callback( array $atts = array(), string $content = '' ): string {
        return $this->get_output( shortcode_atts( $this->get_default_attributes(), $atts ), $content );
    }

}
