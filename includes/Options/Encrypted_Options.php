<?php
/**
 * Encrypted options.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Options;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Encrypted_Options class.
 *
 * @since 1.8.0
 */
class Encrypted_Options implements Options {

    /**
     * @var Data_Encryption Data encryption instance.
     */
    private Data_Encryption $data_encryption;

    /**
     * @var Options Options instance.
     */
    private Options $options;

    /**
     * Encrypted_WP_Options constructor.
     *
     * @param Data_Encryption $data_encryption Data encryption instance.
     */
    public function __construct( Data_Encryption $data_encryption, Options $options ) {
        $this->data_encryption = $data_encryption;
        $this->options         = $options;
    }

    /**
     * Return the option value based on the given option name.
     *
     * @throws Decryption_Exception If the value could not be decrypted.
     *
     * @param string $name Option name.
     *
     * @return mixed Option value.
     */
    public function get( string $name ) {
        $raw_value = $this->options->get( $name );

        if ( $raw_value === false ) {
            return false;
        }

        return $this->data_encryption->decrypt( $raw_value );
    }

    /**
     * Store the given value to an option with the given name.
     *
     * @throws Encryption_Exception If the value could not be encrypted.
     *
     * @param string $name  Option name.
     * @param mixed  $value Option value.
     *
     * @return bool Whether the option was added successfully.
     */
    public function set( string $name, $value ): bool {
        $encrypted_value = $this->data_encryption->encrypt( $value );

        return $this->options->set( $name, $encrypted_value );
    }

    /**
     * Remove the option with the given name.
     *
     * @param string $name Option name.
     *
     * @return bool Whether the option was removed successfully.
     */
    public function remove( string $name ): bool {
        return $this->options->remove( $name );
    }

    /**
     * Whether the option with the given name exists.
     *
     * @param string $name Option name.
     *
     * @return bool Whether the option exists.
     */
    public function has( string $name ): bool {
        return $this->options->has( $name );
    }

}
