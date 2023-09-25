<?php
/**
 * Table exporter.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Exporter interface.
 *
 * @since 1.2.0
 */
interface Exporter {

    /**
     * Return the WP REST response to export a file with the given table's data.
     *
     * @param Table $table
     *
     * @return WP_REST_Response
     */
    public function export( Table $table ): WP_REST_Response;

}
