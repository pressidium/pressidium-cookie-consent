<?php
/**
 * Rate limiter.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2026 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Rate_Limiting;

use Pressidium\WP\CookieConsent\Dependencies\Psr\SimpleCache\CacheInterface;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Rate_Limiter class.
 *
 * @since 2.0.0
 */
class Rate_Limiter {

    /**
     * @var CacheInterface Cache instance.
     */
    private CacheInterface $cache;

    /**
     * @var int Maximum number of hits allowed within the time window.
     */
    private int $max_hits;

    /**
     * @var int Length of the time window in seconds.
     */
    private int $window_seconds;

    /**
     * Rate_Limiter constructor.
     *
     * @param CacheInterface $cache          Cache instance.
     * @param int            $max_hits       Maximum number of hits allowed within the time window.
     * @param int            $window_seconds Length of the time window in seconds.
     */
    public function __construct( CacheInterface $cache, int $max_hits = 5, int $window_seconds = 60 ) {
        $this->cache          = $cache;
        $this->max_hits       = $max_hits;
        $this->window_seconds = $window_seconds;
    }

    /**
     * Return the salt to use for hashing.
     *
     * @return string Salt.
     */
    private function get_salt(): string {
        if ( defined( 'PRESSIDIUM_COOKIE_CONSENT_RATE_LIMIT_SALT' )
            && PRESSIDIUM_COOKIE_CONSENT_RATE_LIMIT_SALT !== '' ) {
            return PRESSIDIUM_COOKIE_CONSENT_RATE_LIMIT_SALT;
        }

        if ( defined( 'AUTH_SALT' ) && AUTH_SALT !== '' ) {
            return AUTH_SALT;
        }

        // If this is reached, you're either not on a live site or have a serious security issue.
        return 'not-a-secret-rate-limit-salt';
    }

    /**
     * Return a keyed hash of the given identifier using HMAC-SHA256.
     *
     * @param string $identifier Identifier to hash (e.g. an IP address).
     *
     * @return string Hashed identifier.
     */
    private function hash( string $identifier ): string {
        return hash_hmac( 'sha256', $identifier, $this->get_salt() );
    }

    /**
     * Return the cache key for the given hash.
     *
     * @param string $hash Hashed identifier.
     *
     * @return string Cache key.
     */
    private function get_cache_key( string $hash ): string {
        return 'pcc_rl_' . substr( $hash, 0, 20 );
    }

    /**
     * Whether the given identifier has exceeded the rate limit.
     *
     * @param string $identifier Identifier to check (e.g. an IP address).
     *
     * @return bool Whether the identifier is throttled.
     */
    public function is_throttled( string $identifier ): bool {
        $key  = $this->get_cache_key( $this->hash( $identifier ) );
        $data = $this->cache->get( $key );

        if ( empty( $data ) ) {
            $this->cache->set( $key, array( 'hits' => 1, 'start' => time() ), $this->window_seconds );

            return false;
        }

        if ( $data['hits'] >= $this->max_hits ) {
            return true;
        }

        $remaining = max( 1, $this->window_seconds - ( time() - $data['start'] ) );
        $this->cache->set( $key, array( 'hits' => $data['hits'] + 1, 'start' => $data['start'] ), $remaining );

        return false;
    }

}