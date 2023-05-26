import { useRef, useEffect } from '@wordpress/element';

// eslint-disable-next-line import/prefer-default-export
export const usePrevious = (value) => {
  const ref = useRef();

  useEffect(() => {
    ref.current = value;
  }, [value]);

  return ref.current;
};
