import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
  Button,
  Flex,
  FlexItem,
  Modal,
  CheckboxControl,
  TextControl,
  __experimentalScrollable as Scrollable,
} from '@wordpress/components';

import { CountryFlag, countries } from './Countries';

function NewGeoRuleRegionModal(props) {
  const {
    isOpen,
    onClose,
    onAdd,
    existingRegions = [],
  } = props;

  const [selectedCountries, setSelectedCountries] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');

  const sortedCountries = (
    Object.keys(countries)
      .map((code) => ({ code, name: countries[code] }))
      .toSorted((a, b) => (a.name > b.name ? 1 : -1))
  );

  const filteredCountries = sortedCountries.filter(({ code, name }) => {
    if (!searchTerm) {
      return true;
    }

    const term = searchTerm.toLowerCase();

    return name.toLowerCase().includes(term) || code.toLowerCase().includes(term);
  });

  const availableCountries = filteredCountries.filter(
    ({ code }) => !existingRegions.includes(code),
  );

  const selectCountry = useCallback(
    (code) => setSelectedCountries((prev) => (prev.includes(code) ? prev : [...prev, code])),
    [],
  );

  const deselectCountry = useCallback(
    (code) => setSelectedCountries((prev) => prev.filter((c) => c !== code)),
    [],
  );

  const selectAll = useCallback(
    () => setSelectedCountries(availableCountries.map(({ code }) => code)),
    [availableCountries],
  );

  const deselectAll = useCallback(() => setSelectedCountries([]), []);

  const handleAdd = useCallback(() => {
    if (selectedCountries.length > 0) {
      onAdd(selectedCountries);
    }
    setSelectedCountries([]);
    setSearchTerm('');
    onClose();
  }, [selectedCountries, onAdd, onClose]);

  const handleClose = useCallback(() => {
    setSelectedCountries([]);
    setSearchTerm('');
    onClose();
  }, [onClose]);

  if (!isOpen) {
    return null;
  }

  return (
    <Modal
      title={__('Add Regions', 'pressidium-cookie-consent')}
      onRequestClose={handleClose}
    >
      <TextControl
        value={searchTerm}
        placeholder={__('Search countries', 'pressidium-cookie-consent')}
        onChange={(value) => setSearchTerm(value)}
      />
      <Flex style={{ marginBottom: '8px' }}>
        <FlexItem>
          <Button
            variant="tertiary"
            onClick={selectAll}
          >
            {__('Select all', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>
        <FlexItem>
          <Button
            variant="tertiary"
            onClick={deselectAll}
          >
            {__('Deselect all', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>
      </Flex>
      <Scrollable style={{ maxHeight: '300px' }}>
        {availableCountries.map(({ code, name }) => (
          <CheckboxControl
            key={code}
            label={
              <Flex style={{ justifyContent: 'flex-start' }}>
                <FlexItem>
                  <CountryFlag country={code} style={{ verticalAlign: 'middle' }} height={16} />
                </FlexItem>
                <FlexItem>{`${name} (${code.toUpperCase()})`}</FlexItem>
              </Flex>
            }
            checked={selectedCountries.includes(code)}
            onChange={(checked) => {
              if (checked) {
                selectCountry(code);
              } else {
                deselectCountry(code);
              }
            }}
          />
        ))}
        {availableCountries.length === 0 && (
          <p style={{ color: '#757575' }}>
            {__('No countries available.', 'pressidium-cookie-consent')}
          </p>
        )}
      </Scrollable>
      <Flex style={{ marginTop: '16px', justifyContent: 'flex-end' }}>
        <FlexItem>
          <Button
            variant="secondary"
            onClick={handleClose}
          >
            {__('Cancel', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>
        <FlexItem>
          <Button
            variant="primary"
            onClick={handleAdd}
            disabled={selectedCountries.length === 0}
          >
            {__('Add', 'pressidium-cookie-consent')}
          </Button>
        </FlexItem>
      </Flex>
    </Modal>
  );
}

export default NewGeoRuleRegionModal;
