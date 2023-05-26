import { Panel, PanelBody } from '@wordpress/components';

import ColorControl from './ColorControl';

function ColorsPanel(props) {
  const { items, onChange } = props;

  return (
    <Panel className="pressidium-colors-panel">
      {items.map((item) => (
        <PanelBody initialOpen>
          <ColorControl
            label={item.label}
            color={item.color}
            onChange={(color) => onChange(item.key, color)}
          />
        </PanelBody>
      ))}
    </Panel>
  );
}

export default ColorsPanel;
