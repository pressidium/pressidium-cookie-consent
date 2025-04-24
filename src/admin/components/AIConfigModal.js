import {
  useState,
  useEffect,
  useCallback,
  useMemo,
} from '@wordpress/element';
import {
  Flex,
  FlexItem,
  Modal,
  TextControl,
  SelectControl,
  ExternalLink,
  Button,
  Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
  linkOff as LogoutIcon,
  login as LoginIcon,
} from '@wordpress/icons';

import { useAI } from '../hooks/ai';

/**
 * List of AI providers for the select control.
 *
 * @type {{label: string, value: string}[]}
 */
const AI_PROVIDERS = [
  {
    label: 'OpenAI',
    value: 'openai',
  },
  {
    label: 'Gemini',
    value: 'gemini',
  },
];

/**
 * Mapping of AI providers to their API key generation URLs.
 *
 * @type {object}
 */
const apiKeyUrlMapping = {
  openai: 'https://platform.openai.com/account/api-keys',
  gemini: 'https://ai.google.dev/gemini-api/docs/api-key',
};

/**
 * Mapping of AI providers to their model documentation URLs.
 *
 * @type {object}
 */
const modelUrlMapping = {
  openai: 'https://platform.openai.com/docs/models',
  gemini: 'https://ai.google.dev/gemini-api/docs/models',
};

function AIConfigModal(props) {
  const {
    isOpen,
    onClose,
    appendNotice = () => {},
    dismissNotice = (id) => {},
  } = props;

  if (!isOpen) {
    return null;
  }

  const {
    updateProvider,
    updateApiKey,
    updateModel,
    fetchSelectedProvider,
    fetchAvailableModels,
    fetchSelectedModel,
  } = useAI();

  const [selectedProvider, setSelectedProvider] = useState(null);
  const [availableModels, setAvailableModels] = useState([]);
  const [selectedModel, setSelectedModel] = useState(null);
  const [apiKey, setApiKey] = useState(null);

  const [isFetching, setIsFetching] = useState(false);
  const [isLoggingIn, setIsLoggingIn] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  /**
   * Fetch all available models and the currently selected model.
   *
   * @type {(() => Promise<void>)}
   */
  const fetchModels = useCallback(async () => {
    setIsFetching(true);

    const availableModelsResponse = await fetchAvailableModels();

    setAvailableModels(availableModelsResponse.success && availableModelsResponse.models
      ? availableModelsResponse.models : []);

    const selectedModelResponse = await fetchSelectedModel();

    setSelectedModel(selectedModelResponse.success && selectedModelResponse.model
      ? selectedModelResponse.model : null);

    setIsFetching(false);

    if (!availableModelsResponse.success && apiKey) {
      appendNotice({
        message: __('Something went wrong while fetching AI models. Is your API key valid?', 'pressidium-cookie-consent'),
        status: 'error',
        id: 'ai-models-not-fetched-error',
      });
    } else {
      dismissNotice('ai-models-not-fetched-error');
    }
  }, [apiKey]);

  /*
   * Fetch the currently selected AI provider when the modal is opened.
   */
  useEffect(() => {
    (async () => {
      setIsFetching(true);

      const response = await fetchSelectedProvider();

      setIsFetching(false);

      if (!response.success || !response.provider) {
        // If the provider is not set, default to OpenAI
        setSelectedProvider('openai');
        return;
      }

      setSelectedProvider(response.provider);
    })();
  }, []);

  /*
   * Fetch all available models and the currently selected model when the provider changes.
   * We need to fetch the models again because each provider has its own models.
   */
  useEffect(() => {
    (async () => {
      await fetchModels();
    })();
  }, [selectedProvider]);

  /**
   * Send a request to update the currently selected AI provider when the user changes it.
   *
   * @type {() => Promise<void>}
   */
  const onChangeProvider = useCallback(async (value) => {
    const response = await updateProvider(value);

    if (!response.success) {
      appendNotice({
        message: response.error || __('Could not update AI provider.', 'pressidium-cookie-consent'),
        status: 'error',
        id: 'ai-provider-not-updated-error',
      });

      return;
    }

    setSelectedProvider(value);
  }, []);

  /**
   * Update the API key state when the user types in the text field.
   *
   * @type {() => void}
   */
  const onChangeApiKey = useCallback((value) => {
    setApiKey(value);
  }, []);

  /**
   * Send a request to remove the API key, logging the user out of the AI provider.
   *
   * @type {() => Promise<void>}
   */
  const onLogout = useCallback(async () => {
    setIsLoggingOut(true);

    const response = await updateApiKey('');

    if (!response.success) {
      appendNotice({
        message: response.error || __('Could not remove API key.', 'pressidium-cookie-consent'),
        status: 'error',
        id: 'ai-api-key-not-removed-error',
      });

      setIsLoggingOut(false);
      return;
    }

    setApiKey(null);
    setAvailableModels([]);
    setSelectedModel(null);

    setIsLoggingOut(false);
  }, []);

  /**
   * Send a request to update the API key when the user clicks the "Add API key" button.
   *
   * @type {() => Promise<void>}
   */
  const onSaveApiKey = useCallback(async () => {
    setIsLoggingIn(true);

    const response = await updateApiKey(apiKey);

    if (!response.success) {
      appendNotice({
        message: response.error || __('Could not update API key.', 'pressidium-cookie-consent'),
        status: 'error',
        id: 'ai-api-key-not-updated-error',
      });

      return;
    }

    setApiKey(apiKey);

    await fetchModels();

    setIsLoggingIn(false);
  }, [apiKey]);

  /**
   * Send a request to update the currently selected AI model when the user changes it.
   *
   * @type {() => Promise<void>}
   */
  const onChangeModel = useCallback(async (value) => {
    const response = await updateModel(value);

    if (!response.success) {
      appendNotice({
        message: response.error || __('Could not update AI model.', 'pressidium-cookie-consent'),
        status: 'error',
        id: 'ai-model-not-updated-error',
      });

      return;
    }

    setSelectedModel(value);
  }, []);

  const generateApiKeyUrl = useMemo(
    () => apiKeyUrlMapping[selectedProvider] || 'https://github.com/pressidium/pressidium-cookie-consent/wiki/AI-Integration#Generate-API-key',
    [selectedProvider],
  );

  const learnMoreAboutModelsUrl = useMemo(
    () => modelUrlMapping[selectedProvider] || 'https://github.com/pressidium/pressidium-cookie-consent/wiki/AI-Integration#Models',
    [selectedProvider],
  );

  return (
    <Modal
      title={__('AI configuration', 'pressidium-cookie-consent')}
      onRequestClose={onClose}
    >
      <Flex direction="column" gap={4}>
        <FlexItem>
          <p style={{ maxWidth: '400px' }}>
            {__('You can connect to an AI provider to use models for generating content. Choose a provider to get started.', 'pressidium-cookie-consent')}
          </p>
        </FlexItem>
        {!isFetching && selectedProvider ? (
          <FlexItem>
            <Flex direction="column" gap={0}>
              <FlexItem>
                <SelectControl
                  label={__('AI provider', 'pressidium-cookie-consent')}
                  value={selectedProvider}
                  options={AI_PROVIDERS}
                  onChange={onChangeProvider}
                  className="pressidium-select-control"
                />
              </FlexItem>
              <FlexItem>
                <ExternalLink href="https://github.com/pressidium/pressidium-cookie-consent/wiki/AI-Integration">
                  {__('Learn more about the AI features of this plugin', 'pressidium-cookie-consent')}
                </ExternalLink>
              </FlexItem>
            </Flex>
          </FlexItem>
        ) : null}

        {!isFetching && availableModels.length === 0 ? (
          <Flex direction="column" gap={4}>
            <FlexItem>
              <Flex direction="column" gap={0}>
                <FlexItem>
                  <TextControl
                    label={__('API key', 'pressidium-cookie-consent')}
                    className="pressidium-text-control"
                    value={apiKey}
                    onChange={onChangeApiKey}
                  />
                </FlexItem>
                <FlexItem>
                  <ExternalLink href={generateApiKeyUrl}>
                    {__('Generate your API key', 'pressidium-cookie-consent')}
                  </ExternalLink>
                </FlexItem>
              </Flex>
            </FlexItem>
            <FlexItem>
              <Button
                variant="secondary"
                icon={LoginIcon}
                onClick={onSaveApiKey}
                disabled={isLoggingIn || !apiKey}
                isBusy={isLoggingIn}
              >
                {__('Add API key', 'pressidium-cookie-consent')}
              </Button>
            </FlexItem>
          </Flex>
        ) : null}

        {!isFetching && availableModels.length > 0 ? (
          <Flex direction="column" gap={4}>
            <FlexItem>
              <Button
                variant="secondary"
                icon={LogoutIcon}
                onClick={onLogout}
                disabled={isLoggingOut}
                isBusy={isLoggingOut}
              >
                {__('Remove API key', 'pressidium-cookie-consent')}
              </Button>
            </FlexItem>
            <FlexItem>
              <Flex direction="column" gap={0}>
                <FlexItem>
                  <SelectControl
                    label={__('Model', 'pressidium-cookie-consent')}
                    value={selectedModel}
                    options={availableModels.map(({ id, name }) => ({ label: name, value: id }))}
                    onChange={onChangeModel}
                    className="pressidium-select-control"
                  />
                </FlexItem>
                <FlexItem>
                  <ExternalLink href={learnMoreAboutModelsUrl}>
                    {__('Explore available models and compare their capabilities', 'pressidium-cookie-consent')}
                  </ExternalLink>
                </FlexItem>
              </Flex>
            </FlexItem>
          </Flex>
        ) : null}

        {isFetching ? (
          <Flex justify="center">
            <FlexItem>
              <Spinner />
            </FlexItem>
          </Flex>
        ) : null}

        <FlexItem>
          <p style={{ maxWidth: '400px' }} className="pressidium-fine-print">
            {__('AI-generated translations and cookie descriptions may occasionally be inaccurate or misleading. Always review and verify the output.', 'pressidium-cookie-consent')}
          </p>
        </FlexItem>
      </Flex>
    </Modal>
  );
}

export default AIConfigModal;
