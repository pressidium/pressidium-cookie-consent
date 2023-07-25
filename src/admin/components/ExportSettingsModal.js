import { __ } from '@wordpress/i18n';
import {
  Button,
  Flex,
  FlexItem,
  Modal,
} from '@wordpress/components';

function ExportSettingsModal(props) {
  const { isOpen, onClose, exportSettings } = props;

  if (!isOpen) {
    return null;
  }

  return (
    <Modal
      title={__('Export settings?', 'pressidium-cookie-consent')}
      onRequestClose={onClose}
    >
      <Flex direction="column">
        <p>
          <strong>
            {__('You have unsaved changes.', 'pressidium-cookie-consent')}
          </strong>
          &nbsp;
          {__('Those changes won\'t be exported. Are you sure you want to proceed?', 'pressidium-cookie-consent')}
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
              onClick={() => {
                exportSettings();
                onClose();
              }}
            >
              {__('Export anyway', 'pressidium-cookie-consent')}
            </Button>
          </FlexItem>
        </Flex>
      </Flex>
    </Modal>
  );
}

export default ExportSettingsModal;
