import { useContext } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

import { AIContext } from './context';

// eslint-disable-next-line import/prefer-default-export
export const useAI = () => {
  const ctx = useContext(AIContext);

  /**
   * Send a request to the API.
   *
   * This function is a wrapper around the `apiFetch()` WordPress function.
   *
   * It constructs the request URL using the provided path and adds the necessary
   * query parameter for the nonce for `GET` requests. Or it adds the nonce to
   * the request body for `POST` requests.
   *
   * It also throws an error if the request fails or if the response does not
   * contain a `success` property with a value of `true`. So, it allows us to
   * handle errors in a single try/catch block.
   *
   * @param {object} options
   * @param {string} options.path   Path to the API endpoint.
   * @param {string} options.method HTTP method to use.
   * @param {string} options.nonce  Nonce to use for the request.
   * @param {object} options.data   Data to send with the request.
   *
   * @return {Promise<object>}
   */
  const apiRequest = async (options) => {
    const response = await apiFetch(options);

    if (!('success' in response) || !response.success) {
      const error = new Error(response.error || '');
      error.code = response.code || null;

      throw error;
    }

    return response;
  };

  /**
   * Return a failed result for the given error.
   *
   * Errors may be thrown by `apiRequest()` or be rejections of `apiFetch()`
   * (e.g. a `WP_Error` response), both of which have `message` and `code` properties.
   *
   * @param {Error|object} error Error thrown while sending the request.
   *
   * @return {{success: false, error: ?string, code: ?string}}
   */
  const failure = (error) => ({
    success: false,
    error: (error && error.message) || null,
    code: (error && error.code) || null,
  });

  /**
   * Send a GET request to the API.
   *
   * This function is a wrapper around the `apiFetch()` WordPress function.
   *
   * It constructs the request URL using the provided path and adds the necessary
   * query parameter for the nonce.
   *
   * It also throws an error if the request fails or if the response does not
   * contain a `success` property with a value of `true`. So, it allows us to
   * handle errors in a single try/catch block.
   *
   * @param {string} path  Path to the API endpoint.
   * @param {string} nonce Nonce to use for the request.
   *
   * @return {Promise<Object>}
   */
  const apiGetRequest = async (path, nonce) => (
    apiRequest({
      path: addQueryArgs(path, { nonce }),
      method: 'GET',
    })
  );

  /**
   * Send a POST request to the API.
   *
   * This function is a wrapper around the `apiFetch()` WordPress function.
   *
   * It adds the nonce to the request body.
   *
   * It also throws an error if the request fails or if the response does not
   * contain a `success` property with a value of `true`. So, it allows us to
   * handle errors in a single try/catch block.
   *
   * @param {string} path      Path to the API endpoint.
   * @param {string} nonce     Nonce to use for the request.
   * @param {object} [data={}] Data to send with the request.
   *
   * @return {Promise<Object>}
   */
  const apiPostRequest = async (path, nonce, data = {}) => (
    apiRequest({
      path,
      method: 'POST',
      data: {
        nonce,
        ...data,
      },
    })
  );

  /**
   * Update the AI provider.
   *
   * @param {string} provider AI provider to use.
   *
   * @return {Promise<{success: true}|{success: false, error: ?string, code: ?string}>}
   */
  const updateProvider = async (provider) => {
    const { provider_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      await apiPostRequest(route, nonce, { provider });

      return { success: true };
    } catch (error) {
      return failure(error);
    }
  };

  /**
   * Update the API key.
   *
   * @param {string} apiKey API key to use.
   *
   * @return {Promise<{success: true}|{success: false, error: ?string, code: ?string}>}
   */
  const updateApiKey = async (apiKey) => {
    const { credentials_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      await apiPostRequest(route, nonce, { api_key: apiKey });

      return { success: true };
    } catch (error) {
      return failure(error);
    }
  };

  const updateModel = async (model) => {
    const { model_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      await apiPostRequest(route, nonce, { model });

      return { success: true };
    } catch (error) {
      return failure(error);
    }
  };

  /**
   * Return the currently selected AI provider.
   *
   * @return {Promise<{success: true, provider}|{success: false, error: ?string, code: ?string}>}
   */
  const fetchSelectedProvider = async () => {
    const { provider_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      const response = await apiGetRequest(route, nonce);

      return { success: true, provider: response.provider };
    } catch (error) {
      return failure(error);
    }
  };

  /**
   * Return all AI models available for the currently selected provider.
   *
   * @return {Promise<{success: true, models}|{success: false, error: ?string, code: ?string}>}
   */
  const fetchAvailableModels = async () => {
    const { models_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      const response = await apiGetRequest(route, nonce);

      return { success: true, models: response.models };
    } catch (error) {
      return failure(error);
    }
  };

  /**
   * Return the currently selected AI model.
   *
   * @return {Promise<{success: true, model}|{success: false, error: ?string, code: ?string}>}
   */
  const fetchSelectedModel = async () => {
    const { model_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      const response = await apiGetRequest(route, nonce);

      return { success: true, model: response.model };
    } catch (error) {
      return failure(error);
    }
  };

  /**
   * Generate a translation for the given text and destination language.
   *
   * @param {string} text Text to translate.
   * @param {string} lang Language to translate to.
   *
   * @return {Promise<{success: true, translation}|{success: false, error: ?string, code: ?string}>}
   */
  const generateTranslation = async (text, lang) => {
    if (ctx.isGenerating) {
      // Already generating using AI, return the original text
      return {
        success: false,
        error: 'Please wait until the running generation is finished',
      };
    }

    ctx.startGenerating();

    const { translate_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      const response = await apiPostRequest(route, nonce, { text, lang });

      ctx.stopGenerating();

      return { success: true, translation: response.translation };
    } catch (error) {
      ctx.stopGenerating();

      return failure(error);
    }
  };

  /**
   * Generate a translation for the given object and destination language.
   *
   * @param {object} object Object to translate its values.
   * @param {string} lang   Language to translate to.
   *
   * @return {Promise<{success: true, translation}|{success: false, error: ?string, code: ?string}>}
   */
  const generateTranslationForObject = async (object, lang) => {
    if (ctx.isGenerating) {
      // Already generating using AI, return the original text
      return {
        success: false,
        error: 'Please wait until the running generation is finished',
      };
    }

    ctx.startGenerating();

    const { translate_all_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      const response = await apiPostRequest(
        route,
        nonce,
        { json: JSON.stringify(object), lang },
      );

      ctx.stopGenerating();

      return { success: true, translation: response.translation };
    } catch (error) {
      ctx.stopGenerating();

      return failure(error);
    }
  };

  /**
   * Generate a cookie description for the given cookie name.
   *
   * @param {string} cookieName Name of the cookie to describe.
   *
   * @return {Promise<{success: true, translation}|{success: false, error: ?string, code: ?string}>}
   */
  const generateCookieDescription = async (cookieName) => {
    if (ctx.isGenerating) {
      // Already generating using AI, bail early
      return {
        success: false,
        error: 'Please wait until the running generation is finished',
      };
    }

    ctx.startGenerating();

    const { cookie_description_route: route, nonce } = pressidiumCCAdminDetails.api;

    try {
      const response = await apiPostRequest(route, nonce, { cookie_name: cookieName });

      ctx.stopGenerating();

      return { success: true, description: response.description };
    } catch (error) {
      ctx.stopGenerating();

      return failure(error);
    }
  };

  return {
    isGenerating: ctx.isGenerating,
    updateProvider,
    updateApiKey,
    updateModel,
    fetchSelectedProvider,
    fetchAvailableModels,
    fetchSelectedModel,
    generateTranslation,
    generateTranslationForObject,
    generateCookieDescription,
  };
};
