import apiFetch from '@wordpress/api-fetch';

import './lib/cookieconsent';

import './scss/main.scss';

((details) => {
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

  const { settings, record_consents: recordConsents } = details;

  const updateConsentRecords = async (cookie) => {
    if (!recordConsents) {
      return;
    }

    try {
      await apiFetch({
        path: details.api.consent_route,
        method: 'POST',
        data: {
          consent_date: cookie.consent_date,
          uuid: cookie.consent_uuid,
          url: window.location.href,
          user_agent: window.navigator.userAgent,
          necessary_consent: cookie.level.includes('necessary'),
          analytics_consent: cookie.level.includes('analytics'),
          targeting_consent: cookie.level.includes('targeting'),
        },
      });
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error(error);
    }
  };

  window.pressidiumCookieConsent = initCookieConsent();
  window.pressidiumCookieConsent.run({
    ...settings,
    onAccept: updateConsentRecords,
    onChange: updateConsentRecords,
  });

  /*
   * Make sure the buttons have the `.has-background` and `.has-text-color`
   * classes, so their colors won't be overridden by the theme.
   */
  document
    .querySelectorAll('#cc--main button')
    .forEach((button) => {
      button.classList.add('has-background', 'has-text-color');
    });
})(pressidiumCCClientDetails);
