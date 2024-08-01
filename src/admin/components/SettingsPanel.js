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
import { removeElement, delay } from '../utils';

import Panel from './Panel';
import Footer from './Footer';

import GeneralTab from './tabs/GeneralTab';
import CookiesTab from './tabs/CookiesTab';
import TranslationsTab from './tabs/TranslationsTab';
import ModalsTab from './tabs/ModalsTab';
import FloatingButtonTab from './tabs/FloatingButtonTab';
import ConsentModeTab from './tabs/ConsentModeTab';
import BlockedScriptsTab from './tabs/BlockedScriptsTab';
import ConsentRecordsTab from './tabs/ConsentRecordsTab';
import LogsTab from './tabs/LogsTab';
import AboutTab from './tabs/AboutTab';

import SettingsContext from '../store/context';
import * as ActionTypes from '../store/actionTypes';

function SettingsPanel() {
  const [isFetching, setIsFetching] = useState(false);
  const [isExportingCsv, setIsExportingCsv] = useState(false);
  const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
  const [notices, setNotices] = useState([]);
  const [selectedTab, setSelectedTab] = useState('general');
  const [fonts, setFonts] = useState([]);

  const { state, dispatch } = useContext(SettingsContext);

  const appendNotice = useCallback(({ message, status, id = null }) => {
    setNotices((prevNotices) => [
      ...prevNotices,
      {
        id: id || prevNotices.length,
        message,
        status,
      },
    ]);
  }, []);

  const dismissNotice = useCallback((id) => {
    setNotices((prevNotices) => prevNotices.filter((notice) => notice.id !== id));
  }, []);

  const onDismissNotice = useCallback((id) => {
    dismissNotice(id);
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
      type: ActionTypes.SET_SETTINGS,
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
        appendNotice({
          message: __('Settings saved successfully.', 'pressidium-cookie-consent'),
          status: 'success',
        });
      } else {
        appendNotice({
          message: __('Could not save settings.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        appendNotice({
          message: __('Could not pass security check.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      } else {
        appendNotice({
          message: __('Could not save settings.', 'pressidium-cookie-consent'),
          status: 'error',
        });
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
        appendNotice({
          message: __('Settings reset successfully.', 'pressidium-cookie-consent'),
          status: 'success',
        });
      } else {
        appendNotice({
          message: __('Could not reset settings.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        appendNotice({
          message: __('Could not pass security check.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      } else {
        appendNotice({
          message: __('Could not reset settings.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      }
    }

    try {
      const data = await fetchSettings();

      dispatch({
        type: ActionTypes.SET_SETTINGS,
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
        appendNotice({
          message: __('All consent records were cleared successfully.', 'pressidium-cookie-consent'),
          status: 'success',
        });
      } else {
        appendNotice({
          message: __('Could not clear records.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        appendNotice({
          message: __('Could not pass security check.', 'pressidium-cookie-consent'),
          status: 'error',
        });
      } else {
        appendNotice({
          message: __('Could not clear records.', 'pressidium-cookie-consent'),
          status: 'error',
        });
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

  const eraseConsentCookies = () => {
    const cookieName = window.pressidiumCookieConsent.getConfig('cookie_name');
    window.pressidiumCookieConsent.eraseCookies(cookieName);
  };

  const resetPreview = (customSettings = {}) => {
    // Re-create the style element
    const styleElement = document.querySelector('#pressidium-cc-styles');

    if (styleElement) {
      let css = '';

      if (ccSettings.pressidium_options.font.slug !== 'default') {
        css += `--cc-font-family: ${ccSettings.pressidium_options.font.family};\n`;
      }

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

    // Remove existing consent element(s)
    removeElement(document.querySelector('#cc--main'));

    // Re-initialize cookie consent
    window.pressidiumCookieConsent = window.initCookieConsent();
    window.pressidiumCookieConsent.run({
      ...ccSettings,
      ...customSettings,
      onAccept: () => window.pressidiumFloatingButton.show(),
      onChange: () => window.pressidiumFloatingButton.show(),
    });

    // Re-initialize floating button
    window.pressidiumFloatingButton.init(ccSettings.pressidium_options.floating_button);
  };

  const previewConsentModal = () => {
    eraseConsentCookies();
    resetPreview();

    // Force show consent modal
    window.pressidiumCookieConsent.show();
  };

  const previewSettingsModal = () => {
    eraseConsentCookies();
    resetPreview({ autorun: false });

    // Show settings modal
    window.pressidiumCookieConsent.showSettings();
  };

  const previewFloatingButton = async () => {
    resetPreview();

    window.pressidiumFloatingButton.hide();

    /*
     * The recreated floating button is hidden by default.
     *
     * We use `delay()` which is a Promise-based version of `setTimeout()`
     * to show the floating button after a `0` ms delay. This is necessary
     * to ensure that the floating button is shown with the CSS transition.
     *
     * If we show the floating button immediately after hiding it, the
     * transition will not be applied and the button will appear instantly.
     */
    await delay(0);

    window.pressidiumFloatingButton.show();
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
      appendNotice({
        message: __('Could not export settings.', 'pressidium-cookie-consent'),
        status: 'error',
      });
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
        type: ActionTypes.SET_SETTINGS,
        payload: parsedData,
      });
    } catch (error) {
      console.error('Could not import settings', error);
      appendNotice({
        message: error.message,
        status: 'error',
      });
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
      appendNotice({
        message: __('Could not export consent records.', 'pressidium-cookie-consent'),
        status: 'error',
      });
      setIsExportingCsv(false);
      return;
    }

    const contentType = response.headers.get('Content-Type');

    if (!contentType.toLowerCase().startsWith('text/csv')) {
      // Failed to fetch logs, bail early
      // eslint-disable-next-line no-console
      console.error('Invalid content type while exporting CSV', contentType);
      appendNotice({
        message: __('Could not export consent records.', 'pressidium-cookie-consent'),
        status: 'error',
      });
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
    const {
      cookie_table: cookieTable,
      hide_empty_categories: hideEmptyCategories,
      gcm,
    } = state.pressidium_options;

    const noCookiesListed = ['necessary', 'analytics', 'targeting', 'preferences']
      .every((category) => cookieTable[category].length === 0);

    const shouldShowNotice = hideEmptyCategories && gcm.enabled && noCookiesListed;

    const noticeId = 'empty-categories-no-cookies-gcm-warning';
    const noticeExists = notices.find(({ id }) => id === noticeId);

    if (shouldShowNotice && !noticeExists) {
      appendNotice({
        message: __('Empty categories are hidden, and no cookies are listed, which might lead to issues with Google Consent Mode.', 'pressidium-cookie-consent'),
        status: 'warning',
        id: noticeId,
      });
    } else if (!shouldShowNotice && noticeExists) {
      dismissNotice(noticeId);
    }
  }, [state.pressidium_options]);

  useEffect(() => {
    (async () => {
      try {
        const data = await apiFetch({
          path: '/wp/v2/font-families',
          method: 'GET',
        });

        if (Array.isArray(data) && data.length > 0) {
          setFonts([
            {
              name: 'Default',
              slug: 'default',
              family: 'inherit',
            },
            ...data
              .map(({ font_family_settings: settings }) => ({
                name: settings.name,
                slug: settings.slug,
                family: settings.fontFamily,
              }))
              .toSorted((a, b) => a.name.localeCompare(b.name)),
          ]);
        }
      } catch (error) {
        console.error(error.message);
        console.warn('Could not fetch installed fonts (maybe running on WordPress < 6.5?)');
      }
    })();
  }, []);

  useEffect(() => {
    (async () => {
      setIsFetching(true);

      try {
        const data = await fetchSettings();

        dispatch({
          type: ActionTypes.SET_SETTINGS,
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
      {notices.map(({ message, status, id }) => (
        <Notice
          onRemove={() => onDismissNotice(id)}
          status={status}
        >
          {message}
        </Notice>
      ))}
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
              name: 'modals',
              title: __('Modals', 'pressidium-cookie-consent'),
              className: 'tab-modals',
              Component: ModalsTab,
            },
            {
              name: 'floating-button',
              title: __('Floating Button', 'pressidium-cookie-consent'),
              className: 'tab-floating-button',
              Component: FloatingButtonTab,
            },
            {
              name: 'consent-mode',
              title: __('Consent Mode', 'pressidium-cookie-consent'),
              className: 'tab-consent-mode',
              Component: ConsentModeTab,
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
            },
            {
              name: 'logs',
              title: __('Logs', 'pressidium-cookie-consent'),
              className: 'tab-logs',
              Component: LogsTab,
            },
            {
              name: 'about',
              title: __('About', 'pressidium-cookie-consent'),
              className: 'tab-about',
              Component: AboutTab,
            },
          ]}
        >
          {({ Component }) => {
            const componentPropsMap = {
              general: {
                fonts,
              },
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
          previewFloatingButton={previewFloatingButton}
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
