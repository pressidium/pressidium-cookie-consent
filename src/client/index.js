import './lib/cookieconsent';

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

  const { settings, gcm, record_consents: recordConsents } = details;

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

    // Fire custom event for developers to extend the functionality
    const event = new CustomEvent(
      'pressidium-cookie-consent-changed',
      { detail: { cookie, changedCategories } },
    );
    window.dispatchEvent(event);
  };

  window.pressidiumCookieConsent = initCookieConsent();
  window.pressidiumCookieConsent.run({
    ...settings,
    onAccept,
    onChange,
  });

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
})(pressidiumCCClientDetails, window.gtag);
