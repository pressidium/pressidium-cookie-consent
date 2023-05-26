import {
  useState,
  useEffect,
  useContext,
  useMemo,
  useCallback,
} from '@wordpress/element';
import { TabPanel, Spinner, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import { useBeforeunload } from 'react-beforeunload';

import { usePrevious } from '../hooks';
import { removeElement } from '../utils';

import Panel from './Panel';
import Footer from './Footer';

import GeneralTab from './tabs/GeneralTab';
import CookiesTab from './tabs/CookiesTab';
import TranslationsTab from './tabs/TranslationsTab';
import ConsentModalTab from './tabs/ConsentModalTab';
import SettingsModalTab from './tabs/SettingsModalTab';
import BlockedScriptsTab from './tabs/BlockedScriptsTab';

import SettingsContext from '../store/context';

function SettingsPanel() {
  const [isFetching, setIsFetching] = useState(false);
  const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
  const [noticeStatus, setNoticeStatus] = useState('info');
  const [noticeMessage, setNoticeMessage] = useState(null);

  const { state, dispatch } = useContext(SettingsContext);

  const dismissNotice = useCallback(() => {
    setNoticeStatus('info');
    setNoticeMessage(null);
  }, []);

  const fetchSettings = async () => {
    const { route } = pressidiumCCAdminDetails.api;

    const options = {
      path: route,
      method: 'GET',
    };

    try {
      const response = await apiFetch(options);

      if (!('success' in response) || !response.success || !('data' in response)) {
        // Failed to fetch settings, bail early
        console.error('Error fetching settings', response);
        return;
      }

      const { data } = response;

      dispatch({
        type: 'SET_SETTINGS',
        payload: data,
      });
    } catch (error) {
      // Unexpected error occurred, bail early
      console.error('Error fetching settings', error);
    }
  };

  const saveSettings = async () => {
    const { route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: route,
      method: 'POST',
      data: {
        settings: state,
        nonce,
      },
    };

    try {
      const response = await apiFetch(options);

      if ('success' in response && response.success) {
        setNoticeStatus('success');
        setNoticeMessage(__('Settings saved successfully.', 'pressidium-cookie-consent'));
      } else {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not save settings.', 'pressidium-cookie-consent'));
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not pass security check.', 'pressidium-cookie-consent'));
      } else {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not save settings.', 'pressidium-cookie-consent'));
      }
    }

    setHasUnsavedChanges(false);
  };

  const ccSettings = useMemo(() => {
    const settings = { ...state };

    const analyticsTable = settings.pressidium_options.cookie_table.analytics;
    const targetingTable = settings.pressidium_options.cookie_table.targeting;

    const primaryBtnRole = settings.pressidium_options.primary_btn_role;
    const secondaryBtnRole = settings.pressidium_options.secondary_btn_role;

    Object.keys(settings.languages).forEach((language) => {
      settings.languages[language].settings_modal.blocks[2].cookie_table = analyticsTable;
      settings.languages[language].settings_modal.blocks[3].cookie_table = targetingTable;

      settings.languages[language].consent_modal.primary_btn.role = primaryBtnRole;
      settings.languages[language].consent_modal.secondary_btn.role = secondaryBtnRole;
    });

    return settings;
  }, [state]);

  const resetPreview = (customSettings = {}) => {
    // Re-create the style element
    const styleElement = document.querySelector('#pressidium-cc-styles');

    if (styleElement) {
      let css = '';

      Object.keys(ccSettings.pressidium_options.colors).forEach((key) => {
        const value = ccSettings.pressidium_options.colors[key];
        css += `--cc-${key}: ${value};\n`;
      });

      styleElement.innerHTML = `
        .pressidium-cc-theme {
          ${css}
        }
      `;
    }

    // Erase consent cookies
    const cookieName = window.pressidiumCookieConsent.getConfig('cookie_name');
    window.pressidiumCookieConsent.eraseCookies(cookieName);

    // Remove existing consent element(s)
    removeElement(document.querySelector('#cc--main'));

    // Re-initialize cookie consent
    window.pressidiumCookieConsent = window.initCookieConsent();
    window.pressidiumCookieConsent.run({
      ...ccSettings,
      ...customSettings,
    });
  };

  const previewConsentModal = () => {
    resetPreview();

    // Force show consent modal
    window.pressidiumCookieConsent.show();
  };

  const previewSettingsModal = () => {
    resetPreview({ autorun: false });

    // Show settings modal
    window.pressidiumCookieConsent.showSettings();
  };

  useBeforeunload((e) => {
    if (!hasUnsavedChanges) {
      return '';
    }

    /*
     * Some browsers used to display the returned string in
     * the confirmation dialog, enabling the event handle to
     * display a custom message to the user. However, this is
     * deprecated and no longer supported in most browsers.
     */
    const customMessage = __(
      'You have unsaved changes. Are you sure you want to leave?',
      'pressidium-cookie-consent',
    );

    e.preventDefault();
    e.returnValue = customMessage;

    return customMessage;
  });

  const prevState = usePrevious(state);

  useEffect(() => {
    if (prevState && !isFetching) {
      setHasUnsavedChanges(true);
    }
  }, [state]);

  useEffect(() => {
    (async () => {
      setIsFetching(true);

      await fetchSettings();

      setIsFetching(false);
    })();
  }, []);

  if (isFetching) {
    return (
      <Spinner />
    );
  }

  return (
    <>
      {noticeMessage && (
        <Notice
          status={noticeStatus}
          onDismiss={dismissNotice}
        >
          {noticeMessage}
        </Notice>
      )}
      <Panel>
        <TabPanel
          className="my-tab-panel"
          activeClass="active-tab"
          tabs={[
            {
              name: 'general',
              title: __('General', 'pressidium-cookie-consent'),
              className: 'tab-general',
              Component: GeneralTab,
            },
            {
              name: 'cookies',
              title: __('Cookies', 'pressidium-cookie-consent'),
              className: 'tab-cookies',
              Component: CookiesTab,
            },
            {
              name: 'translations',
              title: __('Translations', 'pressidium-cookie-consent'),
              className: 'tab-translations',
              Component: TranslationsTab,
            },
            {
              name: 'consent-modal',
              title: __('Consent Modal', 'pressidium-cookie-consent'),
              className: 'tab-consent-modal',
              Component: ConsentModalTab,
            },
            {
              name: 'settings-modal',
              title: __('Settings Modal', 'pressidium-cookie-consent'),
              className: 'tab-settings-modal',
              Component: SettingsModalTab,
            },
            {
              name: 'blocked-scripts',
              title: __('Blocked Scripts', 'pressidium-cookie-consent'),
              className: 'tab-blocked-scripts',
              Component: BlockedScriptsTab,
            },
          ]}
        >
          {({ Component }) => (
            <Component />
          )}
        </TabPanel>
        <Footer
          save={saveSettings}
          previewConsentModal={previewConsentModal}
          previewSettingsModal={previewSettingsModal}
        />
      </Panel>
    </>
  );
}

export default SettingsPanel;
