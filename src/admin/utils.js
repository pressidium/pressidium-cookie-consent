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
 * Wait for a given number of milliseconds before resolving.
 *
 * @param {number} ms Number of milliseconds to wait.
 *
 * @return {Promise} Promise that resolves after the given number of milliseconds.
 */
export const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
