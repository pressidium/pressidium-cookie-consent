<?php
/**
 * AI Factory.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * AI_Factory class.
 *
 * @since 1.8.0
 */
final class AI_Factory {

    /**
     * Create an AI instance.
     *
     * @throws InvalidArgumentException If the AI type is invalid.
     *
     * @param string $type AI type.
     *
     * @return AI
     */
    public static function create( string $type ): AI {
        switch ( $type ) {
            case 'gpt':
                return new GPT();
            case 'gemini':
                return new Gemini();
            default:
                throw new InvalidArgumentException( 'Invalid AI type' );
        }
    }

}
