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
