import { useState, useContext, useCallback } from '@wordpress/element';
import { Button, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import styled from 'styled-components';

import SettingsContext from '../store/context';

const StyledFooter = styled.div`
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-top: none;
`;

function Footer(props) {
  const { save, previewConsentModal, previewSettingsModal } = props;

  const [isSaving, setIsSaving] = useState(false);
  const [isPreviewing, setIsPreviewing] = useState(false);

  const { state } = useContext(SettingsContext);

  const onSave = useCallback(() => {
    (async () => {
      setIsSaving(true);

      await save(state);

      setIsSaving(false);
    })();
  }, [state]);

  const onConsentModalPreview = useCallback(() => {
    (async () => {
      setIsPreviewing(true);

      await previewConsentModal(state);

      setIsPreviewing(false);
    })();
  }, [state]);

  const onSettingsModalPreview = useCallback(() => {
    (async () => {
      setIsPreviewing(true);

      await previewSettingsModal(state);

      setIsPreviewing(false);
    })();
  }, [state]);

  return (
    <StyledFooter>
      <Flex justify="flex-start">
        <FlexItem>
          <Button
            variant="primary"
            onClick={onSave}
            isBusy={isSaving}
          >
            {__('Save', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>

        <FlexItem>
          <Button
            variant="secondary"
            onClick={onConsentModalPreview}
            isBusy={isPreviewing}
          >
            {__('Preview Consent', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>

        <FlexItem>
          <Button
            variant="secondary"
            onClick={onSettingsModalPreview}
            isBusy={isPreviewing}
          >
            {__('Preview Settings', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>
      </Flex>
    </StyledFooter>
  );
}

export default Footer;
