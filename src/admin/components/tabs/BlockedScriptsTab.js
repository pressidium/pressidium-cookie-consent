import {
  Panel,
  PanelBody,
  PanelRow,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import BlockedScriptsTable from '../BlockedScriptsTable';

function BlockedScriptsTab() {
  return (
    <Panel>
      <PanelBody initialOpen>
        <PanelRow>
          <p>
            {__('Make sure that you’ve enabled the “Page Scripts” option, located under the General tab.', 'pressidium-cookie-consent')}
          </p>
        </PanelRow>
        <PanelRow>
          <p>
            {__('Script blocking may not always be effective due to variations in script loading methods.', 'pressidium-cookie-consent')}
          </p>
        </PanelRow>
        <PanelRow>
          <BlockedScriptsTable />
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default BlockedScriptsTab;
