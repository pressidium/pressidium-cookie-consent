import countries from './countries';

export const nameByCountryCode = (countryCode) => {
  if (countries[countryCode]) {
    return countries[countryCode];
  }

  return countryCode;
};

export const isNumeric = (value) => /^\d+$/.test(value);

export const kebabToCamelCase = (str) => str.replace(/-([a-z])/g, (g) => g[1].toUpperCase());

export const countryCodeToCamelCase = (countryCode) => {
  const arr = countryCode.split('-');

  if (arr.length === 1) {
    return countryCode.toLowerCase();
  }

  return `${arr[0].toLowerCase()}${arr[1].charAt(0).toUpperCase()}${arr[1].slice(1).toLowerCase()}`;
};
