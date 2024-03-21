import { useState, useEffect, useCallback } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  Spinner, Button,
} from '@wordpress/components';
import { download as DownloadIcon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import styled from 'styled-components';

import Table, {
  Column,
  Header,
  Row,
  Pagination,
} from '../Table';
import ClearRecordsModal from '../ClearRecordsModal';

const LineWrapColumn = styled(Column)`
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

function ConsentRecordsTab({ isExportingCsv, exportConsentRecords, clearRecords }) {
  const [consentRecords, setConsentRecords] = useState([]);
  const [isFetching, setIsFetching] = useState(false);
  const [isClearing, setIsClearing] = useState(false);
  const [isClearRecordsModalOpen, setIsClearRecordsModalOpen] = useState(false);
  const [page, setPage] = useState(1);

  const [totalConsentRecords, setTotalConsentRecords] = useState(0);
  const [totalPages, setTotalPages] = useState(0);

  const openClearRecordsModal = useCallback(() => setIsClearRecordsModalOpen(true), []);
  const closeClearRecordsModal = useCallback(() => setIsClearRecordsModalOpen(false), []);

  const fetchConsentRecords = async () => {
    const { consents_route: route, nonce } = pressidiumCCAdminDetails.api;

    const options = {
      path: addQueryArgs(route, { nonce, page, per_page: 10 }),
      method: 'GET',
      parse: false, // required to get the headers
    };

    const response = await apiFetch(options);
    const parsedResponse = await response.json();

    if (!('success' in parsedResponse) || !parsedResponse.success || !('data' in parsedResponse)) {
      // Failed to fetch consent records, bail early
      // eslint-disable-next-line no-console
      console.error('Error fetching consent records', parsedResponse);
      throw new Error('Invalid response while fetching consent records');
    }

    const { headers } = response;

    if (!headers.has('X-WP-Total') || !headers.has('X-WP-TotalPages')) {
      // Failed to fetch consent records, bail early
      // eslint-disable-next-line no-console
      console.error('No pagination headers found', headers);
      throw new Error('No pagination headers found while fetching consent records');
    }

    return {
      data: parsedResponse.data,
      headers,
    };
  };

  useEffect(() => {
    (async () => {
      setIsFetching(true);

      try {
        const { data, headers } = await fetchConsentRecords();

        setConsentRecords(data);
        setTotalConsentRecords(parseInt(headers.get('X-WP-Total'), 10));
        setTotalPages(parseInt(headers.get('X-WP-TotalPages'), 10));
      } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Could not fetch consent records', error);
      }

      setIsFetching(false);
    })();
  }, [page]);

  const onClearRecords = useCallback(() => {
    (async () => {
      setIsClearing(true);

      await clearRecords();

      setIsClearing(false);
    })();
  }, [clearRecords]);

  if (consentRecords.length === 0) {
    return (
      <Panel>
        <PanelBody initialOpen>
          <PanelRow>
            <p>
              {__('There are no consent records yet.', 'pressidium-cookie-consent')}
            </p>
          </PanelRow>
        </PanelBody>
      </Panel>
    );
  }

  return (
    <Panel>
      <PanelBody initialOpen>
        <PanelRow>
          <p>
            {__('Here you’ll find all the consent records that have been collected by the plugin.', 'pressidium-cookie-consent')}
          </p>
        </PanelRow>
        <PanelRow>
          <Flex direction="column" style={{ maxWidth: '100%' }}>
            <FlexItem style={{ overflowX: 'scroll' }}>
              <Table>
                <Header>
                  <Column style={{ minWidth: '160px' }}>
                    {__('Date', 'pressidium-cookie-consent')}
                  </Column>
                  <Column style={{ minWidth: '280px' }}>
                    {__('ID', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('URL', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('Location', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('IP address', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('User Agent', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('Necessary consent', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('Analytics consent', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('Targeting consent', 'pressidium-cookie-consent')}
                  </Column>
                  <Column>
                    {__('Preferences consent', 'pressidium-cookie-consent')}
                  </Column>
                  <Column style={{ minWidth: '160px' }}>
                    {__('Recorded at', 'pressidium-cookie-consent')}
                  </Column>
                  <Column style={{ minWidth: '160px' }}>
                    {__('Last update at', 'pressidium-cookie-consent')}
                  </Column>
                </Header>
                {consentRecords.map((consentRecord) => (
                  <Row>
                    <Column style={{ minWidth: '160px' }}>
                      <span>
                        {consentRecord.consent_date}
                      </span>
                    </Column>
                    <LineWrapColumn style={{ minWidth: '280px', maxWidth: 'none' }}>
                      <span>
                        {consentRecord.id}
                      </span>
                    </LineWrapColumn>
                    <LineWrapColumn>
                      <span>
                        <a
                          href={consentRecord.url}
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          {consentRecord.url}
                        </a>
                      </span>
                    </LineWrapColumn>
                    <LineWrapColumn>
                      <span>
                        {consentRecord.geo_location || __('Unknown', 'pressidium-cookie-consent')}
                      </span>
                    </LineWrapColumn>
                    <Column>
                      <span>
                        {consentRecord.ip_address}
                      </span>
                    </Column>
                    <LineWrapColumn>
                      <span>
                        {consentRecord.user_agent}
                      </span>
                    </LineWrapColumn>
                    <Column>
                      <span>
                        {consentRecord.necessary_consent
                          ? __('Accepted', 'pressidium-cookie-consent')
                          : __('Denied', 'pressidium-cookie-consent')}
                      </span>
                    </Column>
                    <Column>
                      <span>
                        {consentRecord.analytics_consent
                          ? __('Accepted', 'pressidium-cookie-consent')
                          : __('Denied', 'pressidium-cookie-consent')}
                      </span>
                    </Column>
                    <Column>
                      <span>
                        {consentRecord.targeting_consent
                          ? __('Accepted', 'pressidium-cookie-consent')
                          : __('Denied', 'pressidium-cookie-consent')}
                      </span>
                    </Column>
                    <Column>
                      <span>
                        {consentRecord.preferences_consent
                          ? __('Accepted', 'pressidium-cookie-consent')
                          : __('Denied', 'pressidium-cookie-consent')}
                      </span>
                    </Column>
                    <Column style={{ minWidth: '160px' }}>
                      <span>
                        {consentRecord.created_at}
                      </span>
                    </Column>
                    <Column style={{ minWidth: '160px' }}>
                      <span>
                        {consentRecord.updated_at}
                      </span>
                    </Column>
                  </Row>
                ))}
              </Table>
            </FlexItem>
            <FlexItem>
              <Flex>
                <FlexItem>
                  <Flex>
                    <FlexItem>
                      <Button
                        variant="secondary"
                        icon={DownloadIcon}
                        onClick={exportConsentRecords}
                        style={{ paddingRight: '10px' }}
                        isBusy={isExportingCsv}
                        disabled={isExportingCsv}
                      >
                        {__('Export CSV', 'pressidium-cookie-consent')}
                      </Button>
                    </FlexItem>
                    <FlexItem>
                      <Button
                        variant="secondary"
                        className="is-destructive"
                        onClick={openClearRecordsModal}
                        isBusy={isClearing}
                      >
                        {__('Clear Records', 'pressidium-cookie-consent')}
                      </Button>
                      <ClearRecordsModal
                        isOpen={isClearRecordsModalOpen}
                        onClose={closeClearRecordsModal}
                        clearRecords={onClearRecords}
                      />
                    </FlexItem>
                  </Flex>
                </FlexItem>
                <FlexItem>
                  <Flex>
                    <FlexItem>
                      {isFetching && <Spinner />}
                    </FlexItem>
                    <FlexItem>
                      <Pagination
                        currentPage={page}
                        numPages={totalPages}
                        changePage={setPage}
                        totalItems={totalConsentRecords}
                        style={{ justifyContent: 'flex-end' }}
                      />
                    </FlexItem>
                  </Flex>
                </FlexItem>
              </Flex>
            </FlexItem>
          </Flex>
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default ConsentRecordsTab;
