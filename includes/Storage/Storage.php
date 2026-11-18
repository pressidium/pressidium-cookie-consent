<?php
/**
 * Base storage class.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2026 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Storage;

use TypeError;
use Traversable;
use DateInterval;

use Pressidium\WP\CookieConsent\Dependencies\Psr\SimpleCache\InvalidArgumentException;
use Pressidium\WP\CookieConsent\Dependencies\Psr\SimpleCache\CacheInterface;

use Pressidium\WP\CookieConsent\Utils\Date_Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Storage abstract class.
 *
 * @since 1.0.0
 */
abstract class Storage implements CacheInterface {

    /**
     * Return all keys to wipe when the `clear()` method is called.
     *
     * @return string[] All string-based keys.
     */
    abstract protected function get_all_keys();

    /**
     * Return the TTL in seconds.
     *
     * @throws TypeError If the given TTL is not valid.
     *
     * @param null|int|DateInterval $ttl TTL value.
     *
     * @return int TTL in seconds.
     */
    protected function get_ttl_in_seconds( $ttl ) {
        if ( is_null( $ttl ) ) {
            return 0;
        }

        if ( is_int( $ttl ) ) {
            return $ttl;
        }

        if ( $ttl instanceof DateInterval ) {
            return Date_Utils::date_interval_to_seconds( $ttl );
        }

        throw new TypeError( 'Argument must be an integer, null, or a DateInterval object.' );
    }

    /**
     * Validate the given key.
     *
     * By default, we only check if the key is a non-empty string.
     * Override this method to implement custom validation.
     *
     * @throws Invalid_Argument If the given key is not valid.
     *
     * @param string $key Key to check.
     *
     * @return void
     */
    protected function validate_key( $key ) {
        if ( ! is_string( $key ) || empty( $key ) ) {
            throw new Invalid_Argument( 'Key is not valid.' );
        }
    }

    /**
     * Validate the given iterable value.
     *
     * @throws Invalid_Argument If the given value is neither an array nor a Traversable.
     *
     * @param iterable $value The value to check.
     *
     * @return void
     */
    private function validate_iterable( $value ) {
        if ( ! is_array( $value ) && ! ( $value instanceof Traversable ) ) {
            throw new Invalid_Argument( 'Argument must be an array or a Traversable object.' );
        }
    }

    /**
     * Wipe clean all values returned by the `get_all_keys()` method.
     *
     * @return bool `true` if all values were deleted successfully, `false` otherwise.
     */
    public function clear() {
        $deleted_successfully = true;

        foreach ( $this->get_all_keys() as $key ) {
            try {
                $deleted_successfully = $deleted_successfully && $this->delete( $key );
            } catch ( InvalidArgumentException ) {
                $deleted_successfully = false;
            }
        }

        return $deleted_successfully;
    }

    /**
     * Fetch multiple values.
     *
     * @throws InvalidArgumentException If keys is neither an array nor a Traversable,
     *                                  or if any of the keys is not valid.
     *
     * @param iterable $keys          A list of `key => value` pairs to fetch.
     * @param mixed    $default_value Default value to return if the item doesn't exist or is expired.
     *
     * @return iterable An array of `key => value` pairs. Keys that do not exist
     *                  or are expired will have the default value.
     */
    public function getMultiple( $keys, $default_value = null ) {
        $this->validate_iterable( $keys );

        $values = array();

        foreach ( $keys as $key ) {
            $values[ $key ] = $this->get( $key, $default_value );
        }

        return $values;
    }

    /**
     * Set multiple values.
     *
     * @throws InvalidArgumentException If keys is neither an array nor a Traversable,
     *                                  or if any of the keys is not valid.
     *
     * @param iterable              $values A list of `key => value` pairs to set.
     * @param null|int|DateInterval $ttl    (Optional) The TTL value.
     *                                      Defaults to no expiration.
     *
     * @return bool Whether the values were set successfully.
     */
    public function setMultiple( $values, $ttl = null ) {
        $this->validate_iterable( $values );

        $set_successfully = true;

        foreach ( $values as $key => $value ) {
            $set_successfully = $set_successfully && $this->set( $key, $value, $ttl );
        }

        return $set_successfully;
    }

    /**
     * Delete multiple values.
     *
     * @throws InvalidArgumentException If keys is neither an array nor a Traversable,
     *                                  or if any of the keys is not valid.
     *
     * @param iterable $keys A list of string-based keys of items to be deleted.
     *
     * @return bool Whether the values were deleted successfully.
     */
    public function deleteMultiple( $keys ) {
        $this->validate_iterable( $keys );

        $deleted_successfully = true;

        foreach ( $keys as $key ) {
            $deleted_successfully = $deleted_successfully && $this->delete( $key );
        }

        return $deleted_successfully;
    }

}
