import { __ } from '@wordpress/i18n';

import LightTheme from './light';
import DarkTurquoiseTheme from './dark-turquoise';
import DarkBlueTheme from './dark-blue';

export { LightTheme, DarkTurquoiseTheme, DarkBlueTheme };

export default [
  {
    label: __('Light', 'pressidium-cookie-consent'),
    value: 'light',
    theme: LightTheme,
  },
  {
    label: __('Dark Blue', 'pressidium-cookie-consent'),
    value: 'dark-blue',
    theme: DarkBlueTheme,
  },
  {
    label: __('Dark Turqoise', 'pressidium-cookie-consent'),
    value: 'dark-turquoise',
    theme: DarkTurquoiseTheme,
  },
];
