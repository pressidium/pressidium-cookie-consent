<?php
/**
 * Column.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Column class.
 *
 * @since 1.2.0
 */
class Column {

    /**
     * @var string Name of the column.
     */
    private string $name;

    /**
     * @var string|Raw Default value for the column.
     */
    private $default;

    /**
     * @var ?string Column type.
     */
    private ?string $type = '';

    /**
     * @var ?int Column length.
     */
    private ?int $length = null;

    /**
     * @var array Enum values for the column.
     */
    private array $values = array();

    /**
     * @var bool Whether the column is nullable.
     */
    private bool $nullable = true;

    /**
     * @var bool Whether the column is set to auto increment.
     */
    private bool $auto_increment = false;

    /**
     * @var bool Whether the column is the primary key.
     */
    private bool $primary_key = false;

    /**
     * @var bool Whether the column is unique.
     */
    private bool $is_unique = false;

    /**
     * @var bool Whether the column is unsigned.
     */
    private bool $is_unsigned = false;

    /**
     * @var string|Raw ON UPDATE value for the column.
     */
    private $on_update_value = '';

    /**
     * Column constructor.
     *
     * @param string  $name   Column name.
     * @param ?string $type   Column type.
     * @param ?int    $length Column length.
     */
    public function __construct( string $name, string $type = '', int $length = null ) {
        $this->name   = $name;
        $this->type   = $type;
        $this->length = $length;
    }

    /**
     * Return the column's name.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Return the column's type.
     *
     * @return string
     */
    private function get_type(): string {
        return $this->type;
    }

    /**
     * Return the column's length.
     *
     * @return string
     */
    private function get_length(): string {
        return $this->length ? "({$this->length})" : '';
    }

    /**
     * Return the column's enum values.
     *
     * @return string
     */
    private function get_values(): string {
        return $this->values ? "('" . implode( "','", $this->values ) . "')" : '';
    }

    /**
     * Set the column's enum values.
     *
     * @param array $values Enum values.
     *
     * @return $this
     */
    public function set_values( array $values ): Column {
        $this->values = $values;

        return $this; // chainable
    }

    /**
     * Return the `DEFAULT` keyword followed by the default value for the column if it is set.
     *
     * @return string
     */
    private function get_default(): string {
        if ( $this->default instanceof Raw ) {
            return "DEFAULT {$this->default}";
        }

        return $this->default ? "DEFAULT '{$this->default}'" : '';
    }

    /**
     * Set the default value for the column.
     *
     * @param string|Raw $value Default value.
     *
     * @return Column
     */
    public function default_to( $value ): Column {
        $this->default = $value;

        return $this; // chainable
    }

    /**
     * Return the `NOT NULL` keyword if the column is not nullable.
     *
     * @return string
     */
    private function get_nullable(): string {
        return $this->nullable ? '' : 'NOT NULL';
    }

    /**
     * Set the column to be nullable.
     *
     * @return Column
     */
    public function nullable(): Column {
        $this->nullable = true;

        return $this; // chainable
    }

    /**
     * Set the column to be not nullable.
     *
     * @return Column
     */
    public function not_nullable(): Column {
        $this->nullable = false;

        return $this; // chainable
    }

    /**
     * Return the `AUTO_INCREMENT` keyword if the column is set to auto increment.
     *
     * @return string
     */
    private function get_auto_increment(): string {
        return $this->auto_increment ? 'AUTO_INCREMENT' : '';
    }

    /**
     * Set the column to auto increment.
     *
     * @return Column
     */
    public function auto_increment(): Column {
        $this->auto_increment = true;

        return $this; // chainable
    }

    /**
     * Whether the column is the primary key or part of a composite primary key.
     *
     * @return bool
     */
    public function is_primary_key(): bool {
        return $this->primary_key;
    }

    /**
     * Set the column to be the primary key.
     *
     * @return Column
     */
    public function primary(): Column {
        $this->primary_key = true;

        return $this; // chainable
    }

    /**
     * Return the `UNIQUE` keyword if the column is unique.
     *
     * @return string
     */
    private function get_unique(): string {
        return $this->is_unique ? 'UNIQUE' : '';
    }

    /**
     * Set the column to be unique.
     *
     * @return Column
     */
    public function unique(): Column {
        $this->is_unique = true;

        return $this; // chainable
    }

    /**
     * Return the `UNSIGNED` keyword if the column is unsigned.
     *
     * @return string
     */
    private function get_unsigned(): string {
        return $this->is_unsigned ? 'UNSIGNED' : '';
    }

    /**
     * Set the column to be unsigned.
     *
     * @return Column
     */
    public function unsigned(): Column {
        $this->is_unsigned = true;

        return $this; // chainable
    }

    /**
     * Return the `ON UPDATE` keyword if the column has an `ON UPDATE` value.
     *
     * @return string
     */
    private function get_on_update(): string {
        if ( $this->on_update_value instanceof Raw ) {
            return "ON UPDATE {$this->on_update_value}";
        }

        return $this->on_update_value ? "ON UPDATE '{$this->on_update_value}'" : '';
    }

    /**
     * Set the `ON UPDATE` value for the column.
     *
     * @param string|Raw $value
     *
     * @return $this
     */
    public function on_update( $value ): Column {
        $this->on_update_value = $value;

        return $this; // chainable
    }

    /**
     * Return the SQL string for the column.
     *
     * @return string
     */
    public function get_sql(): string {
        return implode(
            ' ',
            array(
                $this->get_name(),
                "{$this->get_type()}{$this->get_length()}{$this->get_values()}",
                $this->get_default(),
                $this->get_nullable(),
                $this->get_auto_increment(),
                $this->get_unique(),
                $this->get_unsigned(),
                $this->get_on_update(),
            )
        );
    }

}
