<?php
/**
 * Settings.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use Pressidium\WP\CookieConsent\Options\Options;
use Pressidium\WP\CookieConsent\Utils\WP_Utils;

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
            'autoShow'               => true,
            'disablePageInteraction' => false,
            'autoClearCookies'       => false,
            'manageScriptTags'       => false,
            'hideFromBots'           => true,
            'reconsent'              => true,
            'cookie'                 => array(
                'expiresAfterDays' => 182,
                'path'             => '/',
                'domain'           => WP_Utils::get_domain(),
                'name'             => 'pressidium_cookie_consent',
            ),
            'categories'             => array(
                'necessary'   => array(
                    'enabled'  => true,
                    'readOnly' => true,
                ),
                'analytics'   => array(
                    'enabled'  => false,
                    'readOnly' => false,
                ),
                'targeting'   => array(
                    'enabled'  => false,
                    'readOnly' => false,
                ),
                'preferences' => array(
                    'enabled'  => false,
                    'readOnly' => false,
                ),
            ),
            'language'               => array(
                'default'      => 'en',
                'autoDetect'   => 'browser',
                'translations' => array(
                    'en' => array(
                        'consentModal'     => array(
                            'title'              => 'Cookie Consent',
                            'description'        => 'Hi, we use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features.',
                            'acceptAllBtn'       => 'Accept all',
                            'acceptNecessaryBtn' => 'Accept necessary',
                            'showPreferencesBtn' => 'Show preferences',
                            'closeIconLabel'     => 'Close',
                            'footer'             => '<a href="#link">Privacy Policy</a><a href="#link">Terms and conditions</a>',
                            'footerLinks'        => array(
                                array(
                                    'url'   => '#link',
                                    'label' => 'Privacy Policy',
                                ),
                                array(
                                    'url'   => '#link',
                                    'label' => 'Terms and conditions',
                                ),
                            ),
                        ),
                        'preferencesModal' => array(
                            'title'              => 'Cookie preferences',
                            'savePreferencesBtn' => 'Save preferences',
                            'acceptAllBtn'       => 'Accept all',
                            'acceptNecessaryBtn' => 'Accept necessary',
                            'closeIconLabel'     => 'Close',
                            'sections' => array(
                                array(
                                    'title'       => 'Cookie usage 📢',
                                    'description' => 'We use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features. Click on the different category headings to find out more and change our default settings. However, blocking some types of cookies may impact your experience of the site and the services we are able to offer.',
                                ),
                                array(
                                    'title'          => 'Strictly necessary cookies',
                                    'description'    => 'These cookies are necessary for the website to function and cannot be switched off in our systems. You can set your browser to block or alert you about these cookies, but some parts of the site may not then work.',
                                    'linkedCategory' => 'necessary',
                                ),
                                array(
                                    'title'          => 'Performance and Analytics cookies',
                                    'description'    => 'These cookies allow us to analyze visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.',
                                    'linkedCategory' => 'analytics',
                                ),
                                array(
                                    'title'          => 'Advertisement and Targeting cookies',
                                    'description'    => 'These cookies may be set through our site by our social media providers and/or our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites. They do not store directly personal information, but are based on uniquely identifying your browser and internet device.',
                                    'linkedCategory' => 'targeting',
                                ),
                                array(
                                    'title'          => 'Functionality and Preferences cookies',
                                    'description'    => 'These cookies allow us to provide enhanced functionality and personalization by storing user preferences.',
                                    'linkedCategory' => 'preferences',
                                ),
                                array(
                                    'title'       => 'More information',
                                    'description' => 'For any queries in relation to our policy on cookies and your choices, please contact us.',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'guiOptions'             => array(
                'consentModal'     => array(
                    'layout'             => 'box',
                    'position'           => 'bottom right',
                    'equalWeightButtons' => false,
                    'flipButtons'        => false,
                ),
                'preferencesModal' => array(
                    'layout'             => 'box',
                    'position'           => 'left',
                    'equalWeightButtons' => false,
                    'flipButtons'        => false,
                ),
            ),
            'pressidiumOptions'      => array(
                'cookieTable'         => array(
                    'necessary'   => array(),
                    'analytics'   => array(),
                    'targeting'   => array(),
                    'preferences' => array(),
                ),
                'cookieTableHeaders'  => array(
                    'translations' => array(
                        'en' => array(
                            'name'        => 'Name',
                            'domain'      => 'Domain',
                            'expiration'  => 'Expiration',
                            'path'        => 'Path',
                            'description' => 'Description',
                        ),
                    ),
                ),
                'consentModalCloseIcon'  => true,
                'showConsentModalFooter' => true,
                'blockedScripts'        => array(),
                'font'                => array(
                    'name'   => 'Default',
                    'slug'   => 'default',
                    'family' => '',
                ),
                'floatingButton'      => array(
                    'enabled'    => false,
                    'size'       => 'sm',
                    'position'   => 'left',
                    'icon'       => 'pressidium',
                    'transition' => 'fade-in-up',
                ),
                'colors'              => array(
                    'bg'                             => '#f9faff',
                    'primary-color'                  => '#112954',
                    'btn-primary-bg'                 => '#3859d0',
                    'btn-primary-color'              => '#f9faff',
                    'btn-primary-hover-bg'           => '#1d2e38',
                    'btn-primary-hover-color'        => '#f9faff',
                    'btn-secondary-bg'               => '#dfe7f9',
                    'btn-secondary-color'            => '#112954',
                    'btn-secondary-hover-bg'         => '#c6d1ea',
                    'btn-secondary-hover-color'      => '#112954',
                    'toggle-off-bg'                  => '#8fa8d6',
                    'toggle-on-knob-bg'              => '#3859d0',
                    'toggle-readonly-bg'             => '#cbd8f1',
                    'toggle-knob-bg'                 => '#fff',
                    'toggle-knob-icon-color'         => '#ecf2fa',
                    'cookie-category-block-bg'       => '#ebeff9',
                    'cookie-category-block-hover-bg' => '#dbe5f9',
                    'separator-border-color'         => '#f1f3f5',
                    'block-text'                     => '#112954',
                    'cookie-table-border'            => '#e1e7f3',
                    'overlay-bg'                     => 'rgba(230, 235, 255, .85)',
                    'webkit-scrollbar-bg'            => '#ebeff9',
                    'webkit-scrollbar-bg-hover'      => '#3859d0',
                    'btn-floating-bg'                => '#3859d0',
                    'btn-floating-icon'              => '#f9faff',
                    'btn-floating-hover-bg'          => '#1d2e38',
                    'btn-floating-hover-icon'        => '#f9faff',
                ),
                'recordConsents'      => true,
                'hideEmptyCategories' => false,
                'gcm'                 => array(
                    'enabled'          => false,
                    'implementation'   => 'gtag',
                    'adsDataRedaction' => false,
                    'urlPassthrough'   => false,
                    'regions'          => array(),
                ),
                'googleTagGateway' => array(
                    'proxyEnabled' => false,
                    'gtagId' => '',
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
