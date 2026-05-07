import {
  useState,
  useEffect,
  useContext,
  useCallback,
} from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  ExternalLink,
  Spinner,
  Notice,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import styled from 'styled-components';

import SettingsContext from '../../store/context';
import * as ActionTypes from '../../store/actionTypes';

import Badge from '../Badge';

const StyledNotice = styled(Notice)`
    width: 100%;
    margin: -16px -16px 4px !important;
`;

function TagGatewayTab() {
  const { state, dispatch } = useContext(SettingsContext);

  const [isCheckingHealth, setIsCheckingHealth] = useState(false);
  const [isHealthy, setIsHealthy] = useState(null);

  const [properSetupNoticeDismissed, setProperSetupNoticeDismissed] = useState(false);
  const [measurementPathNoticeDismissed, setMeasurementPathNoticeDismissed] = useState(false);

  const dismissProperSetupNotice = useCallback(() => {
    setProperSetupNoticeDismissed(true);
  }, []);

  const dismissMeasurementPathNotice = useCallback(() => {
    setMeasurementPathNoticeDismissed(true);
  }, []);

  const onTagGatewaySettingChange = useCallback((key, value) => {
    dispatch({
      type: ActionTypes.UPDATE_TAG_GATEWAY_SETTING,
      payload: {
        key,
        value,
      },
    });
  }, []);

  const isTagGatewayHealthy = useCallback(async () => {
    const response = await fetch('/pressidium-cookie-consent-metrics/healthy', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    return response.ok;
  }, []);

  useEffect(() => {
    (async () => {
      setIsCheckingHealth(true);

      try {
        setIsHealthy(await isTagGatewayHealthy());
      } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Could not fetch Google tag gateway health status', error);

        setIsHealthy(false);
      }

      setIsCheckingHealth(false);
    })();
  }, []);

  return (
    <Panel>
      <PanelBody initialOpen>
        {!properSetupNoticeDismissed ? (
          <PanelRow>
            <StyledNotice
              status="info"
              politeness="polite"
              onDismiss={dismissProperSetupNotice}
            >
              {__('We recommend setting up the Google tag gateway via your CDN or load balancer for best performance. If that’s not possible, you can enable our one-click PHP proxy as a quick and easy alternative.', 'pressidium-cookie-consent')}
            </StyledNotice>
          </PanelRow>
        ) : null}
        {!measurementPathNoticeDismissed && state.pressidiumOptions.googleTagGateway.proxyEnabled ? (
          <PanelRow>
            <StyledNotice
              status="info"
              politeness="polite"
              onDismiss={dismissMeasurementPathNotice}
            >
              {__('Remember to update the Google tag or Google Tag Manager scripts on the website to use the following measurement path:', 'pressidium-cookie-consent')}
              <code>/pressidium-cookie-consent-metrics</code>
            </StyledNotice>
          </PanelRow>
        ) : null}
        <PanelRow>
          <Flex direction="column" gap={0}>
            <FlexItem>
              <ToggleControl
                label={__('Google tag gateway proxy', 'pressidium-cookie-consent')}
                help={state.pressidiumOptions.googleTagGateway.proxyEnabled
                  ? __('Will route traffic to Google tag gateway', 'pressidium-cookie-consent')
                  : __('Won\'t route traffic to Google tag gateway', 'pressidium-cookie-consent')}
                checked={state.pressidiumOptions.googleTagGateway.proxyEnabled}
                className="pressidium-toggle-control"
                onChange={(value) => onTagGatewaySettingChange('proxy_enabled', value)}
              />
            </FlexItem>
            <FlexItem>
              <ExternalLink href="https://developers.google.com/tag-platform/tag-manager/gateway/setup-guide">
                {__('Learn more about Google tag gateway', 'pressidium-cookie-consent')}
              </ExternalLink>
            </FlexItem>
          </Flex>
        </PanelRow>
        <PanelRow>
          <Flex
            direction="column"
            gap={0}
            style={{
              opacity: state.pressidiumOptions.googleTagGateway.proxyEnabled ? 1.0 : 0.4,
            }}
          >
            <FlexItem>
              <TextControl
                label={__('Google tag ID', 'pressidium-cookie-consent')}
                help={__('A Google tag ID is an identifier to load a given Google tag', 'pressidium-cookie-consent')}
                className="pressidium-text-control"
                placeholder="G-12345"
                value={state.pressidiumOptions.googleTagGateway.gtagId}
                onChange={(value) => onTagGatewaySettingChange('gtag_id', value)}
                disabled={!state.pressidiumOptions.googleTagGateway.proxyEnabled}
              />
            </FlexItem>
            <FlexItem>
              <ExternalLink href="https://support.google.com/tagmanager/answer/15107467">
                {__('Find your Google tag ID', 'pressidium-cookie-consent')}
              </ExternalLink>
            </FlexItem>
          </Flex>
        </PanelRow>
        <PanelRow>
          {isCheckingHealth ? (
            <Spinner />
          ) : (
            <Badge
              label={__('Status', 'pressidium-cookie-consent')}
              value={isHealthy
                ? __('Healthy', 'pressidium-cookie-consent')
                : __('Unreachable', 'pressidium-cookie-consent')}
              help={isHealthy
                ? __('The Google tag gateway is reachable. Will route traffic through it.', 'pressidium-cookie-consent')
                : __('The Google tag gateway is not reachable. Please check your configuration.', 'pressidium-cookie-consent')}
              status={isHealthy ? 'success' : 'warning'}
            />
          )}
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default TagGatewayTab;
