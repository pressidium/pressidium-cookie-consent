/**
 * Integrate with the WP Consent API.
 *
 * {@link https://github.com/WordPress/wp-consent-level-api}
 * {@link https://wordpress.org/plugins/wp-consent-api/}
 * {@link https://github.com/pressidium/pressidium-cookie-consent/wiki/Control-programmatically}
 */
(() => {
  // Map Pressidium Cookie Consent categories to WP Consent API categories
  const categoryMapping = {
    analytics: ['statistics', 'statistics-anonymous'],
    targeting: ['marketing'],
    preferences: ['preferences'],
  };

  // Pressidium Cookie Consent categories
  const categories = Object.keys(categoryMapping);

  /**
   * Either the user has accepted or changed their consent.
   *
   * @return {void}
   */
  const onConsentUpdate = () => {
    if (!('wp_set_consent' in window)) {
      // WP Consent API is not available, bail early
      return;
    }

    /*
     * Get the `wp_set_consent()` function from the global scope,
     * or provide a no-op function if it doesn't exist.
     */
    const { wp_set_consent: wpSetConsent = (category, value) => {} } = window;

    // Always allow functional (i.e. necessary) cookies
    wpSetConsent('functional', 'allow');

    // Iterate over each Pressidium Cookie Consent category
    categories.forEach((category) => {
      if (!(category in categoryMapping)) {
        // No WP Consent API categories for this Pressidium Cookie Consent category, skip
        return;
      }

      // Check if the category is allowed or denied
      const value = pressidiumCookieConsent.allowedCategory(category) ? 'allow' : 'deny';

      // Map Pressidium Cookie Consent categories to WP Consent API categories
      const wpConsentApiCategories = categoryMapping[category];

      /*
       * Set the consent value for each WP Consent API category.
       *
       * We need a loop here because a single Pressidium Cookie Consent
       * category may map to multiple WP Consent API categories.
       *
       * For example, `analytics` maps to `statistics` and `statistics-anonymous`.
       */
      wpConsentApiCategories.forEach((wpConsentApiCategory) => {
        wpSetConsent(wpConsentApiCategory, value);
      });
    });
  };

  // Set consent type and dispatch event
  window.wp_consent_type = 'optin';

  const event = new CustomEvent('wp_consent_type_defined');
  document.dispatchEvent(event);

  // Listen for Pressidium Cookie Consent events and update the WP Consent API
  window.addEventListener('pressidium-cookie-consent-accepted', onConsentUpdate);
  window.addEventListener('pressidium-cookie-consent-changed', onConsentUpdate);
})();
