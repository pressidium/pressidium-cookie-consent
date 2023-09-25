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

        $csv_output = '"' . implode( '","', array_keys( $rows[0] ) ) . '"';

        foreach ( $rows as $row ) {
            $csv_output .= "\r\n" . '"' . implode( '","', $row ) . '"';
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
