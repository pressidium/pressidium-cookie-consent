import {
  useState,
  useContext,
  useCallback,
  useMemo,
} from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  ToggleControl,
  RadioControl,
  SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import SettingsContext from '../../store/context';
import * as ActionTypes from '../../store/actionTypes';

import icons from '../../icons';

function FloatingButtonTab() {
  const [selectedIcon, setSelectedIcon] = useState('pressidium');

  const { state, dispatch } = useContext(SettingsContext);

  const onSettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_FLOATING_BUTTON_SETTING,
      payload: {
        key,
        value,
      },
    });
  }, []);

  const onIconChange = useCallback((value) => {
    setSelectedIcon(value);
    onSettingChange('icon', value);
  }, []);

  const Icon = useMemo(
    () => icons.find((icon) => icon.value === selectedIcon).Component,
    [icons, selectedIcon],
  );

  return (
    <Panel>
      <PanelBody initialOpen>
        <PanelRow>
          <ToggleControl
            label={__('Enabled', 'pressidium-cookie-consent')}
            help={state.pressidium_options.floating_button.enabled
              ? __('Will show a floating button to open the settings modal', 'pressidium-cookie-consent')
              : __('Won\'t show a floating button', 'pressidium-cookie-consent')}
            checked={state.pressidium_options.floating_button.enabled}
            className="pressidium-toggle-control"
            onChange={(value) => onSettingChange('enabled', value)}
          />
        </PanelRow>
        <PanelRow>
          <RadioControl
            label={__('Size', 'pressidium-cookie-consent')}
            selected={state.pressidium_options.floating_button.size}
            options={[
              { label: __('Small', 'pressidium-cookie-consent'), value: 'sm' },
              { label: __('Large', 'pressidium-cookie-consent'), value: 'lg' },
            ]}
            onChange={(value) => onSettingChange('size', value)}
          />
        </PanelRow>

        <PanelRow>
          <RadioControl
            label={__('Position', 'pressidium-cookie-consent')}
            selected={state.pressidium_options.floating_button.position}
            options={[
              { label: __('Left', 'pressidium-cookie-consent'), value: 'left' },
              { label: __('Right', 'pressidium-cookie-consent'), value: 'right' },
            ]}
            onChange={(value) => onSettingChange('position', value)}
          />
        </PanelRow>

        <PanelRow>
          <Flex justify="flex-start" align="flex-end">
            <FlexItem>
              <SelectControl
                label={__('Icon', 'pressidium-cookie-consent')}
                value={selectedIcon}
                options={icons.map(({ label, value }) => ({ label, value }))}
                onChange={onIconChange}
                className="pressidium-select-control"
              />
            </FlexItem>
            <FlexItem>
              <Icon />
            </FlexItem>
          </Flex>
        </PanelRow>

        <PanelRow>
          <RadioControl
            label={__('Transition', 'pressidium-cookie-consent')}
            selected={state.pressidium_options.floating_button.transition}
            options={[
              { label: __('No transition (immediately appear)', 'pressidium-cookie-consent'), value: '' },
              { label: __('Fade in', 'pressidium-cookie-consent'), value: 'fade-in' },
              { label: __('Fade in up', 'pressidium-cookie-consent'), value: 'fade-in-up' },
              { label: __('Fade in zoom', 'pressidium-cookie-consent'), value: 'fade-in-zoom' },
              { label: __('Zoom in', 'pressidium-cookie-consent'), value: 'zoom-in' },
              { label: __('Slide in horizontally', 'pressidium-cookie-consent'), value: 'slide-in-horizontal' },
              { label: __('Slide in vertically', 'pressidium-cookie-consent'), value: 'slide-in-vertical' },
            ]}
            onChange={(value) => onSettingChange('transition', value)}
          />
        </PanelRow>
      </PanelBody>

    </Panel>
  );
}

export default FloatingButtonTab;
