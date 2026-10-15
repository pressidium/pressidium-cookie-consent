import { useContext, useMemo, useCallback } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  RadioControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
  megaphone as MegaphoneIcon,
  cog as CogIcon,
} from '@wordpress/icons';

import SettingsContext from '../../store/context';
import * as ActionTypes from '../../store/actionTypes';

function ModalsTab() {
  const { state, dispatch } = useContext(SettingsContext);

  const [
    posY = 'bottom',
    posX = 'right',
  ] = useMemo(() => state.guiOptions.consentModal.position.split(' '), [state]);

  const onConsentModalSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_CONSENT_MODAL_SETTING,
      payload: {
        key,
        value,
      },
    });
  }, []);

  const onPreferencesModalSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_PREFERENCES_MODAL_SETTING,
      payload: {
        key,
        value,
      },
    });
  }, []);

  const onCloseIconSettingChange = useCallback((value) => {
    dispatch({
      type: ActionTypes.UPDATE_CLOSE_ICON_SETTING,
      payload: { value },
    });
  }, []);

  const onPressidiumOptionChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_PRESSIDIUM_OPTION,
      payload: { key, value },
    });
  }, []);

  return (
    <Panel>
      <PanelBody
        title={__('Consent Modal', 'pressidium-cookie-consent')}
        icon={MegaphoneIcon}
        initialOpen
      >
        <PanelRow>
          <RadioControl
            label={__('Layout', 'pressidium-cookie-consent')}
            selected={state.guiOptions.consentModal.layout}
            options={[
              { label: __('Box', 'pressidium-cookie-consent'), value: 'box' },
              { label: __('Cloud', 'pressidium-cookie-consent'), value: 'cloud' },
              { label: __('Bar', 'pressidium-cookie-consent'), value: 'bar' },
            ]}
            onChange={(value) => onConsentModalSettingChange('layout', value)}
          />
        </PanelRow>

        <PanelRow>
          <RadioControl
            label={__('Vertical position', 'pressidium-cookie-consent')}
            selected={posY}
            options={[
              { label: __('Top', 'pressidium-cookie-consent'), value: 'top' },
              { label: __('Middle', 'pressidium-cookie-consent'), value: 'middle' },
              { label: __('Bottom', 'pressidium-cookie-consent'), value: 'bottom' },
            ]}
            onChange={(value) => onConsentModalSettingChange('position', `${value} ${posX}`)}
          />
        </PanelRow>

        <PanelRow>
          <div
            style={{
              width: '100%',
              opacity: ['box', 'cloud'].includes(state.guiOptions.consentModal.layout) ? 1.0 : 0.4,
            }}
          >
            <RadioControl
              label={__('Horizontal position', 'pressidium-cookie-consent')}
              selected={posX}
              options={[
                { label: __('Left', 'pressidium-cookie-consent'), value: 'left' },
                { label: __('Center', 'pressidium-cookie-consent'), value: 'center' },
                { label: __('Right', 'pressidium-cookie-consent'), value: 'right' },
              ]}
              onChange={(value) => onConsentModalSettingChange('position', `${posY} ${value}`)}
              disabled={!['box', 'cloud'].includes(state.guiOptions.consentModal.layout)}
            />
          </div>
        </PanelRow>

        <PanelRow>
          <ToggleControl
            label={__('Equal weight buttons', 'pressidium-cookie-consent')}
            help={state.guiOptions.consentModal.equalWeightButtons
              ? __('Buttons have equal weight', 'pressidium-cookie-consent')
              : __('Buttons won\'t have equal weight', 'pressidium-cookie-consent')}
            checked={state.guiOptions.consentModal.equalWeightButtons}
            className="pressidium-toggle-control"
            onChange={(value) => onConsentModalSettingChange('equalWeightButtons', value)}
          />
        </PanelRow>

        <PanelRow>
          <ToggleControl
            label={__('Flip buttons', 'pressidium-cookie-consent')}
            help={state.guiOptions.consentModal.flipButtons
              ? __('Buttons are inverted', 'pressidium-cookie-consent')
              : __('Enable to invert buttons', 'pressidium-cookie-consent')}
            checked={state.guiOptions.consentModal.flipButtons}
            className="pressidium-toggle-control"
            onChange={(value) => onConsentModalSettingChange('flipButtons', value)}
          />
        </PanelRow>

        <PanelRow>
          <div
            style={{
              width: '100%',
              opacity: state.guiOptions.consentModal.layout === 'box' ? 1.0 : 0.4,
            }}
          >
            <ToggleControl
              label={__('Show close icon', 'pressidium-cookie-consent')}
              help={(state.pressidiumOptions.consentModalCloseIcon ?? true)
                ? __('Close icon is visible', 'pressidium-cookie-consent')
                : __('Close icon is hidden', 'pressidium-cookie-consent')}
              checked={state.pressidiumOptions.consentModalCloseIcon ?? true}
              className="pressidium-toggle-control"
              disabled={state.guiOptions.consentModal.layout !== 'box'}
              onChange={(value) => onCloseIconSettingChange(value)}
            />
          </div>
        </PanelRow>

        <PanelRow>
          <ToggleControl
            label={__('Show footer', 'pressidium-cookie-consent')}
            help={(state.pressidiumOptions.showConsentModalFooter ?? true)
              ? __('Will show the footer', 'pressidium-cookie-consent')
              : __('Won\'t show the footer', 'pressidium-cookie-consent')}
            checked={state.pressidiumOptions.showConsentModalFooter ?? true}
            className="pressidium-toggle-control"
            onChange={(value) => onPressidiumOptionChange('showConsentModalFooter', value)}
          />
        </PanelRow>
      </PanelBody>

      <PanelBody
        title={__('Preferences Modal', 'pressidium-cookie-consent')}
        icon={CogIcon}
        initialOpen
      >
        <PanelRow>
          <RadioControl
            label={__('Layout', 'pressidium-cookie-consent')}
            selected={state.guiOptions.preferencesModal.layout}
            options={[
              { label: __('Box', 'pressidium-cookie-consent'), value: 'box' },
              { label: __('Bar', 'pressidium-cookie-consent'), value: 'bar' },
            ]}
            onChange={(value) => onPreferencesModalSettingChange('layout', value)}
          />
        </PanelRow>

        <PanelRow>
          <div
            style={{
              width: '100%',
              opacity: state.guiOptions.preferencesModal.layout === 'bar' ? 1.0 : 0.4,
            }}
          >
            <RadioControl
              label={__('Position', 'pressidium-cookie-consent')}
              selected={state.guiOptions.preferencesModal.position}
              options={[
                { label: __('Left', 'pressidium-cookie-consent'), value: 'left' },
                { label: __('Right', 'pressidium-cookie-consent'), value: 'right' },
              ]}
              onChange={(value) => onPreferencesModalSettingChange('position', value)}
              disabled={state.guiOptions.preferencesModal.layout !== 'bar'}
            />
          </div>
        </PanelRow>

        <PanelRow>
          <ToggleControl
            label={__('Equal weight buttons', 'pressidium-cookie-consent')}
            help={state.guiOptions.preferencesModal.equalWeightButtons
              ? __('Buttons have equal weight', 'pressidium-cookie-consent')
              : __('Buttons won\'t have equal weight', 'pressidium-cookie-consent')}
            checked={state.guiOptions.preferencesModal.equalWeightButtons}
            className="pressidium-toggle-control"
            onChange={(value) => onPreferencesModalSettingChange('equalWeightButtons', value)}
          />
        </PanelRow>

        <PanelRow>
          <ToggleControl
            label={__('Flip buttons', 'pressidium-cookie-consent')}
            help={state.guiOptions.preferencesModal.flipButtons
              ? __('Buttons are inverted', 'pressidium-cookie-consent')
              : __('Enable to invert buttons', 'pressidium-cookie-consent')}
            checked={state.guiOptions.preferencesModal.flipButtons}
            className="pressidium-toggle-control"
            onChange={(value) => onPreferencesModalSettingChange('flipButtons', value)}
          />
        </PanelRow>

      </PanelBody>
    </Panel>
  );
}

export default ModalsTab;
