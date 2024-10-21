import { __ } from '@wordpress/i18n';

import LightTheme from './light';
import LightFuchsiaTheme from './light-fuchsia';
import LightGreenTheme from './light-green';
import LightOrangeTheme from './light-orange';
import LightPurpleTheme from './light-purple';
import LightRedTheme from './light-red';
import LightTealTheme from './light-teal';

import DarkBlueTheme from './dark-blue';
import DarkGreenTheme from './dark-green';
import DarkOrangeTheme from './dark-orange';
import DarkPurpleTheme from './dark-purple';
import DarkRedTheme from './dark-red';
import DarkTurquoiseTheme from './dark-turquoise';
import DarkYellowTheme from './dark-yellow';

export { LightTheme, DarkTurquoiseTheme, DarkBlueTheme };

export default [
  {
    label: __('Light Blue', 'pressidium-cookie-consent'),
    value: 'light',
    theme: LightTheme,
  },
  {
    label: __('Light Fuchsia', 'pressidium-cookie-consent'),
    value: 'light-fuchsia',
    theme: LightFuchsiaTheme,
  },
  {
    label: __('Light Green', 'pressidium-cookie-consent'),
    value: 'light-green',
    theme: LightGreenTheme,
  },
  {
    label: __('Light Orange', 'pressidium-cookie-consent'),
    value: 'light-orange',
    theme: LightOrangeTheme,
  },
  {
    label: __('Light Purple', 'pressidium-cookie-consent'),
    value: 'light-purple',
    theme: LightPurpleTheme,
  },
  {
    label: __('Light Red', 'pressidium-cookie-consent'),
    value: 'light-red',
    theme: LightRedTheme,
  },
  {
    label: __('Light Teal', 'pressidium-cookie-consent'),
    value: 'light-teal',
    theme: LightTealTheme,
  },
  {
    label: __('Dark Blue', 'pressidium-cookie-consent'),
    value: 'dark-blue',
    theme: DarkBlueTheme,
  },
  {
    label: __('Dark Green', 'pressidium-cookie-consent'),
    value: 'dark-green',
    theme: DarkGreenTheme,
  },
  {
    label: __('Dark Orange', 'pressidium-cookie-consent'),
    value: 'dark-orange',
    theme: DarkOrangeTheme,
  },
  {
    label: __('Dark Purple', 'pressidium-cookie-consent'),
    value: 'dark-purple',
    theme: DarkPurpleTheme,
  },
  {
    label: __('Dark Red', 'pressidium-cookie-consent'),
    value: 'dark-red',
    theme: DarkRedTheme,
  },
  {
    label: __('Dark Turquoise', 'pressidium-cookie-consent'),
    value: 'dark-turquoise',
    theme: DarkTurquoiseTheme,
  },
  {
    label: __('Dark Yellow', 'pressidium-cookie-consent'),
    value: 'dark-yellow',
    theme: DarkYellowTheme,
  },
];
