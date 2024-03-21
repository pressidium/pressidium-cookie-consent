import {
  useState,
  useMemo,
  useEffect,
  useCallback,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
  Button,
  Flex,
  FlexItem,
  Modal,
  SelectControl,
  CheckboxControl,
  TextControl,
  __experimentalScrollable as Scrollable,
} from '@wordpress/components';

import iso3166 from 'iso-3166-2';

import { countries } from './Countries';

function NewRegionModal(props) {
  const { isOpen, onClose, addRegion } = props;

  const [selectedCountry, setSelectedCountry] = useState('us');
  const [selectedSubdivisions, setSelectedSubdivisions] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');

  const subdivisions = useMemo(
    () => {
      const { sub } = iso3166.country(selectedCountry);

      if (!sub) {
        return [];
      }

      return Object.keys(sub)
        .map((code) => ({
          code: code.toLowerCase(),
          name: sub[code].name,
        }));
    },
    [selectedCountry],
  );

  const clearSelectedSubdivisions = useCallback(
    () => setSelectedSubdivisions([]),
    [],
  );

  const selectSubdivision = useCallback(
    (code) => {
      setSelectedSubdivisions((prev) => (prev.includes(code) ? prev : [...prev, code]));
    },
    [],
  );

  const deselectSubdivision = useCallback(
    (code) => {
      setSelectedSubdivisions((prev) => prev
        .filter((item) => item !== code));
    },
    [],
  );

  useEffect(() => {
    // Clear the selected subdivisions and the search term when the selected country changes
    clearSelectedSubdivisions();
    setSearchTerm('');
  }, [selectedCountry]);

  if (!isOpen) {
    return null;
  }

  return (
    <Modal
      title={__('New Region', 'pressidium-cookie-consent')}
      onRequestClose={onClose}
    >
      <Flex direction="column">
        <SelectControl
          label={__('Country', 'pressidium-cookie-consent')}
          value={selectedCountry}
          options={
            Object
              .keys(countries)
              .map((key) => ({
                label: countries[key],
                value: key,
              }))
              .toSorted((a, b) => ((a.label > b.label) ? 1 : -1))
          }
          onChange={setSelectedCountry}
        />
        {subdivisions.length > 0 && (
          <>
            <Flex direction="row">
              <FlexItem>
                <Button
                  variant="tertiary"
                  onClick={() => {
                    setSelectedSubdivisions(subdivisions.map(({ code }) => code));
                  }}
                >
                  {__('Select all', 'pressidium-cookie-consent')}
                </Button>
              </FlexItem>
              <FlexItem>
                <Button
                  variant="tertiary"
                  onClick={() => {
                    setSelectedSubdivisions([]);
                  }}
                >
                  {__('Deselect all', 'pressidium-cookie-consent')}
                </Button>
              </FlexItem>
            </Flex>
            <TextControl
              value={searchTerm}
              placeholder={__('Search regions', 'pressidium-cookie-consent')}
              onChange={(value) => setSearchTerm(value)}
            />
            <Scrollable className="pressidium-scrollable">
              {subdivisions
                .toSorted((a, b) => ((a.name > b.name) ? 1 : -1))
                .filter(({ code, name }) => {
                  if (!searchTerm) {
                    return true;
                  }

                  const lowerCaseTerm = searchTerm.toLowerCase();

                  return name.toLowerCase().includes(lowerCaseTerm)
                    || code.toLowerCase().includes(lowerCaseTerm);
                })
                .map(({ code, name }) => (
                  <CheckboxControl
                    label={name}
                    checked={selectedSubdivisions.includes(code)}
                    onChange={(checked) => {
                      if (checked) {
                        selectSubdivision(code);
                      } else {
                        deselectSubdivision(code);
                      }
                    }}
                  />
                ))}
            </Scrollable>
          </>
        )}
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
                addRegion(selectedCountry, selectedSubdivisions);
                setSelectedSubdivisions([]);
                onClose();
              }}
            >
              {__('Add', 'pressidium-cookie-consent')}
            </Button>
          </FlexItem>
        </Flex>
      </Flex>
    </Modal>
  );
}

export default NewRegionModal;
