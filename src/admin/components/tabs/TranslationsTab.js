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
import { __ } from '@wordpress/i18n';
import { plus as PlusIcon } from '@wordpress/icons';

import SettingsContext from '../../store/context';
import Wrapper from '../Wrapper';
import TranslationsTable from '../TranslationsTable';
import NewLanguageModal from '../NewLanguageModal';

function TranslationsTab() {
  const { state, dispatch } = useContext(SettingsContext);

  const [isNewLanguageModalOpen, setIsNewLanguageModalOpen] = useState(false);
  const [selectedLanguage, setSelectedLanguage] = useState('');

  const formattedSelectedLanguageCode = useMemo(
    () => selectedLanguage.toUpperCase(),
    [selectedLanguage],
  );

  const openNewLanguageModal = useCallback(() => setIsNewLanguageModalOpen(true), []);
  const closeNewLanguageModal = useCallback(() => setIsNewLanguageModalOpen(false), []);

  const languages = useMemo(() => Object.keys(state.languages), [state.languages]);

  const onAddLanguage = useCallback((language) => {
    dispatch({
      type: 'ADD_LANGUAGE',
      payload: {
        language,
      },
    });
  }, []);

  const onDeleteLanguage = useCallback((language) => {
    setSelectedLanguage('');

    dispatch({
      type: 'DELETE_LANGUAGE',
      payload: {
        language,
      },
    });
  }, []);

  const onAutoDetectionStrategyChange = useCallback((strategy) => {
    dispatch({
      type: 'UPDATE_GENERAL_SETTING',
      payload: {
        key: 'auto_language',
        value: strategy,
      },
    });
  }, []);

  const onConsentModalLanguageSettingChange = useCallback((key, value) => {
    dispatch({
      type: 'UPDATE_CONSENT_MODAL_LANGUAGE_SETTING',
      payload: {
        language: selectedLanguage,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onPrimaryButtonTextChange = useCallback((value) => {
    dispatch({
      type: 'UPDATE_PRIMARY_BUTTON_TEXT',
      payload: {
        language: selectedLanguage,
        value,
      },
    });
  }, [selectedLanguage]);

  const onSecondaryButtonTextChange = useCallback((value) => {
    dispatch({
      type: 'UPDATE_SECONDARY_BUTTON_TEXT',
      payload: {
        language: selectedLanguage,
        value,
      },
    });
  }, [selectedLanguage]);

  const onSettingsModalLanguageSettingChange = useCallback((key, value) => {
    dispatch({
      type: 'UPDATE_SETTINGS_MODAL_LANGUAGE_SETTING',
      payload: {
        language: selectedLanguage,
        key,
        value,
      },
    });
  }, [selectedLanguage]);

  const onCookieTableHeadersChange = useCallback((index, key, value) => {
    dispatch({
      type: 'UPDATE_COOKIE_TABLE_HEADERS_LANGUAGE_SETTING',
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
      type: 'UPDATE_SETTINGS_MODAL_BLOCK_LANGUAGE_SETTING',
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
            <Panel>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Consent modal', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].consent_modal.title}
                    onChange={(value) => onConsentModalLanguageSettingChange('title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextareaControl
                    label="Description"
                    value={state.languages[selectedLanguage].consent_modal.description}
                    onChange={(value) => onConsentModalLanguageSettingChange('description', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Primary button"
                    value={state.languages[selectedLanguage].consent_modal.primary_btn.text}
                    onChange={(value) => onPrimaryButtonTextChange(value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Secondary button"
                    value={state.languages[selectedLanguage].consent_modal.secondary_btn.text}
                    onChange={(value) => onSecondaryButtonTextChange(value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Settings modal', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].settings_modal.title}
                    onChange={(value) => onSettingsModalLanguageSettingChange('title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Cookie usage heading"
                    value={state.languages[selectedLanguage].settings_modal.blocks[0].title}
                    onChange={(value) => onBlockLanguageSettingChange(0, 'title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Description"
                    value={state.languages[selectedLanguage].settings_modal.blocks[0].description}
                    onChange={(value) => onBlockLanguageSettingChange(0, 'description', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Save settings button"
                    value={state.languages[selectedLanguage].settings_modal.save_settings_btn}
                    onChange={(value) => onSettingsModalLanguageSettingChange('save_settings_btn', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Accept all button"
                    value={state.languages[selectedLanguage].settings_modal.accept_all_btn}
                    onChange={(value) => onSettingsModalLanguageSettingChange('accept_all_btn', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Reject all button"
                    value={state.languages[selectedLanguage].settings_modal.reject_all_btn}
                    onChange={(value) => onSettingsModalLanguageSettingChange('reject_all_btn', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Close button"
                    value={state.languages[selectedLanguage].settings_modal.close_btn_label}
                    onChange={(value) => onSettingsModalLanguageSettingChange('close_btn_label', value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Cookie table headers', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Cookie name header"
                    value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[0].name}
                    onChange={(value) => onCookieTableHeadersChange(0, 'name', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Cookie domain header"
                    value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[1].domain}
                    onChange={(value) => onCookieTableHeadersChange(1, 'domain', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Cookie expiration header"
                    value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[2].expiration}
                    onChange={(value) => onCookieTableHeadersChange(2, 'expiration', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Cookie path header"
                    value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[3].path}
                    onChange={(value) => onCookieTableHeadersChange(3, 'path', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Cookie description header"
                    value={state.languages[selectedLanguage].settings_modal.cookie_table_headers[4].description}
                    onChange={(value) => onCookieTableHeadersChange(4, 'description', value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Necessary cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].settings_modal.blocks[1].title}
                    onChange={(value) => onBlockLanguageSettingChange(1, 'title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Description"
                    value={state.languages[selectedLanguage].settings_modal.blocks[1].description}
                    onChange={(value) => onBlockLanguageSettingChange(1, 'description', value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Analytics cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].settings_modal.blocks[2].title}
                    onChange={(value) => onBlockLanguageSettingChange(2, 'title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Description"
                    value={state.languages[selectedLanguage].settings_modal.blocks[2].description}
                    onChange={(value) => onBlockLanguageSettingChange(2, 'description', value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Targeting cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].settings_modal.blocks[3].title}
                    onChange={(value) => onBlockLanguageSettingChange(3, 'title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Description"
                    value={state.languages[selectedLanguage].settings_modal.blocks[3].description}
                    onChange={(value) => onBlockLanguageSettingChange(3, 'description', value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('Preferences cookies', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].settings_modal.blocks[4].title}
                    onChange={(value) => onBlockLanguageSettingChange(4, 'title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Description"
                    value={state.languages[selectedLanguage].settings_modal.blocks[4].description}
                    onChange={(value) => onBlockLanguageSettingChange(4, 'description', value)}
                  />
                </PanelRow>
              </PanelBody>
              <PanelBody
                title={`(${formattedSelectedLanguageCode}) ${__('More information block', 'pressidium-cookie-consent')}`}
                initialOpen
              >
                <PanelRow>
                  <TextControl
                    label="Title"
                    value={state.languages[selectedLanguage].settings_modal.blocks[5].title}
                    onChange={(value) => onBlockLanguageSettingChange(5, 'title', value)}
                  />
                </PanelRow>
                <PanelRow>
                  <TextControl
                    label="Description"
                    value={state.languages[selectedLanguage].settings_modal.blocks[5].description}
                    onChange={(value) => onBlockLanguageSettingChange(5, 'description', value)}
                  />
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
