import { useContext, useMemo, useCallback } from '@wordpress/element';
import {
  Flex,
  FlexItem,
  Button,
  TextControl,
  SelectControl,
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

function BlockedScriptsTable() {
  const { state, dispatch } = useContext(SettingsContext);

  const blockedScripts = useMemo(() => {
    if (!state.pressidium_options.blocked_scripts) {
      return [];
    }

    return state.pressidium_options.blocked_scripts;
  }, [state]);

  const onAddScript = useCallback(() => {
    dispatch({
      type: 'ADD_BLOCKED_SCRIPT',
    });
  }, []);

  const onUpdateScript = useCallback((index, key, value) => {
    dispatch({
      type: 'UPDATE_BLOCKED_SCRIPT',
      payload: {
        index,
        key,
        value,
      },
    });
  }, []);

  const onDeleteScript = useCallback((index) => {
    dispatch({
      type: 'DELETE_BLOCKED_SCRIPT',
      payload: {
        index,
      },
    });
  }, []);

  const hasBlockedScripts = useMemo(
    () => Array.isArray(blockedScripts) && blockedScripts.length > 0,
    [blockedScripts],
  );

  return (
    <Flex direction="column" gap={4} style={{ width: '100%' }}>
      <FlexItem>
        {hasBlockedScripts ? (
          <Table width="100%">
            <Header>
              <Column>
                {__('Script Source', 'pressidium-cookie-consent')}
              </Column>
              <Column>
                {__('Cookie Category', 'pressidium-cookie-consent')}
              </Column>
              <Column style={{ maxWidth: '70px' }}>
                {__('Is Regex?', 'pressidium-cookie-consent')}
              </Column>
              <Column style={{ maxWidth: '50px' }}>
                {__('Actions', 'pressidium-cookie-consent')}
              </Column>
            </Header>
            {blockedScripts.map((blockedScript, index) => (
              <Row>
                <Column>
                  <TextControl
                    value={blockedScript.src}
                    placeholder="https://example.com/script.js"
                    onChange={(value) => onUpdateScript(index, 'src', value)}
                  />
                </Column>
                <Column>
                  <SelectControl
                    value={blockedScript.category}
                    options={[
                      {
                        label: __('Analytics', 'pressidium-cookie-consent'),
                        value: 'analytics',
                      },
                      {
                        label: __('Targeting', 'pressidium-cookie-consent'),
                        value: 'targeting',
                      },
                    ]}
                    onChange={(value) => onUpdateScript(index, 'category', value)}
                  />
                </Column>
                <Column style={{ maxWidth: '70px' }}>
                  <ToggleControl
                    checked={blockedScript.is_regex}
                    onChange={(value) => onUpdateScript(index, 'is_regex', value)}
                    className="pressidium-no-margin"
                  />
                </Column>
                <Column style={{ maxWidth: '50px', lineHeight: 1 }}>
                  <StyledButton
                    icon={TrashIcon}
                    label={__('Delete', 'pressidium-cookie-consent')}
                    onClick={() => onDeleteScript(index)}
                  />
                </Column>
              </Row>
            ))}
          </Table>
        ) : (
          <p>
            {__('No blocked scripts.', 'pressidium-cookie-consent')}
          </p>
        )}
      </FlexItem>
      <FlexItem>
        <Button
          icon={PlusIcon}
          onClick={onAddScript}
          style={{ paddingRight: '10px' }}
          isPrimary
        >
          {__('New Script', 'pressidium-cookie-consent')}
        </Button>
      </FlexItem>
    </Flex>
  );
}

export default BlockedScriptsTable;
