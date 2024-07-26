/* eslint-disable no-underscore-dangle, class-methods-use-this */

const template = document.createElement('template');
template.innerHTML = `
  <style>
    :host {
      display: block;
      
      position: fixed;
      bottom: 1rem;
      
      z-index: 1;
    }
    
    :host(.left) {
      left: 1rem;
    }

    :host(.right) {
      right: 1rem;
    }

    .button {
      border-radius: 100%;
      z-index: 1000;
      cursor: pointer;
      border: none;
      box-shadow: 0 0.3rem 0.6rem rgba(2, 2, 3, 0.2);
      background-color: var(--cc-btn-floating-bg, #3859d0);
      transition: opacity 450ms, transform 450ms;
    }
    
    .button:hover {
      background-color: var(--cc-btn-floating-hover-bg, #1d2e38);
    }
    
    .button.sm {
      width: 3rem;
      height: 3rem;
      padding: 0.5rem;
    }
    
    .button.lg {
      width: 4rem;
      height: 4rem;
      padding: 0.7rem;
    }
    
    .button.hidden {
      visibility: hidden;
    }
    
    .button.visible {
      visibility: visible;
    }
    
    .button.hidden.fade-in {
      opacity: 0;
    }
    
    .button.visible.fade-in {
      opacity: 1;
    }
    
    .button.hidden.fade-in-up {
      transform: translateY(0.7rem);
      opacity: 0;
    }
    
    .button.visible.fade-in-up {
      transform: translateY(0);
      opacity: 1;
    }
    
    .button.hidden.fade-in-zoom {
      transform: scale(0);
      opacity: 0;
    }
    
    .button.visible.fade-in-zoom {
      transform: scale(1);
      opacity: 1;
    }
    
    .button.hidden.zoom-in {
      transform: scale(0);
    }
    
    .button.visible.zoom-in {
      transform: scale(1);
    }
    
    :host(.left) .button.hidden.slide-in-horizontal.sm {
      transform: translateX(-4rem);
    }
    
    :host(.left) .button.hidden.slide-in-horizontal.lg {
      transform: translateX(-5rem);
    }
    
    :host(.right) .button.hidden.slide-in-horizontal.sm {
      transform: translateX(4rem);
    }
    
    :host(.right) .button.hidden.slide-in-horizontal.lg {
      transform: translateX(5rem);
    }

    .button.visible.slide-in-horizontal {
      transform: translateX(0);
    }
    
    .button.hidden.slide-in-vertical.sm {
      transform: translateY(4rem);
    }
    
    .button.hidden.slide-in-vertical.lg {
      transform: translateY(5rem);
    }

    .button.visible.slide-in-vertical {
      transform: translateY(0);
    }
    
    [name="icon"] {
      fill: var(--cc-btn-floating-icon, #f9faff);
    }
    
    [name="icon"]:hover {
      fill: var(--cc-btn-floating-hover-icon, #f9faff);
    }
    
    @media (prefers-reduced-motion) {
      .button {
        transition: none;
      }
    }
  </style>
  <button class="button">
    <slot name="icon"></slot>
  </button>
`;

class FloatingButton extends HTMLElement {
  /**
   * FloatingButton constructor.
   */
  constructor() {
    super();

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(template.content.cloneNode(true));

    // eslint-disable-next-line no-underscore-dangle
    this._size = this.getAttribute('size') || 'sm';
    this._position = this.getAttribute('position') || 'left';
    this._status = this.getAttribute('status') || 'hidden';
    this._label = this.getAttribute('label') || 'Cookie settings';
    this._transition = this.getAttribute('transition') || 'fade-in-up';

    this._initIconSlot();
  }

  /**
   * Return the names of the attributes to observe.
   *
   * When an attribute changes, `attributeChangedCallback()` gets fired.
   *
   * @return {string[]} Attributes to observe.
   */
  get observedAttributes() {
    return [
      'size',
      'position',
      'label',
      'status',
      'transition',
    ];
  }

  /**
   * Return the size.
   *
   * @return {string}
   */
  get size() {
    return this._size || '';
  }

  /**
   * Setter, so we can keep it in sync with the `size` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set size(value) {
    this._size = value;

    this._update();
    this.setAttribute('size', this._size);
  }

  /**
   * Return the position.
   *
   * @return {string}
   */
  get position() {
    return this._position || '';
  }

  /**
   * Setter, so we can keep it in sync with the `position` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set position(value) {
    this._position = value;

    this._update();
    this.setAttribute('position', this._position);
  }

  /**
   * Return the label.
   *
   * @return {string}
   */
  get label() {
    return this._label || '';
  }

  /**
   * Setter, so we can keep it in sync with the `label` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set label(value) {
    this._label = value;

    this._update();
    this.setAttribute('label', this._label);
  }

  /**
   * Return the status.
   *
   * @return {string}
   */
  get status() {
    return this._status || '';
  }

  /**
   * Setter, so we can keep it in sync with the `status` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set status(value) {
    this._status = value;

    this._update();
    this.setAttribute('status', this._status);
  }

  /**
   * Return the transition.
   *
   * @return {string}
   */
  get transition() {
    return this._transition || '';
  }

  /**
   * Setter, so we can keep it in sync with the `transition` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set transition(value) {
    this._transition = value;

    this._update();
    this.setAttribute('transition', this._transition);
  }

  /**
   * Add event listeners and update attributes when the component is connected to the DOM.
   *
   * @return {void}
   */
  connectedCallback() {
    this
      .shadowRoot
      .querySelector('.button')
      .addEventListener('click', () => {
        // Immediately show the settings modal when the button is clicked
        window.pressidiumCookieConsent.showSettings(0);
      });

    this._update();
  }

  /**
   * An attribute was added, removed, or changed.
   *
   * @param {string} name     Attribute name.
   * @param {string} oldValue Previous value.
   * @param {string} newValue New value.
   *
   * @return {void}
   */
  attributeChangedCallback(name, oldValue, newValue) {
    switch (name) {
      case 'size':
        this._size = newValue;
        break;
      case 'position':
        this._position = newValue;
        break;
      case 'label':
        this._label = newValue;
        break;
      case 'status':
        this._status = newValue;
        break;
      case 'transition':
        this._transition = newValue;
        break;
      default:
        break;
    }
  }

  /**
   * Update the size of the button.
   *
   * @return {void}
   */
  _updateSize() {
    const button = this.shadowRoot.querySelector('.button');

    if (!button) {
      return;
    }

    const sizes = ['sm', 'lg'];

    sizes.forEach((size) => button.classList.remove(size));
    button.classList.add(sizes.includes(this._size) ? this._size : 'sm');
  }

  /**
   * Update the position of the button.
   *
   * @return {void}
   */
  _updatePosition() {
    const positions = ['left', 'right'];

    positions.forEach((position) => this.classList.remove(position));
    this.classList.add(positions.includes(this._position) ? this._position : 'left');
  }

  /**
   * Update the transition of the button.
   *
   * @return {void}
   */
  _updateTransition() {
    const button = this.shadowRoot.querySelector('.button');

    if (!button) {
      return;
    }

    const transitions = [
      'fade-in',
      'fade-in-up',
      'fade-in-zoom',
      'zoom-in',
      'slide-in-horizontal',
      'slide-in-vertical',
    ];

    transitions.forEach((transition) => button.classList.remove(transition));

    if (transitions.includes(this._transition)) {
      button.classList.add(this._transition);
    }
  }

  /**
   * Update the a11y label of the button.
   *
   * Used by screen readers and other assistive technologies.
   *
   * @return {void}
   */
  _updateLabel() {
    /*
     * This has to be set to the host element, not the button.
     * Otherwise, it won't be read by some screen readers.
     */
    this.setAttribute('aria-label', this._label);
    this.setAttribute('role', 'button');
  }

  /**
   * Update the status of the button.
   *
   * @return {void}
   */
  _updateStatus() {
    const button = this.shadowRoot.querySelector('.button');

    if (!button) {
      return;
    }

    const statuses = ['visible', 'hidden'];

    statuses.forEach((status) => button.classList.remove(status));
    button.classList.add(statuses.includes(this._status) ? this._status : 'hidden');
  }

  /**
   * Initialize the icon slot.
   *
   * @return {void}
   */
  _initIconSlot() {
    const slot = this.shadowRoot.querySelector('slot[name="icon"]');

    slot.addEventListener('slotchange', () => {
      const [svg] = slot.assignedNodes();

      svg.setAttribute('aria-hidden', 'true');
      svg.setAttribute('focusable', 'false');
    });
  }

  /**
   * Update the shadow DOM to reflect the inner state of this component.
   *
   * @return {void}
   */
  _update() {
    this._updateSize();
    this._updatePosition();
    this._updateTransition();
    this._updateLabel();
    this._updateStatus();
  }
}

export default FloatingButton;
