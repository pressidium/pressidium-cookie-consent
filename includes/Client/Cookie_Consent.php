<?php
/**
 * Cookie Consent.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Client;

use const Pressidium\WP\CookieConsent\PLUGIN_DIR;
use const Pressidium\WP\CookieConsent\PLUGIN_URL;

use Pressidium\WP\CookieConsent\Hooks\Actions;
use Pressidium\WP\CookieConsent\Hooks\Filters;

use Pressidium\WP\CookieConsent\Settings;
use Pressidium\WP\CookieConsent\Utils\WP_Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Cookie_Consent class.
 *
 * @since 1.0.0
 */
class Cookie_Consent implements Actions, Filters {

    /**
     * @var array Settings.
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
     * Return the cookie consent settings.
     *
     * @return array
     */
    private function get_settings(): array {
        /*
         * Deep clone the array by merging it with an empty array,
         * so we can modify it without affecting the original.
         */
        $cc_settings = array_merge( array(), $this->settings );

        $category_sections_map = array(
            'necessary'   => 1,
            'analytics'   => 2,
            'targeting'   => 3,
            'preferences' => 4,
        );

        $consent_modal_close_icon = $cc_settings['pressidiumOptions']['consentModalCloseIcon'] ?? true;

        $show_footer = $cc_settings['pressidiumOptions']['showConsentModalFooter'] ?? true;

        foreach ( $cc_settings['language']['translations'] as $language => $language_settings ) {
            $cc_settings['language']['translations'][ $language ]['consentModal']['closeIconLabel']     = $consent_modal_close_icon ? 'Close' : null;
            $cc_settings['language']['translations'][ $language ]['preferencesModal']['closeIconLabel'] = 'Close';

            if ( ! $show_footer ) {
                $cc_settings['language']['translations'][ $language ]['consentModal']['footer'] = '';
            } else {
                $footer_links = $language_settings['consentModal']['footerLinks'] ?? array();
                $footer_html  = '';

                foreach ( $footer_links as $link ) {
                    $url   = trim( $link['url'] ?? '' );
                    $label = trim( $link['label'] ?? '' );

                    if ( empty( $url ) || empty( $label ) ) {
                        continue;
                    }

                    $footer_html .= sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( $url ),
                        esc_html( $label )
                    );
                }

                $cc_settings['language']['translations'][ $language ]['consentModal']['footer'] = $footer_html;
            }
            
            unset( $cc_settings['language']['translations'][ $language ]['consentModal']['footerLinks'] );

            foreach ( $category_sections_map as $category => $index ) {
                $body = $cc_settings['pressidiumOptions']['cookieTable'][ $category ];

                if ( empty( $body ) ) {
                    unset( $cc_settings['language']['translations'][ $language ]['preferencesModal']['sections'][ $index ]['cookieTable'] );
                    continue;
                }

                $headers = $cc_settings['pressidiumOptions']['cookieTableHeaders']['translations'][ $language ]
                    ?? $cc_settings['pressidiumOptions']['cookieTableHeaders']['translations']['en']
                    ?? array();

                $cc_settings['language']['translations'][ $language ]['preferencesModal']['sections'][ $index ]['cookieTable'] = array(
                    'headers' => $headers,
                    'body'    => $body,
                );
            }
        }

        if ( ! $cc_settings['reconsent'] ) {
            unset( $cc_settings['revision'] );
        }

        unset( $cc_settings['reconsent'] );
        unset( $cc_settings['pressidiumOptions'] );

        return $cc_settings;
    }

    /**
     * Enqueue scripts.
     *
     * @return void
     */
    public function enqueue_scripts(): void {
        if ( WP_Utils::is_ninja_forms_preview() ) {
            // Ninja Forms preview, do not enqueue scripts
            return;
        }

        $assets_file = PLUGIN_DIR . 'public/bundle.client.asset.php';

        if ( ! file_exists( $assets_file ) ) {
            // File doesn't exist, bail early
            return;
        }

        $assets = require $assets_file;

        $dependencies = $assets['dependencies'] ?? array();
        $version      = $assets['version'] ?? filemtime( $assets_file );

        wp_enqueue_style(
            'cookie-consent-client-style',
            PLUGIN_URL . 'public/bundle.client.css',
            array(),
            $version
        );

        wp_enqueue_script(
            'cookie-consent-client-script',
            PLUGIN_URL . 'public/bundle.client.js',
            $dependencies,
            $version,
            true
        );

        wp_localize_script(
            'cookie-consent-client-script',
            'pressidiumCCClientDetails',
            array(
                'settings'          => $this->get_settings(),
                'api'               => array(
                    'rest_url'        => rest_url(),
                    'route'          => 'pressidium-cookie-consent/v1/settings',
                    'consent_route'  => 'pressidium-cookie-consent/v1/consent',
                    'consents_route' => 'pressidium-cookie-consent/v1/consents',
                ),

                /*
                 * `wp_localize_script()` casts all scalars to strings,
                 * so we need a nested array to avoid the stringification
                 * of our boolean values.
                 */
                'additional_options' => array(
                    'recordConsents'      => boolval( $this->settings['pressidiumOptions']['recordConsents'] ?? true ),
                    'hideEmptyCategories' => boolval( $this->settings['pressidiumOptions']['hideEmptyCategories'] ?? false ),
                    'floatingButton'      => $this->settings['pressidiumOptions']['floatingButton'] ?? array(),
                    'gcm'                 => $this->settings['pressidiumOptions']['gcm'] ?? array(),
                ),
            )
        );
    }

    /**
     * Enqueue scripts on admin pages.
     *
     * @param string $hook_suffix The current admin page.
     *
     * @return void
     */
    public function admin_enqueue_scripts( string $hook_suffix ): void {
        if ( $hook_suffix !== 'toplevel_page_pressidium-cookie-consent' ) {
            // Not on the Cookie Consent settings page, bail early
            return;
        }

        $this->enqueue_scripts();
    }

    /**
     * Print inline script.
     *
     * @return void
     */
    private function print_inline_script(): void {
        if ( ! $this->settings['manageScriptTags'] || empty( $this->settings['pressidiumOptions']['blockedScripts'] ) ) {
            // Either "Manage script tags" is disabled, or there are no blocked scripts, bail early
            return;
        }

        $block_scripts_path_suffix = 'public/block-scripts.js';
        $block_scripts_file_path   = PLUGIN_DIR . $block_scripts_path_suffix;
        $block_scripts_url         = PLUGIN_URL . $block_scripts_path_suffix;

        if ( ! file_exists( $block_scripts_file_path ) ) {
            // File doesn't exist, bail early
            return;
        }
        ?>

        <script type="text/javascript" data-pressidium-cc-no-block>
            window.pressidiumCCBlockedScripts = <?php echo wp_json_encode( $this->settings['pressidiumOptions']['blockedScripts'] ); ?>;
            window.pressidiumCCCookieName = '<?php echo esc_js( $this->settings['cookie']['name'] ); ?>';
        </script>

        <script src="<?php echo esc_url( $block_scripts_url ); ?>" type="text/javascript" data-pressidium-cc-no-block></script>

        <?php
    }

    /**
     * Print inline script for Google Consent Mode.
     *
     * @return void
     */
    private function print_consent_mode_inline_script(): void {
        if ( ! $this->settings['pressidiumOptions']['gcm']['enabled'] ) {
            // GCM is not enabled, bail early
            return;
        }
        ?>

        <script type="text/javascript" data-pressidium-cc-no-block>
            function onPressidiumCookieConsentUpdated(event) {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    event: event.type,
                    consent: event.detail,
                });
            }

            window.addEventListener('pressidium-cookie-consent-accepted', onPressidiumCookieConsentUpdated);
            window.addEventListener('pressidium-cookie-consent-changed', onPressidiumCookieConsentUpdated);
        </script>

        <?php
    }

    /**
     * Print inline style.
     *
     * @return void
     */
    private function print_inline_style(): void {
        ?>

        <style id="pressidium-cc-styles">
            .pressidium-cc-theme {
                <?php
                $font_slug   = $this->settings['pressidiumOptions']['font']['slug'] ?? 'default';
                $font_family = $this->settings['pressidiumOptions']['font']['family'] ?? 'inherit';

                if ( $font_slug !== 'default' ) {
                    echo "--cc-font-family: {$font_family};\n";
                }

                foreach ( $this->settings['pressidiumOptions']['colors'] as $key => $value ) {
                    $value = esc_attr( $value );
                    echo "--cc-{$key}: {$value};\n";
                }
                ?>
            }
        </style>

        <?php
    }

    /**
     * Print inline scripts/stylesheets in the head.
     *
     * @return void
     */
    public function print_inline_head(): void {
        $this->print_inline_script();
        $this->print_consent_mode_inline_script();
        $this->print_inline_style();
    }

    /**
     * Filter the body classes to add the `.pressidium-cc-theme` class.
     *
     * @param string[] $classes CSS classes.
     *
     * @return string[] CSS classes with the `.pressidium-cc-theme` class added.
     */
    public function add_pressidium_theme_class( array $classes ): array {
        $pressidium_cc_theme_class = 'pressidium-cc-theme';

        if ( ! in_array( $pressidium_cc_theme_class, $classes ) ) {
            $classes[] = $pressidium_cc_theme_class;
        }

        return $classes;
    }

    /**
     * Filter the body classes to add the `.pressidium-cc-theme` class.
     *
     * @param string $classes Space-separated CSS classes.
     *
     * @return string Space-separated CSS classes with the `.pressidium-cc-theme` class added.
     */
    public function add_pressidium_theme_class_to_admin( string $classes ): string {
        $classes_arr = explode( ' ', $classes );

        return implode( ' ', $this->add_pressidium_theme_class( $classes_arr ) );
    }

    /**
     * Return the actions to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'wp_enqueue_scripts'    => array( 'enqueue_scripts' ),
            'admin_enqueue_scripts' => array( 'admin_enqueue_scripts' ),
            // Priority `-10` to ensure it's printed before any other inline scripts
            'wp_head'               => array( 'print_inline_head', -10 ),
            'admin_head'            => array( 'print_inline_head' ),
        );
    }

    /**
     * Return the filters to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_filters(): array {
        return array(
            'body_class'       => array( 'add_pressidium_theme_class' ),
            'admin_body_class' => array( 'add_pressidium_theme_class_to_admin' ),
        );
    }

}
