import { render } from '@wordpress/element';

import SettingsProvider from './store/provider';

import SettingsPanel from './components/SettingsPanel';

import './scss/main.scss';

function Root() {
  return (
    <SettingsProvider>
      <SettingsPanel />
    </SettingsProvider>
  );
}

render(<Root />, document.getElementById('pressidium-cookie-consent-root'));
