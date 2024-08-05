<?php
/**
 * Settings.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use Pressidium\WP\CookieConsent\Options\Options;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class Settings {

    /**
     * @var string Options key.
     */
    const OPTIONS_KEY = 'pressidium_cookie_consent_settings';

    /**
     * @var Options An instance of the `Options` class.
     */
    private Options $options;

    /**
     * Settings constructor.
     *
     * @param Options $options An instance of the `Options` class.
     */
    public function __construct( Options $options ) {
        $this->options = $options;
    }

    /**
     * Return default values for the settings.
     *
     * @return array
     */
    private function get_default_values(): array {
        return array(
            'autorun' => true,
            'force_consent' => false,
            'autoclear_cookies' => false,
            'page_scripts' => false,
            'hide_from_bots' => true,
            'reconsent' => true,
            'delay' => 0,
            'cookie_expiration' => 182,
            'cookie_path' => '/',
            'cookie_domain' => Utils::get_domain(),
            'auto_language' => 'browser',
            'cookie_name' => 'pressidium_cookie_consent',
            'languages' => array(
                'en' => array(
                    'consent_modal' => array(
                        'title' => 'Cookie Consent',
                        'description' => 'Hi, we use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features.  <button type="button" data-cc="c-settings" class="cc-link">Cookie Settings</button>',
                        'primary_btn' => array(
                            'text' => 'Accept all',
                            'role' => 'accept_all',
                        ),
                        'secondary_btn' => array(
                            'text' => 'Accept necessary',
                            'role' => 'accept_necessary',
                        ),
                    ),
                    'settings_modal' => array(
                        'title' => 'Cookie preferences',
                        'save_settings_btn' => 'Save settings',
                        'accept_all_btn' => 'Accept all',
                        'reject_all_btn' => 'Reject all',
                        'close_btn_label' => 'Close',
                        'cookie_table_headers' => array(
                            array( 'name' => 'Name' ),
                            array( 'domain' => 'Domain' ),
                            array( 'expiration' => 'Expiration' ),
                            array( 'path' => 'Path' ),
                            array( 'description' => 'Description' ),
                        ),
                        'blocks' => array(
                            array(
                                'title' => 'Cookie usage 📢',
                                'description' => 'We use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features. Click on the different category headings to find out more and change our default settings. However, blocking some types of cookies may impact your experience of the site and the services we are able to offer.',
                            ),
                            array(
                                'title' => 'Strictly necessary cookies',
                                'description' => 'These cookies are necessary for the website to function and cannot be switched off in our systems. You can set your browser to block or alert you about these cookies, but some parts of the site may not then work.',
                                'toggle' => array(
                                    'value' => 'necessary',
                                    'enabled' => true,
                                    'readonly' => true,
                                ),
                                'cookie_table' => array(),
                            ),
                            array(
                                'title' => 'Performance and Analytics cookies',
                                'description' => 'These cookies allow us to analyze visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.',
                                'toggle' => array(
                                    'value' => 'analytics',
                                    'enabled' => false,
                                    'readonly' => false,
                                ),
                                'cookie_table' => array(),
                            ),
                            array(
                                'title' => 'Advertisement and Targeting cookies',
                                'description' => 'These cookies may be set through our site by our social media providers and/or our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites. They do not store directly personal information, but are based on uniquely identifying your browser and internet device.',
                                'toggle' => array(
                                    'value' => 'targeting',
                                    'enabled' => false,
                                    'readonly' => false,
                                ),
                                'cookie_table' => array(),
                            ),
                            array(
                                'title' => 'Functionality and Preferences cookies',
                                'description' => 'These cookies allow us to provide enhanced functionality and personalization by storing user preferences.',
                                'toggle' => array(
                                    'value' => 'preferences',
                                    'enabled' => false,
                                    'readonly' => false,
                                ),
                                'cookie_table' => array(),
                            ),
                            array(
                                'title' => 'More information',
                                'description' => 'For any queries in relation to our policy on cookies and your choices, please contact us.',
                            ),
                        ),
                    ),
                ),
            ),
            'gui_options' => array(
                'consent_modal' => array(
                    'layout' => 'box',
                    'position' => 'bottom right',
                    'transition' => 'slide',
                    'swap_buttons' => false,
                ),
                'settings_modal' => array(
                    'layout' => 'box',
                    'position' => 'left',
                    'transition' => 'slide',
                ),
            ),
            'pressidium_options' => array(
                'primary_btn_role' => 'accept_all',
                'secondary_btn_role' => 'accept_necessary',
                'cookie_table' => array(
                    'necessary' => array(),
                    'analytics' => array(),
                    'targeting' => array(),
                    'preferences' => array(),
                ),
                'blocked_scripts' => array(),
                'font' => array(
                    'name'   => 'Default',
                    'slug'   => 'default',
                    'family' => '',
                ),
                'floating_button' => array(
                    'enabled'    => false,
                    'size'       => 'sm',
                    'position'   => 'left',
                    'icon'       => 'pressidium',
                    'transition' => 'fade-in-up',
                ),
                'colors' => array(
                    'bg' => '#f9faff',
                    'text' => '#112954',
                    'btn-primary-bg' => '#3859d0',
                    'btn-primary-text' => '#f9faff',
                    'btn-primary-hover-bg' => '#1d2e38',
                    'btn-primary-hover-text' => '#f9faff',
                    'btn-secondary-bg' => '#dfe7f9',
                    'btn-secondary-text' => '#112954',
                    'btn-secondary-hover-bg' => '#c6d1ea',
                    'btn-secondary-hover-text' => '#112954',
                    'toggle-bg-off' => '#8fa8d6',
                    'toggle-bg-on' => '#3859d0',
                    'toggle-bg-readonly' => '#cbd8f1',
                    'toggle-knob-bg' => '#fff',
                    'toggle-knob-icon-color' => '#ecf2fa',
                    'cookie-category-block-bg' => '#ebeff9',
                    'cookie-category-block-bg-hover' => '#dbe5f9',
                    'section-border' => '#f1f3f5',
                    'block-text' => '#112954',
                    'cookie-table-border' => '#e1e7f3',
                    'overlay-bg' => 'rgba(230, 235, 255, .85)',
                    'webkit-scrollbar-bg' => '#ebeff9',
                    'webkit-scrollbar-bg-hover' => '#3859d0',
                    'btn-floating-bg' => '#3859d0',
                    'btn-floating-icon' => '#f9faff',
                    'btn-floating-hover-bg' => '#1d2e38',
                    'btn-floating-hover-icon' => '#f9faff',
                ),
                'record_consents' => true,
                'hide_empty_categories' => false,
                'gcm' => array(
                    'enabled' => false,
                    'implementation' => 'gtag',
                    'ads_data_redaction' => false,
                    'url_passthrough' => false,
                    'regions' => array(),
                ),
            ),
        );
    }

    /**
     * Return settings.
     *
     * @return array
     */
    public function get(): array {
        $settings = $this->options->get( self::OPTIONS_KEY );

        if ( ! empty( $settings ) ) {
            return $settings;
        }

        return $this->get_default_values();
    }

    /**
     * Set settings.
     *
     * @param array $settings Settings to store.
     *
     * @return bool Whether the settings were stored successfully.
     */
    public function set( array $settings ): bool {
        if ( empty( $settings ) ) {
            $settings = $this->get_default_values();
        }

        return $this->options->set( self::OPTIONS_KEY, Emoji::encode_array( $settings ) );
    }

    /**
     * Remove settings.
     *
     * @return bool Whether the settings were removed successfully.
     */
    public function remove(): bool {
        return $this->options->remove( self::OPTIONS_KEY );
    }

}
