import { useState, useEffect } from '@wordpress/element';
import {
  Button,
  ColorIndicator,
  ColorPicker,
  Flex,
  FlexItem,
  Popover,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import styled from 'styled-components';

const StyledOverlay = styled.div`
  position: fixed;
  top: 0;
  left: 0;  
  width: 100vw;
  height: 100vh;
  background: transparent;
  cursor: default;
`;

function ColorControl(props) {
  const {
    label = __('Color', 'pressidium-cookie-consent'),
    color = '#0073aa',
    // eslint-disable-next-line no-unused-vars
    onChange = (newColor) => {},
  } = props;

  const [isOpen, setIsOpen] = useState(false);
  const [currentColor, setCurrentColor] = useState(color);

  useEffect(() => {
    setCurrentColor(currentColor);
  }, [color]);

  const toggle = () => {
    setIsOpen((state) => !state);
  };

  const onColorChange = (newColor) => {
    setCurrentColor(newColor);
    onChange(newColor);
  };

  return (
    <Button variant="tertiary" onClick={toggle}>
      <Flex direction="row">
        <FlexItem>
          <ColorIndicator colorValue={color} />
        </FlexItem>
        <FlexItem>
          <span className="pressidium-colors-panel__button-label">
            {label}
          </span>
        </FlexItem>
      </Flex>
      {isOpen && (
        <>
          <StyledOverlay />
          <Popover placement="right">
            {/* eslint-disable-next-line jsx-a11y/click-events-have-key-events,
                jsx-a11y/no-static-element-interactions */}
            <div onClick={(e) => e.stopPropagation()}>
              <ColorPicker
                color={color}
                onChange={onColorChange}
                defaultValue={color}
              />
            </div>
          </Popover>
        </>
      )}
    </Button>
  );
}

export default ColorControl;
