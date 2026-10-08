<?php
/**
 * Table CSV exporter.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

use const Pressidium\WP\CookieConsent\PLUGIN_FILE;

use Pressidium\WP\CookieConsent\Logging\Logger;

use WP_REST_Response;
use WP_HTTP_Response;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * CSV_Exporter class.
 *
 * @since 1.2.0
 */
class CSV_Exporter implements Exporter {

    /**
     * @var Logger An instance of `Logger`.
     */
    private Logger $logger;

    /**
     * CSV_Exporter constructor.
     *
     * @param Logger $logger An instance of `Logger`.
     */
    public function __construct( Logger $logger ) {
        $this->logger = $logger;
    }

    /**
     * Whether the given value starts with a character that could start a formula.
     *
     * @link https://community.owasp.org/attacks/CSV_Injection
     *
     * @since 2.0.0
     *
     * @param string $value Non-empty value to check.
     *
     * @return bool
     */
    private function starts_with_formula_trigger( string $value ): bool {
        if ( strpos( "=+-@\t\r\n", $value[0] ) !== false ) {
            return true;
        }

        $fullwidth_triggers = array(
            "\u{FF1D}", // ＝ fullwidth equals
            "\u{FF0B}", // ＋ fullwidth plus
            "\u{FF0D}", // － fullwidth minus
            "\u{FF20}", // ＠ fullwidth at
        );

        // Check double-byte full-width characters by comparing the leading byte sequence
        foreach ( $fullwidth_triggers as $trigger ) {
            if ( strncmp( $value, $trigger, strlen( $trigger ) ) === 0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the given value is a number carrying an explicit sign.
     *
     * Such a value is not a formula, so `-5` stays a number instead of becoming
     * text. The leading character is checked before `is_numeric()` on purpose:
     * PHP accepts leading whitespace in numeric strings, so `is_numeric( "\t5" )`
     * is `true` and a tab-prefixed value would otherwise skip escaping and carry
     * the tab straight through.
     *
     * @since 2.0.0
     *
     * @param string $value Non-empty value to check.
     *
     * @return bool
     */
    private function is_signed_number( string $value ): bool {
        if ( $value[0] !== '-' && $value[0] !== '+' ) {
            return false;
        }

        return is_numeric( $value );
    }

    /**
     * Escape a single value for inclusion in the CSV output.
     *
     * Formula injection: Spreadsheet applications evaluate a cell that starts with
     * a formula-initiating character. Consent records hold visitor-supplied values
     * such as the URL and the user agent, so without this an attacker can have a
     * formula run on the machine of the administrator opening the export.
     * Prefixing with a single quote (`'`) marks the cell as literal text.
     *
     * Quoting: Fields are wrapped in double quotes (`"`), so a double quote inside
     * a value has to be written twice, per RFC 4180. Otherwise, a value containing
     * `"` ends its field early, which both corrupts the row and lets a crafted
     * user agent inject extra columns into the export.
     *
     * @link https://community.owasp.org/attacks/CSV_Injection
     * @link https://www.rfc-editor.org/info/rfc4180/
     *
     * @since 2.0.0
     *
     * @param mixed $value Value to escape.
     *
     * @return string
     */
    private function escape_cell( $value ): string {
        $value = (string) $value;

        if (
            $value !== ''
            && $this->starts_with_formula_trigger( $value )
            && ! $this->is_signed_number( $value )
        ) {
            // Prepend the cell field with a single quote
            $value = "'" . $value;
        }

        // Escape every double quote using an additional double quote
        return str_replace( '"', '""', $value );
    }

    /**
     * Return the content for the CSV file.
     *
     * @param Table $table The table to export.
     *
     * @return ?string The CSV file content, or `null` if the table has no data.
     */
    private function get_csv_content( Table $table ): ?string {
        $rows = $table->get_all_rows();

        if ( empty( $rows ) ) {
            $this->logger->warning( 'Attempted to export a table with no data.' );
            return null;
        }

        // Wrap each cell field in double quotes
        $csv_output = '"' . implode( '","', array_map( array( $this, 'escape_cell' ), array_keys( $rows[0] ) ) ) . '"';

        foreach ( $rows as $row ) {
            $csv_output .= "\r\n" . '"' . implode( '","', array_map( array( $this, 'escape_cell' ), $row ) ) . '"';
        }

        return $csv_output;
    }

    /**
     * Return the name for the CSV file.
     *
     * @param Table $table The table to export.
     *
     * @return string
     */
    private function get_filename( Table $table ): string {
        $timestamp = gmdate( 'Y-m-d-H-i-s' );

        try {
            return sprintf( '%s_%s', $table->get_table_slug(), $timestamp );
        } catch ( Exception $exception ) {
            $this->logger->warning( 'Could not get the table slug, falling back to a generic name.' );

            $file_info      = pathinfo( PLUGIN_FILE );
            $file_extension = $file_info['extension'];
            $plugin_name    = basename( PLUGIN_FILE, '.' . $file_extension );

            return sprintf( '%s_%s', $plugin_name . '_table', $timestamp );
        }
    }

    /**
     * Serve the exported file.
     *
     * @param bool             $served Whether the request has already been served.
     * @param WP_HTTP_Response $result Result to send to the client. Usually a `WP_REST_Response`.
     *
     * @return bool Whether the request has been served.
     */
    public function do_export( bool $served, WP_HTTP_Response $result ): bool {
        $is_csv   = false;
        $csv_data = null;

        foreach ( $result->get_headers() as $header => $value ) {
            if ( strtolower( $header ) === 'content-type' ) {
                // Confirm that we really want to serve a CSV file
                $is_csv   = strpos( $value, 'text/csv' ) === 0;
                $csv_data = $result->get_data();
                break;
            }
        }

        if ( ! $is_csv || empty( $csv_data ) ) {
            return $served;
        }

        // Output the CSV data
        echo $csv_data;

        return true;
    }

    /**
     * Return a WP REST Response to export a CSV file with the given table's data.
     *
     * @param Table $table The table to export.
     *
     * @return WP_REST_Response
     */
    public function export( Table $table ): WP_REST_Response {
        $response = new WP_REST_Response();

        $content = $this->get_csv_content( $table );

        if ( empty( $content ) ) {
            $response->set_data( 'not-found' );
            $response->set_status( 404 );

            return $response;
        }

        $filename = $this->get_filename( $table );
        $headers  = array(
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Length'      => strlen( $content ),
            'Content-Disposition' => 'filename=' . $filename . '.csv',
        );

        $response->set_data( $content );
        $response->set_headers( $headers );

        add_filter( 'rest_pre_serve_request', array( $this, 'do_export' ), 0, 2 );

        return $response;
    }

}
