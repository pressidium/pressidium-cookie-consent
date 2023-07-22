/* eslint-disable no-underscore-dangle, class-methods-use-this */

const template = document.createElement('template');
template.innerHTML = `
  <style>
    :host {
      display: block;
    }
    
    .dialog {
      width: 100%;
      max-width: 570px;
      padding: 0;
    
      background-color: #ffffff;
      border: none;
      border-radius: 2px;
      box-shadow: 0 0 34px 0 rgba(0, 0, 0, 0.5);
    }
    
    .wrapper {
      width: 100%;
      height: 100%;
      padding: 32px 40px;
  
      box-sizing: border-box;
  
      display: flex;
      flex-direction: column;
    }
    
    .title {
      font-weight: 600;
      font-size: 1.7rem;
      line-height: 2.25rem;
      color: #000;
  
      margin: 0;
      padding: 0;
      
      display: none;
    }
  
    .description {
      font-weight: 400;
      font-size: 0.9rem;
      line-height: 1.625;
      color: rgba(0, 0, 0, 0.88);
  
      margin: 4px 0 16px 0;
      padding: 0;
      
      display: none;
    }
  </style>
  <dialog class="dialog">
    <div class="wrapper" data-wrapper>
      <h3 class="title"></h3>
      <p class="description"></p>
      <div class="content">
        <slot name="content"></slot>
      </div>
    </div>
  </dialog>
`;

/**
 * Whether the browser supports the <dialog> element.
 *
 * @return {boolean}
 */
const isDialogSupported = () => typeof HTMLDialogElement === 'function';

class Dialog extends HTMLElement {
  /**
   * Dialog constructor.
   */
  constructor() {
    super();

    if (!isDialogSupported()) {
      // eslint-disable-next-line no-console
      console.warn('Browser does not support the <dialog> element');
      return;
    }

    // A `ref` property to reference the inner dialog element
    this._ref = null;

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(template.content.cloneNode(true));

    this._title = this.getAttribute('title') || null;
    this._description = this.getAttribute('description') || null;

    const slot = this.shadowRoot.querySelector('slot[name="content"]');
    slot.addEventListener('slotchange', () => {
      const [content] = slot.assignedNodes();

      // TODO: Improve this a bit
      if (content) {
        const children = content.children || [];
        [...children].forEach((child) => {
          if (child.hasAttribute('data-close')) {
            child.addEventListener('click', () => {
              this.close();
            });
          }
        });
      }
    });

    this._update();
  }

  /**
   * Return the names of the attributes to observe.
   *
   * When an attribute changes, `attributeChangedCallback()` gets fired.
   *
   * @return {string[]} Attributes to observe.
   */
  get observedAttributes() {
    return ['title', 'description'];
  }

  /**
   * Return the title.
   *
   * @return {string}
   */
  get title() {
    return this._title || '';
  }

  /**
   * Setter, so we can keep it in sync with the `title` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set title(value) {
    this._title = value;

    this._update();
    this.setAttribute('title', this._title);
  }

  /**
   * Return the description.
   *
   * @return {string}
   */
  get description() {
    return this._description || '';
  }

  /**
   * Setter, so we can keep it in sync with the `description` attribute.
   *
   * @param {string} value Value to set the property to.
   */
  set description(value) {
    this._description = value;

    this._update();
    this.setAttribute('description', this._description);
  }

  /**
   * Return a reference to the inner dialog element.
   *
   * @return {HTMLDialogElement}
   */
  get ref() {
    return this._ref;
  }

  /**
   * Update the shadow DOM to reflect the inner state of this component.
   *
   * @private
   *
   * @return {void}
   */
  _update() {
    const titleElement = this.shadowRoot.querySelector('.title');
    const descriptionElement = this.shadowRoot.querySelector('.description');

    if (this._title) {
      titleElement.style.display = 'block';
      titleElement.textContent = this._title;
    }

    if (this._description) {
      descriptionElement.style.display = 'block';
      descriptionElement.textContent = this._description;
    }
  }

  /**
   * Store a reference to the inner dialog element.
   *
   * Lifecycle method.
   * This is called when the custom element is first connected into the DOM.
   *
   * @return {void}
   */
  connectedCallback() {
    this._ref = this.shadowRoot.querySelector('dialog');

    const dialog = this.shadowRoot.querySelector('.dialog');
    const wrapper = this.shadowRoot.querySelector('.wrapper');

    // Close the dialog when clicking outside of it
    dialog.addEventListener('click', () => dialog.close());
    wrapper.addEventListener('click', (e) => e.stopPropagation());
  }

  /**
   * An attribute was added, removed, or changed.
   *
   * Lifecycle method.
   *
   * @param {string} name     Attribute name.
   * @param {string} oldValue Previous value.
   * @param {string} newValue New value.
   *
   * @return {void}
   */
  attributeChangedCallback(name, oldValue, newValue) {
    switch (name) {
      case 'title':
        this._title = newValue;
        break;
      case 'description':
        this._description = newValue;
        break;
      default:
        break;
    }
  }

  /**
   * Close the dialog.
   *
   * @return {void}
   */
  close() {
    this.ref.close();
  }

  /**
   * Display the dialog modelessly.
   *
   * This still allows interaction with content outside the dialog.
   *
   * @return {void}
   */
  show() {
    this.ref.show();
  }

  /**
   * Display the dialog as a modal.
   *
   * This display the dialog over the top of any other dialogs that might
   * be present. Everything outside the dialog are inert with interactions
   * outside the dialog being blocked.
   *
   * @return {void}
   */
  showModal() {
    this.ref.showModal();
  }
}

export default Dialog;
