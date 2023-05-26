// eslint-disable-next-line import/prefer-default-export
export const removeElement = (element) => {
  if (element) {
    element.parentNode.removeChild(element);
  }
};
