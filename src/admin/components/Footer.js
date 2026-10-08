import {
  useState,
  useContext,
  useCallback,
  useEffect,
  useRef,
} from '@wordpress/element';
import {
  Button,
  Flex,
  FlexItem,
  FormFileUpload,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import styled from 'styled-components';

import SettingsContext from '../store/context';

import ExportSettingsModal from './ExportSettingsModal';
import ResetSettingsModal from './ResetSettingsModal';

const StyledFooter = styled.div`
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-top: none;
  background-color: #fff;
  position: sticky;
  bottom: 0;
  transition: box-shadow 0.2s ease;
  box-shadow: ${({ $hasShadow }) => ($hasShadow ? '0 -4px 8px rgba(0, 0, 0, 0.1)' : 'none')};
  /*
   * Extend the clip region upward (negative top value) so the top shadow is fully
   * visible, while left/right values of 0 clip exactly at the element's edges,
   * preventing the shadow from bleeding out horizontally.
   */
  clip-path: inset(-10px 0 0 0);
`;

function Footer(props) {
  const {
    save,
    previewConsentModal,
    previewSettingsModal,
    previewFloatingButton,
    hasUnsavedChanges,
    exportSettings,
    importSettings,
    resetSettings,
  } = props;

  const [isSaving, setIsSaving] = useState(false);
  const [isPreviewing, setIsPreviewing] = useState(false);
  const [isExporting, setIsExporting] = useState(false);
  const [isImporting, setIsImporting] = useState(false);
  const [isResetting, setIsResetting] = useState(false);

  const [isExportSettingsModalOpen, setIsExportSettingsModalOpen] = useState(false);
  const [isResetSettingsModalOpen, setIsResetSettingsModalOpen] = useState(false);

  const { state } = useContext(SettingsContext);

  const sentinelRef = useRef(null);
  const [hasShadow, setHasShadow] = useState(false);

  useEffect(() => {
    if (!sentinelRef.current) {
      return null;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        setHasShadow(!entry.isIntersecting);
      },
      { threshold: 0 },
    );

    observer.observe(sentinelRef.current);

    return () => observer.disconnect();
  }, []);

  const openExportSettingsModal = useCallback(() => setIsExportSettingsModalOpen(true), []);
  const closeExportSettingsModal = useCallback(() => setIsExportSettingsModalOpen(false), []);

  const openResetSettingsModal = useCallback(() => setIsResetSettingsModalOpen(true), []);
  const closeResetSettingsModal = useCallback(() => setIsResetSettingsModalOpen(false), []);

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

  const onFloatingButtonPreview = useCallback(() => {
    (async () => {
      setIsPreviewing(true);

      await previewFloatingButton(state);

      setIsPreviewing(false);
    })();
  }, [state]);

  const onExportSettings = useCallback(() => {
    (async () => {
      setIsExporting(true);

      await exportSettings();

      setIsExporting(false);
    })();
  }, [exportSettings]);

  const onExportSettingsButtonClick = useCallback(() => {
    (async () => {
      if (hasUnsavedChanges) {
        openExportSettingsModal();
      } else {
        onExportSettings();
      }
    })();
  }, [hasUnsavedChanges, exportSettings]);

  const onImportSettings = useCallback((event) => {
    (async () => {
      setIsImporting(true);

      await importSettings(event.currentTarget.files);

      setIsImporting(false);
    })();
  }, [importSettings]);

  const onResetSettings = useCallback(() => {
    (async () => {
      setIsResetting(true);

      await resetSettings();

      setIsResetting(false);
    })();
  }, [resetSettings]);

  return (
    <>
      <StyledFooter $hasShadow={hasShadow}>
        <Flex justify="space-between" wrap>
          <FlexItem>
            <Flex justify="flex-start" wrap>
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

              <FlexItem>
                <Button
                  variant="secondary"
                  onClick={onFloatingButtonPreview}
                  isBusy={isPreviewing}
                >
                  {__('Preview Floating Button', 'pressidium-cookie-consent')}
                </Button>
              </FlexItem>
            </Flex>
          </FlexItem>

          <FlexItem>
            <Flex justify="flex-start" wrap>
              <FlexItem>
                <Button
                  variant="secondary"
                  onClick={onExportSettingsButtonClick}
                  isBusy={isExporting}
                >
                  {__('Export Settings', 'pressidium-cookie-consent')}
                </Button>
                <ExportSettingsModal
                  isOpen={isExportSettingsModalOpen}
                  onClose={closeExportSettingsModal}
                  exportSettings={onExportSettings}
                />
              </FlexItem>

              <FlexItem>
                <FormFileUpload
                  variant="secondary"
                  accept="application/json"
                  onChange={onImportSettings}
                  isBusy={isImporting}
                >
                  {__('Import Settings', 'pressidium-cookie-consent')}
                </FormFileUpload>
              </FlexItem>

              <FlexItem>
                <Button
                  variant="secondary"
                  className="is-destructive"
                  onClick={openResetSettingsModal}
                  isBusy={isResetting}
                >
                  {__('Reset Settings', 'pressidium-cookie-consent')}
                </Button>
                <ResetSettingsModal
                  isOpen={isResetSettingsModalOpen}
                  onClose={closeResetSettingsModal}
                  resetSettings={onResetSettings}
                />
              </FlexItem>
            </Flex>
          </FlexItem>
        </Flex>
      </StyledFooter>
      <div ref={sentinelRef} />
    </>
  );
}

export default Footer;
