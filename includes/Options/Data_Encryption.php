<?php
/**
 * Data Encryption.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Options;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Data_Encryption class.
 *
 * Based on the `Google\Site_Kit\Core\Storage\Data_Encryption` class of Site Kit by Google.
 *
 * @link https://github.com/google/site-kit-wp/blob/1.148.0/includes/Core/Storage/Data_Encryption.php
 *
 * @since 1.8.0
 */
final class Data_Encryption {

    /**
     * @var string Key to use for encryption.
     */
    private string $key;

    /**
     * @var string Salt to use for encryption.
     */
    private string $salt;

    /**
     * Data_Encryption constructor.
     */
    public function __construct() {
        $this->key  = $this->get_default_key();
        $this->salt = $this->get_default_salt();
    }

    /**
     * Return the default encryption key to use.
     *
     * @return string Default (not user-based) encryption key.
     */
    private function get_default_key(): string {
        if ( defined( 'PRESSIDIUM_COOKIE_CONSENT_ENCRYPTION_KEY' )
            && PRESSIDIUM_COOKIE_CONSENT_ENCRYPTION_KEY !== '' ) {
            return PRESSIDIUM_COOKIE_CONSENT_ENCRYPTION_KEY;
        }

        if ( defined( 'LOGGED_IN_KEY' ) && LOGGED_IN_KEY !== '' ) {
            return LOGGED_IN_KEY;
        }

        /*
         * No secure key is available. Return an empty string rather than a hardcoded
         * one: `encrypt()` and `decrypt()` refuse to run without a key, so the plugin
         * fails closed instead of protecting data with a value that is published in
         * this file.
         */
        return '';
    }

    /**
     * Return the default encryption salt to use.
     *
     * @return string Encryption salt.
     */
    private function get_default_salt(): string {
        if ( defined( 'PRESSIDIUM_COOKIE_CONSENT_ENCRYPTION_SALT' )
            && PRESSIDIUM_COOKIE_CONSENT_ENCRYPTION_SALT !== '' ) {
            return PRESSIDIUM_COOKIE_CONSENT_ENCRYPTION_SALT;
        }

        if ( defined( 'LOGGED_IN_SALT' ) && LOGGED_IN_SALT !== '' ) {
            return LOGGED_IN_SALT;
        }

        // No secure salt available. Fail closed, see `get_default_key()`.
        return '';
    }

    /**
     * Whether a secure key and salt are both available.
     *
     * @since 2.0.0
     *
     * @return bool
     */
    private function has_secure_keys(): bool {
        return $this->key !== '' && $this->salt !== '';
    }

    /**
     * Encrypt a value.
     *
     * If a user-based key is set, that key is used. Otherwise, the default key is used.
     *
     * @throws Encryption_Exception If encryption fails, if OpenSSL is unavailable,
     *                              or if no secure key is available.
     *
     * @param string $value Value to encrypt.
     *
     * @return string Encrypted value.
     */
    public function encrypt( string $value ): string {
        if ( ! extension_loaded( 'openssl' ) ) {
            throw new Encryption_Exception(
                'OpenSSL is not available, refusing to store the value unencrypted'
            );
        }

        if ( ! $this->has_secure_keys() ) {
            throw new Encryption_Exception(
                'No secure encryption key is available, refusing to store the value'
            );
        }

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = openssl_random_pseudo_bytes( $ivlen );

        $raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
        if ( ! $raw_value ) {
            throw new Encryption_Exception( 'Failed to encrypt data' );
        }

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        return base64_encode( $iv . $raw_value );
    }

    /**
     * Decrypt a value.
     *
     * If a user-based key is set, that key is used. Otherwise, the default key is used.
     *
     * @throws Decryption_Exception If decryption fails, if OpenSSL is unavailable,
     *                              or if no secure key is available.
     *
     * @param string $raw_value Value to decrypt.
     *
     * @return string Decrypted value.
     */
    public function decrypt( string $raw_value ): string {
        if ( ! extension_loaded( 'openssl' ) ) {
            throw new Decryption_Exception( 'OpenSSL is not available, cannot decrypt the value' );
        }

        if ( ! $this->has_secure_keys() ) {
            throw new Decryption_Exception( 'No secure encryption key is available, cannot decrypt the value' );
        }

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $decoded_value = base64_decode( $raw_value, true );

        if ( $decoded_value === false ) {
            return $raw_value;
        }

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = substr( $decoded_value, 0, $ivlen );

        $decoded_value = substr( $decoded_value, $ivlen );

        $value = openssl_decrypt( $decoded_value, $method, $this->key, 0, $iv );
        if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
            throw new Decryption_Exception( 'Failed to decrypt data' );
        }

        return substr( $value, 0, - strlen( $this->salt ) );
    }

}
