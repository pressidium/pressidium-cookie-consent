<?php
/**
 * Element abstract class.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\HTML;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Element class.
 *
 * @since 1.8.0
 */
abstract class Element {

    /**
     * @var array<string, string> HTML attributes.
     */
    private array $attributes = array();

    /**
     * Set the HTML attributes of the element.
     *
     * @param string $key   Attribute key.
     * @param string $value Attribute value.
     *
     * @return Element
     */
    public function add_attribute( string $key, string $value ): Element {
        $this->attributes[ $key ] = $value;

        return $this; // chainable
    }

    /**
     * Return the HTML attributes of the element as a string.
     *
     * @return string
     */
    protected function render_attributes(): string {
        $attributes = array();

        foreach ( $this->attributes as $key => $value ) {
            $attributes[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
        }

        return implode( ' ', $attributes );
    }

    /**
     * Return the HTML representation of the element.
     *
     * @return string
     */
    abstract public function get_html(): string;

}
