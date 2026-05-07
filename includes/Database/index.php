<?php
/**
 * Database index.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2026 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Index class.
 *
 * @since 2.0.0
 */
class Index {

    /**
     * @var string Index name.
     */
    private string $name;

    /**
     * @var array Column names included in the index.
     */
    private array $columns;

    /**
     * Index constructor.
     *
     * @param string $name    Index name.
     * @param array  $columns Column names.
     */
    public function __construct( string $name, array $columns ) {
        $this->name    = $name;
        $this->columns = $columns;
    }

    /**
     * Return the SQL string for the index.
     *
     * @return string
     */
    public function get_sql(): string {
        return sprintf( 'KEY %s (%s)', $this->name, implode( ', ', $this->columns ) );
    }

}
