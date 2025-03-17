<?php
/**
 * Blocks Manager.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Blocks;

use Pressidium\WP\CookieConsent\Logging\Logger;

// Prevent direct access to files
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Blocks_Manager class.
 *
 * @since 1.8.0
 */
final class Blocks_Manager {

    /**
     * @var Logger An instance of the `Logger` class.
     */
    private Logger $logger;

    /**
     * Blocks_Manager constructor.
     *
     * @param Logger $logger An instance of the `Logger` class.
     */
    public function __construct( Logger $logger ) {
        $this->logger = $logger;
    }

    /**
     * Register the block using the metadata loaded from the `block.json` file.
     *
     *  Behind the scenes, it registers also all assets, so they can be enqueued
     *  through the block editor in the corresponding context.
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     *
     * @param Block $block Block to register.
     *
     * @return void
     */
    public function register( Block $block ): void {
        if ( ! function_exists( 'register_block_type' ) ) {
            // Block editor is not available, bail early
            return;
        }

        $registered_block = register_block_type(
            $block->get_block_json_path(),
            $block->get_args()
        );

        if ( $registered_block === false ) {
            $this->logger->error( 'Could not register block' );
        }
    }

}
