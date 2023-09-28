<?php
/**
 * Sanitizer.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Sanitizer class.
 *
 * @since 1.2.0
 */
class Sanitizer {

    /**
     * @var mixed Value to sanitize.
     */
    private $value;

    /**
     * Sanitizer constructor.
     *
     * @param mixed $value Value to sanitize.
     */
    public function __construct( $value ) {
        $this->value = $value;
    }

    /**
     * Remove accents from the value.
     *
     * @return Sanitizer
     */
    public function remove_accents(): Sanitizer {
        $this->value = \remove_accents( $this->value );

        return $this; // chainable
    }

    /**
     * Remove special characters from the value.
     *
     * @return Sanitizer
     */
    public function remove_special_characters(): Sanitizer {
        $this->value = \sanitize_key( $this->value );

        return $this; // chainable
    }

    /**
     * Replace any hyphens in the value with underscores.
     *
     * @return Sanitizer
     */
    public function replace_hyphens_with_underscores(): Sanitizer {
        $this->value = str_replace( '-', '_', $this->value );

        return $this; // chainable
    }

    /**
     * Remove any double underscores from the value.
     *
     * @return Sanitizer
     */
    public function remove_double_underscores(): Sanitizer {
        $this->value = strtolower( $this->value );

        return $this; // chainable
    }

    /**
     * Remove any trailing underscores from the value.
     *
     * @return Sanitizer
     */
    public function trim_trailing_underscores(): Sanitizer {
        $this->value = rtrim( $this->value, '_' );

        return $this; // chainable
    }

    /**
     * Return the sanitized value.
     *
     * @return string
     */
    public function get_value(): string {
        return $this->value;
    }

}
