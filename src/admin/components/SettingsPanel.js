import {
  useState,
  useEffect,
  useContext,
  useMemo,
  useCallback,
} from '@wordpress/element';
import { TabPanel, Spinner, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
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
import ConsentRecordsTab from './tabs/ConsentRecordsTab';
import LogsTab from './tabs/LogsTab';

import SettingsContext from '../store/context';

function SettingsPanel() {
  const [isFetching, setIsFetching] = useState(false);
  const [isExportingCsv, setIsExportingCsv] = useState(false);
  const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
  const [noticeStatus, setNoticeStatus] = useState('info');
  const [noticeMessage, setNoticeMessage] = useState(null);
  const [selectedTab, setSelectedTab] = useState('general');

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

    const response = await apiFetch(options);

    if (!('success' in response) || !response.success || !('data' in response)) {
      // Failed to fetch settings, bail early
      // eslint-disable-next-line no-console
      console.error('Error fetching settings', response);
      throw new Error('Invalid response while fetching settings');
    }

    const { data } = response;

    return data;
  };

  const validateState = () => {
    let cleanState = { ...state };

    const blockedScripts = state?.pressidium_options?.blocked_scripts;

    if (Array.isArray(blockedScripts) && blockedScripts.length > 0) {
      cleanState = {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          blocked_scripts: blockedScripts.filter(({ src }) => src && src.trim().length > 0),
        },
      };
    }

    dispatch({
      type: 'SET_SETTINGS',
      payload: cleanState,
    });
  };

  const saveSettings = async (data) => {
    const { route, nonce } = pressidiumCCAdminDetails.api;

    validateState();

    const options = {
      path: route,
      method: 'POST',
      data: {
        settings: data,
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

  const resetSettings = async () => {
    const { route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: route,
      method: 'DELETE',
      data: {
        nonce,
      },
    };

    try {
      const response = await apiFetch(options);

      if ('success' in response && response.success) {
        setNoticeStatus('success');
        setNoticeMessage(__('Settings reset successfully.', 'pressidium-cookie-consent'));
      } else {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not reset settings.', 'pressidium-cookie-consent'));
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not pass security check.', 'pressidium-cookie-consent'));
      } else {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not reset settings.', 'pressidium-cookie-consent'));
      }
    }

    try {
      const data = await fetchSettings();

      dispatch({
        type: 'SET_SETTINGS',
        payload: data,
      });
    } catch (error) {
      console.error('Could not reload default settings', error);
    }
  };

  const clearRecords = async () => {
    const { consents_route: route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: route,
      method: 'DELETE',
      data: {
        nonce,
      },
    };

    try {
      const response = await apiFetch(options);

      if ('success' in response && response.success) {
        setNoticeStatus('success');
        setNoticeMessage(__('All consent records were cleared successfully.', 'pressidium-cookie-consent'));
      } else {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not clear records.', 'pressidium-cookie-consent'));
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not pass security check.', 'pressidium-cookie-consent'));
      } else {
        setNoticeStatus('error');
        setNoticeMessage(__('Could not clear records.', 'pressidium-cookie-consent'));
      }
    }
  };

  const ccSettings = useMemo(() => {
    const settings = { ...state };

    const analyticsTable = settings.pressidium_options.cookie_table.analytics;
    const targetingTable = settings.pressidium_options.cookie_table.targeting;
    const preferencesTable = settings.pressidium_options.cookie_table.preferences;

    const primaryBtnRole = settings.pressidium_options.primary_btn_role;
    const secondaryBtnRole = settings.pressidium_options.secondary_btn_role;

    Object.keys(settings.languages).forEach((language) => {
      settings.languages[language].settings_modal.blocks[2].cookie_table = analyticsTable;
      settings.languages[language].settings_modal.blocks[3].cookie_table = targetingTable;
      settings.languages[language].settings_modal.blocks[4].cookie_table = preferencesTable;

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

  const getCurrentTimestamp = () => {
    const date = new Date();

    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');

    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const seconds = date.getSeconds().toString().padStart(2, '0');

    return `${year}-${month}-${day}_${hours}-${minutes}-${seconds}`;
  };

  const downloadJsonFile = (data) => {
    const blobType = 'text/json;charset=utf-8';
    const blob = new Blob([JSON.stringify(data)], { type: blobType });
    const url = URL.createObjectURL(blob);

    const currentTimestamp = getCurrentTimestamp();
    const filename = `pressidium-cookie-consent-settings_${currentTimestamp}.json`;

    const anchor = document.createElement('a');
    anchor.setAttribute('href', url);
    anchor.setAttribute('download', filename);
    anchor.click();
    anchor.remove();

    URL.revokeObjectURL(url);
  };

  const downloadCsvFile = async (response) => {
    const blob = await response.blob();
    const url = URL.createObjectURL(blob);

    const filename = response.headers.get('Content-Disposition').split('filename=')[1];

    const anchor = document.createElement('a');
    anchor.setAttribute('href', url);
    anchor.setAttribute('download', filename);
    anchor.click();
    anchor.remove();
  };

  const exportSettings = async () => {
    try {
      const data = await fetchSettings();
      downloadJsonFile(data);
    } catch (error) {
      setNoticeStatus('error');
      setNoticeMessage(__('Could not export settings.', 'pressidium-cookie-consent'));
    }
  };

  const importSettings = async (files) => {
    try {
      if (files.length === 0) {
        throw new Error(__('No files selected', 'pressidium-cookie-consent'));
      }

      const [file] = files;

      const data = await file.text();
      const parsedData = JSON.parse(data);

      await saveSettings(parsedData);

      dispatch({
        type: 'SET_SETTINGS',
        payload: parsedData,
      });
    } catch (error) {
      console.error('Could not import settings', error);
      setNoticeStatus('error');
      setNoticeMessage(__('Could not import settings.', 'pressidium-cookie-consent'));
    }
  };

  const exportConsentRecords = async () => {
    const { export_route: route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: addQueryArgs(route, { nonce }),
      method: 'GET',
      parse: false,
    };

    setIsExportingCsv(true);

    const response = await apiFetch(options);

    if (response.status !== 200) {
      // Failed to fetch logs, bail early
      // eslint-disable-next-line no-console
      console.error('Error exporting CSV', response);
      setNoticeStatus('error');
      setNoticeMessage(__('Could not export consent records.', 'pressidium-cookie-consent'));
      setIsExportingCsv(false);
      return;
    }

    const contentType = response.headers.get('Content-Type');

    if (!contentType.toLowerCase().startsWith('text/csv')) {
      // Failed to fetch logs, bail early
      // eslint-disable-next-line no-console
      console.error('Invalid content type while exporting CSV', contentType);
      setNoticeStatus('error');
      setNoticeMessage(__('Could not export consent records.', 'pressidium-cookie-consent'));
      setIsExportingCsv(false);
      return;
    }

    await downloadCsvFile(response);

    setIsExportingCsv(false);
  };

  useBeforeunload(hasUnsavedChanges ? (e) => {
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
  } : null);

  const prevState = usePrevious(state);

  useEffect(() => {
    if (prevState && !isFetching) {
      setHasUnsavedChanges(true);
    }
  }, [state]);

  useEffect(() => {
    (async () => {
      setIsFetching(true);

      try {
        const data = await fetchSettings();

        dispatch({
          type: 'SET_SETTINGS',
          payload: data,
        });
      } catch (error) {
        console.error('Could not fetch settings', error);
      }

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
          onSelect={(tabName) => setSelectedTab(tabName)}
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
            {
              name: 'consent-records',
              title: __('Consent Records', 'pressidium-cookie-consent'),
              className: 'tab-consent-records',
              Component: ConsentRecordsTab,
              foo: 'bar',
            },
            {
              name: 'logs',
              title: __('Logs', 'pressidium-cookie-consent'),
              className: 'tab-logs',
              Component: LogsTab,
            },
          ]}
        >
          {({ Component }) => {
            const componentPropsMap = {
              'consent-records': {
                isExportingCsv,
                exportConsentRecords,
                clearRecords,
              },
            };

            const props = selectedTab in componentPropsMap
              ? componentPropsMap[selectedTab]
              : {};

            return (
              // eslint-disable-next-line react/jsx-props-no-spreading
              <Component {...props} />
            );
          }}
        </TabPanel>
        <Footer
          save={() => saveSettings(state)}
          previewConsentModal={previewConsentModal}
          previewSettingsModal={previewSettingsModal}
          hasUnsavedChanges={hasUnsavedChanges}
          exportSettings={exportSettings}
          importSettings={importSettings}
          resetSettings={resetSettings}
        />
      </Panel>
    </>
  );
}

export default SettingsPanel;
