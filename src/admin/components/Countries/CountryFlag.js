import * as FlagIcons from './icons';

import {
  nameByCountryCode,
  isNumeric,
  countryCodeToCamelCase,
} from './utils';

function CountryFlag(props) {
  const {
    country,
    width = 'auto',
    height = 'auto',
    style = {},
  } = props;

  const iconName = `${countryCodeToCamelCase(country)}Icon`;
  const countryName = nameByCountryCode(country);

  if (!(iconName in FlagIcons)) {
    return null;
  }

  const FlagIcon = FlagIcons[iconName];

  return (
    <img
      src={FlagIcon}
      alt={`${countryName} flag`}
      style={{
        width: isNumeric(width) ? `${width}px` : width,
        height: isNumeric(height) ? `${height}px` : height,
        ...style,
      }}
    />
  );
}

export default CountryFlag;
