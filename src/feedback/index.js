import { render } from '@wordpress/element';

import Feedback from './components/Feedback';

import './web-components';

import './scss/main.scss';

(() => {
  const isDialogSupported = () => typeof HTMLDialogElement === 'function';

  if (!isDialogSupported()) {
    return;
  }

  const slug = 'pressidium-cookie-consent';

  const selectors = {
    deactivationLink: `#the-list [data-slug="${slug}"] .deactivate a`,
    dialog: '#pressidium-cookie-consent-feedback-dialog',
    feedbackRoot: '#pressidium-cookie-consent-feedback-root',
  };

  const deactivationLinkElement = document.querySelector(selectors.deactivationLink);

  /** @type {Dialog} */
  const dialogComponent = document.querySelector(selectors.dialog);

  const dialog = dialogComponent.ref;

  if (deactivationLinkElement === null || dialog === null) {
    return;
  }

  const deactivationLink = deactivationLinkElement.getAttribute('href');

  const deactivate = () => {
    window.location.href = deactivationLink;
  };

  const onDeactivateLinkClick = (e) => {
    // Prevent the deactivation of the plugin
    e.preventDefault();

    if (dialog === null) {
      /*
       * Something went wrong with our custom web component,
       * just deactivate the plugin immediately.
       */
      deactivate();
      return;
    }

    // Open the feedback modal
    dialog.showModal();
  };

  deactivationLinkElement.addEventListener('click', onDeactivateLinkClick);

  const feedbackRootElement = document.querySelector(selectors.feedbackRoot);
  render(<Feedback deactivationLink={deactivationLink} />, feedbackRootElement);
})();
