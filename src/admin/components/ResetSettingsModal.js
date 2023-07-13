import { __ } from '@wordpress/i18n';
import {
  Button,
  Flex,
  FlexItem,
  Modal,
} from '@wordpress/components';

function ResetSettingsModal(props) {
  const { isOpen, onClose, resetSettings } = props;

  if (!isOpen) {
    return null;
  }

  return (
    <Modal
      title={__('Reset settings?', 'pressidium-cookie-consent')}
      onRequestClose={onClose}
    >
      <Flex direction="column">
        <p>
          <strong>
            {__('This action cannot be undone.', 'pressidium-cookie-consent')}
          </strong>
          &nbsp;
          {
            __(
              'You are about to reset all settings to the default values. Are you sure you want to proceed?',
              'pressidium-cookie-consent',
            )
          }
        </p>
        <Flex justify="flex-end">
          <FlexItem>
            <Button
              variant="tertiary"
              onClick={onClose}
            >
              {__('Cancel', 'pressidium-cookie-consent')}
            </Button>
          </FlexItem>
          <FlexItem>
            <Button
              variant="primary"
              className="is-destructive"
              onClick={() => {
                resetSettings();
                onClose();
              }}
            >
              {__('Reset settings', 'pressidium-cookie-consent')}
            </Button>
          </FlexItem>
        </Flex>
      </Flex>
    </Modal>
  );
}

export default ResetSettingsModal;
