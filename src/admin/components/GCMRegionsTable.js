import { Button, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
  edit as EditIcon,
  trash as TrashIcon,
} from '@wordpress/icons';

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

const LineWrapColumn = styled(Column)`
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

function GCMRegionsTable(props) {
  const {
    regions = [],
    // eslint-disable-next-line no-unused-vars
    onEdit = (regionIndex) => {},
    // eslint-disable-next-line no-unused-vars
    onDelete = (regionIndex) => {},
    disabled = false,
  } = props;

  return (
    <Table style={{ width: '340px' }}>
      <Header>
        <Column>
          {__('Country', 'pressidium-cookie-consent')}
        </Column>
        <Column>
          {__('Regions', 'pressidium-cookie-consent')}
        </Column>
        <Column style={{ maxWidth: '70px' }}>
          {__('Actions', 'pressidium-cookie-consent')}
        </Column>
      </Header>
      {regions.map(({ country, subdivisions }, index) => (
        <Row>
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
                  maxWidth: '120px',
                  overflow: 'hidden',
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap',
                }}
              >
                {nameByCountryCode(country)}
              </FlexItem>
            </Flex>
          </Column>
          <LineWrapColumn style={{ maxWidth: '120px' }}>
            {subdivisions.length > 0
              ? subdivisions.map((subdivision) => subdivision.toUpperCase()).join(', ')
              : __('All regions', 'pressidium-cookie-consent')}
          </LineWrapColumn>
          <Column style={{ maxWidth: '70px' }}>
            <Flex style={{ justifyContent: 'flex-start' }}>
              <FlexItem>
                <StyledButton
                  icon={EditIcon}
                  label={__('Edit', 'pressidium-cookie-consent')}
                  onClick={() => onEdit(index)}
                  disabled={disabled}
                />
              </FlexItem>
              <FlexItem>
                <StyledButton
                  icon={TrashIcon}
                  label={__('Delete', 'pressidium-cookie-consent')}
                  onClick={() => onDelete(index)}
                  disabled={disabled}
                />
              </FlexItem>
            </Flex>
          </Column>
        </Row>
      ))}
    </Table>
  );
}

export default GCMRegionsTable;
