<?php
/**
 * Cookies shortcode attributes.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Shortcodes\Cookies_Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Shortcode_Attributes class.
 *
 * @since 1.8.0
 */
class Shortcode_Attributes {

    /**
     * @var string Cookie category.
     */
    private string $category;

    /**
     * @var string[] List of columns to show.
     */
    private array $show_columns;

    /**
     * Set the cookie category.
     *
     * @param mixed $category
     *
     * @return Shortcode_Attributes
     */
    public function set_category( $category ): Shortcode_Attributes {
        $this->category = sanitize_text_field( $category );

        return $this; // chainable
    }

    /**
     * Return the cookie category.
     *
     * @return string
     */
    public function get_category(): string {
        return $this->category;
    }

    /**
     * Set the columns to show.
     *
     * @param mixed $show_columns
     *
     * @return Shortcode_Attributes
     */
    public function set_show_columns( $show_columns ): Shortcode_Attributes {
        $this->show_columns = explode( ',', sanitize_text_field( $show_columns ) );

        return $this; // chainable
    }

    /**
     * Return the columns to show.
     *
     * @return string[]
     */
    public function get_columns_to_show(): array {
        return $this->show_columns;
    }

}
