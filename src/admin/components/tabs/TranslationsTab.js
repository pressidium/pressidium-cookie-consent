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

  const languages = useMemo(() => Object.keys(state.languages), [state.languages]);
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

      const originalStrings = state.languages[primaryLanguageCode];
      const objectToTranslate = {
        consent_modal: {
          title: originalStrings.consent_modal.title,
          description: originalStrings.consent_modal.description,
          primary_btn: {
            text: originalStrings.consent_modal.primary_btn.text,
          },
          secondary_btn: {
            text: originalStrings.consent_modal.secondary_btn.text,
          },
        },
        settings_modal: {
          ...originalStrings.settings_modal,
          blocks: [
            {
              ...originalStrings.settings_modal.blocks[0],
            },
            {
              title: originalStrings.settings_modal.blocks[1].title,
              description: originalStrings.settings_modal.blocks[1].description,
            },
            {
              title: originalStrings.settings_modal.blocks[2].title,
              description: originalStrings.settings_modal.blocks[2].description,
            },
            {
              title: originalStrings.settings_modal.blocks[3].title,
              description: originalStrings.settings_modal.blocks[3].description,
            },
            {
              title: originalStrings.settings_modal.blocks[4].title,
              description: originalStrings.settings_modal.blocks[4].description,
            },
            {
              title: originalStrings.settings_modal.blocks[5].title,
              description: originalStrings.settings_modal.blocks[5].description,
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
      type: ActionTypes.UPDATE_GENERAL_SETTING,
      payload: {
        key: 'auto_language',
        value: strategy,
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

  const onPrimaryButtonTextChange = useCallback((value) => {
    dispatch({
      type: ActionTypes.UPDATE_PRIMARY_BUTTON_TEXT,
      payload: {
        language: selectedLanguage,
        value,
      },
    });
  }, [selectedLanguage]);

  const onSecondaryButtonTextChange = useCallback((value) => {
    dispatch({
      type: ActionTypes.UPDATE_SECONDARY_BUTTON_TEXT,
      payload: {
        language: selectedLanguage,
        value,
      },
    });
  }, [selectedLanguage]);

  const onSettingsModalLanguageSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_SETTINGS_MODAL_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onCookieTableHeadersChange = useCallback((index, key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_COOKIE_TABLE_HEADERS_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        index,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onBlockLanguageSettingChange = useCallback((index, key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_SETTINGS_MODAL_BLOCK_LANGUAGE_SETTING,
      payload: {
        language: selectedLanguage,
        index,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  return (
    <Wrapper>
      <Flex align="flex-start" gap={4}>
        <FlexItem>
          <Flex direction="column" gap={4}>
            <FlexItem>
              <RadioControl
                label={__('Language auto-detection strategy', 'pressidium-cookie-consent')}
                help={state.auto_language === 'browser'
                  ? __('Read the user\'s browser language', 'pressidium-cookie-consent')
                  : __('Read value from <html lang="..."> of current page', 'pressidium-cookie-consent')}
                selected={state.auto_language}
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
                        state.languages[primaryLanguageCode].consent_modal.title,
                        selectedLanguage,
                        (translation) => {
                          onConsentModalLanguageSettingChange('title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].consent_modal.title}
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
                        state.languages[primaryLanguageCode].consent_modal.description,
                        selectedLanguage,
                        (translation) => {
                          onConsentModalLanguageSettingChange('description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].consent_modal.description}
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
                        state.languages[primaryLanguageCode].consent_modal.primary_btn.text,
                        selectedLanguage,
                        (translation) => {
                          onPrimaryButtonTextChange(translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Primary button"
                      value={state.languages[selectedLanguage].consent_modal.primary_btn.text}
                      onChange={(value) => onPrimaryButtonTextChange(value)}
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
                        state.languages[primaryLanguageCode].consent_modal.secondary_btn.text,
                        selectedLanguage,
                        (translation) => {
                          onSecondaryButtonTextChange(translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Secondary button"
                      value={state.languages[selectedLanguage].consent_modal.secondary_btn.text}
                      onChange={(value) => onSecondaryButtonTextChange(value)}
                    />
                  </AIControlWrapper>
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Settings modal', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <AIControlWrapper
                    label={__('AI Translate', 'pressidium-cookie-consent')}
                    openSettings={openAIConfigModal}
                    isGenerating={isGenerating}
                    generate={() => {
                      translate(
                        state.languages[primaryLanguageCode].settings_modal.title,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].settings_modal.title}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[0].title,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(0, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie usage heading"
                      value={state.languages[selectedLanguage].settings_modal.blocks[0].title}
                      onChange={(value) => onBlockLanguageSettingChange(0, 'title', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[0].description,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(0, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].settings_modal.blocks[0].description}
                      onChange={(value) => onBlockLanguageSettingChange(0, 'description', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.save_settings_btn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('save_settings_btn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Save settings button"
                      value={state.languages[selectedLanguage].settings_modal.save_settings_btn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('save_settings_btn', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.accept_all_btn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('accept_all_btn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Accept all button"
                      value={state.languages[selectedLanguage].settings_modal.accept_all_btn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('accept_all_btn', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.reject_all_btn,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('reject_all_btn', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Reject all button"
                      value={state.languages[selectedLanguage].settings_modal.reject_all_btn}
                      onChange={(value) => onSettingsModalLanguageSettingChange('reject_all_btn', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.close_btn_label,
                        selectedLanguage,
                        (translation) => {
                          onSettingsModalLanguageSettingChange('close_btn_label', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Close button"
                      value={state.languages[selectedLanguage].settings_modal.close_btn_label}
                      onChange={(value) => onSettingsModalLanguageSettingChange('close_btn_label', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.cookie_table_headers[0].name,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange(0, 'name', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie name header"
                      value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[0].name}
                      onChange={(value) => onCookieTableHeadersChange(0, 'name', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.cookie_table_headers[1].domain,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange(1, 'domain', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie domain header"
                      value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[1].domain}
                      onChange={(value) => onCookieTableHeadersChange(1, 'domain', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.cookie_table_headers[2].expiration,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange(2, 'expiration', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie expiration header"
                      value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[2].expiration}
                      onChange={(value) => onCookieTableHeadersChange(2, 'expiration', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.cookie_table_headers[3].path,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange(3, 'path', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie path header"
                      value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[3].path}
                      onChange={(value) => onCookieTableHeadersChange(3, 'path', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.cookie_table_headers[4].description,
                        selectedLanguage,
                        (translation) => {
                          onCookieTableHeadersChange(4, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Cookie description header"
                      value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[4].description}
                      onChange={(value) => onCookieTableHeadersChange(4, 'description', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[1].title,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(1, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].settings_modal.blocks[1].title}
                      onChange={(value) => onBlockLanguageSettingChange(1, 'title', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[1].description,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(1, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].settings_modal.blocks[1].description}
                      onChange={(value) => onBlockLanguageSettingChange(1, 'description', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[2].title,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(2, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].settings_modal.blocks[2].title}
                      onChange={(value) => onBlockLanguageSettingChange(2, 'title', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[2].description,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(2, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].settings_modal.blocks[2].description}
                      onChange={(value) => onBlockLanguageSettingChange(2, 'description', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[3].title,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(3, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].settings_modal.blocks[3].title}
                      onChange={(value) => onBlockLanguageSettingChange(3, 'title', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[3].description,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(3, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].settings_modal.blocks[3].description}
                      onChange={(value) => onBlockLanguageSettingChange(3, 'description', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[4].title,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(4, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].settings_modal.blocks[4].title}
                      onChange={(value) => onBlockLanguageSettingChange(4, 'title', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[4].description,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(4, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].settings_modal.blocks[4].description}
                      onChange={(value) => onBlockLanguageSettingChange(4, 'description', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[5].title,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(5, 'title', translation);
                        },
                      );
                    }}
                  >
                    <TextControl
                      label="Title"
                      value={state.languages[selectedLanguage].settings_modal.blocks[5].title}
                      onChange={(value) => onBlockLanguageSettingChange(5, 'title', value)}
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
                        state.languages[primaryLanguageCode].settings_modal.blocks[5].description,
                        selectedLanguage,
                        (translation) => {
                          onBlockLanguageSettingChange(5, 'description', translation);
                        },
                      );
                    }}
                  >
                    <TextareaControl
                      label="Description"
                      value={state.languages[selectedLanguage].settings_modal.blocks[5].description}
                      onChange={(value) => onBlockLanguageSettingChange(5, 'description', value)}
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
