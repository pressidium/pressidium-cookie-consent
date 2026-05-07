<?php
/**
 * PSR-16 compliant object-oriented Transient API wrapper.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2026 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Storage;

use DateInterval;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Transient class.
 *
 * @link https://www.php-fig.org/psr/psr-16/
 * @link https://developer.wordpress.org/apis/handbook/transients/
 *
 * @since 2.0.0
 */
final class Transient extends Storage {

    /**
     * @var string[] Transient keys set in this request.
     */
    private $stored_keys = array();

    /**
     * Return all keys set in this request.
     *
     * @return string[] All string-based keys.
     */
    protected function get_all_keys() {
        return $this->stored_keys;
    }

    /**
     * Validate the given key.
     *
     * Transients should be 172 characters or fewer, as WordPress will
     * prefix the name with '_transient_' or '_transient_timeout_' in
     * the options table (depending on whether it expires or not).
     *
     * @throws Invalid_Argument If the given key is not valid.
     *
     * @param string $key Key to check.
     *
     * @return void
     */
    protected function validate_key( $key ) {
        parent::validate_key( $key );

        // Additional check for transient name length
        if ( strlen( $key ) > 172 ) {
            throw new Invalid_Argument( 'Key is not valid.' );
        }
    }

    /**
     * Fetch a value.
     *
     * @link https://developer.wordpress.org/reference/functions/get_transient/
     *
     * @throws Invalid_Argument If the key is invalid.
     *
     * @param string $key           Key to fetch.
     * @param mixed  $default_value Default value to return if the item doesn't exist or is expired.
     *
     * @return mixed The fetched value, or the default value
     *               if the item doesn't exist or is expired.
     */
    public function get( $key, $default_value = null ) {
        $this->validate_key( $key );

        $value = get_transient( $key );
        return $value === false ? $default_value : $value;
    }

    /**
     * Set a value.
     *
     * @link https://developer.wordpress.org/reference/functions/set_transient/
     *
     * @throws Invalid_Argument If the key is invalid.
     *
     * @param string                $key   Key to set.
     * @param mixed                 $value Value to set.
     * @param null|int|DateInterval $ttl   (Optional) The TTL value.
     *                                     Omitting, setting to `null` or `0`
     *                                     will default to no expiration.
     *
     * @return bool Whether the value was set successfully.
     */
    public function set( $key, $value, $ttl = null ) {
        $this->validate_key( $key );

        $transient_saved = set_transient( $key, $value, $this->get_ttl_in_seconds( $ttl ) );

        if ( $transient_saved ) {
            $this->stored_keys[] = $key;
        }

        return $transient_saved;
    }

    /**
     * Delete a value.
     *
     * @link https://developer.wordpress.org/reference/functions/delete_transient/
     *
     * @throws Invalid_Argument If the key is invalid.
     *
     * @param string $key Key to delete.
     *
     * @return bool Whether the item was deleted successfully.
     */
    public function delete( $key ) {
        $this->validate_key( $key );

        $transient_deleted = delete_transient( $key );
        return $transient_deleted === true;
    }

    /**
     * Determine whether an item exists and is not expired.
     *
     * This will return `false` if the transient does not exist, does not have a value,
     * or has expired.
     *
     * @throws Invalid_Argument If the key is invalid.
     *
     * @return bool `true` if the item exists _and_ is
     *               not expired, or `false` otherwise.
     */
    public function has( $key ) {
        $this->validate_key( $key );

        $value = $this->get( $key, false );
        return $value !== false;
    }

}
