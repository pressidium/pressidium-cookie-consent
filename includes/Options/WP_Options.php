<?php
/**
 * WordPress Options.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Options;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * WP_Options class.
 *
 * @since 1.0.0
 */
class WP_Options implements Options {

    /**
     * Return the option value based on the given option name.
     *
     * @param string $name Option name.
     *
     * @return mixed Option value.
     */
    public function get( string $name ) {
        return get_option( $name );
    }

    /**
     * Store the given value to an option with the given name.
     *
     * @param string $name  Option name.
     * @param mixed  $value Option value.
     *
     * @return bool Whether the option was added successfully.
     */
    public function set( string $name, $value ): bool {
        $old_value = get_option( $name );

        if ( $old_value === $value ) {
            return true;
        }

        return update_option( $name, $value );
    }

    /**
     * Remove the option with the given name.
     *
     * If the option does not exist, this method will return `true`.
     *
     * @param string $name Option name.
     *
     * @return bool Whether the option was removed successfully.
     */
    public function remove( string $name ): bool {
        if ( ! $this->has( $name ) ) {
            return true;
        }

        return delete_option( $name );
    }

    /**
     * Whether the option with the given name exists.
     *
     * @param string $name Option name.
     *
     * @return bool Whether the option exists.
     */
    public function has( string $name ): bool {
        return get_option( $name ) !== false;
    }

}
