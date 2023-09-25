import { __ } from '@wordpress/i18n';
import {
  Button,
  Flex,
  FlexItem,
  Modal,
} from '@wordpress/components';

function ClearRecordsModal(props) {
  const { isOpen, onClose, clearRecords } = props;

  if (!isOpen) {
    return null;
  }

  return (
    <Modal
      title={__('Clear records?', 'pressidium-cookie-consent')}
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
              'You are about to permanently clear all consent records. Are you sure you want to proceed?',
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
                clearRecords();
                onClose();
              }}
            >
              {__('Delete records', 'pressidium-cookie-consent')}
            </Button>
          </FlexItem>
        </Flex>
      </Flex>
    </Modal>
  );
}

export default ClearRecordsModal;
