import { useState, useCallback, useMemo } from '@wordpress/element';

import { AIContext } from './context';

// eslint-disable-next-line import/prefer-default-export, react/function-component-definition
export const AIProvider = (props) => {
  const { children } = props;

  const [isGenerating, setIsGenerating] = useState(false);

  const startGenerating = useCallback(() => setIsGenerating(true), []);
  const stopGenerating = useCallback(() => setIsGenerating(false), []);

  const value = useMemo(() => ({
    isGenerating,
    startGenerating,
    stopGenerating,
  }), [isGenerating]);

  if (typeof window === 'undefined') {
    return children;
  }

  return (
    <AIContext.Provider value={value}>
      {children}
    </AIContext.Provider>
  );
};
