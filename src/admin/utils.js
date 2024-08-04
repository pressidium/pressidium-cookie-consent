/**
 * Remove the given element from the DOM.
 *
 * @param {HTMLElement} element Element to remove.
 *
 * @return {void}
 */
export const removeElement = (element) => {
  if (element) {
    element.parentNode.removeChild(element);
  }
};

/**
 * Deep copy the given object.
 *
 * Note that this method won't work with objects that contain
 * `Date` objects, functions, symbols, or circular references.
 *
 * @param {object} obj Object to copy.
 *
 * @return {object} Deep copy of the given object.
 */
export const deepCopy = (obj) => JSON.parse(JSON.stringify(obj));

/**
 * Wait for a given number of milliseconds before resolving.
 *
 * @param {number} ms Number of milliseconds to wait.
 *
 * @return {Promise} Promise that resolves after the given number of milliseconds.
 */
export const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
