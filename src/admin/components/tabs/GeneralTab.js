import {
  useState,
  useContext,
  useCallback,
} from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  TextControl,
  SelectControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
  settings as SettingsIcon,
  color as ColorIcon,
} from '@wordpress/icons';

import SettingsContext from '../../store/context';

import ColorsPanel from '../ColorsPanel';
import themes from '../../themes';

const getThemeByName = (themeName) => {
  const { theme } = themes.find(({ value }) => value === themeName);
  return theme;
};

function GeneralTab() {
  const [selectedTheme, setSelectedTheme] = useState('light');

  const { state, dispatch } = useContext(SettingsContext);

  const onGeneralSettingChange = (key, value) => {
    dispatch({
      type: 'UPDATE_GENERAL_SETTING',
      payload: {
        key,
        value,
      },
    });
  };

  const setColors = useCallback((colors) => {
    dispatch({
      type: 'UPDATE_COLOR_SETTINGS',
      payload: colors,
    });
  }, []);

  const setColor = useCallback((key, value) => {
    dispatch({
      type: 'UPDATE_COLOR_SETTING',
      payload: {
        key,
        value,
      },
    });
  }, []);

  const onThemeChange = (value) => {
    setSelectedTheme(value);
    const theme = getThemeByName(value);
    setColors(theme);
  };

  const onColorChange = (key, value) => {
    setColor(key, value);
  };

  const onPressidiumOptionChange = (key, value) => {
    dispatch({
      type: 'UPDATE_PRESSIDIUM_OPTION',
      payload: {
        key,
        value,
      },
    });
  };

  return (
    <div>
      <Panel>
        <PanelBody
          title={__('Configuration', 'pressidium-cookie-consent')}
          icon={SettingsIcon}
          initialOpen
        >
          <PanelRow>
            <ToggleControl
              label={__('Autorun', 'pressidium-cookie-consent')}
              help={state.autorun
                ? __('Will show the cookie consent as soon as possible', 'pressidium-cookie-consent')
                : __('You will have to manually call the `.show()` method', 'pressidium-cookie-consent')}
              checked={state.autorun}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('autorun', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Force consent', 'pressidium-cookie-consent')}
              help={state.force_consent
                ? __('Page navigation will be blocked until user action', 'pressidium-cookie-consent')
                : __('Users will be able to navigate without needing to consent first', 'pressidium-cookie-consent')}
              checked={state.force_consent}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('force_consent', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Auto-clear cookies', 'pressidium-cookie-consent')}
              help={state.autoclear_cookies
                ? __('Cookies will be deleted automatically when user opts-out of a specific category inside cookie settings', 'pressidium-cookie-consent')
                : __('Cookies will have to be deleted manually', 'pressidium-cookie-consent')}
              checked={state.autoclear_cookies}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('autoclear_cookies', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Page scripts', 'pressidium-cookie-consent')}
              help={state.page_scripts
                ? __('Will manage existing third-party script tags', 'pressidium-cookie-consent')
                : __('Won\'t manage existing third-party script tags', 'pressidium-cookie-consent')}
              checked={state.page_scripts}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('page_scripts', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Hide from bots', 'pressidium-cookie-consent')}
              help={state.hide_from_bots
                ? __('Won\'t run when a bot/crawler/webdriver is detected', 'pressidium-cookie-consent')
                : __('Will always run, even when a bot/crawler/webdriver is detected', 'pressidium-cookie-consent')}
              checked={state.hide_from_bots}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('hide_from_bots', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Re-consent', 'pressidium-cookie-consent')}
              help={state.reconsent
                ? __('Will ask users to “re-consent” when a cookies list changes', 'pressidium-cookie-consent')
                : __('Won\'t ask users for consent more than once', 'pressidium-cookie-consent')}
              checked={state.reconsent}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('reconsent', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Record consents', 'pressidium-cookie-consent')}
              help={state.pressidium_options.record_consents
                ? __('Will record user consents to be able to provide proof of consent for auditing purposes', 'pressidium-cookie-consent')
                : __('Won\'t record any user consents', 'pressidium-cookie-consent')}
              checked={state.pressidium_options.record_consents}
              className="pressidium-toggle-control"
              onChange={(value) => onPressidiumOptionChange('record_consents', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Delay', 'pressidium-cookie-consent')}
              help={__('Number of milliseconds before showing the consent modal', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.delay}
              onChange={(value) => onGeneralSettingChange('delay', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Cookie expiration', 'pressidium-cookie-consent')}
              help={__('Number of days before the cookie expires', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.cookie_expiration}
              onChange={(value) => onGeneralSettingChange('cookie_expiration', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Cookie path', 'pressidium-cookie-consent')}
              help={__('Path where the cookie will be set', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.cookie_path}
              onChange={(value) => onGeneralSettingChange('cookie_path', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Cookie domain', 'pressidium-cookie-consent')}
              help={__('Specify your domain or a subdomain', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.cookie_domain}
              onChange={(value) => onGeneralSettingChange('cookie_domain', value)}
            />
          </PanelRow>
        </PanelBody>

        <PanelBody
          title={__('Colors', 'pressidium-cookie-consent')}
          icon={ColorIcon}
          initialOpen
        >
          <PanelRow>
            <SelectControl
              label={__('Theme', 'pressidium-cookie-consent')}
              value={selectedTheme}
              options={themes.map(({ label, value }) => ({ label, value }))}
              onChange={onThemeChange}
              className="pressidium-select-control"
            />
          </PanelRow>
          <PanelRow>
            <ColorsPanel
              items={[
                {
                  key: 'bg',
                  label: __('Background', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors.bg,
                },
                {
                  key: 'text',
                  label: __('Text', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors.text,
                },
                {
                  key: 'btn-primary-bg',
                  label: __('Primary background', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['btn-primary-bg'],
                },
                {
                  key: 'btn-primary-text',
                  label: __('Primary text', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['btn-primary-text'],
                },
                {
                  key: 'btn-primary-hover-bg',
                  label: __(
                    'Primary hover background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['btn-primary-hover-bg'],
                },
                {
                  key: 'btn-secondary-bg',
                  label: __(
                    'Secondary background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['btn-secondary-bg'],
                },
                {
                  key: 'btn-secondary-text',
                  label: __('Secondary text', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['btn-secondary-text'],
                },
                {
                  key: 'btn-secondary-hover-bg',
                  label: __(
                    'Secondary hover background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['btn-secondary-hover-bg'],
                },
                {
                  key: 'toggle-bg-off',
                  label: __(
                    'Toggle background (off)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['toggle-bg-off'],
                },
                {
                  key: 'toggle-bg-on',
                  label: __(
                    'Toggle background (on)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['toggle-bg-on'],
                },
                {
                  key: 'toggle-bg-readonly',
                  label: __(
                    'Toggle background (readonly)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['toggle-bg-readonly'],
                },
                {
                  key: 'toggle-knob-bg',
                  label: __(
                    'Toggle knob background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['toggle-knob-bg'],
                },
                {
                  key: 'toggle-knob-icon-color',
                  label: __(
                    'Toggle knob icon color',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['toggle-knob-icon-color'],
                },
                {
                  key: 'cookie-category-block-bg',
                  label: __(
                    'Cookie category block background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['cookie-category-block-bg'],
                },
                {
                  key: 'cookie-category-block-bg-hover',
                  label: __(
                    'Cookie category block background (hover)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['cookie-category-block-bg-hover'],
                },
                {
                  key: 'section-border',
                  label: __('Section border', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['section-border'],
                },
                {
                  key: 'block-text',
                  label: __('Block text', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['block-text'],
                },
                {
                  key: 'cookie-table-border',
                  label: __('Cookie table border', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['cookie-table-border'],
                },
                {
                  key: 'overlay-bg',
                  label: __('Overlay background', 'pressidium-cookie-consent'),
                  color: state.pressidium_options.colors['overlay-bg'],
                },
                {
                  key: 'webkit-scrollbar-bg',
                  label: __(
                    'Scrollbar background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['webkit-scrollbar-bg'],
                },
                {
                  key: 'webkit-scrollbar-bg-hover',
                  label: __(
                    'Scrollbar background (hover)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidium_options.colors['webkit-scrollbar-bg-hover'],
                },
              ]}
              onChange={onColorChange}
            />
          </PanelRow>
        </PanelBody>
      </Panel>
    </div>
  );
}

export default GeneralTab;
