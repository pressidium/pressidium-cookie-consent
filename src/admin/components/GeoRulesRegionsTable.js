import { Button, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { trash as TrashIcon } from '@wordpress/icons';

import styled from 'styled-components';

import Table, { Header, Row, Column } from './Table';
import { CountryFlag, nameByCountryCode } from './Countries';

const StyledButton = styled(Button)`
    color: #3c434a;
    min-width: 24px;
    height: 24px;
    padding: 0;
    &:hover {
        color: #0073aa;
    },
`;

function GeoRulesRegionsTable(props) {
  const {
    regions = [],
    // eslint-disable-next-line no-unused-vars
    onDelete = (regionIndex) => {},
    disabled = false,
  } = props;

  return (
    <Table style={{ width: '280px' }}>
      <Header>
        <Column>
          {__('Country', 'pressidium-cookie-consent')}
        </Column>
        <Column style={{ maxWidth: '70px' }}>
          {__('Actions', 'pressidium-cookie-consent')}
        </Column>
      </Header>
      {regions.map((country, index) => (
        <Row key={country}>
          <Column>
            <Flex style={{ justifyContent: 'flex-start' }}>
              <FlexItem>
                <CountryFlag
                  country={country}
                  style={{ verticalAlign: 'middle' }}
                  height={20}
                />
              </FlexItem>
              <FlexItem
                style={{
                  maxWidth: '160px',
                  overflow: 'hidden',
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap',
                }}
              >
                {nameByCountryCode(country)}
              </FlexItem>
            </Flex>
          </Column>
          <Column style={{ maxWidth: '70px' }}>
            <StyledButton
              icon={TrashIcon}
              label={__('Delete', 'pressidium-cookie-consent')}
              onClick={() => onDelete(index)}
              disabled={disabled}
            />
          </Column>
        </Row>
      ))}
    </Table>
  );
}

export default GeoRulesRegionsTable;