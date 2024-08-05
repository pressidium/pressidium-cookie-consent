<?php
/**
 * Blueprint.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Blueprint class.
 *
 * @since 1.2.0
 */
class Blueprint {

    /**
     * @var Column[] Array of columns.
     */
    private array $columns;

    /**
     * Blueprint constructor.
     */
    public function __construct() {
        $this->columns = array();
    }

    /**
     * Create a new column.
     *
     * @param string $column_name Name of the column.
     * @param string $column_type Type of the column.
     * @param ?int   $length      Length of the column.
     *
     * @return Column
     */
    private function create_column( string $column_name, string $column_type, int $length = null ): Column {
        $column = new Column( $column_name, $column_type, $length );

        $this->columns[ $column_name ] = $column;

        return $column;
    }

    /**
     * Create a new string column.
     *
     * Equivalent to `VARCHAR` in MySQL.
     *
     * @param string $column_name Name of the column.
     * @param ?int   $length      Length of the column.
     *
     * @return Column
     */
    public function string( string $column_name, int $length = null ): Column {
        return $this->create_column( $column_name, 'VARCHAR', $length );
    }

    /**
     * Create a new text column.
     *
     * Equivalent to `TEXT` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function text( string $column_name ): Column {
        return $this->create_column( $column_name, 'TEXT' );
    }

    /**
     * Create a new integer column.
     *
     * Equivalent to `INT` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function integer( string $column_name ): Column {
        return $this->create_column( $column_name, 'INT' );
    }

    /**
     * Create a new float column.
     *
     * Equivalent to `FLOAT` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function float( string $column_name ): Column {
        return $this->create_column( $column_name, 'FLOAT' );
    }

    /**
     * Create a new boolean column.
     *
     * Equivalent to `TINYINT(1)` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function boolean( string $column_name ): Column {
        return $this->create_column( $column_name, 'TINYINT', 1 );
    }

    /**
     * Create a new enum column.
     *
     * Equivalent to `ENUM` in MySQL.
     *
     * @param string $column_name Name of the column.
     * @param array  $values      Array of enum values.
     *
     * @return Column
     */
    public function enum( string $column_name, array $values ): Column {
        $column = $this->create_column( $column_name, 'ENUM' );
        $column->set_values( $values );

        return $column;
    }

    /**
     * Create a new date column.
     *
     * Equivalent to `DATE` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function date( string $column_name ): Column {
        return $this->create_column( $column_name, 'DATE' );
    }

    /**
     * Create a new time column.
     *
     * Equivalent to `TIME` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function time( string $column_name ): Column {
        return $this->create_column( $column_name, 'TIME' );
    }

    /**
     * Create a new datetime column.
     *
     * Equivalent to `DATETIME` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function datetime( string $column_name ): Column {
        return $this->create_column( $column_name, 'DATETIME' );
    }

    /**
     * Create a new timestamp column.
     *
     * Equivalent to `TIMESTAMP` in MySQL.
     *
     * @param string $column_name Name of the column.
     *
     * @return Column
     */
    public function timestamp( string $column_name ): Column {
        return $this->create_column( $column_name, 'TIMESTAMP' );
    }

    /**
     * Create the `created_at` and `updated_at` columns.
     *
     * @param bool $default_to_now Whether to default the columns to the current timestamp.
     *
     * @return void
     */
    public function timestamps( bool $default_to_now = true ): void {
        $current_timestamp = new Raw( 'CURRENT_TIMESTAMP' );

        $created_at_column = $this->timestamp( 'created_at' )
                                  ->not_nullable();

        $updated_at_column = $this->timestamp( 'updated_at' )
                                  ->not_nullable()
                                  ->on_update( $current_timestamp );

        if ( $default_to_now ) {
            $created_at_column->default_to( $current_timestamp );
            $updated_at_column->default_to( $current_timestamp );
        }
    }

    /**
     * Return the primary key for the table's blueprint object.
     *
     * @link https://developer.wordpress.org/reference/functions/dbdelta/
     *
     * @return string
     */
    private function get_primary_key(): string {
        $columns = array();

        foreach ( $this->columns as $column ) {
            if ( $column->is_primary_key() ) {
                $columns[] = $column->get_name();
            }
        }

        if ( empty( $columns ) ) {
            return '';
        }

        /*
         * Keep two spaces between `PRIMARY KEY` and the column(s),
         * which is required for `dbDelta()` to work correctly.
         */
        return sprintf( 'PRIMARY KEY  (%s)', implode( ', ', $columns ) );
    }

    /**
     * Generate SQL for the table's blueprint object.
     *
     * @param string $table_name      Name of the table.
     * @param string $charset_collate Database character collate.
     *
     * @return string
     */
    public function generate( string $table_name, string $charset_collate = '' ): string {
        $columns_sql = implode(
            ", \n",
            array_map(
                function( Column $column ) {
                    return $column->get_sql();
                },
                $this->columns
            )
        );

        $primary_key = $this->get_primary_key();

        if ( ! empty( $primary_key ) ) {
            $columns_sql .= ", \n{$primary_key}";
        }

        return "CREATE TABLE {$table_name} (\n{$columns_sql}\n) {$charset_collate}";
    }

}
