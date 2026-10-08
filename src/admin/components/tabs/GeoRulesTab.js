import { useState, useContext, useCallback } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  Button,
  RadioControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { plus as PlusIcon } from '@wordpress/icons';

import SettingsContext from '../../store/context';
import * as ActionTypes from '../../store/actionTypes';

import GeoRulesRegionsTable from '../GeoRulesRegionsTable';
import NewGeoRuleRegionModal from '../NewGeoRuleRegionModal';

function GeoRulesTab() {
  const { state, dispatch } = useContext(SettingsContext);

  const [isOptInModalOpen, setIsOptInModalOpen] = useState(false);
  const [isOptOutModalOpen, setIsOptOutModalOpen] = useState(false);

  const { geoRules } = state.pressidiumOptions;

  const onDefaultModeChange = useCallback((value) => {
    dispatch({
      type: ActionTypes.UPDATE_GEO_RULES_SETTING,
      payload: { key: 'defaultMode', value },
    });
  }, [dispatch]);

  const onAddOptInRegions = useCallback((countries) => {
    countries.forEach((country) => {
      dispatch({
        type: ActionTypes.ADD_GEO_RULES_REGION,
        payload: { listKey: 'optInRegions', country },
      });
    });
  }, [dispatch]);

  const onAddOptOutRegions = useCallback((countries) => {
    countries.forEach((country) => {
      dispatch({
        type: ActionTypes.ADD_GEO_RULES_REGION,
        payload: { listKey: 'optOutRegions', country },
      });
    });
  }, [dispatch]);

  const onDeleteOptInRegion = useCallback((index) => {
    dispatch({
      type: ActionTypes.DELETE_GEO_RULES_REGION,
      payload: { listKey: 'optInRegions', index },
    });
  }, [dispatch]);

  const onDeleteOptOutRegion = useCallback((index) => {
    dispatch({
      type: ActionTypes.DELETE_GEO_RULES_REGION,
      payload: { listKey: 'optOutRegions', index },
    });
  }, [dispatch]);

  return (
    <>
      <Panel>
        <PanelBody
          title={__('Default consent mode', 'pressidium-cookie-consent')}
          initialOpen
        >
          <PanelRow>
            <Flex>
              <FlexItem>
                <RadioControl
                  selected={geoRules.defaultMode}
                  options={[
                    { label: __('Opt-in', 'pressidium-cookie-consent'), value: 'opt-in' },
                    { label: __('Opt-out', 'pressidium-cookie-consent'), value: 'opt-out' },
                  ]}
                  onChange={onDefaultModeChange}
                  help={geoRules.defaultMode === 'opt-in'
                    ? __('Scripts will run only if the user accepts that category (GDPR compliant).', 'pressidium-cookie-consent')
                    : __('Scripts will run automatically (it is generally not GDPR compliant). Once the user has provided consent, this option is ignored.', 'pressidium-cookie-consent')}
                />
              </FlexItem>
            </Flex>
          </PanelRow>
        </PanelBody>

        <PanelBody
          title={__('Opt-in regions', 'pressidium-cookie-consent')}
          initialOpen
        >
          <PanelRow>
            <Flex direction="column" gap={4} style={{ maxWidth: '600px' }}>
              <FlexItem>
                <p>{__('Countries where users must explicitly consent before scripts run.', 'pressidium-cookie-consent')}</p>
              </FlexItem>
              {geoRules.optInRegions.length > 0 && (
                <FlexItem>
                  <GeoRulesRegionsTable
                    regions={geoRules.optInRegions}
                    onDelete={onDeleteOptInRegion}
                  />
                </FlexItem>
              )}
              <FlexItem>
                <Button
                  variant="secondary"
                  icon={PlusIcon}
                  onClick={() => setIsOptInModalOpen(true)}
                >
                  {__('Add Region', 'pressidium-cookie-consent')}
                </Button>
              </FlexItem>
            </Flex>
          </PanelRow>
        </PanelBody>

        <PanelBody
          title={__('Opt-out regions', 'pressidium-cookie-consent')}
          initialOpen
        >
          <PanelRow>
            <Flex direction="column" gap={4} style={{ maxWidth: '600px' }}>
              <FlexItem>
                <p>{__('Countries where scripts run automatically unless the user opts out.', 'pressidium-cookie-consent')}</p>
              </FlexItem>
              {geoRules.optOutRegions.length > 0 && (
                <FlexItem>
                  <GeoRulesRegionsTable
                    regions={geoRules.optOutRegions}
                    onDelete={onDeleteOptOutRegion}
                  />
                </FlexItem>
              )}
              <FlexItem>
                <Button
                  variant="secondary"
                  icon={PlusIcon}
                  onClick={() => setIsOptOutModalOpen(true)}
                >
                  {__('Add Region', 'pressidium-cookie-consent')}
                </Button>
              </FlexItem>
            </Flex>
          </PanelRow>
        </PanelBody>
      </Panel>

      <NewGeoRuleRegionModal
        isOpen={isOptInModalOpen}
        onClose={() => setIsOptInModalOpen(false)}
        onAdd={onAddOptInRegions}
        existingRegions={geoRules.optInRegions}
      />
      <NewGeoRuleRegionModal
        isOpen={isOptOutModalOpen}
        onClose={() => setIsOptOutModalOpen(false)}
        onAdd={onAddOptOutRegions}
        existingRegions={geoRules.optOutRegions}
      />
    </>
  );
}

export default GeoRulesTab;
