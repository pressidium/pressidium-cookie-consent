import { useState, useEffect, useCallback } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  Spinner,
  Button,
} from '@wordpress/components';
import {
  copy as CopyIcon,
} from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import LogView from '../LogView';
import ClearLogsModal from '../ClearLogsModal';

function LogsTab() {
  const [logs, setLogs] = useState('');
  const [isFetching, setIsFetching] = useState(false);
  const [isClearing, setIsClearing] = useState(false);
  const [isClearLogsModalOpen, setIsClearLogsModalOpen] = useState(false);

  const openClearLogsModal = useCallback(() => setIsClearLogsModalOpen(true), []);
  const closeClearLogsModal = useCallback(() => setIsClearLogsModalOpen(false), []);

  const fetchLogs = async () => {
    const { logs_route: route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: `${route}?nonce=${nonce}`,
      method: 'GET',
    };

    const response = await apiFetch(options);

    if (!('success' in response) || !response.success || !('data' in response)) {
      // Failed to fetch logs, bail early
      // eslint-disable-next-line no-console
      console.error('Error fetching logs', response);
      throw new Error('Invalid response while fetching logs');
    }

    const { data } = response;

    return data;
  };

  const updateLogs = async () => {
    setIsFetching(true);

    try {
      const data = await fetchLogs();
      setLogs(data);
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error('Could not fetch logs', error);
    }

    setIsFetching(false);
  };

  const clearLogs = async () => {
    const { logs_route: route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: route,
      method: 'DELETE',
      data: {
        nonce,
      },
    };

    try {
      await apiFetch(options);
      await updateLogs();
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error('Error clearing logs', error);
    }
  };

  useEffect(() => {
    let interval = null;

    (async () => {
      interval = setInterval(updateLogs, 3000);
      await updateLogs();
    })();

    return () => {
      setLogs('');
      setIsFetching(false);

      if (interval !== null) {
        clearInterval(interval);
      }
    };
  }, []);

  const copyToClipboard = useCallback(async () => {
    try {
      await navigator.clipboard.writeText(logs);
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error('Could not copy logs to clipboard', error);
    }
  }, [logs]);

  const onClearLogs = useCallback(() => {
    (async () => {
      setIsClearing(true);

      await clearLogs();

      setIsClearing(false);
    })();
  }, [clearLogs]);

  return (
    <Panel>
      <PanelBody initialOpen>
        <PanelRow>
          <p>
            {__('In this section, you’ll find vital debugging logs. If you encounter any issues, open an issue and include these logs for reference in our investigation.', 'pressidium-cookie-consent')}
          </p>
        </PanelRow>
        <PanelRow>
          <Flex
            direction="column"
            gap={4}
            style={{ width: '100%' }}
          >
            <FlexItem>
              <LogView logs={logs} />
            </FlexItem>
            <FlexItem>
              <Flex justify="flex-start">
                <FlexItem>
                  <Button
                    variant="secondary"
                    icon={CopyIcon}
                    onClick={copyToClipboard}
                    style={{ paddingRight: '10px' }}
                    disabled={logs === '' || logs === null}
                  >
                    {__('Copy to clipboard', 'pressidium-cookie-consent')}
                  </Button>
                </FlexItem>
                <FlexItem>
                  <Button
                    variant="secondary"
                    className="is-destructive"
                    onClick={openClearLogsModal}
                    disabled={isClearing}
                    isBusy={isClearing}
                  >
                    {__('Clear logs', 'pressidium-cookie-consent')}
                  </Button>
                  <ClearLogsModal
                    isOpen={isClearLogsModalOpen}
                    onClose={closeClearLogsModal}
                    clearLogs={onClearLogs}
                  />
                </FlexItem>
                {isFetching ? (
                  <FlexItem>
                    <Spinner />
                  </FlexItem>
                ) : null}
              </Flex>
            </FlexItem>
          </Flex>
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default LogsTab;
