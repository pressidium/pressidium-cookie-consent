import { useReducer, useMemo } from '@wordpress/element';

import SettingsContext from './context';
import settingsReducer from './reducer';
import initialState from './initialState';

function SettingsProvider(props) {
  const { children } = props;

  const [state, dispatch] = useReducer(settingsReducer, initialState);

  const value = useMemo(
    () => ({ state, dispatch }),
    [state],
  );

  return (
    <SettingsContext.Provider value={value}>
      {children}
    </SettingsContext.Provider>
  );
}

export default SettingsProvider;
