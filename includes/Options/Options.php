<?php
/**
 * Options interface.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Options;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Options interface.
 *
 * @since 1.0.0
 */
interface Options {

    /**
     * Return the option value based on the given option name.
     *
     * @param string $name Option name.
     *
     * @return mixed Option value.
     */
    public function get( string $name );

    /**
     * Store the given value to an option with the given name.
     *
     * @param string $name   Option name.
     * @param mixed  $value  Option value.
     *
     * @return bool Whether the option was added successfully.
     */
    public function set( string $name, $value ): bool;

    /**
     * Remove the option with the given name.
     *
     * @param string $name Option name.
     *
     * @return bool Whether the option was removed successfully.
     */
    public function remove( string $name ): bool;

    /**
     * Whether the option with the given name exists.
     *
     * @param string $name Option name.
     *
     * @return bool Whether the option exists.
     */
    public function has( string $name ): bool;

}
