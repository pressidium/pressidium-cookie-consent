import * as FlagIcons from './icons';

import { nameByLanguageCode, isNumeric, kebabToCamelCase } from './utils';

function LanguageFlag(props) {
  const {
    language,
    width = 'auto',
    height = 'auto',
    style = {},
  } = props;

  const iconName = `${kebabToCamelCase(language)}Icon`;
  const languageName = nameByLanguageCode(language);

  if (!(iconName in FlagIcons)) {
    return null;
  }

  const FlagIcon = FlagIcons[iconName];

  return (
    <img
      src={FlagIcon}
      alt={`${languageName} flag`}
      style={{
        width: isNumeric(width) ? `${width}px` : width,
        height: isNumeric(height) ? `${height}px` : height,
        ...style,
      }}
    />
  );
}

export default LanguageFlag;
