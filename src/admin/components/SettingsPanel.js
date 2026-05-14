import {
  useState,
  useEffect,
  useContext,
  useMemo,
  useCallback,
  createInterpolateElement,
} from '@wordpress/element';
import {
  TabPanel,
  Flex,
  FlexItem,
  Spinner,
  Panel as WPPanel,
  PanelHeader,
  PanelBody,
  PanelRow,
  Button,
  Notice,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import {
  help as HelpIcon,
  people as PeopleIcon,
  starFilled as StarIcon,
} from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { useBeforeunload } from 'react-beforeunload';

import {
  pressidium as PressidiumIcon,
  performance as PerformanceIcon,
} from './icons';

import { usePrevious } from '../hooks';
import { delay, deepCopy } from '../utils';

import Panel from './Panel';
import Footer from './Footer';
import Badge from './Badge';
import Emoji from './Emoji';

import GeneralTab from './tabs/GeneralTab';
import CookiesTab from './tabs/CookiesTab';
import TranslationsTab from './tabs/TranslationsTab';
import ModalsTab from './tabs/ModalsTab';
import FloatingButtonTab from './tabs/FloatingButtonTab';
import ConsentModeTab from './tabs/ConsentModeTab';
import TagGatewayTab from './tabs/TagGatewayTab';
import BlockedScriptsTab from './tabs/BlockedScriptsTab';
import ConsentRecordsTab from './tabs/ConsentRecordsTab';
import LogsTab from './tabs/LogsTab';
import AboutTab from './tabs/AboutTab';

import SettingsContext from '../store/context';
import * as ActionTypes from '../store/actionTypes';
import AIConfigModal from './AIConfigModal';

function SettingsPanel() {
  const { performanceBanner } = pressidiumCCAdminDetails.assets;
  const { performance_plugin_search: performancePluginSearch = null } = pressidiumCCAdminDetails.urls;

  const [isFetching, setIsFetching] = useState(false);
  const [isExportingCsv, setIsExportingCsv] = useState(false);
  const [isAIConfigModalOpen, setIsAIConfigModalOpen] = useState(false);
  const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
  const [notices, setNotices] = useState([]);
  const [selectedTab, setSelectedTab] = useState('general');
  const [fonts, setFonts] = useState([]);

  const { state, dispatch } = useContext(SettingsContext);

  const urls = {
    docs: 'https://github.com/pressidium/pressidium-cookie-consent/wiki',
    review: 'https://wordpress.org/support/plugin/pressidium-cookie-consent/reviews/#new-post',
    github: 'https://github.com/pressidium/pressidium-cookie-consent/blob/master/CONTRIBUTING.md',
    pressidium: 'https://pressidium.com/free-trial/?utm_source=pccplugin&utm_medium=metabox&utm_campaign=wpplugins',
    performancePlugin: 'https://pressidium.com/open-source/performance-plugin/?utm_source=pccplugin&utm_medium=metabox&utm_campaign=wpplugins',
  };

  urls.performancePluginSearch = performancePluginSearch ?? urls.performancePlugin;

  const emojis = {
    rocket: <>&#128640;</>,
  };

  const appendNotice = useCallback(({ message, status, id = null }) => {
    setNotices((prevNotices) => {
      const noticeExists = prevNotices.find((notice) => notice.id === id);

      if (id !== null && noticeExists) {
        // Notice already exists, do not append twice
        return prevNotices;
      }

      return [
        ...prevNotices,
        {
          id: id || prevNotices.length,
          message,
          status,
        },
      ];
    });
  }, []);

  const dismissNotice = useCallback((id) => {
    setNotices((prevNotices) => prevNotices.filter((notice) => notice.id !== id));
  }, []);

  const onDismissNotice = useCallback((id) => {
    dismissNotice(id);
  }, []);

  const openAIConfigModal = () => setIsAIConfigModalOpen(true);
  const closeAIConfigModal = () => setIsAIConfigModalOpen(false);

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

    const blockedScripts = state?.pressidiumOptions?.blockedScripts;

    if (Array.isArray(blockedScripts) && blockedScripts.length > 0) {
      cleanState = {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          blockedScripts: blockedScripts.filter(({ src }) => src && src.trim().length > 0),
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
          id: 'settings-saved-success',
        });
      } else {
        appendNotice({
          message: __('Could not save settings.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'settings-not-saved-error',
        });
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        appendNotice({
          message: __('Could not pass security check.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'failed-security-check-error',
        });
      } else {
        appendNotice({
          message: __('Could not save settings.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'settings-not-saved-error',
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
          id: 'settings-reset-success',
        });
      } else {
        appendNotice({
          message: __('Could not reset settings.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'settings-not-reset-error',
        });
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        appendNotice({
          message: __('Could not pass security check.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'failed-security-check-error',
        });
      } else {
        appendNotice({
          message: __('Could not reset settings.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'settings-not-reset-error',
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
          id: 'consent-records-cleared-success',
        });
      } else {
        appendNotice({
          message: __('Could not clear records.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'consent-records-not-cleared-error',
        });
      }
    } catch (error) {
      if ('code' in error && error.code === 'invalid_nonce') {
        appendNotice({
          message: __('Could not pass security check.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'failed-security-check-error',
        });
      } else {
        appendNotice({
          message: __('Could not clear records.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'consent-records-not-cleared-error',
        });
      }
    }
  };

  const ccSettings = useMemo(() => {
    const settings = { ...state };

    const necessaryTable = settings.pressidiumOptions.cookieTable.necessary;
    const analyticsTable = settings.pressidiumOptions.cookieTable.analytics;
    const targetingTable = settings.pressidiumOptions.cookieTable.targeting;
    const preferencesTable = settings.pressidiumOptions.cookieTable.preferences;

    const showCloseIcon = settings.pressidiumOptions.consentModalCloseIcon ?? true;
    const showFooter = settings.pressidiumOptions.showConsentModalFooter ?? true;

    Object.keys(settings.language.translations).forEach((language) => {
      settings.language
        .translations[language].preferencesModal.sections[1].cookieTable = necessaryTable;
      settings.language
        .translations[language].preferencesModal.sections[2].cookieTable = analyticsTable;
      settings.language
        .translations[language].preferencesModal.sections[3].cookieTable = targetingTable;
      settings.language
        .translations[language].preferencesModal.sections[4].cookieTable = preferencesTable;

      settings.language.translations[language].consentModal.closeIconLabel = showCloseIcon ? 'Close' : null;

      const footerLinks = settings.language.translations[language].consentModal.footerLinks ?? [];
      settings.language.translations[language].consentModal.footer = showFooter
        ? footerLinks
          .filter(({ url, label }) => url.trim().length > 0 && label.trim().length > 0)
          .map(({ url, label }) => `<a href="${url}">${label}</a>`)
          .join('')
        : '';
    });

    return settings;
  }, [state]);

  const resetPreview = async (customSettings = {}) => {
    // Re-create the style element
    const styleElement = document.querySelector('#pressidium-cc-styles');

    if (styleElement) {
      let css = '';

      if (ccSettings.pressidiumOptions.font.slug !== 'default') {
        css += `--cc-font-family: ${ccSettings.pressidiumOptions.font.family};\n`;
      }

      Object.keys(ccSettings.pressidiumOptions.colors).forEach((key) => {
        const value = ccSettings.pressidiumOptions.colors[key];
        css += `--cc-${key}: ${value};\n`;
      });

      styleElement.innerHTML = `
        .pressidium-cc-theme {
          ${css}
        }
      `;
    }

    // Re-initialize cookie consent
    const config = deepCopy({
      ...ccSettings,
      ...customSettings,
      onConsent: () => window.pressidiumFloatingButton.show(),
      onChange: () => window.pressidiumFloatingButton.show(),
    });

    if (ccSettings.pressidiumOptions.hideEmptyCategories) {
      Object.keys(ccSettings.language.translations).forEach((language) => {
        config.language.translations[language].preferencesModal.sections = ccSettings
          .language.translations[language]
          .preferencesModal
          .sections
          .filter((section) => !('cookieTable' in section) || section.cookieTable.length > 0);
      });
    }

    const {
      pressidiumOptions,
      shouldDeleteCookie = false,
      ...cookieConsentConfig
    } = config;

    window.pressidiumCookieConsent.reset(shouldDeleteCookie);
    await window.pressidiumCookieConsent.run(cookieConsentConfig);

    // Re-initialize floating button
    window.pressidiumFloatingButton.init(ccSettings.pressidiumOptions.floatingButton);
  };

  const previewConsentModal = async () => {
    await resetPreview({ shouldDeleteCookie: true });

    // Force show consent modal
    window.pressidiumCookieConsent.show();
  };

  const previewSettingsModal = async () => {
    await resetPreview({ autoShow: false, shouldDeleteCookie: true });

    // Show preferences modal
    window.pressidiumCookieConsent.showPreferences();
  };

  const previewFloatingButton = async () => {
    await resetPreview({ shouldDeleteCookie: false });

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
        id: 'settings-not-exported-error',
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

      const newData = await fetchSettings();

      dispatch({
        type: ActionTypes.SET_SETTINGS,
        payload: newData,
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
        id: 'consent-records-not-exported-error',
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
        id: 'consent-records-not-exported-error',
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

  const handleConditionalNotice = (shouldShowNotice, id, message) => {
    const noticeExists = notices.find(({ id: noticeId }) => noticeId === id);

    if (shouldShowNotice && !noticeExists) {
      appendNotice({
        message,
        status: 'warning',
        id,
      });
    } else if (!shouldShowNotice && noticeExists) {
      dismissNotice(id);
    }
  };

  useEffect(() => {
    const {
      cookieTable,
      hideEmptyCategories,
      gcm,
    } = state.pressidiumOptions;

    const noCookiesListed = ['necessary', 'analytics', 'targeting', 'preferences']
      .every((category) => cookieTable[category].length === 0);

    const shouldShowNotice = hideEmptyCategories && gcm.enabled && noCookiesListed;

    handleConditionalNotice(
      shouldShowNotice,
      'empty-categories-no-cookies-gcm-warning',
      __('Empty categories are hidden, and no cookies are listed, which might lead to issues with Google Consent Mode.', 'pressidium-cookie-consent')
    );
  }, [state.pressidiumOptions]);

  useEffect(() => {
    const isCookiePathValid = state.cookie.path && state.cookie.path.length > 0;

    handleConditionalNotice(
      !isCookiePathValid,
      'cookie-path-warning',
      __('The cookie path is not set. This may cause cookies to be set incorrectly.', 'pressidium-cookie-consent'),
    );
  }, [state.cookie.path]);

  useEffect(() => {
    const actualDomain = pressidiumCCAdminDetails.domain || window.location.hostname;

    handleConditionalNotice(
      actualDomain !== state.cookie.domain,
      'cookie-domain-warning',
      __('The cookie domain is not set to the actual domain. This may cause cookies to be set incorrectly.', 'pressidium-cookie-consent'),
    );
  }, [state.cookie.domain]);

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

      <Flex justify="flex-start" align="flex-start">
        <FlexItem>
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
                  className: 'tab-cookies-list',
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
                  name: 'tag-gateway',
                  title: __('Tag Gateway', 'pressidium-cookie-consent'),
                  className: 'tab-tag-gateway',
                  Component: TagGatewayTab,
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
                  cookies: {
                    openAIConfigModal,
                    appendNotice,
                  },
                  translations: {
                    openAIConfigModal,
                    appendNotice,
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
        </FlexItem>
        <FlexItem
          className="pressidium-hide-on-xl"
          style={{ maxWidth: '300px' }}
        >
          <Flex direction="column">
            <FlexItem>
              <WPPanel>
                <PanelHeader>
                  {__('Need help?', 'pressidium-cookie-consent')}
                </PanelHeader>
                <PanelBody>
                  <PanelRow>
                    {__('Browse our step-by-step documentation to set up, customize, and make the most of the plugin.', 'pressidium-cookie-consent')}
                  </PanelRow>
                  <PanelRow>
                    <Button
                      icon={HelpIcon}
                      href={urls.docs}
                      target="_blank"
                      variant="secondary"
                    >
                      {__('Read Documentation', 'pressidium-cookie-consent')}
                    </Button>
                  </PanelRow>
                </PanelBody>
              </WPPanel>
            </FlexItem>
            <FlexItem>
              <WPPanel>
                <PanelHeader>
                  {__('Enjoying the plugin?', 'pressidium-cookie-consent')}
                </PanelHeader>
                <PanelBody>
                  <PanelRow>
                    {__('Share the love! Drop a positive review, keep us smiling and help others find their new favorite plugin!', 'pressidium-cookie-consent')}
                  </PanelRow>
                  <PanelRow>
                    <Button
                      icon={StarIcon}
                      href={urls.review}
                      target="_blank"
                      variant="secondary"
                    >
                      {__('Leave a Review', 'pressidium-cookie-consent')}
                    </Button>
                  </PanelRow>
                </PanelBody>
              </WPPanel>
            </FlexItem>
            <FlexItem>
              <WPPanel>
                <PanelHeader>
                  {__('Shape the future', 'pressidium-cookie-consent')}
                </PanelHeader>
                <PanelBody>
                  <PanelRow>
                    {__('Report issues, suggest improvements, or contribute code. Every bit of feedback helps us grow and improve.', 'pressidium-cookie-consent')}
                  </PanelRow>
                  <PanelRow>
                    <Button
                      icon={PeopleIcon}
                      href={urls.github}
                      target="_blank"
                      variant="secondary"
                    >
                      {__('Contribute on GitHub', 'pressidium-cookie-consent')}
                    </Button>
                  </PanelRow>
                </PanelBody>
              </WPPanel>
            </FlexItem>
            <FlexItem>
              <WPPanel>
                <PanelHeader>
                  <Flex justify="flex-start">
                    <FlexItem>
                      <Badge
                        value={__('New', 'pressidium-cookie-consent')}
                        status="success"
                        style={{
                          padding: '0 0.7em',
                          lineHeight: '2.1em',
                          backgroundColor: '#0f9200',
                          color: '#ffffff',
                        }}
                      />
                    </FlexItem>
                    <FlexItem>
                      {__('Pressidium Performance', 'pressidium-cookie-consent')}
                    </FlexItem>
                    <FlexItem>
                      <Emoji symbol={emojis.rocket} />
                    </FlexItem>
                  </Flex>
                </PanelHeader>
                <PanelBody>
                  <PanelRow>
                    <a
                      href={urls.performancePlugin}
                      target="_blank"
                      rel="noreferrer noopener"
                    >
                      <img
                        src={performanceBanner}
                        alt={__('Learn more about the Pressidium Performance plugin', 'pressidium-cookie-consent')}
                        style={{ width: '100%' }}
                      />
                    </a>
                  </PanelRow>
                  <PanelRow>
                    <span>
                      {
                        createInterpolateElement(
                          __('Boost your website in minutes with the <a>Pressidium Performance plugin</a>.', 'pressidium-cookie-consent'),
                          {
                            a: (
                              // eslint-disable-next-line max-len
                              // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                              <a
                                href={urls.performancePluginSearch}
                                target="_blank"
                                rel="noreferrer noopener"
                              />
                            ),
                          },
                        )
                      }
                    </span>
                  </PanelRow>
                  <PanelRow>
                    {__('Optimize images, minify CSS & JavaScript, and increase page speed, without a complicated setup.', 'pressidium-cookie-consent')}
                  </PanelRow>
                  <PanelRow>
                    <Button
                      icon={PerformanceIcon}
                      href={urls.performancePlugin}
                      target="_blank"
                      variant="secondary"
                    >
                      {__('Learn more', 'pressidium-cookie-consent')}
                    </Button>
                  </PanelRow>
                </PanelBody>
              </WPPanel>
            </FlexItem>
            <FlexItem>
              <WPPanel>
                <PanelHeader>
                  {__('Built by Pressidium®', 'pressidium-cookie-consent')}
                </PanelHeader>
                <PanelBody>
                  <PanelRow>
                    {__('Managed hosting for WordPress optimized for performance, security, and scalability.', 'pressidium-cookie-consent')}
                  </PanelRow>
                  <PanelRow>
                    {__('Go Further. Go Faster. Go EDGE ⚡', 'pressidium-cookie-consent')}
                  </PanelRow>
                  <PanelRow>
                    <span style={{ fontWeight: 600 }}>
                      {__('Enjoy 14-days of superior hosting for free!', 'pressidium-cookie-consent')}
                    </span>
                  </PanelRow>
                  <PanelRow>
                    <Button
                      icon={PressidiumIcon}
                      href={urls.pressidium}
                      target="_blank"
                      variant="secondary"
                    >
                      {__('Start your Free Trial', 'pressidium-cookie-consent')}
                    </Button>
                  </PanelRow>
                </PanelBody>
              </WPPanel>
            </FlexItem>
          </Flex>
        </FlexItem>
      </Flex>

      <AIConfigModal
        isOpen={isAIConfigModalOpen}
        onClose={closeAIConfigModal}
        appendNotice={appendNotice}
        dismissNotice={dismissNotice}
      />
    </>
  );
}

export default SettingsPanel;
