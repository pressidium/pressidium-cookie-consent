import {
  Panel,
  PanelBody,
  PanelRow,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
  table as TableIcon,
  chartBar as ChartBarIcon,
  people as PeopleIcon,
  more as MoreIcon,
} from '@wordpress/icons';

import CookiesTable from '../CookiesTable';

function CookiesTab(props) {
  const { appendNotice, openAIConfigModal } = props;

  return (
    <Panel>
      <PanelBody
        title={__('Necessary cookies', 'pressidium-cookie-consent')}
        icon={TableIcon}
        initialOpen
      >
        <PanelRow>
          <CookiesTable
            category="necessary"
            appendNotice={appendNotice}
            openAIConfigModal={openAIConfigModal}
          />
        </PanelRow>
      </PanelBody>

      <PanelBody
        title={__('Analytics cookies', 'pressidium-cookie-consent')}
        icon={ChartBarIcon}
        initialOpen
      >
        <PanelRow>
          <CookiesTable
            category="analytics"
            appendNotice={appendNotice}
            openAIConfigModal={openAIConfigModal}
          />
        </PanelRow>
      </PanelBody>

      <PanelBody
        title={__('Targeting cookies', 'pressidium-cookie-consent')}
        icon={PeopleIcon}
        initialOpen
      >
        <PanelRow>
          <CookiesTable
            category="targeting"
            appendNotice={appendNotice}
            openAIConfigModal={openAIConfigModal}
          />
        </PanelRow>
      </PanelBody>

      <PanelBody
        title={__('Preferences cookies', 'pressidium-cookie-consent')}
        icon={MoreIcon}
        initialOpen
      >
        <PanelRow>
          <CookiesTable
            category="preferences"
            appendNotice={appendNotice}
            openAIConfigModal={openAIConfigModal}
          />
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default CookiesTab;
