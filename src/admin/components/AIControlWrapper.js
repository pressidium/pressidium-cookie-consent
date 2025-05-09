import { __ } from '@wordpress/i18n';
import { cog as CogIcon } from '@wordpress/icons';

import { sparkle as SparkleIcon } from './icons';

import ToolbarWrapper from './ToolbarWrapper';

function AIControlWrapper(props) {
  const {
    children,
    isGenerating = false,
    generate = () => {},
    openSettings = () => {},
    label = null,
  } = props;

  return (
    <ToolbarWrapper
      label={__('AI options', 'pressidium-cookie-consent')}
      className={isGenerating ? 'pressidium-ai-control' : ''}
      orientation="vertical"
      buttons={[
        {
          icon: SparkleIcon,
          onClick: generate,
          label: label !== null ? label : __('AI Generate', 'pressidium-cookie-consent'),
          disabled: isGenerating,
        },
        {
          icon: CogIcon,
          onClick: openSettings,
          label: __('AI Settings', 'pressidium-cookie-consent'),
        },
      ]}
    >
      {children}
    </ToolbarWrapper>
  );
}

export default AIControlWrapper;
