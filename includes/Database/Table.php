<?php
/**
 * Table.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

use Pressidium\WP\CookieConsent\Sanitizer;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Table abstract class.
 *
 * @since 1.2.0
 */
abstract class Table {

    /**
     * @var string Database character collate.
     */
    private string $charset_collate;

    /**
     * @var string The assigned WordPress table prefix for the site.
     */
    private string $prefix;

    /**
     * Table constructor.
     */
    public function __construct() {
        global $wpdb;

        $this->charset_collate = $wpdb->get_charset_collate();
        $this->prefix          = $wpdb->prefix;
    }

    /**
     * Return the table name without the prefix.
     *
     * @return string
     */
    abstract protected function get_table_name(): string;

    /**
     * Build the table schema.
     *
     * @param Blueprint $table Table's blueprint.
     *
     * @return void
     */
    abstract protected function get_table_schema( Blueprint $table ): void;

    /**
     * Return the schema version of the table.
     *
     * @return string
     */
    abstract public function get_version(): string;

    /**
     * Return the table's prefix.
     *
     * @return string
     */
    public function get_prefix(): string {
        return $this->prefix;
    }

    /**
     * Sanitize the given table name.
     *
     * @throws Exception If the table name is empty.
     *
     * @param string $table_name Table name to sanitize.
     *
     * @return string
     */
    private function sanitize_table_name( string $table_name ): string {
        if ( empty( $table_name ) ) {
            throw new Exception( 'Table name cannot be empty.' );
        }

        $table_name_sanitizer = new Sanitizer( $table_name );
        $sanitized_table_name = $table_name_sanitizer
            ->remove_accents()
            ->remove_special_characters()
            ->replace_hyphens_with_underscores()
            ->get_value();

        if ( empty( $sanitized_table_name ) ) {
            throw new Exception( 'Sanitized table name cannot be empty.' );
        }

        return $sanitized_table_name;
    }

    /**
     * Return the table's slug.
     *
     * The slug is the sanitized table's name.
     *
     * @throws Exception If the table name is empty.
     *
     * @return string
     */
    public function get_table_slug(): string {
        return $this->sanitize_table_name( $this->get_table_name() );
    }

    /**
     * Check if the table exists in the database.
     *
     * @return bool `true` if the table exists, `false` if it doesn't.
     */
    public function exists(): bool {
        global $wpdb;

        try {
            $table_name = $this->prefix . $this->get_table_slug();
        } catch ( Exception $exception ) {
            // Table name doesn't exist, return `false`
            return false;
        }

        // Use `$wpdb->get_var()` to execute a SQL query to check for the table's existence
        $result = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
        );

        // If the result matches the provided table name, the table exists; otherwise, it doesn't
        return $result === $table_name;
    }

    /**
     * Insert a row into the table.
     *
     * Wrapper around `wpdb::insert()`.
     *
     * @link https://developer.wordpress.org/reference/classes/wpdb/insert/
     *
     * @throws Exception If the table name is empty.
     *
     * @param array        $data   Data to insert (in column => value pairs).
     *                             Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     *                             Sending a `null` value will cause the column to be set to NULL - the corresponding
     *                             format is ignored in this case.
     * @param array|string $format Optional. An array of formats to be mapped to each of the values in $data.
     *                             If `string`, that format will be used for all the values in $data.
     *                             A format is one of `'%d'`, `'%f'`, `'%s'` (integer, float, string).
     *                             If omitted, all values in $data will be treated as strings unless
     *                             otherwise specified in `wpdb::$field_types`.
     *
     * @return int|false The number of rows inserted, or `false` on error.
     */
    public function insert( array $data, $format = null ) {
        global $wpdb;

        return $wpdb->insert( $this->prefix . $this->get_table_slug(), $data, $format );
    }

    /**
     * Update a row in the table.
     *
     * Wrapper around `wpdb::update()`.
     *
     * @link https://developer.wordpress.org/reference/classes/wpdb/update/
     *
     * @throws Exception If the table name is empty.
     *
     * @param array        $data         Data to update (in column => value pairs).
     *                                   Both $data columns and $data values should be "raw" (neither should be SQL
     *                                   escaped). Sending a `null` value will cause the column to be set to NULL -
     *                                   the corresponding format is ignored in this case.
     * @param array        $where        A named array of WHERE clauses (in column => value pairs).
     *                                   Multiple clauses will be joined with `AND`s. Both $where columns and $where
     *                                   values should be "raw" (neither should be SQL escaped). Sending a `null`
     *                                   value will create an `IS NULL` comparison - the corresponding format will be
     *                                   ignored in this case.
     * @param array|string $format       Optional. An array of formats to be mapped to each of the values in $data.
     *                                   If `string`, that format will be used for all the values in $data.
     *                                   A format is one of `'%d'`, `'%f'`, `'%s'` (integer, float, string).
     *                                   If omitted, all values in $data will be treated as strings unless
     *                                   otherwise specified in `wpdb::$field_types`.
     * @param array|string $where_format Optional. An array of formats to be mapped to each of the values in $where.
     *                                   If `string`, that format will be used for all the values in $where.
     *                                   A format is one of `'%d'`, `'%f'`, `'%s'` (integer, float, string).
     *                                   If omitted, all values in $where will be treated as strings.
     *
     * @return int|false The number of rows updated, or `false` on error.
     */
    public function update( array $data, array $where, $format = null, $where_format = null ) {
        global $wpdb;

        return $wpdb->update( $this->prefix . $this->get_table_slug(), $data, $where, $format, $where_format );
    }

    /**
     * Return the total number of rows in the table.
     *
     * @return int
     */
    public function get_total_number_of_rows(): int {
        global $wpdb;

        $table_name = $this->get_prefix() . $this->get_table_name();

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name}"
            )
        );
    }

    /**
     * Return table rows for the given page and number of items per page.
     *
     * @param int $page     Page number.
     * @param int $per_page Number of items per page.
     *
     * @return array
     */
    public function get_rows( int $page, int $per_page = 20 ): array {
        global $wpdb;

        $table_name = $this->get_prefix() . $this->get_table_name();

        $offset = ( $page - 1 ) * $per_page;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Return all table rows.
     *
     * @return array
     */
    public function get_all_rows(): array {
        global $wpdb;

        $table_name = $this->get_prefix() . $this->get_table_name();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC"
            ),
            ARRAY_A
        );
    }

    /**
     * Clear the table.
     *
     * @return bool
     */
    public function clear(): bool {
        global $wpdb;

        $table_name = $this->get_prefix() . $this->get_table_name();

        return $wpdb->query(
            $wpdb->prepare(
                "TRUNCATE TABLE {$table_name}"
            )
        );
    }

    /**
     * Actually modify the database to create the table.
     *
     * @throws Exception If the table name is empty.
     *
     * @return void
     */
    public function create(): void {
        if ( ! function_exists( '\dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $table_name = $this->prefix . $this->get_table_slug();

        $schema = Schema::create(
            $table_name,
            $this->charset_collate,
            function( Blueprint $table ) {
                $this->get_table_schema( $table );
            }
        );

        $sql = $schema->generate();

        dbDelta( $sql );
    }

}
