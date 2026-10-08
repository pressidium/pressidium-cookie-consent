import {
  useState,
  useContext,
  useMemo,
  useCallback,
} from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  Button,
  RadioControl,
  TextControl,
  TextareaControl,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import {
  plus as PlusIcon,
  language as TranslateIcon,
  cog as CogIcon,
} from '@wordpress/icons';

import SettingsContext from '../../store/context';
import * as ActionTypes from '../../store/actionTypes';

import { useAI } from '../../hooks/ai';
import { nameByLanguageCode } from '../Languages';

import Wrapper from '../Wrapper';
import TranslationsTable from '../TranslationsTable';
import NewLanguageModal from '../NewLanguageModal';
import AIControlWrapper from '../AIControlWrapper';

function TranslationsTab(props) {
  const { openAIConfigModal, appendNotice } = props;

  const { state, dispatch } = useContext(SettingsContext);

  const [isNewLanguageModalOpen, setIsNewLanguageModalOpen] = useState(false);
  const [selectedLanguage, setSelectedLanguage] = useState('');
  const [isTranslatingAll, setIsTranslatingAll] = useState(false);

  const { isGenerating, generateTranslation, generateTranslationForObject } = useAI();

  const formattedSelectedLanguageCode = useMemo(
    () => selectedLanguage.toUpperCase(),
    [selectedLanguage],
  );

  const openNewLanguageModal = useCallback(() => setIsNewLanguageModalOpen(true), []);
  const closeNewLanguageModal = useCallback(() => setIsNewLanguageModalOpen(false), []);

  const languages = useMemo(
    () => Object.keys(state.language.translations),
    [state.language.translations],
  );

  const primaryLanguageCode = useMemo(
    () => (languages.length > 0 ? languages[0] : null),
    [languages],
  );

  const translate = useCallback((text, langCode, callback) => {
    (async () => {
      const response = await generateTranslation(text, nameByLanguageCode(langCode));

      if (!response.success) {
        // eslint-disable-next-line no-console
        console.error(response.error);

        appendNotice({
          message: __('Translation failed. Double-check your AI settings — the integration may not be fully configured.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'ai-text-not-translated-error',
        });
      }

      callback(response.translation);
    })();
  }, []);

  const translateAll = useCallback((langCode) => {
    (async () => {
      setIsTranslatingAll(true);

      const originalStrings = state.language.translations[primaryLanguageCode];
      const objectToTranslate = {
        consentModal: {
          title: originalStrings.consentModal.title,
          description: originalStrings.consentModal.description,
          acceptAllBtn: originalStrings.consentModal.acceptAllBtn,
          acceptNecessaryBtn: originalStrings.consentModal.acceptNecessaryBtn,
          showPreferencesBtn: originalStrings.consentModal.showPreferencesBtn,
        },
        preferencesModal: {
          ...originalStrings.preferencesModal,
          sections: [
            {
              ...originalStrings.preferencesModal.sections[0],
            },
            {
              title: originalStrings.preferencesModal.sections[1].title,
              description: originalStrings.preferencesModal.sections[1].description,
            },
            {
              title: originalStrings.preferencesModal.sections[2].title,
              description: originalStrings.preferencesModal.sections[2].description,
            },
            {
              title: originalStrings.preferencesModal.sections[3].title,
              description: originalStrings.preferencesModal.sections[3].description,
            },
            {
              title: originalStrings.preferencesModal.sections[4].title,
              description: originalStrings.preferencesModal.sections[4].description,
            },
            {
              title: originalStrings.preferencesModal.sections[5].title,
              description: originalStrings.preferencesModal.sections[5].description,
            },
          ],
        },
      };

      const response = await generateTranslationForObject(
        objectToTranslate,
        nameByLanguageCode(langCode),
      );

      if (!response.success) {
        // eslint-disable-next-line no-console
        console.error(response.error);

        appendNotice({
          message: __('Translation failed. Double-check your AI settings — the integration may not be fully configured.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'ai-text-not-translated-error',
        });

        setIsTranslatingAll(false);
        return;
      }

      try {
        const translatedObject = JSON.parse(response.translation);

        // eslint-disable-next-line max-len
        translatedObject.settings_modal.blocks = translatedObject.settings_modal.blocks.map((block, index) => {
          if (Object.hasOwn(originalStrings.settings_modal.blocks[index], 'toggle')) {
            return {
              ...block,
              toggle: originalStrings.settings_modal.blocks[index].toggle,
            };
          }

          return block;
        });

        dispatch({
          type: ActionTypes.UPDATE_ENTIRE_LANGUAGE,
          payload: {
            language: langCode,
            translation: translatedObject,
          },
        });
      } catch (error) {
        appendNotice({
          message: __('Translation failed. AI provided an invalid translation. Please try again or choose a different model.', 'pressidium-cookie-consent'),
          status: 'error',
          id: 'ai-text-not-translated-error',
        });
      } finally {
        setIsTranslatingAll(false);
      }
    })();
  }, [state, primaryLanguageCode]);

  const onAddLanguage = useCallback((language) => {
    dispatch({
      type: ActionTypes.ADD_LANGUAGE,
      payload: {
        language,
      },
    });
  }, []);

  const onDeleteLanguage = useCallback((language) => {
    setSelectedLanguage('');

    dispatch({
      type: ActionTypes.DELETE_LANGUAGE,
      payload: {
        language,
      },
    });
  }, []);

  const onAutoDetectionStrategyChange = useCallback((strategy) => {
    dispatch({
      type: ActionTypes.UPDATE_LANGUAGE_AUTO_DETECT_SETTING,
      payload: {
        strategy,
      },
    });
  }, []);

  const onConsentModalLanguageSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_CONSENT_MODAL_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onFooterLinkFieldChange = useCallback((index, field, value) => {
    const currentLinks = state.language.translations[selectedLanguage]?.consentModal?.footerLinks
      ?? [{ url: '', label: '' }, { url: '', label: '' }];

    const updated = currentLinks.map((link, i) => (
      i === index ? { ...link, [field]: value } : link
    ));

    onConsentModalLanguageSettingChange('footerLinks', updated);
  }, [selectedLanguage, state.language.translations, onConsentModalLanguageSettingChange]);

  const onSettingsModalLanguageSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_PREFERENCES_MODAL_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onSectionLanguageSettingChange = useCallback((index, key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_PREFERENCES_MODAL_BLOCK_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        index,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onCookieTableHeadersChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_COOKIE_TABLE_HEADERS_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        key,
        value,
      },
    });
  });

  return (
    <Wrapper>
      <Flex align="flex-start" gap={4}>
        <FlexItem>
          <Flex direction="column" gap={4}>
            <FlexItem>
              <RadioControl
                label={__('Language auto-detection strategy', 'pressidium-cookie-consent')}
                help={state.language.autoDetect === 'browser'
                  ? __('Read the user\'s browser language', 'pressidium-cookie-consent')
                  : __('Read value from <html lang="..."> of current page', 'pressidium-cookie-consent')}
                selected={state.language.autoDetect}
                options={[
                  { label: __('Browser', 'pressidium-cookie-consent'), value: 'browser' },
                  { label: __('document', 'pressidium-cookie-consent'), value: 'document' },
                ]}
                onChange={(value) => onAutoDetectionStrategyChange(value)}
              />
            </FlexItem>
            <FlexItem>
              <TranslationsTable
                languages={languages}
                onEdit={setSelectedLanguage}
                onDelete={onDeleteLanguage}
              />
            </FlexItem>
            <FlexItem>
              <Button
                icon={PlusIcon}
                onClick={openNewLanguageModal}
                style={{ paddingRight: '10px' }}
                isPrimary
              >
                {__('New Language', 'pressidium-cookie-consent')}
              </Button>
              <NewLanguageModal
                isOpen={isNewLanguageModalOpen}
                onClose={closeNewLanguageModal}
                addLanguage={onAddLanguage}
              />
            </FlexItem>
          </Flex>
        </FlexItem>
        <FlexItem style={{ flex: '1 1 0px' }}>
          {selectedLanguage && (
            <Panel className={isTranslatingAll ? 'is-translating-all' : ''}>
              <PanelBody initialOpen>
                <PanelRow>
                  <Flex justify="flex-start">
                    <FlexItem>
                      <Button
                        variant="primary"
                        icon={TranslateIcon}
                        onClick={() => translateAll(selectedLanguage)}
                        showTooltip
                        label={sprintf(
                          // translators: %s: Language name.
                          __('Translate all strings to %s using AI', 'pressidium-cookie-consent'),
                          nameByLanguageCode(selectedLanguage),
                        )}
                        style={{ paddingRight: '10px' }}
                      >
                        {__('AI Translation', 'pressidium-cookie-consent')}
                      </Button>
                    </FlexItem>
                    <FlexItem>
                      <Button
                        variant="secondary"
                        icon={CogIcon}
                        onClick={openAIConfigModal}
                        style={{ paddingRight: '10px' }}
                      >
                        {__('AI Settings', 'pressidium-cookie-consent')}
                      </Button>
                    </FlexItem>
                  </Flex>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Consent modal', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.title,
                        selectedLanguage,
                        (translation) => {
                          onConsentModalLanguageSettingChange('title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.title}
                      onChange={(value) => onConsentModalLanguageSettingChange('title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.description,
                        selectedLanguage,
                        (translation) => {
                          onConsentModalLanguageSettingChange('description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.description}
                      onChange={(value) => onConsentModalLanguageSettingChange('description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.acceptAllBtn,
                        selectedLanguage,
                        (translation) => {
                          onConsentModalLanguageSettingChange('acceptAllBtn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Accept all button', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.acceptAllBtn}
                      onChange={(value) => onConsentModalLanguageSettingChange('acceptAllBtn', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.acceptNecessaryBtn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('acceptNecessaryBtn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Accept necessary button', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.acceptNecessaryBtn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('acceptNecessaryBtn', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.showPreferencesBtn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('showPreferencesBtn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Show preferences button', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.showPreferencesBtn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('showPreferencesBtn', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label={__('Footer link 1 URL', 'pressidium-cookie-consent')}
                    value={state.language.translations[selectedLanguage].consentModal.footerLinks?.[0]?.url ?? ''}
                    onChange={(value) => onFooterLinkFieldChange(0, 'url', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.footerLinks?.[0]?.label ?? '',
                        selectedLanguage,
                        (translation) => {
                          onFooterLinkFieldChange(0, 'label', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Footer link 1 label', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.footerLinks?.[0]?.label ?? ''}
                      onChange={(value) => onFooterLinkFieldChange(0, 'label', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label={__('Footer link 2 URL', 'pressidium-cookie-consent')}
                    value={state.language.translations[selectedLanguage].consentModal.footerLinks?.[1]?.url ?? ''}
                    onChange={(value) => onFooterLinkFieldChange(1, 'url', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].consentModal.footerLinks?.[1]?.label ?? '',
                        selectedLanguage,
                        (translation) => {
                          onFooterLinkFieldChange(1, 'label', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Footer link 2 label', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].consentModal.footerLinks?.[1]?.label ?? ''}
                      onChange={(value) => onFooterLinkFieldChange(1, 'label', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Preferences modal', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.title,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.title}
                      onChange={(value) => onSettingsModalLanguageSettingChange('title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[0].title,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(0, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Cookie usage heading', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[0].title}
                      onChange={(value) => onSectionLanguageSettingChange(0, 'title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[0].description,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(0, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[0].description}
                      onChange={(value) => onSectionLanguageSettingChange(0, 'description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.savePreferencesBtn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('savePreferencesBtn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Save preferences button', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.savePreferencesBtn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('savePreferencesBtn', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.acceptAllBtn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('acceptAllBtn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Accept all button', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.acceptAllBtn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('acceptAllBtn', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.acceptNecessaryBtn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('acceptNecessaryBtn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Accept necessary button', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.acceptNecessaryBtn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('acceptNecessaryBtn', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Cookie table headers', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].name,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange('name', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie name header"
                      value={state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].name}
                      onChange={(value) => onCookieTableHeadersChange('name', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].domain,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange('domain', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie domain header"
                      value={state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].domain}
                      onChange={(value) => onCookieTableHeadersChange('domain', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].expiration,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange('expiration', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie expiration header"
                      value={state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].expiration}
                      onChange={(value) => onCookieTableHeadersChange('expiration', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].path,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange('path', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie path header"
                      value={state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].path}
                      onChange={(value) => onCookieTableHeadersChange('path', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].description,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange('description', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie description header"
                      value={state.pressidiumOptions.cookieTableHeaders.translations[selectedLanguage].description}
                      onChange={(value) => onCookieTableHeadersChange('description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Necessary cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[1].title,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(1, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[1].title}
                      onChange={(value) => onSectionLanguageSettingChange(1, 'title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[1].description,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(1, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[1].description}
                      onChange={(value) => onSectionLanguageSettingChange(1, 'description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Analytics cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[2].title,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(2, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[2].title}
                      onChange={(value) => onSectionLanguageSettingChange(2, 'title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[2].description,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(2, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[2].description}
                      onChange={(value) => onSectionLanguageSettingChange(2, 'description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Targeting cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[3].title,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(3, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[3].title}
                      onChange={(value) => onSectionLanguageSettingChange(3, 'title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[3].description,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(3, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[3].description}
                      onChange={(value) => onSectionLanguageSettingChange(3, 'description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Preferences cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[4].title,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(4, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[4].title}
                      onChange={(value) => onSectionLanguageSettingChange(4, 'title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[4].description,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(4, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[4].description}
                      onChange={(value) => onSectionLanguageSettingChange(4, 'description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('More information block', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[5].title,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(5, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label={__('Title', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[5].title}
                      onChange={(value) => onSectionLanguageSettingChange(5, 'title', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.language.translations[primaryLanguageCode].preferencesModal.sections[5].description,
                        selectedLanguage,
                        (translation) => {
                          onSectionLanguageSettingChange(5, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label={__('Description', 'pressidium-cookie-consent')}
                      value={state.language.translations[selectedLanguage].preferencesModal.sections[5].description}
                      onChange={(value) => onSectionLanguageSettingChange(5, 'description', value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
            </Panel>
          )}
        </FlexItem>
      </Flex>
    </Wrapper>
  );
}

export default TranslationsTab;
