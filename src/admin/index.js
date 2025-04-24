import { render } from '@wordpress/element';

import SettingsProvider from './store/provider';
import { AIProvider } from './hooks/ai';

import SettingsPanel from './components/SettingsPanel';

import './scss/main.scss';

function Root() {
  return (
    <SettingsProvider>
      <AIProvider>
        <SettingsPanel />
      </AIProvider>
    </SettingsProvider>
  );
}

render(<Root />, document.getElementById('pressidium-cookie-consent-root'));
