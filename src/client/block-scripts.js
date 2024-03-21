((blockedScripts, cookieName) => {
  const getCookie = (name, filter, obtainValue) => {
    let found;

    if (filter === 'one') {
      found = document.cookie.match(`(^|;)\\s*${name}\\s*=\\s*([^;]+)`);
      found = found ? (obtainValue ? found.pop() : name) : '';

      if (found && name === cookieName) {
        try {
          found = JSON.parse(found);
        } catch (e) {
          try {
            found = JSON.parse(decodeURIComponent(found));
          } catch (error) {
            // Cookie value is not a valid JSON string
            found = {};
          }
        }
        found = JSON.stringify(found);
      }
    } else if (filter === 'all') {
      // Array of names of all existing cookies
      const cookies = document.cookie.split(/;\s*/);
      found = [];

      for (let i = 0; i < cookies.length; i += 1) {
        found.push(cookies[i].split('=')[0]);
      }
    }

    return found;
  };

  const isCategoryAllowed = (cookieCategory) => {
    const allowedCategories = JSON.parse(getCookie(cookieName, 'one', true) || '{}').categories || [];
    return allowedCategories.indexOf(cookieCategory) > -1;
  };

  if (isCategoryAllowed('analytics')
    && isCategoryAllowed('targeting')
    && isCategoryAllowed('preferences')) {
    // All categories are allowed, bail early
    return;
  }

  if (!Array.isArray(blockedScripts) || blockedScripts.length === 0) {
    // No blocked scripts, bail early
    return;
  }

  const getBlockedScriptCategory = (src) => {
    const blockedScript = blockedScripts.find((item) => {
      if (item.is_regex) {
        const regex = new RegExp(item.src, 'i');
        return regex.test(src);
      }

      return item.src === src;
    });

    if (blockedScript) {
      return blockedScript.category;
    }

    return null;
  };

  // Firefox workaround
  const beforeScriptExecute = (node) => {
    const handler = (event) => {
      const category = getBlockedScriptCategory(node.src || '');
      if (!isCategoryAllowed(category)) {
        event.preventDefault();
      }

      node.removeEventListener('beforescriptexecute', handler);
    };

    return handler;
  };

  const existingScripts = document.querySelectorAll('script');
  existingScripts.forEach((node) => {
    const category = getBlockedScriptCategory(node.src || '');

    if (category !== null && !node.hasAttribute('data-pressidium-cc-no-block')) {
      if (isCategoryAllowed(category)) {
        // Category is allowed, bail early
        return;
      }

      node.setAttribute('type', 'text/plain');
      node.setAttribute('data-cookiecategory', category);
    }
  });

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.type !== 'childList') {
        return;
      }

      mutation.addedNodes.forEach((node) => {
        const tagName = node.tagName || '';

        if (tagName.toLowerCase() !== 'script') {
          return;
        }

        const category = getBlockedScriptCategory(node.src || '');

        if (category !== null && !node.hasAttribute('data-pressidium-cc-no-block')) {
          if (isCategoryAllowed(category)) {
            // Category is allowed, bail early
            return;
          }

          node.setAttribute('type', 'text/plain');
          node.setAttribute('data-cookiecategory', category);

          node.addEventListener('beforescriptexecute', beforeScriptExecute(node));
        }
      });
    });
  });

  observer.observe(document.documentElement, {
    childList: true,
    subtree: true,
  });

  // Monkey patch `document.createElement()` to intercept script tags
  const originalCreateElement = document.createElement;

  // eslint-disable-next-line func-names
  document.createElement = function (...args) {
    const tagName = args[0];

    if (tagName.toLowerCase() !== 'script') {
      return originalCreateElement.bind(document)(...args);
    }

    const element = originalCreateElement.bind(document)(...args);
    const category = getBlockedScriptCategory(element.src || '');

    if (category !== null && !element.hasAttribute('data-pressidium-cc-no-block')) {
      if (isCategoryAllowed(category)) {
        // Category is allowed, bail early
        return element;
      }

      element.setAttribute('type', 'text/plain');
      element.setAttribute('data-cookiecategory', category);
    }

    Object.defineProperties(element, {
      src: {
        get() {
          return element.getAttribute('src');
        },
        set(value) {
          element.setAttribute('src', value);

          const cat = getBlockedScriptCategory(value || '');

          if (cat !== null && !element.hasAttribute('data-pressidium-cc-no-block')) {
            if (isCategoryAllowed(cat)) {
              // Category is allowed, bail early
              return;
            }

            element.setAttribute('type', 'text/plain');
            element.setAttribute('data-cookiecategory', cat);
          }
        },
      },
    });

    return element;
  };
})(window.pressidiumCCBlockedScripts || [], window.pressidiumCCCookieName || '');
