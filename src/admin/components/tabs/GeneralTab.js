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
import * as ActionTypes from '../../store/actionTypes';

import ColorsPanel from '../ColorsPanel';
import themes from '../../themes';

const getThemeByName = (themeName) => {
  const { theme } = themes.find(({ value }) => value === themeName);
  return theme;
};

function GeneralTab({ fonts }) {
  const [selectedTheme, setSelectedTheme] = useState('light');

  const { state, dispatch } = useContext(SettingsContext);

  const onGeneralSettingChange = (key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_GENERAL_SETTING,
      payload: {
        key,
        value,
      },
    });
  };

  const onCookieSettingChange = (key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_COOKIE_SETTING,
      payload: {
        key,
        value,
      },
    });
  };

  const setColors = useCallback((colors) => {
    dispatch({
      type: ActionTypes.UPDATE_COLOR_SETTINGS,
      payload: colors,
    });
  }, []);

  const setColor = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_COLOR_SETTING,
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

  const onFontChange = (value) => {
    const font = fonts.find(({ slug }) => slug === value);
    dispatch({
      type: ActionTypes.UPDATE_FONT_SETTING,
      payload: font,
    });
  };

  const onPressidiumOptionChange = (key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_PRESSIDIUM_OPTION,
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
              label={__('Auto show', 'pressidium-cookie-consent')}
              help={state.autoShow
                ? __('Will show the cookie consent as soon as possible', 'pressidium-cookie-consent')
                : __('You will have to manually call the `.show()` method', 'pressidium-cookie-consent')}
              checked={state.autoShow}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('autoShow', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Disable page interaction', 'pressidium-cookie-consent')}
              help={state.disablePageInteraction
                ? __('Page interaction will be blocked until user action', 'pressidium-cookie-consent')
                : __('Users will be able to interact without needing to consent first', 'pressidium-cookie-consent')}
              checked={state.disablePageInteraction}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('disablePageInteraction', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Auto-clear cookies', 'pressidium-cookie-consent')}
              help={state.autoClearCookies
                ? __('Cookies will be deleted automatically when user opts-out of a specific category inside cookie settings', 'pressidium-cookie-consent')
                : __('Cookies will have to be deleted manually', 'pressidium-cookie-consent')}
              checked={state.autoClearCookies}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('autoClearCookies', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Manage script tags', 'pressidium-cookie-consent')}
              help={state.manageScriptTags
                ? __('Will manage existing third-party script tags', 'pressidium-cookie-consent')
                : __('Won\'t manage existing third-party script tags', 'pressidium-cookie-consent')}
              checked={state.manageScriptTags}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('manageScriptTags', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Hide from bots', 'pressidium-cookie-consent')}
              help={state.hideFromBots
                ? __('Won\'t run when a bot/crawler/webdriver is detected', 'pressidium-cookie-consent')
                : __('Will always run, even when a bot/crawler/webdriver is detected', 'pressidium-cookie-consent')}
              checked={state.hideFromBots}
              className="pressidium-toggle-control"
              onChange={(value) => onGeneralSettingChange('hideFromBots', value)}
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
              help={state.pressidiumOptions.recordConsents
                ? __('Will record user consents to be able to provide proof of consent for auditing purposes', 'pressidium-cookie-consent')
                : __('Won\'t record any user consents', 'pressidium-cookie-consent')}
              checked={state.pressidiumOptions.recordConsents}
              className="pressidium-toggle-control"
              onChange={(value) => onPressidiumOptionChange('recordConsents', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Hide empty categories', 'pressidium-cookie-consent')}
              help={state.pressidiumOptions.hideEmptyCategories
                ? __('Will hide a cookie category if it has no cookies', 'pressidium-cookie-consent')
                : __('Won\'t hide any cookie categories', 'pressidium-cookie-consent')}
              checked={state.pressidiumOptions.hideEmptyCategories}
              className="pressidium-toggle-control"
              onChange={(value) => onPressidiumOptionChange('hideEmptyCategories', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Cookie expiration', 'pressidium-cookie-consent')}
              help={__('Number of days before the cookie expires', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.cookie.expiresAfterDays}
              onChange={(value) => onCookieSettingChange('expiresAfterDays', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Cookie path', 'pressidium-cookie-consent')}
              help={__('Path where the cookie will be set', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.cookie.path}
              onChange={(value) => onCookieSettingChange('path', value)}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={__('Cookie domain', 'pressidium-cookie-consent')}
              help={__('Specify your domain or a subdomain', 'pressidium-cookie-consent')}
              className="pressidium-text-control"
              value={state.cookie.domain}
              onChange={(value) => onCookieSettingChange('domain', value)}
            />
          </PanelRow>
        </PanelBody>

        <PanelBody
          title={__('Font & Colors', 'pressidium-cookie-consent')}
          icon={ColorIcon}
          initialOpen
        >
          {fonts.length > 0 ? (
            <PanelRow>
              <SelectControl
                label={__('Font', 'pressidium-cookie-consent')}
                value={state.pressidiumOptions.font.slug}
                options={fonts.map(({ name, slug }) => ({ label: name, value: slug }))}
                onChange={onFontChange}
                className="pressidium-select-control"
                help={__('Fonts you have installed via the Font Library', 'pressidium-cookie-consent')}
              />
            </PanelRow>
          ) : null}
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
                  color: state.pressidiumOptions.colors.bg,
                },
                {
                  key: 'primary-color',
                  label: __('Primary color', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['primary-color'],
                },
                {
                  key: 'btn-primary-bg',
                  label: __('Button primary background', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-primary-bg'],
                },
                {
                  key: 'btn-primary-color',
                  label: __('Button primary color', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-primary-color'],
                },
                {
                  key: 'btn-primary-hover-bg',
                  label: __(
                    'Button primary hover background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['btn-primary-hover-bg'],
                },
                {
                  key: 'btn-primary-hover-color',
                  label: __(
                    'Button primary hover color',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['btn-primary-hover-color'],
                },
                {
                  key: 'btn-secondary-bg',
                  label: __(
                    'Button secondary background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['btn-secondary-bg'],
                },
                {
                  key: 'btn-secondary-color',
                  label: __('Button secondary color', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-secondary-color'],
                },
                {
                  key: 'btn-secondary-hover-bg',
                  label: __(
                    'Button secondary hover background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['btn-secondary-hover-bg'],
                },
                {
                  key: 'btn-secondary-hover-color',
                  label: __(
                    'Button secondary hover color',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['btn-secondary-hover-color'],
                },
                {
                  key: 'toggle-off-bg',
                  label: __(
                    'Toggle background (off)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['toggle-off-bg'],
                },
                {
                  key: 'toggle-on-knob-bg',
                  label: __(
                    'Toggle background (on)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['toggle-on-knob-bg'],
                },
                {
                  key: 'toggle-readonly-bg',
                  label: __(
                    'Toggle background (readonly)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['toggle-readonly-bg'],
                },
                {
                  key: 'toggle-knob-bg',
                  label: __(
                    'Toggle knob background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['toggle-knob-bg'],
                },
                {
                  key: 'toggle-knob-icon-color',
                  label: __(
                    'Toggle knob icon color',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['toggle-knob-icon-color'],
                },
                {
                  key: 'cookie-category-block-bg',
                  label: __(
                    'Cookie category block background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['cookie-category-block-bg'],
                },
                {
                  key: 'cookie-category-block-hover-bg',
                  label: __(
                    'Cookie category block background (hover)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['cookie-category-block-hover-bg'],
                },
                {
                  key: 'separator-border-color',
                  label: __('Separator border color', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['separator-border-color'],
                },
                {
                  key: 'block-text',
                  label: __('Block text', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['block-text'],
                },
                {
                  key: 'cookie-table-border',
                  label: __('Cookie table border', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['cookie-table-border'],
                },
                {
                  key: 'overlay-bg',
                  label: __('Overlay background', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['overlay-bg'],
                },
                {
                  key: 'webkit-scrollbar-bg',
                  label: __(
                    'Scrollbar background',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['webkit-scrollbar-bg'],
                },
                {
                  key: 'webkit-scrollbar-bg-hover',
                  label: __(
                    'Scrollbar background (hover)',
                    'pressidium-cookie-consent',
                  ),
                  color: state.pressidiumOptions.colors['webkit-scrollbar-bg-hover'],
                },
                {
                  key: 'btn-floating-bg',
                  label: __('Floating button background', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-floating-bg'],
                },
                {
                  key: 'btn-floating-icon',
                  label: __('Floating button icon', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-floating-icon'],
                },
                {
                  key: 'btn-floating-hover-bg',
                  label: __('Floating button hover background', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-floating-hover-bg'],
                },
                {
                  key: 'btn-floating-hover-icon',
                  label: __('Floating button hover icon', 'pressidium-cookie-consent'),
                  color: state.pressidiumOptions.colors['btn-floating-hover-icon'],
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
