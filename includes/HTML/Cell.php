<?php
/**
 * Table cell HTML renderer.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\HTML;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Cell class.
 *
 * @since 1.8.0
 */
final class Cell extends Element {

    /**
     * @var string Cell content.
     */
    private string $content = '';

    /**
     * Set the cell content.
     *
     * @param string $content
     *
     * @return Cell
     */
    public function set_content( string $content ): Cell {
        $this->content = $content;

        return $this; // chainable
    }

    /**
     * Return the HTML representation of the cell.
     *
     * @return string
     */
    public function get_html(): string {
        return '<td ' . $this->render_attributes() . '>' . wp_kses_post( $this->content ) . '</td>';
    }

}
