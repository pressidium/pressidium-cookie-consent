import { useContext, useCallback } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  RadioControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import SettingsContext from '../../store/context';
import * as ActionTypes from '../../store/actionTypes';

function SettingsModalTab() {
  const { state, dispatch } = useContext(SettingsContext);

  const onSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_SETTINGS_MODAL_SETTING,
      payload: {
        key,
        value,
      },
    });
  }, []);

  return (
    <Panel>
      <PanelBody initialOpen>
        <PanelRow>
          <RadioControl
            label={__('Layout', 'pressidium-cookie-consent')}
            selected={state.gui_options.settings_modal.layout}
            options={[
              { label: __('Box', 'pressidium-cookie-consent'), value: 'box' },
              { label: __('Bar', 'pressidium-cookie-consent'), value: 'bar' },
            ]}
            onChange={(value) => onSettingChange('layout', value)}
          />
        </PanelRow>

        <PanelRow>
          <div
            style={{
              width: '100%',
              opacity: state.gui_options.settings_modal.layout === 'bar' ? 1.0 : 0.4,
            }}
          >
            <RadioControl
              label={__('Position', 'pressidium-cookie-consent')}
              selected={state.gui_options.settings_modal.position}
              options={[
                { label: __('Left', 'pressidium-cookie-consent'), value: 'left' },
                { label: __('Right', 'pressidium-cookie-consent'), value: 'right' },
              ]}
              onChange={(value) => onSettingChange('position', value)}
              disabled={state.gui_options.settings_modal.layout !== 'bar'}
            />
          </div>
        </PanelRow>

        <PanelRow>
          <RadioControl
            label={__('Transition', 'pressidium-cookie-consent')}
            selected={state.gui_options.settings_modal.transition}
            options={[
              { label: __('Slide', 'pressidium-cookie-consent'), value: 'slide' },
              { label: __('Zoom', 'pressidium-cookie-consent'), value: 'zoom' },
            ]}
            onChange={(value) => onSettingChange('transition', value)}
          />
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default SettingsModalTab;
