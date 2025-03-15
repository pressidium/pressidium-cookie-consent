<?php
/**
 * Cookies block.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Blocks\Cookies_Block;

use Pressidium\WP\CookieConsent\Blocks\Block;
use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Settings;

use const Pressidium\WP\CookieConsent\PLUGIN_DIR;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Cookies_Block class.
 *
 * @since 1.8.0
 */
class Cookies_Block implements Block, Actions {

    /**
     * @var array<string, mixed> Settings.
     */
    private array $settings;

    /**
     * Cookie_Consent constructor.
     *
     * @param Settings $settings_object An instance of the `Settings` class.
     */
    public function __construct( Settings $settings_object ) {
        $this->settings = $settings_object->get();
    }

    /**
     * Return the path to the `block.json` file.
     *
     * @return string
     */
    public function get_block_json_path(): string {
        return PLUGIN_DIR . 'public/blocks/cookies/block.json';
    }

    /**
     * Return the block registration arguments.
     *
     * @return array<string, mixed>
     */
    public function get_args(): array {
        return array(
            'editor_script' => 'pressidium-cookies-editor-script',
        );
    }

    /**
     * Pass data to the block.
     *
     * `wp_localize_script()` is primarily used to offer localized translations to scripts.
     * However, in this case, we use it to pass data (i.e. the registered cookies) to the block.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     *
     * @return void
     */
    public function pass_data_to_block(): void {
        wp_localize_script(
            'pressidium-cookies-editor-script',
            'pressidiumCookiesBlockData',
            array(
                // Pass the registered cookies to the block
                'cookies' => $this->settings['pressidium_options']['cookie_table'],
            )
        );
    }

    /**
     * Return the actions to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'admin_enqueue_scripts' => array( 'pass_data_to_block' ),
        );
    }

}
