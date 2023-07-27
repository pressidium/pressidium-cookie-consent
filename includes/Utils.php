<?php
/**
 * Utilities.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Utils class.
 *
 * @since 1.0.0
 */
class Utils {

    /**
     * Return the domain of this WordPress website.
     *
     * @since 1.1.0
     *
     * @return string
     */
    public static function get_domain(): string {
        $domain = parse_url( get_site_url(), PHP_URL_HOST );

        if ( ! $domain ) {
            return $_SERVER['HTTP_HOST'];
        }

        return $domain;
    }

    /**
     * Check if a string starts with a given substring.
     *
     * Equivalent to `str_starts_with()` in PHP 8
     *
     * @since 1.1.0
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for in the `haystack`.
     *
     * @return bool `true` if `haystack` begins with `needle`, `false` otherwise.
     */
    public static function starts_with( string $haystack, string $needle ): bool {
        if ( empty( $needle ) ) {
            return true;
        }

        return strpos( $haystack, $needle ) === 0;
    }

    /**
     * Check if a string ends with a given substring.
     *
     * Equivalent to `str_ends_with()` in PHP 8.
     *
     * @since 1.0.0
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for in the `haystack`.
     *
     * @return bool `true` if `haystack` ends with `needle`, `false` otherwise.
     */
    public static function ends_with( string $haystack, string $needle ): bool {
        if ( empty( $needle ) ) {
            return true;
        }

        $length = strlen( $needle );
        return $length <= 0 || substr( $haystack, -$length ) === $needle;
    }

    /**
     * Return the given string with the given prefix removed.
     *
     * @since 1.1.0
     *
     * @param string $prefix Prefix to remove from the string.
     * @param string $str    String to remove the prefix from.
     *
     * @return string
     */
    public static function strip_prefix( string $prefix, string $str ): string {
        if ( self::starts_with( $str, $prefix ) ) {
            return substr( $str, strlen( $prefix ) );
        }

        return $str;
    }

    /**
     * Return the given string with the given suffix removed.
     *
     * @since 1.1.0
     *
     * @param string $suffix Suffix to remove from the string.
     * @param string $str    String to remove the suffix from.
     *
     * @return string
     */
    public static function strip_suffix( string $suffix, string $str ): string {
        if ( self::ends_with( $str, $suffix ) && ! empty( $suffix ) ) {
            return substr( $str, 0, -strlen( $suffix ) );
        }

        return $str;
    }


    /**
     * Return the given string with the leading slash removed (if any).
     *
     * @since 1.1.0
     *
     * @param string $string String to remove the leading slash from.
     *
     * @return string String with leading slash removed.
     */
    public static function unleading_slash_it( string $string ): string {
        return self::strip_prefix( '/', $string );
    }

    /**
     * Return the given string with the trailing slash removed (if any).
     *
     * @since 1.1.0
     *
     * @param string $string String to remove the trailing slash from.
     *
     * @return string String with trailing slash removed
     */
    public static function untrailing_slash_it( string $string ): string {
        return self::strip_suffix( '/', $string );
    }

    /**
     * Convert any emoji characters in the values of the given array to their equivalent HTML entity.
     *
     * This allows us to store emoji in a DB using the utf8 character set.
     *
     * @since 1.1.0
     *
     * @link https://developer.wordpress.org/reference/functions/wp_encode_emoji/
     *
     * @param array $arr Array to encode emoji characters in.
     *
     * @return array Array with emoji characters encoded.
     */
    public static function encode_emoji_array( array $arr ): array {
        $encoded = array();

        foreach ( $arr as $key => $value ) {
            if ( is_array( $value ) ) {
                $encoded[ $key ] = self::encode_emoji_array( $value );
                continue;
            }

            if ( is_string( $value ) ) {
                $encoded[ $key ] = wp_encode_emoji( $value );
                continue;
            }

            $encoded[ $key ] = $value;
        }

        return $encoded;
    }


    /**
     * Decode emoji characters in a nested array.
     * This method takes an array as input and iterates through its elements. If an element is a string, it checks
     * whether it contains any emoji characters. If so, it replaces those emoji characters with their respective
     * HTML entities. If the element is an array, it recursively calls the method to decode emoji characters in
     * the nested array.
     *
     * @param array $arr The input array containing strings and/or nested arrays.
     *
     * @return array The input array with emoji characters replaced by their respective HTML entities.
     */
    public static function decode_emoji_array(array $arr): array
    {
        $encoded = [];

        foreach($arr as $key => $value) {
            if( is_array( $value ) ) {
                $encoded[$key] = self::decode_emoji_array( $value );
                continue;
            }

            if( is_string( $value ) )
                $value = self::staticize_emoji( $value );

            $encoded[$key] = $value;
        }

        return $encoded;
    }

    /**
     * Staticize emoji characters in a given text.
     * This method checks the input text for emoji characters and replaces them with their respective HTML entities.
     * It utilizes the WordPress `wp_encode_emoji()` function to encode emoji characters in the text. Before encoding,
     * it quickly narrows down the list of emojis that might be present in the text and need replacement. If the text
     * doesn't contain any characters that might be emojis, it returns the input text as it is.
     *
     * @param string $text The input text to be checked for emoji characters.
     *
     * @return string The input text with emoji characters replaced by their respective HTML entities.
     */
    protected static function staticize_emoji(string $text): string
    {
        // If the text does not contain '&#x', there is no need to check for emojis, so we can return the input text early.
        if( !str_contains( $text, '&#x' ) ) {
            // Check if the text is ASCII or contains only standard ASCII characters using mb_check_encoding().
            // If true, it means the text doesn't contain any emoji characters.
            if( ( function_exists( 'mb_check_encoding' ) && mb_check_encoding( $text, 'ASCII' ) ) || !preg_match( '/[^\x00-\x7F]/', $text ) ) {
                // The text doesn't contain anything that might be an emoji, so we can return early.
                return $text;
            } else {
                // Encode any emoji characters in the text using the wp_encode_emoji() function.
                $encoded_text = wp_encode_emoji( $text );
                if( $encoded_text === $text ) {
                    // If the encoded text is the same as the original text, it means there were no emoji characters to encode.
                    return $encoded_text;
                }
                $text = $encoded_text;
            }
        }

        // Get a list of emoji entities using the _wp_emoji_list('entities') function.
        $emoji = _wp_emoji_list( 'entities' );

        // Quickly narrow down the list of emoji that might be in the text and need replacing.
        $possible_emoji = [];
        foreach($emoji as $emojum) {
            if( str_contains( $text, $emojum ) ) {
                // If the text contains this emoji, add it to the list of possible emoji for replacement.
                $possible_emoji[$emojum] = html_entity_decode( $emojum );
            }
        }

        // If there are no possible emoji for replacement, return the original text.
        if( !$possible_emoji )
            return $text;

        $output = '';

        /*
         * HTML loop taken from smiley function, which was taken from texturize function.
         * It'll never be consolidated.
         *
         * First, capture the tags as well as the content in between.
         */
        $textarr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE );
        $stop = count( $textarr );

        // Ignore processing of specific tags.
        $tags_to_ignore = 'code|pre|style|script|textarea';
        $ignore_block_element = '';

        for($i = 0; $i < $stop; $i++) {
            $content = $textarr[$i];

            // If we're in an ignore block, wait until we find its closing tag.
            if( '' === $ignore_block_element && preg_match( '/^<(' . $tags_to_ignore . ')>/', $content, $matches ) )
                $ignore_block_element = $matches[1];

            // If it's not a tag and not in an ignore block, and it contains emoji entities, replace the emoji with their HTML entities.
            if( '' === $ignore_block_element && strlen( $content ) > 0 && '<' !== $content[0] && str_contains( $content, '&#x' ) ) {
                foreach($possible_emoji as $emojum => $emoji_char) {
                    if( !str_contains( $content, $emojum ) )
                        continue;
                    $content = str_replace( $emojum, $emoji_char, $content );
                }
            }

            // Did we exit the ignore block?
            if( '' !== $ignore_block_element && '</' . $ignore_block_element . '>' === $content )
                $ignore_block_element = '';

            $output .= $content;
        }

        // Finally, remove any stray U+FE0F characters.
        return str_replace( '&#xfe0f;', '', $output );
    }

}
