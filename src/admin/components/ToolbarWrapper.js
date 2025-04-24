import { useState, useEffect, useRef } from '@wordpress/element';
import {
  Toolbar,
  ToolbarButton,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import clsx from 'clsx';

function ToolbarWrapper(props) {
  const {
    children,
    label = __('Options', 'pressidium-cookie-consent'),
    buttons = [],
    className = '',
    orientation = 'horizontal',
  } = props;

  const [isFocused, setIsFocused] = useState(false);

  const wrapper = useRef(null);

  const handleFocusIn = () => setIsFocused(true);
  const handleFocusOut = (event) => {
    if (wrapper.current && !wrapper.current.contains(event.relatedTarget)) {
      setIsFocused(false);
    }
  };

  useEffect(() => {
    if (wrapper.current) {
      wrapper.current.addEventListener('focusin', handleFocusIn);
      wrapper.current.addEventListener('focusout', handleFocusOut);
    }

    return () => {
      if (wrapper.current) {
        wrapper.current.removeEventListener('focusin', handleFocusIn);
        wrapper.current.removeEventListener('focusout', handleFocusOut);
      }
    };
  }, []);

  return (
    <div
      style={{ position: 'relative', width: '100%' }}
      className={clsx(className, 'pressidium-toolbar', { 'is-focused': isFocused })}
      ref={wrapper}
    >
      {isFocused ? (
        <Toolbar
          label={label}
          orientation={orientation}
        >
          {buttons.map((button) => (
            <ToolbarButton {...button} />
          ))}
        </Toolbar>
      ) : null}
      {children}
    </div>
  );
}

export default ToolbarWrapper;
