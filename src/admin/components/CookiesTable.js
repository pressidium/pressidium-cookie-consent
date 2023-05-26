import { useContext, useMemo, useCallback } from '@wordpress/element';
import {
  Flex,
  FlexItem,
  Button,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
  plus as PlusIcon,
  trash as TrashIcon,
} from '@wordpress/icons';

import styled from 'styled-components';

import SettingsContext from '../store/context';
import Table, { Header, Row, Column } from './Table';

const StyledButton = styled(Button)`
  color: #3c434a;
  min-width: 24px;
  height: 24px;
  padding: 0;
  &:hover {
    color: #0073aa;
  },
`;

function CookiesTable(props) {
  const { category = 'necessary' } = props;

  const { state, dispatch } = useContext(SettingsContext);

  const cookies = useMemo(
    () => {
      if (!state.pressidium_options.cookie_table) {
        return [];
      }

      return state.pressidium_options.cookie_table[category];
    },
    [state, category],
  );

  const onAddCookie = useCallback(() => {
    dispatch({
      type: 'ADD_COOKIE_TABLE_ROW',
      payload: {
        category,
      },
    });
  }, [category]);

  const onUpdateCookie = useCallback((index, key, value) => {
    dispatch({
      type: 'UPDATE_COOKIE_TABLE_ROW',
      payload: {
        category,
        index,
        key,
        value,
      },
    });
  }, [category]);

  const onDeleteCookie = useCallback((index) => {
    dispatch({
      type: 'DELETE_COOKIE_TABLE_ROW',
      payload: {
        category,
        index,
      },
    });
  }, [category]);

  const hasCookies = useMemo(() => Array.isArray(cookies) && cookies.length > 0, [cookies]);

  return (
    <Flex direction="column" gap={4}>
      <FlexItem>
        {hasCookies ? (
          <Table width="100%">
            <Header>
              <Column>
                {__('Name', 'pressidium-cookie-consent')}
              </Column>
              <Column>
                {__('Domain', 'pressidium-cookie-consent')}
              </Column>
              <Column>
                {__('Expiration', 'pressidium-cookie-consent')}
              </Column>
              <Column>
                {__('Path', 'pressidium-cookie-consent')}
              </Column>
              <Column>
                {__('Description', 'pressidium-cookie-consent')}
              </Column>
              <Column style={{ maxWidth: '70px' }}>
                {__('Is Regex?', 'pressidium-cookie-consent')}
              </Column>
              <Column style={{ maxWidth: '50px' }}>
                {__('Actions', 'pressidium-cookie-consent')}
              </Column>
            </Header>
            {cookies.map((cookie, index) => (
              <Row>
                <Column>
                  <TextControl
                    value={cookie.name}
                    placeholder="example_cookie"
                    onChange={(value) => onUpdateCookie(index, 'name', value)}
                  />
                </Column>
                <Column>
                  <TextControl
                    value={cookie.domain}
                    placeholder="example.com"
                    onChange={(value) => onUpdateCookie(index, 'domain', value)}
                  />
                </Column>
                <Column>
                  <TextControl
                    value={cookie.expiration}
                    placeholder="1 year"
                    onChange={(value) => onUpdateCookie(index, 'expiration', value)}
                  />
                </Column>
                <Column>
                  <TextControl
                    value={cookie.path}
                    placeholder="/"
                    onChange={(value) => onUpdateCookie(index, 'path', value)}
                  />
                </Column>
                <Column>
                  <TextareaControl
                    value={cookie.description}
                    placeholder={__('This is an example cookie.', 'pressidium-cookie-consent')}
                    onChange={(value) => onUpdateCookie(index, 'description', value)}
                  />
                </Column>
                <Column style={{ maxWidth: '70px' }}>
                  <ToggleControl
                    checked={cookie.is_regex}
                    onChange={(value) => onUpdateCookie(index, 'is_regex', value)}
                    className="pressidium-no-margin"
                  />
                </Column>
                <Column style={{ maxWidth: '50px', lineHeight: 1 }}>
                  <StyledButton
                    icon={TrashIcon}
                    label={__('Delete', 'pressidium-cookie-consent')}
                    onClick={() => onDeleteCookie(index)}
                  />
                </Column>
              </Row>
            ))}
          </Table>
        ) : (
          <p>
            {__('No cookies in this category.', 'pressidium-cookie-consent')}
          </p>
        )}
      </FlexItem>
      <FlexItem>
        <Button
          icon={PlusIcon}
          onClick={onAddCookie}
          style={{ paddingRight: '10px' }}
          isPrimary
        >
          {__('New Cookie', 'pressidium-cookie-consent')}
        </Button>
      </FlexItem>
    </Flex>
  );
}

export default CookiesTable;
