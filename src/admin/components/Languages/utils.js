import languages from './languages';

export const nameByLanguageCode = (languageCode) => {
  if (languages[languageCode]) {
    return languages[languageCode];
  }

  return languageCode;
};

export const isNumeric = (value) => /^\d+$/.test(value);

export const kebabToCamelCase = (str) => str.replace(/-([a-z])/g, (g) => g[1].toUpperCase());

export const languageCodeToCamelCase = (languageCode) => {
  const arr = languageCode.split('-');

  if (arr.length === 1) {
    return languageCode.toLowerCase();
  }

  const [language, country] = arr;
  return `${language.toLowerCase()}${country.charAt(0).toUpperCase()}${country.slice(1).toLowerCase()}`;
};
