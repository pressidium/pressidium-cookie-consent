import './lib/cookieconsent';

import './components';
import './wp-consent-api';

import './scss/main.scss';

((details, gtag) => {
  if (typeof initCookieConsent !== 'function') {
    // Cookie Consent is not loaded, bail early
    return;
  }

  if (typeof details === 'undefined') {
    // Client details are not available, bail early
    return;
  }

  if (!('settings' in details)) {
    // Settings are not available, bail early
    return;
  }

  const denied = {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    functionality_storage: 'denied',
    personalization_storage: 'denied',
    security_storage: 'denied',
  };

  const { settings, additional_options: additionalOptions } = details;

  const {
    record_consents: recordConsents,
    hide_empty_categories: hideEmptyCategories,
    floating_button: floatingButton,
    gcm,
  } = additionalOptions;

  /**
   * Return the i18n strings.
   *
   * @param {?string} language Optional language code. If omitted, the
   *                           currently selected language will be used.
   *
   * @return {object} An object containing the i18n strings.
   */
  const getI18nStrings = (language = null) => {
    if (!window.pressidiumCookieConsent) {
      return {};
    }

    const currentLanguage = language || window.pressidiumCookieConsent.getConfig('current_lang');
    return settings.languages[currentLanguage];
  };

  /**
   * Show the floating button.
   *
   * @return {void}
   */
  const showFloatingButton = () => {
    if (!floatingButton.enabled) {
      return;
    }

    document
      .querySelector('pressidium-floating-button')
      .status = 'visible';
  };

  /**
   * Hide the floating button.
   *
   * @return {void}
   */
  const hideFloatingButton = () => {
    if (!floatingButton.enabled) {
      return;
    }

    document
      .querySelector('pressidium-floating-button')
      .status = 'hidden';
  };

  /**
   * Initialize the floating button custom element.
   *
   * @param {object}  options                     Options to initialize the floating button with.
   * @param {boolean} [options.enabled=false]     Whether the floating button is enabled.
   * @param {string}  [options.size='sm']         The size of the floating button.
   * @param {string}  [options.position='left']   The position of the floating button.
   * @param {string}  [options.icon='pressidium'] The icon to use for the floating button.
   * @param {string}  [options.transition='']     The transition to use for the floating button.
   *
   * @return {void}
   */
  const initFloatingButton = (options = {}) => {
    const {
      enabled = false,
      size = 'sm',
      position = 'left',
      icon = 'pressidium',
      transition = '',
    } = options;

    if (!enabled) {
      // Floating button is disabled, bail early
      return;
    }

    const i18nStrings = getI18nStrings();

    // Remove any previously created floating buttons
    const existingButton = document.querySelector('pressidium-floating-button');
    if (existingButton) {
      existingButton.remove();
    }

    // Create the floating button
    const button = document.createElement('pressidium-floating-button');

    button.size = size;
    button.label = i18nStrings.settings_modal.title;
    button.position = position;
    button.status = 'hidden';
    button.transition = transition;

    switch (icon) {
      case 'pressidium':
        button.innerHTML = `
          <svg slot="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
            <path d="M158.7,140.72c-2.72,2.24-7.25,6.4-9.16,11.2a32.62,32.62,0,0,0-1.86,11.24,21.61,21.61,0,0,0,.16,2.84c2.84,5,12.07,5.47,17.1,5.08,5.7-.45,14.24-2.33,17.92-7.14a22.63,22.63,0,0,0,3.59-8.1,18.83,18.83,0,0,0,0-10.71C183.52,136.17,170.14,133.86,158.7,140.72Z" />
            <path d="M97.42,140.72c-11.43-6.86-24.82-4.55-27.76,4.41a18.75,18.75,0,0,0,0,10.71,22.54,22.54,0,0,0,3.6,8.1c3.67,4.81,12.21,6.69,17.91,7.14,5,.39,14.26-.1,17.11-5.08a23,23,0,0,0,.15-2.84,32.38,32.38,0,0,0-1.86-11.24C104.67,147.12,100.14,143,97.42,140.72Z" />
            <path d="M149.25,153.7h0c-.08.23-.16.46-.23.69C149.09,154.17,149.17,153.94,149.25,153.7Z" />
            <circle cx="194.32" cy="56.21" r="8.27" />
            <circle cx="188.99" cy="87.22" r="7.74" />
            <circle cx="225.28" cy="98.04" r="5.29" />
            <path d="M220.67,130.68a23,23,0,0,1-4.65.47,23.29,23.29,0,0,1-17.9-8.36l-1.08.47a34.31,34.31,0,0,1,5.19,9.18c4.12,10.88,6,27.72.5,38.78-13,.32-42.81,5.63-61.68,10.2-1.16.28-.54.12,0,0h-2.24c-.45-1.32-1.47-5.77-1.29-19.08.24-18.06,9.58-37.44,25.38-46a31,31,0,0,1-5.38-35.68,23.3,23.3,0,0,1-14.13-24c-1.86-.19-3.76-.35-5.72-.47L141,51.45l-30.69,5.4a99.63,99.63,0,0,0-25.53,6.34c-6.91-7.85-23.1-20.8-55.41-22.3,0,0-7,38.55,10.91,73.33-5.11,15-7.56,29.83-11.42,40.14-.07.18-.13.35-.2.52-.13.35-.27.7-.41,1s-.19.46-.28.68l-.3.68c-.13.3-.27.6-.41.89s-.15.32-.23.47c-.16.34-.33.67-.51,1l-.21.38c-.18.34-.37.66-.56,1l-.18.29-.6.92-.15.2c-.2.3-.42.58-.64.85a1.33,1.33,0,0,1-.14.19c-.24.29-.48.56-.73.83l-.17.18c-.25.26-.5.51-.77.75l-.17.15c-.27.23-.55.46-.83.67a.71.71,0,0,0-.14.1c-.31.21-.61.42-.93.61h0c-.33.19-.65.35-1,.51l-.08,0c-.38.16-.72.29-1.06.4l-.19.06c-.34.11-.7.2-1.06.28l-.19,0c-.37.07-.75.13-1.14.17h-.15c-.42,0-.84.06-1.28.06h0c.09.21.19.41.28.62v0C29.65,202.39,80.19,227,128,227h0c47.8,0,98.28-24.56,113.58-57.91l.16-.09.05,0h0l0,0,.08,0c0-.19,0-.38,0-.58C229,168.32,226,151.4,220.67,130.68ZM53.9,132.44c6-15.86,22.05-24.81,38.11-16.8,16.55,8.25,26.39,28.17,26.63,46.7.18,13.31-.84,17.76-1.29,19.08h-2.24c.5.12,1.12.28,0,0-18.87-4.57-48.72-9.88-61.68-10.2C47.9,160.16,49.77,143.32,53.9,132.44ZM142,191.73l-4.28,2.19a15,15,0,0,1-19.3,0l-4.28-2.19c-2.42-2-1-4.59,2.17-4.59h23.52C143,187.14,144.42,189.7,142,191.73Z" />
          </svg>
        `;
        break;
      case 'generic':
      default:
        button.innerHTML = `
          <svg slot="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1080 1080">
            <path d="m793.47,318.72c0,19.65-15.93,35.58-35.58,35.58s-33.58-15.93-33.58-35.58,14.93-35.58,34.58-35.58,34.58,15.93,34.58,35.58Z"/>
            <path d="m785.16,120.41c16.16,27.99,12.86,58.96-14.01,74.47-26.87,15.51-62.2,11.32-78.36-16.67-16.16-27.99-13.41-64.88,13.46-80.4s62.75-5.4,78.91,22.6Z"/>
            <path d="m1043.43,346.47c-7.41,21.67-31.28,35.46-52.08,28.35s-31.64-30.45-24.23-52.11c7.41-21.67,30.28-35.46,51.07-28.35,20.8,7.11,32.64,30.45,25.23,52.11Z"/>
            <path d="m964.32,545.91c-.76-14.23-2.12-28.29-4.04-42.17-1.26-9.06-11.33-13.96-19.16-9.24-10,6.03-21.72,9.5-34.25,9.5-29.69,0-54.83-19.46-63.36-46.32-2.23-7.01-9.55-10.97-16.65-9.05-.05.01-.1.03-.14.04-17.21,5.61-35.64,8.5-54.8,8.16-89.99-1.62-162.95-75.26-163.79-165.25-.16-17.24,2.3-33.88,7-49.56,2.32-7.75-2.64-15.73-10.59-17.25-44.38-8.46-77.92-47.46-77.92-94.29v-.14c0-7.5-6.38-13.44-13.86-12.87-219.15,16.78-394.36,223.9-394.36,428.43,0,237.22,189.37,433.54,422.96,433.54,210.43,0,435.18-205.3,422.96-433.54Zm-661.54,78.52c-25.16,0-45.55-18.39-45.55-43.55s20.39-45.55,45.55-45.55,49.55,20.39,49.55,45.55-24.4,43.55-49.55,43.55Zm50.55-303.43c-12.74,0-23.07-10.33-23.07-23.07s10.33-23.07,23.07-23.07,23.07,10.33,23.07,23.07-10.33,23.07-23.07,23.07Zm124.8,447.93c-12.33,0-22.32-9.99-22.32-22.32s9.99-22.32,22.32-22.32,22.32,9.99,22.32,22.32-9.99,22.32-22.32,22.32Zm6.68-232.69c-54.51,0-79.27-36.04-79.27-79.27s36.04-78.27,79.27-78.27,82.27,35.04,82.27,78.27-28.74,79.27-82.27,79.27Zm281.37,186.05c-44.77,0-64.6-29.69-61.52-63.53,3.25-35.76,32.51-61.52,61.52-61.52,33.98,0,61.52,27.55,61.52,61.52s-27.55,63.53-61.52,63.53Z"/>
          </svg>
        `;
        break;
    }

    document.body.appendChild(button);

    // Automatically hide it when the consent modal becomes visible
    const intersectionObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // Consent modal is visible, hide floating button
          hideFloatingButton();
        }
      });
    });

    const observeConsentModal = () => {
      const consentModal = document.querySelector('#cm');

      if (!consentModal) {
        return;
      }

      intersectionObserver.observe(consentModal);
    };

    // Automatically hide it when a settings modal is created
    const mutationObserver = new MutationObserver(() => {
      if (document.querySelector('#cm')) {
        observeConsentModal();
      }
    });

    // Start observing the entire document, waiting for the #cm element to be appended to the DOM
    mutationObserver.observe(document, { childList: true, subtree: true });

    // Also, try to observe the element immediately, in case it already exists in the DOM
    observeConsentModal();
  };

  const updateConsentRecords = async (cookie) => {
    if (!recordConsents) {
      return;
    }

    const { rest_url: restUrl, consent_route: route } = details.api;

    try {
      await fetch(`${restUrl}${route}`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({
          'Content-Type': 'application/json;charset=UTF-8',
        }),
        body: JSON.stringify({
          consent_date: cookie.consent_date,
          uuid: cookie.consent_uuid,
          url: window.location.href,
          user_agent: window.navigator.userAgent,
          necessary_consent: cookie.level.includes('necessary'),
          analytics_consent: cookie.level.includes('analytics'),
          targeting_consent: cookie.level.includes('targeting'),
          preferences_consent: cookie.level.includes('preferences'),
        }),
      });
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error(error);
    }
  };

  /**
   * Check if Google Tag is enabled and GCM's implementation is `gtag`.
   *
   * @return {boolean}
   */
  const isGoogleTag = () => (
    gcm.enabled && gcm.implementation === 'gtag'
      && 'gtag' in window && typeof window.gtag === 'function'
  );

  /**
   * Return the GCM consent states based on the given accepted cookie categories.
   *
   * @param {string[]} categories Accepted cookie categories.
   *
   * @return {{
   *  ad_storage: string,
   *  ad_user_data: string,
   *  security_storage: string,
   *  functionality_storage: string,
   *  personalization_storage: string,
   *  ad_personalization: string,
   *  analytics_storage: string
   * }}
   */
  const getConsentStatesByCategories = (categories = []) => {
    const consentStates = { ...denied };

    // Map cookie categories to GCM consent types
    const mapCategoryToType = {
      necessary: ['functionality_storage', 'security_storage'],
      preferences: ['personalization_storage'],
      analytics: ['analytics_storage'],
      targeting: ['ad_storage', 'ad_user_data', 'ad_personalization'],
    };

    categories.forEach((category) => {
      mapCategoryToType[category].forEach((consentType) => {
        consentStates[consentType] = 'granted';
      });
    });

    return consentStates;
  };

  /**
   * Update the GCM consent state via gtag.js based on the given accepted cookie categories.
   *
   * @param {string[]} categories Accepted cookie categories.
   *
   * @return {void}
   */
  const updateGTag = (categories = []) => {
    if (!isGoogleTag()) {
      // No Google Tag, bail early
      return;
    }

    if (!Array.isArray(categories) || categories.length === 0) {
      return;
    }

    const consentModeStates = getConsentStatesByCategories(categories);

    gtag('consent', 'update', consentModeStates);
  };

  /**
   * Update the GCM consent state via GTM based on the given accepted cookie categories.
   *
   * If there are no consent listeners set in `window.pressidiumConsentListeners`,
   * this function will do nothing.
   *
   * @param {string[]} categories Accepted cookie categories.
   *
   * @return {void}
   */
  const updateGTM = (categories = []) => {
    if (!Array.isArray(categories) || categories.length === 0) {
      return;
    }

    window.pressidiumConsentListeners = window.pressidiumConsentListeners || [];
    window.pressidiumConsentListeners.forEach((consentListener) => {
      const consentModeStates = getConsentStatesByCategories(categories);
      consentListener(consentModeStates);
    });
  };

  /**
   * User has accepted the cookie consent.
   *
   * This function will be executed:
   *
   * - At the first moment that consent is given
   * - After every page load, if consent ("accept" or "reject" action) has already been given
   *
   * @param {object} cookie Current value of the cookie.
   *
   * @return {Promise<void>}
   */
  const onAccept = async (cookie) => {
    await updateConsentRecords(cookie);

    if ('categories' in cookie && gcm.enabled) {
      updateGTag(cookie.categories);
      updateGTM(cookie.categories);
    }

    showFloatingButton();

    // Fire custom event for developers to extend the functionality
    const event = new CustomEvent(
      'pressidium-cookie-consent-accepted',
      { detail: { cookie } },
    );
    window.dispatchEvent(event);
  };

  /**
   * User has changed their consent.
   *
   * This function will be executed (only if consent has already been given):
   *
   * - When the user changes their preferences (accepts/rejects a cookie category)
   *
   * @param {object} cookie            Current value of the cookie.
   * @param {array}  changedCategories Array of categories whose state
   *                                   (accepted/rejected) just changed.
   *
   * @return {Promise<void>}
   */
  const onChange = async (cookie, changedCategories) => {
    await updateConsentRecords(cookie);

    if ('categories' in cookie && gcm.enabled) {
      updateGTag(cookie.categories);
      updateGTM(cookie.categories);
    }

    showFloatingButton();

    // Fire custom event for developers to extend the functionality
    const event = new CustomEvent(
      'pressidium-cookie-consent-changed',
      { detail: { cookie, changedCategories } },
    );
    window.dispatchEvent(event);
  };

  const isEmptyCategory = (block) => (
    'toggle' in block && (!('cookie_table' in block) || block.cookie_table.length === 0)
  );

  if (hideEmptyCategories) {
    Object.entries(settings.languages)
      .forEach(([language, languageSettings]) => {
        settings.languages[language].settings_modal.blocks = languageSettings
          .settings_modal
          .blocks
          .filter((block) => !isEmptyCategory(block));
      });
  }

  window.pressidiumCookieConsent = initCookieConsent();
  window.pressidiumCookieConsent.run({
    ...settings,
    onAccept,
    onChange,
  });

  initFloatingButton(floatingButton);

  // Expose global `pressidiumFloatingButton` object
  window.pressidiumFloatingButton = {
    init: initFloatingButton,
    show: showFloatingButton,
    hide: hideFloatingButton,
  };

  /*
   * Since consent mode doesn't save consent choices,
   * we need to update the consent status accordingly
   * on every page load.
   */
  const alreadyAcceptedCategories = window.pressidiumCookieConsent.get('categories');
  updateGTag(alreadyAcceptedCategories);

  /*
   * Make sure the buttons have the `.has-background` and `.has-text-color`
   * classes, so their colors won't be overridden by the theme.
   */
  document
    .querySelectorAll('#cc--main button')
    .forEach((button) => {
      button.classList.add('has-background', 'has-text-color');
    });

  if (details && details['additional_options'] && details['additional_options']['show_close_icon'] === true) {
    var consent_modal_inner_inner = document.getElementById('c-inr');
    if(!consent_modal_inner_inner) {return;}
  
    var consent_modal_header = document.createElement('div');
    consent_modal_header.id = 'c-hdr';
  
    var consent_modal_title = document.getElementById('c-ttl');
    if(consent_modal_title){
      consent_modal_header.appendChild(consent_modal_title);
    }
  
    var consent_modal_close_btn = document.createElement('button');
    consent_modal_close_btn.setAttribute('type', 'button');
    consent_modal_close_btn.id = 'c-c-bn';
    consent_modal_close_btn.className = 'c-bn';
    // Necessary?
    // consent_modal_close_btn.appendChild(generateFocusSpan(2));
  
    // act as secondary btn for now ( i.e. accept necessary only); TODO: if/when issue 110 will be solved, make the close btn issue the reject action
    consent_modal_close_btn.addEventListener('click', function(){
      // First option: click() the secondary button
      // Even if buttons are swapped then the button is #c-s-bn.
      // Drawback: when #c-s-bn button is condifigured to open settings, our close icon should still close the dialog. So maybe it's not enough to blindly click() it.
      // document.getElementById('c-s-bn').click()
      // 
      // Second option: do the very minimum the #c-s-bn hide() function does
      document.getElementById('cm').setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove("show--consent");
    });

    var consent_modal_close_btn_container = document.createElement('div');
    consent_modal_close_btn_container.id = 'c-c-bnc';
    consent_modal_close_btn_container.appendChild(consent_modal_close_btn);
    consent_modal_header.appendChild(consent_modal_close_btn_container);
  
    consent_modal_inner_inner.insertBefore(consent_modal_header, consent_modal_inner_inner.firstChild);
  }
})(pressidiumCCClientDetails, window.gtag);
