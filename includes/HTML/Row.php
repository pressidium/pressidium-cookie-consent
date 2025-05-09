<?php
/**
 * Table row HTML renderer.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\HTML;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Row class.
 *
 * @since 1.8.0
 */
final class Row extends Element {

    /**
     * @var Cell[] Row cells.
     */
    private array $cells = array();

    /**
     * Add a cell to the row.
     *
     * @param Cell $cell
     *
     * @return void
     */
    public function add_cell( Cell $cell ): void {
        $this->cells[] = $cell;
    }

    /**
     * Return the HTML representation of the row.
     *
     * @return string
     */
    public function get_html(): string {
        $html = '<tr ' . $this->render_attributes() . '>';

        foreach ( $this->cells as $cell ) {
            $html .= $cell->get_html();
        }

        $html .= '</tr>';

        return $html;
    }

}
