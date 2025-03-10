<?php
/**
 * Consent Mode.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2024 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Client;

use const Pressidium\WP\CookieConsent\PLUGIN_DIR;
use const Pressidium\WP\CookieConsent\PLUGIN_URL;

use Pressidium\WP\CookieConsent\Hooks\Actions;

use Pressidium\WP\CookieConsent\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Consent_Mode class.
 *
 * @since 1.0.0
 */
class Consent_Mode implements Actions {

    /**
     * @var array Settings.
     */
    private array $settings;

    /**
     * Consent_Mode constructor.
     *
     * @param Settings $settings_object An instance of the `Settings` class.
     */
    public function __construct( Settings $settings_object ) {
        $this->settings = $settings_object->get();
    }

    /**
     * Enqueue scripts with a priority of `-10`.
     *
     * We need to set the default consent state via `gtag`
     * before any commands that send measurement data
     * (such as `config` or `event`), so we need to
     * enqueue the script early.
     *
     * @link https://developers.google.com/tag-platform/gtagjs/reference#consent
     *
     * @return void
     */
    public function early_enqueue_scripts(): void {
        $assets_file = PLUGIN_DIR . 'public/consent-mode.asset.php';

        if ( ! file_exists( $assets_file ) ) {
            // File doesn't exist, bail early
            return;
        }

        $assets = require $assets_file;

        $dependencies = $assets['dependencies'] ?? array();
        $version      = $assets['version'] ?? filemtime( $assets_file );

        wp_enqueue_script(
            'consent-mode-script',
            PLUGIN_URL . 'public/consent-mode.js',
            $dependencies,
            $version,
            false
        );

        wp_localize_script(
            'consent-mode-script',
            'pressidiumCCGCM',
            array(
                // This needs to be nested so our boolean values are not converted to strings
                'gcm' => $this->settings['pressidium_options']['gcm'] ?? array(),
            ),
        );
    }

    /**
     * Return the actions to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'wp_enqueue_scripts' => array( 'early_enqueue_scripts', -10 ),
        );
    }

}
