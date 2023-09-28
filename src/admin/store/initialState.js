export default {
  autorun: true,
  force_consent: false,
  autoclear_cookies: false,
  page_scripts: false,
  hide_from_bots: true,
  reconsent: true,

  delay: 0,
  cookie_expiration: 182,
  cookie_path: '/',
  cookie_domain: pressidiumCCAdminDetails.domain || window.location.hostname,

  auto_language: 'browser',
  cookie_name: 'pressidium_cookie_consent',

  languages: {
    en: {
      consent_modal: {
        title: 'Cookie Consent',
        description: 'Hi, we use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features.  <button type="button" data-cc="c-settings" class="cc-link">Cookie Settings</button>',
        primary_btn: {
          text: 'Accept all',
          role: 'accept_all',
        },
        secondary_btn: {
          text: 'Accept necessary',
          role: 'accept_necessary',
        },
      },
      settings_modal: {
        title: 'Cookie preferences',
        save_settings_btn: 'Save settings',
        accept_all_btn: 'Accept all',
        reject_all_btn: 'Reject all',
        close_btn_label: 'Close',
        cookie_table_headers: [
          { name: 'Name' },
          { domain: 'Domain' },
          { expiration: 'Expiration' },
          { path: 'Path' },
          { description: 'Description' },
        ],
        blocks: [
          {
            title: 'Cookie usage 📢',
            description: 'We use cookies to ensure the website\'s proper operation, to analyze traffic and performance, and to provide social media features. Click on the different category headings to find out more and change our default settings. However, blocking some types of cookies may impact your experience of the site and the services we are able to offer.',
          },
          {
            title: 'Strictly necessary cookies',
            description: 'These cookies are necessary for the website to function and cannot be switched off in our systems. You can set your browser to block or alert you about these cookies, but some parts of the site may not then work.',
            toggle: {
              value: 'necessary',
              enabled: true,
              readonly: true,
            },
            cookie_table: [],
          },
          {
            title: 'Performance and Analytics cookies',
            description: 'These cookies allow us to analyze visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.',
            toggle: {
              value: 'analytics',
              enabled: false,
              readonly: false,
            },
            cookie_table: [],
          },
          {
            title: 'Advertisement and Targeting cookies',
            description: 'These cookies may be set through our site by our social media providers and/or our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites. They do not store directly personal information, but are based on uniquely identifying your browser and internet device.',
            toggle: {
              value: 'targeting',
              enabled: false,
              readonly: false,
            },
            cookie_table: [],
          },
          {
            title: 'More information',
            description: 'For any queries in relation to our policy on cookies and your choices, please contact us.',
          },
        ],
      },
    },
  },

  gui_options: {
    consent_modal: {
      layout: 'box',
      position: 'bottom right',
      transition: 'slide',
      swap_buttons: false,
    },
    settings_modal: {
      layout: 'box',
      position: 'left',
      transition: 'slide',
    },
  },

  pressidium_options: {
    primary_btn_role: 'accept_all',
    secondary_btn_role: 'accept_necessary',
    cookie_table: {
      necessary: [],
      analytics: [],
      targeting: [],
    },
    blocked_scripts: [],
    colors: {
      bg: '#f9faff',
      text: '#112954',
      'btn-primary-bg': '#3859d0',
      'btn-primary-text': '#f9faff',
      'btn-primary-hover-bg': '#1d2e38',
      'btn-secondary-bg': '#dfe7f9',
      'btn-secondary-text': '#112954',
      'btn-secondary-hover-bg': '#c6d1ea',
      'toggle-bg-off': '#8fa8d6',
      'toggle-bg-on': '#3859d0',
      'toggle-bg-readonly': '#cbd8f1',
      'toggle-knob-bg': '#fff',
      'toggle-knob-icon-color': '#ecf2fa',
      'cookie-category-block-bg': '#ebeff9',
      'cookie-category-block-bg-hover': '#dbe5f9',
      'section-border': '#f1f3f5',
      'block-text': '#112954',
      'cookie-table-border': '#e1e7f3',
      'overlay-bg': 'rgba(230, 235, 255, .85)',
      'webkit-scrollbar-bg': '#ebeff9',
      'webkit-scrollbar-bg-hover': '#3859d0',
    },
    record_consents: true,
  },
};
