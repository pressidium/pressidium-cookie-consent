<?php
/**
 * Color Utilities.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Color_Utils class.
 *
 * @since 1.8.0
 */
class Color_Utils {

    /**
     * Return a CSS variable for a color preset by its slug.
     *
     * @param string $color_slug Color slug.
     *
     * @return string CSS variable.
     */
    public static function get_color_preset_var_by_slug( string $color_slug ): string {
        return sprintf( 'var(--wp--preset--color--%s)', $color_slug );
    }

    /**
     * Return the hex code of a color by its slug.
     *
     * @param string $color_slug Color slug.
     *
     * @return ?string Color hex code.
     */
    public static function get_color_hex_by_slug( string $color_slug ): ?string {
        $palettes = wp_get_global_settings( array( 'color', 'palette' ) );

        if ( empty( $palettes ) ) {
            return null;
        }

        foreach ( $palettes as $palette ) {
            if ( empty( $palette ) ) {
                return null;
            }

            foreach ( $palette as $color ) {
                if ( ! empty( $color['slug'] ) && $color['slug'] === $color_slug ) {
                    return $color['color'];
                }
            }
        }

        return null;
    }

}
