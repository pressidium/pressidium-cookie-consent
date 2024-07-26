<?php
/**
 * Settings admin page.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Admin\Settings;

use const Pressidium\WP\CookieConsent\PLUGIN_DIR;
use const Pressidium\WP\CookieConsent\PLUGIN_URL;
use const Pressidium\WP\CookieConsent\VERSION;

use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Hooks\Filters;

use Pressidium\WP\CookieConsent\Admin\Page;
use Pressidium\WP\CookieConsent\Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Settings_Page class.
 *
 * @since 1.0.0
 */
class Settings_Page extends Page implements Actions, Filters {

    /**
     * Return the menu slug.
     *
     * @return string
     */
    protected function get_menu_slug(): string {
        return 'pressidium-cookie-consent';
    }

    /**
     * Return the option group.
     *
     * @return string
     */
    protected function get_option_group(): string {
        return 'pressidium_cookie_consent';
    }

    /**
     * Return the option name.
     *
     * @return string
     */
    protected function get_option_name(): string {
        return 'pressidium_cookie_consent_settings';
    }

    /**
     * Return the page title.
     *
     * @return string
     */
    protected function get_page_title(): string {
        return __( 'Cookie Consent', 'pressidium-cookie-consent' );
    }

    /**
     * Return the menu title.
     *
     * @return string
     */
    protected function get_menu_title(): string {
        return __( 'Cookie Consent', 'pressidium-cookie-consent' );
    }

    /**
     * Return the capability required for this menu to be displayed to the user.
     *
     * Override this method if you want to change the required capability.
     *
     * @return string
     */
    protected function get_capability(): string {
        return 'manage_options';
    }

    /**
     * Return the description of this options page.
     *
     * @return string
     */
    protected function get_description(): string {
        return __( 'Adjust your cookie consent preferences.', 'pressidium-cookie-consent' );
    }

    /**
     * Return the URL to the icon to be used for this menu.
     *
     * @return string
     */
    protected function get_icon(): string {
        // phpcs:ignore Generic.Files.LineLength
        return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDI1NiAyNTYiPjxnIGZpbGw9Im5vbmUiPjxwYXRoIGQ9Ik0xNTguNywxNDAuNzJjLTIuNzIsMi4yNC03LjI1LDYuNC05LjE2LDExLjJhMzIuNjIsMzIuNjIsMCwwLDAtMS44NiwxMS4yNCwyMS42MSwyMS42MSwwLDAsMCwuMTYsMi44NGMyLjg0LDUsMTIuMDcsNS40NywxNy4xLDUuMDgsNS43LS40NSwxNC4yNC0yLjMzLDE3LjkyLTcuMTRhMjIuNjMsMjIuNjMsMCwwLDAsMy41OS04LjEsMTguODMsMTguODMsMCwwLDAsMC0xMC43MUMxODMuNTIsMTM2LjE3LDE3MC4xNCwxMzMuODYsMTU4LjcsMTQwLjcyWiIvPjxwYXRoIGQ9Ik05Ny40MiwxNDAuNzJjLTExLjQzLTYuODYtMjQuODItNC41NS0yNy43Niw0LjQxYTE4Ljc1LDE4Ljc1LDAsMCwwLDAsMTAuNzEsMjIuNTQsMjIuNTQsMCwwLDAsMy42LDguMWMzLjY3LDQuODEsMTIuMjEsNi42OSwxNy45MSw3LjE0LDUsLjM5LDE0LjI2LS4xLDE3LjExLTUuMDhhMjMsMjMsMCwwLDAsLjE1LTIuODQsMzIuMzgsMzIuMzgsMCwwLDAtMS44Ni0xMS4yNEMxMDQuNjcsMTQ3LjEyLDEwMC4xNCwxNDMsOTcuNDIsMTQwLjcyWiIvPjxwYXRoIGQ9Ik0xNDkuMjUsMTUzLjdoMGMtLjA4LjIzLS4xNi40Ni0uMjMuNjlDMTQ5LjA5LDE1NC4xNywxNDkuMTcsMTUzLjk0LDE0OS4yNSwxNTMuN1oiLz48Y2lyY2xlIGN4PSIxOTQuMzIiIGN5PSI1Ni4yMSIgcj0iOC4yNyIvPjxjaXJjbGUgY3g9IjE4OC45OSIgY3k9Ijg3LjIyIiByPSI3Ljc0Ii8+PGNpcmNsZSBjeD0iMjI1LjI4IiBjeT0iOTguMDQiIHI9IjUuMjkiLz48cGF0aCBkPSJNMjIwLjY3LDEzMC42OGEyMywyMywwLDAsMS00LjY1LjQ3LDIzLjI5LDIzLjI5LDAsMCwxLTE3LjktOC4zNmwtMS4wOC40N2EzNC4zMSwzNC4zMSwwLDAsMSw1LjE5LDkuMThjNC4xMiwxMC44OCw2LDI3LjcyLjUsMzguNzgtMTMsLjMyLTQyLjgxLDUuNjMtNjEuNjgsMTAuMi0xLjE2LjI4LS41NC4xMiwwLDBoLTIuMjRjLS40NS0xLjMyLTEuNDctNS43Ny0xLjI5LTE5LjA4LjI0LTE4LjA2LDkuNTgtMzcuNDQsMjUuMzgtNDZhMzEsMzEsMCwwLDEtNS4zOC0zNS42OCwyMy4zLDIzLjMsMCwwLDEtMTQuMTMtMjRjLTEuODYtLjE5LTMuNzYtLjM1LTUuNzItLjQ3TDE0MSw1MS40NWwtMzAuNjksNS40YTk5LjYzLDk5LjYzLDAsMCwwLTI1LjUzLDYuMzRjLTYuOTEtNy44NS0yMy4xLTIwLjgtNTUuNDEtMjIuMywwLDAtNywzOC41NSwxMC45MSw3My4zMy01LjExLDE1LTcuNTYsMjkuODMtMTEuNDIsNDAuMTQtLjA3LjE4LS4xMy4zNS0uMi41Mi0uMTMuMzUtLjI3LjctLjQxLDFzLS4xOS40Ni0uMjguNjhsLS4zLjY4Yy0uMTMuMy0uMjcuNi0uNDEuODlzLS4xNS4zMi0uMjMuNDdjLS4xNi4zNC0uMzMuNjctLjUxLDFsLS4yMS4zOGMtLjE4LjM0LS4zNy42Ni0uNTYsMWwtLjE4LjI5LS42LjkyLS4xNS4yYy0uMi4zLS40Mi41OC0uNjQuODVhMS4zMywxLjMzLDAsMCwxLS4xNC4xOWMtLjI0LjI5LS40OC41Ni0uNzMuODNsLS4xNy4xOGMtLjI1LjI2LS41LjUxLS43Ny43NWwtLjE3LjE1Yy0uMjcuMjMtLjU1LjQ2LS44My42N2EuNzEuNzEsMCwwLDAtLjE0LjFjLS4zMS4yMS0uNjEuNDItLjkzLjYxaDBjLS4zMy4xOS0uNjUuMzUtMSwuNTFsLS4wOCwwYy0uMzguMTYtLjcyLjI5LTEuMDYuNGwtLjE5LjA2Yy0uMzQuMTEtLjcuMi0xLjA2LjI4bC0uMTksMGMtLjM3LjA3LS43NS4xMy0xLjE0LjE3aC0uMTVjLS40MiwwLS44NC4wNi0xLjI4LjA2aDBjLjA5LjIxLjE5LjQxLjI4LjYydjBDMjkuNjUsMjAyLjM5LDgwLjE5LDIyNywxMjgsMjI3aDBjNDcuOCwwLDk4LjI4LTI0LjU2LDExMy41OC01Ny45MWwuMTYtLjA5LjA1LDBoMGwwLDAsLjA4LDBjMC0uMTksMC0uMzgsMC0uNThDMjI5LDE2OC4zMiwyMjYsMTUxLjQsMjIwLjY3LDEzMC42OFpNNTMuOSwxMzIuNDRjNi0xNS44NiwyMi4wNS0yNC44MSwzOC4xMS0xNi44LDE2LjU1LDguMjUsMjYuMzksMjguMTcsMjYuNjMsNDYuNy4xOCwxMy4zMS0uODQsMTcuNzYtMS4yOSwxOS4wOGgtMi4yNGMuNS4xMiwxLjEyLjI4LDAsMC0xOC44Ny00LjU3LTQ4LjcyLTkuODgtNjEuNjgtMTAuMkM0Ny45LDE2MC4xNiw0OS43NywxNDMuMzIsNTMuOSwxMzIuNDRaTTE0MiwxOTEuNzNsLTQuMjgsMi4xOWExNSwxNSwwLDAsMS0xOS4zLDBsLTQuMjgtMi4xOWMtMi40Mi0yLTEtNC41OSwyLjE3LTQuNTloMjMuNTJDMTQzLDE4Ny4xNCwxNDQuNDIsMTg5LjcsMTQyLDE5MS43M1oiLz48L2c+PC9zdmc+';
    }

    /**
     * Return whether the current page is the settings page.
     *
     * @return bool
     */
    private function is_settings_page(): bool {
        if ( ! is_admin() ) {
            return false;
        }

        $screen = get_current_screen();

        if ( empty( $screen ) ) {
            return false;
        }

        return Utils::ends_with( $screen->id, $this->get_menu_slug() );
    }

    /**
     * Enqueue script(s).
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( ! $this->is_settings_page() ) {
            // Not the settings page, bail early
            return;
        }

        $assets_file = PLUGIN_DIR . 'public/' . 'bundle.admin.asset.php';

        if ( ! file_exists( $assets_file ) ) {
            // File doesn't exist, bail early
            return;
        }

        $assets = require $assets_file;

        $dependencies = $assets['dependencies'] ?? array();
        $version      = $assets['version'] ?? filemtime( $assets_file );

        wp_enqueue_style(
            'cookie-consent-admin-style',
            PLUGIN_URL . 'public/bundle.admin.css',
            array( 'wp-components' ),
            $version
        );

        wp_enqueue_script(
            'cookie-consent-admin-script',
            PLUGIN_URL . 'public/bundle.admin.js',
            $dependencies,
            $version,
            true
        );

        wp_localize_script(
            'cookie-consent-admin-script',
            'pressidiumCCAdminDetails',
            array(
                'domain' => Utils::get_domain(),
                'assets' => array(
                    'gtm_template_url' => esc_url( PLUGIN_URL . 'assets/templates/template.tpl' ),
                    'screenshots'      => array(
                        'gallery' => esc_url( PLUGIN_URL . 'assets/images/gtm-gallery.png' ),
                        'import'  => esc_url( PLUGIN_URL . 'assets/images/gtm-import.png' ),
                        'tag'     => esc_url( PLUGIN_URL . 'assets/images/gtm-tag.png' ),
                        'config'  => esc_url( PLUGIN_URL . 'assets/images/gtm-config.png' ),
                    ),
                    'promo'            => esc_url( PLUGIN_URL . 'assets/images/promo.png' ),
                ),
                'api'    => array(
                    'route'          => 'pressidium-cookie-consent/v1/settings',
                    'logs_route'     => 'pressidium-cookie-consent/v1/logs',
                    'consents_route' => 'pressidium-cookie-consent/v1/consents',
                    'export_route'   => 'pressidium-cookie-consent/v1/export',
                    'nonce'          => wp_create_nonce( 'pressidium_cookie_consent_rest' ),
                ),
            )
        );
    }

    /**
     * Add information about this plugin to the left side of the admin footer.
     *
     * @param string|null $content The existing content.
     *
     * @return string|null The modified content including the plugin information.
     */
    public function admin_footer_info( ?string $content ): ?string {
        if ( ! $this->is_settings_page() ) {
            // Not the settings page, bail early
            return $content;
        }

        return sprintf(
            '<span id="pressidium-cc-footer">%s</span>',
            sprintf(
                /* translators: 1: Developer name, 2: Link to the docs. */
                __( 'Developed by %1$s | For more information, read the %2$s.', 'pressidium-cookie-consent' ),
                sprintf(
                    '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
                    // phpcs:ignore Generic.Files.LineLength
                    esc_url( 'https://pressidium.com/?utm_source=cookieconsent&utm_medium=txtlink&utm_campaign=plugins&utm_content=footer' ),
                    esc_html( 'Pressidium®' )
                ),
                sprintf(
                    '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
                    esc_url( 'https://github.com/pressidium/pressidium-cookie-consent/wiki' ),
                    __( 'documentation', 'pressidium-cookie-consent' )
                )
            )
        );
    }

    /**
     * Add plugin version to the right side of the admin footer.
     *
     * @param string|null $content The existing content.
     *
     * @return string|null The modified content including the plugin version.
     */
    public function admin_footer_version( ?string $content ): ?string {
        if ( ! $this->is_settings_page() ) {
            // Not the settings page, bail early
            return $content;
        }

        return sprintf(
            /* translators: Plugin version. */
            __( 'Version %s', 'pressidium-cookie-consent' ),
            esc_html( VERSION )
        );
    }

    /**
     * Return the actions to register.
     *
     * @return array
     */
    public function get_actions(): array {
        $actions = parent::get_actions();

        $actions['admin_enqueue_scripts'] = array( 'enqueue_scripts' );

        return $actions;
    }

    /**
     * Return the filters to register.
     *
     * @return array
     */
    public function get_filters(): array {
        return array(
            'admin_footer_text' => array( 'admin_footer_info' ),
            'update_footer'     => array( 'admin_footer_version', 11 ),
        );
    }

}
