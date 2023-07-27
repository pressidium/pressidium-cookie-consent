<?php
/**
 * Emoji.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Emoji class.
 *
 * @since 1.1.2
 */
class Emoji {

    /**
     * Convert emoji characters to their equivalent HTML entities.
     *
     * @link https://developer.wordpress.org/reference/functions/wp_encode_emoji/
     *
     * @param string $content The content to encode.
     *
     * @return string The encoded content.
     */
    public static function encode( string $content ): string {
        return wp_encode_emoji( $content );
    }

    /**
     * Whether the given text contains HTML entities.
     *
     * @param string $text The text to check.
     *
     * @return bool
     */
    private static function contains_entities( string $text ): bool {
        return str_contains( $text, '&#x' );
    }

    /**
     * Whether the given text is ASCII or contains only standard ASCII characters.
     *
     * @link https://www.php.net/manual/en/function.mb-check-encoding.php
     *
     * @param string $text The text to check.
     *
     * @return bool
     */
    private static function contains_only_ascii( string $text ): bool {
        $is_ascii = ( function_exists( 'mb_check_encoding' ) && mb_check_encoding( $text, 'ASCII' ) );

        return $is_ascii || ! preg_match( '/[^\x00-\x7F]/', $text );
    }

    /**
     * Whether the given text might contain emoji characters.
     *
     * @param string $text The text to check.
     *
     * @return bool Whether the given text might contain emoji characters.
     */
    private static function maybe_contains_emojis( string $text ): bool {
        return ! self::contains_only_ascii( $text );
    }

    /**
     * Return an array of possible emoji HTML entities for the given text.
     *
     * We utilize the `_wp_emoji_list()` private function to get the list of emoji entities.
     *
     * @link https://developer.wordpress.org/reference/functions/_wp_emoji_list/
     *
     * @param string $text The text to check.
     *
     * @return array Array of possible emoji HTML entities.
     */
    private static function get_possible_emoji_entities( string $text ): array {
        $emoji = _wp_emoji_list( 'entities' );

        // Quickly narrow down the list of emoji that might be in the text and need replacing
        $possible_emoji = array();

        foreach ( $emoji as $emojum ) {
            if ( str_contains( $text, $emojum ) ) {
                // If the text contains this emoji, add it to the list of possible emoji for replacement
                $possible_emoji[ $emojum ] = html_entity_decode( $emojum );
            }
        }

        return $possible_emoji;
    }

    /**
     * Whether we should attempt to replace emoji with their equivalent HTML entities.
     *
     * @param string $text               The text to check.
     * @param bool   $in_an_ignore_block Whether we're in an ignore block.
     *
     * @return bool
     */
    private static function should_replace_emoji( string $text, bool $in_an_ignore_block ): bool {
        return ! $in_an_ignore_block && strlen( $text ) > 0 && $text[0] !== '<' && self::contains_entities( $text );
    }

    /**
     * Convert emoji HTML entities to their equivalent characters.
     *
     * Based on:
     *
     * @link https://developer.wordpress.org/reference/functions/wp_staticize_emoji/
     *
     * @param string $text The text to decode.
     *
     * @return string The decoded text.
     */
    public static function decode( string $text ): string {
        if ( ! self::contains_entities( $text ) ) {
            if ( ! self::maybe_contains_emojis( $text ) ) {
                return $text;
            }

            $encoded_text = self::encode( $text );

            if ( $encoded_text === $text ) {
                // There were no emoji characters to encode
                return $text;
            }

            $text = $encoded_text;
        }

        $possible_emoji = self::get_possible_emoji_entities( $text );

        if ( ! $possible_emoji ) {
            // No possible emoji for replacement in the text
            return $text;
        }

        $output = '';

        // Capture the tags as well as the content in between
        $textarr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE );
        $stop    = count( $textarr );

        // Ignore processing of specific tags
        $tags_to_ignore       = 'code|pre|style|script|textarea';
        $ignore_block_element = '';

        for ( $i = 0; $i < $stop; $i++ ) {
            $content = $textarr[ $i ];

            // If we're in an ignore block, wait until we find its closing tag
            $in_an_ignore_block = preg_match( '/^<(' . $tags_to_ignore . ')>/', $content, $matches );

            if ( $ignore_block_element === '' && $in_an_ignore_block ) {
                $ignore_block_element = $matches[1];
            }

            /*
             * If it's not a tag and not in an ignore block, and it contains
             * emoji entities, replace the emoji with their HTML entities.
             */
            if ( self::should_replace_emoji( $content, $ignore_block_element !== '' ) ) {
                foreach ( $possible_emoji as $emojum => $emoji_char ) {
                    if ( ! str_contains( $content, $emojum ) ) {
                        continue;
                    }

                    $content = str_replace( $emojum, $emoji_char, $content );
                }
            }

            // Did we exit the ignore block?
            if ( $ignore_block_element !== '' && $content === '</' . $ignore_block_element . '>' ) {
                $ignore_block_element = '';
            }

            $output .= $content;
        }

        // Finally, remove any stray U+FE0F characters.
        return str_replace( '&#xfe0f;', '', $output );
    }

    /**
     * Convert any strings in the values of the given array via the given callback.
     *
     * @param array    $arr      Array to convert the strings in.
     * @param callable $callback Callback to convert the strings with.
     *
     * @return array Array with the strings converted.
     */
    private static function convert_array( array $arr, callable $callback ): array {
        if ( ! is_callable( $callback ) ) {
            return $arr;
        }

        $converted = array();

        foreach ( $arr as $key => $value ) {
            if ( is_array( $value ) ) {
                $converted[ $key ] = self::convert_array( $value, $callback );
                continue;
            }

            if ( is_string( $value ) ) {
                $converted[ $key ] = $callback( $value );
                continue;
            }

            $converted[ $key ] = $value;
        }

        return $converted;
    }

    /**
     * Convert any emoji characters in the values of the given array to their equivalent HTML entity.
     *
     * This allows us to store emoji in a DB using the utf8 character set.
     *
     * @param array $arr Array to encode emoji characters in.
     *
     * @return array Array with emoji characters encoded.
     */
    public static function encode_array( array $arr ): array {
        return self::convert_array( $arr, array( __CLASS__, 'encode' ) );
    }

    /**
     * Convert any emoji HTML entities in the values of the given array to their equivalent characters.
     *
     * This allows us to display emoji in a text field or textarea.
     *
     * @param array $arr Array to decode emoji HTML entities in.
     *
     * @return array Array with emoji HTML entities decoded.
     */
    public static function decode_array( array $arr ): array {
        return self::convert_array( $arr, array( __CLASS__, 'decode' ) );
    }

}
