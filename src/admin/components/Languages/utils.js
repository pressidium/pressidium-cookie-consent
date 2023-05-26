import languages from './languages';

export const nameByLanguageCode = (languageCode) => {
  if (languages[languageCode]) {
    return languages[languageCode];
  }

  return languageCode;
};

export const isNumeric = (value) => /^\d+$/.test(value);

export const kebabToCamelCase = (str) => str.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
