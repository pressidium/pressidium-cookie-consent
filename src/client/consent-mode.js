/**
 * Google tag implementation.
 *
 * @param {object} gcm Google Consent Mode (GCM) configuration object.
 *
 * @return {void}
 */
const gtagImplementation = (gcm) => {
  window.dataLayer = window.dataLayer || [];

  // eslint-disable-next-line func-names
  window.gtag = function () {
    // eslint-disable-next-line prefer-rest-params
    window.dataLayer.push(arguments);
  };

  const { gtag } = window;

  const denied = {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    functionality_storage: 'denied',
    personalization_storage: 'denied',
    security_storage: 'denied',
  };

  /**
   * Convert the given consent state to the `gtag` format.
   *
   * @param {boolean} value The consent state as a boolean.
   *
   * @return {string} Either 'granted' or 'denied'.
   */
  const convertToGtagConsentState = (value) => (value ? 'granted' : 'denied');

  // Set the default consent state for each region
  gcm.regions.forEach((region) => {
    const subdivisions = 'subdivisions' in region ? region.subdivisions : [];
    const regionCodes = subdivisions.length > 0 ? subdivisions : [region.country];

    const regionDefaultConsentStates = {
      ...denied,
      region: regionCodes,
    };

    Object
      .keys(region.default_consent_states)
      .forEach((consentType) => {
        regionDefaultConsentStates[consentType] = convertToGtagConsentState(
          region.default_consent_states[consentType],
        );
      });

    gtag('consent', 'default', {
      ...regionDefaultConsentStates,
      wait_for_update: 500,
      region: regionCodes,
    });
  });

  /*
   * Set the default consent state for all regions. Most specific
   * parameter takes precedence. So, if a region is defined,
   * it will take precedence over the global default.
   */
  gtag('consent', 'default', {
    ...denied,
    wait_for_update: 500,
  });

  if (gcm.url_passthrough) {
    gtag('set', 'url_passthrough', true);
  }

  if (gcm.ads_data_redaction) {
    gtag('set', 'ads_data_redaction', true);
  }
};

/**
 * Google Tag Manager (GTM) implementation.
 *
 * @return {void}
 */
const gtmImplementation = () => {
  window.pressidiumConsentListeners = window.pressidiumConsentListeners || [];

  window.addPressidiumGCMConsentListener = (consentListener) => {
    window.pressidiumConsentListeners.push(consentListener);
  };
};

((pressidiumCCGCM = {}) => {
  if (!('gcm' in pressidiumCCGCM)) {
    return;
  }

  const { gcm } = pressidiumCCGCM;

  if (!('enabled' in gcm) || !gcm.enabled) {
    // GCM is not enabled, bail early
    return;
  }

  if (gcm.implementation === 'gtag') {
    gtagImplementation(gcm);
    return;
  }

  if (gcm.implementation === 'gtm') {
    gtmImplementation();
  }
})(window.pressidiumCCGCM);
