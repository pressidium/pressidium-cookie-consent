/*
 * Initial state for the cookie consent plugin.
 * You can customize the default values here.
 *
 * Most options are based on the `orestbida/cookieconsent` v3 library.
 *
 * {@link https://cookieconsent.orestbida.com/reference/configuration-reference.html}
 */
export default {
  /*
   * Automatically show the consent modal if consent is not valid.
   */
  autoShow: true,

  /*
   * Creates a dark overlay and blocks the page scroll until consent is expressed.
   */
  disablePageInteraction: false,

  /*
   * Clears cookies when user rejects a specific category.
   * It requires a valid `autoClear` array.
   */
  autoClearCookies: false,
  /*
   * Intercepts all `<script>` tags with a `data-category` attribute,
   * and enables them based on the accepted categories.
   */
  manageScriptTags: false,

  /*
   * Stops the plugin's execution when a bot/crawler is detected,
   * to prevent them from indexing the model's content.
   */
  hideFromBots: true,

  /*
   * Asks your users again for consent after a change in your
   * cookie/privacy policy. Requires a valid `revision` number.
   */
  reconsent: true,

  cookie: {
    /*
     * Number of days before the cookie expires.
     */
    expiresAfterDays: 182,

    /*
     * Cookie path.
     */
    path: '/',

    /*
     * Current domain/subdomain's name. Retrieved automatically, by default.
     */
    domain: pressidiumCCAdminDetails.domain || window.location.hostname,

    /*
     * Cookie name.
     */
    name: 'pressidium_cookie_consent',
  },

  categories: {
    /*
     * Necessary cookies is always enabled.
     */
    necessary: {
      enabled: true,
      readOnly: true,
    },

    /*
     * Analytics cookies are disabled by default.
     */
    analytics: {
      enabled: false,
      readOnly: false,
    },

    /*
     * Targeting cookies are disabled by default.
     */
    targeting: {
      enabled: false,
      readOnly: false,
    },

    /*
     * Preferences cookies are disabled by default.
     */
    preferences: {
      enabled: false,
      readOnly: false,
    },
  },

  language: {
    /*
     * The desired default language.
     */
    default: 'en',

    /*
     * Sets the current language dynamically.
     * - When this is set to `browser`, it retrieves the user's browser language.
     * - When this is set to `document`, it retrieves language from the `lang` attribute.
     */
    autoDetect: 'browser',

    /*
     * Defines the translation(s) content.
     */
    translations: {
      en: {
        consentModal: {
          title: 'Cookie Consent',
          description: 'Hi, we use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features.',
          acceptAllBtn: 'Accept all',
          acceptNecessaryBtn: 'Accept necessary',
          showPreferencesBtn: 'Show preferences',
          closeIconLabel: 'Close',
          footer: '<a href="#link">Privacy Policy</a><a href="#link">Terms and conditions</a>',
          footerLinks: [
            { url: '#link', label: 'Privacy Policy' },
            { url: '#link', label: 'Terms and conditions' },
          ],
        },
        preferencesModal: {
          title: 'Cookie preferences',
          savePreferencesBtn: 'Save settings',
          acceptAllBtn: 'Accept all',
          acceptNecessaryBtn: 'Reject all',
          closeIconLabel: 'Close',
          sections: [
            {
              title: 'Cookie usage 📢',
              description: 'We use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features. Click on the different category headings to find out more and change our default settings. However, blocking some types of cookies may impact your experience of the site and the services we are able to offer.',
            },
            {
              title: 'Strictly necessary cookies',
              description: 'These cookies are necessary for the website to function and cannot be switched off in our systems. You can set your browser to block or alert you about these cookies, but some parts of the site may not then work.',
              linkedCategory: 'necessary',
            },
            {
              title: 'Performance and Analytics cookies',
              description: 'These cookies allow us to analyze visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.',
              linkedCategory: 'analytics',
            },
            {
              title: 'Advertisement and Targeting cookies',
              description: 'These cookies may be set through our site by our social media providers and/or our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites. They do not store directly personal information, but are based on uniquely identifying your browser and internet device.',
              linkedCategory: 'targeting',
            },
            {
              title: 'Functionality and Preferences cookies',
              description: 'These cookies allow us to provide enhanced functionality and personalization by storing user preferences.',
              linkedCategory: 'preferences',
            },
            {
              title: 'More information',
              description: 'For any queries in relation to our policy on cookies and your choices, please contact us.',
            },
          ],
        },
      },
    },
  },

  /*
   * Tweak main UI settings.
   */
  guiOptions: {
    consentModal: {
      layout: 'box',
      position: 'bottom right',
      equalWeightButtons: false,
      flipButtons: false,
    },
    preferencesModal: {
      layout: 'box',
      position: 'left',
      equalWeightButtons: false,
      flipButtons: false,
    },
  },

  /*
   * Plugin-specific options.
   */
  pressidiumOptions: {
    cookieTable: {
      necessary: [],
      analytics: [],
      targeting: [],
      preferences: [],
    },
    cookieTableHeaders: {
      translations: {
        en: {
          name: 'Name',
          domain: 'Domain',
          expiration: 'Expiration',
          path: 'Path',
          description: 'Description',
        },
      },
    },

    /*
     * Shows a footer containing one or two links in the consent modal footer.
     * Often used to display the Privacy Policy and Terms of Service links. 
     */
    showConsentModalFooter: true,

    /*
     * Scripts to block.
     */
    blockedScripts: [],
    font: {
      name: 'Default',
      slug: 'default',
      family: '',
    },

    /*
     * Tweak the floating button settings.
     */
    floatingButton: {
      enabled: true,
      size: 'sm',
      position: 'left',
      icon: 'pressidium',
      transition: 'fade-in-up',
    },

    /*
     * Color palette.
     */
    colors: {
      bg: '#f9faff',
      'primary-color': '#112954',
      'btn-primary-bg': '#3859d0',
      'btn-primary-color': '#f9faff',
      'btn-primary-hover-bg': '#1d2e38',
      'btn-primary-hover-color': '#f9faff',
      'btn-secondary-bg': '#dfe7f9',
      'btn-secondary-color': '#112954',
      'btn-secondary-hover-bg': '#c6d1ea',
      'btn-secondary-hover-color': '#112954',
      'toggle-off-bg': '#8fa8d6',
      'toggle-on-knob-bg': '#3859d0',
      'toggle-readonly-bg': '#cbd8f1',
      'toggle-knob-bg': '#fff',
      'toggle-knob-icon-color': '#ecf2fa',
      'cookie-category-block-bg': '#ebeff9',
      'cookie-category-block-hover-bg': '#dbe5f9',
      'separator-border-color': '#f1f3f5',
      'block-text': '#112954',
      'cookie-table-border': '#e1e7f3',
      'overlay-bg': 'rgba(230, 235, 255, .85)',
      'webkit-scrollbar-bg': '#ebeff9',
      'webkit-scrollbar-bg-hover': '#3859d0',
      'btn-floating-bg': '#3859d0',
      'btn-floating-icon': '#f9faff',
      'btn-floating-hover-bg': '#1d2e38',
      'btn-floating-hover-icon': '#f9faff',
    },

    /*
     * A big X button will be generated (visible only in the `box` layout).
     * It acts the same as the accept necessary button.
     */
    consentModalCloseIcon: true,

    /*
     * Records user consents to be able to provide proof of consent for auditing purposes.
     * Stores a UUID, the URL of the page the user was on when they gave consent,
     * the coarse location (country codes only), the anonymized IP address, the user agent,
     * the consent status for each category, and the date and time of consent.
     */
    recordConsents: true,

    /*
     * Hides cookie categories with no cookies.
     */
    hideEmptyCategories: false,

    /*
     * Google Consent Mode v2 integration.
     */
    gcm: {
      enabled: false,
      implementation: 'gtag',
      adsDataRedaction: false,
      urlPassthrough: false,
      regions: [],
    },

    /*
     * One-click PHP proxy to route traffic to Google tag gateway.
     */
    googleTagGateway: {
      /*
       * When enabled, routes traffic to Google tag gateway.
       */
      proxyEnabled: false,

      /*
       * Google tag ID, the identifier to load a given Google tag.
       */
      gtagId: '',
    },

    /*
     * AI settings. This is used to translate string and for the cookie description
     * generator feature, which uses AI to generate cookie descriptions based on their name.
     */
    ai: {
      /*
       * AI provider.
       */
      provider: 'openai',

      /*
       * AI model.
       */
      model: 'gpt-3.5-turbo',
    },
  },
};
