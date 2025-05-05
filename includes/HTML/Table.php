<?php
/**
 * Table HTML renderer.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\HTML;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Table class.
 *
 * @since 1.8.0
 */
final class Table extends Element {

    /**
     * @var string[] Table column headers.
     */
    private array $headers = array();

    /**
     * @var Row[] Table rows.
     */
    private array $rows = array();

    /**
     * Set the table column headers.
     *
     * @param string[] $headers
     *
     * @return Table
     */
    public function set_headers( array $headers ): Table {
        $this->headers = $headers;

        return $this; // chainable
    }

    /**
     * Add a row to the table.
     *
     * @param Row $row Row to add.
     *
     * @return Table
     */
    public function add_row( Row $row ): Table {
        $this->rows[] = $row;

        return $this; // chainable
    }

    /**
     * Return the HTML representation of the table.
     *
     * @return string
     */
    public function get_html(): string {
        $html = '<table ' . $this->render_attributes() . '>';

        $html .= '<thead><tr>';
        foreach ( $this->headers as $header ) {
            $html .= sprintf( '<th>%s</th>', esc_html( $header ) );
        }
        $html .= '</tr></thead>';

        $html .= '<tbody>';
        foreach ( $this->rows as $row ) {
            $html .= wp_kses_post( $row->get_html() );
        }
        $html .= '</tbody></table>';

        return $html;
    }

}
