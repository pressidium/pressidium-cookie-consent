<?php
/**
 * Block interface.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Blocks;

// Prevent direct access to files
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Block interface.
 *
 * @since 1.8.0
 */
interface Block {

    /**
     * Return the path to the `block.json` file of the block.
     *
     * @return string
     */
    public function get_block_json_path(): string;

    /**
     * Return the block registration arguments.
     *
     * @return array<string, mixed>
     */
    public function get_args(): array;

}
