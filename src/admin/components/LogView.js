import { __ } from '@wordpress/i18n';
import { TextareaControl } from '@wordpress/components';

function LogView(props) {
  const { logs } = props;

  if (logs === null || logs.length === 0) {
    return (
      <p>
        {__('There are no logs yet.', 'pressidium-cookie-consent')}
      </p>
    );
  }

  return (
    <TextareaControl
      label="Logs"
      value={logs}
      rows={12}
      disabled
    />
  );
}

export default LogView;
