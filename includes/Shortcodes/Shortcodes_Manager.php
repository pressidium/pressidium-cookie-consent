<?php
/**
 * Shortcodes Manager.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Shortcodes;

// Prevent direct access to files
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcodes_Manager class.
 *
 * @since 1.8.0
 */
final class Shortcodes_Manager {

    /**
     * Register the shortcode.
     *
     * @see https://developer.wordpress.org/reference/functions/add_shortcode/
     *
     * @param Shortcode $shortcode Shortcode to register.
     *
     * @return void
     */
    public function register( Shortcode $shortcode ): void {
        add_shortcode( $shortcode->get_tag(), array( $shortcode, 'callback' ) );
    }

}
