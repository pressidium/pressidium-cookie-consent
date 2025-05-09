import { useState, useMemo, useCallback } from '@wordpress/element';
import {
  ToolbarDropdownMenu,
  PanelBody,
  PanelRow,
  ToggleControl,
  RadioControl,
} from '@wordpress/components';
import {
  useBlockProps,
  BlockControls,
  InspectorControls,
  AlignmentControl,
  withColors,
  __experimentalUseColorProps as useColorProps,
  __experimentalUseBorderProps as useBorderProps,
  __experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
  __experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from '@wordpress/block-editor';
import {
  alignLeft,
  alignRight,
  alignCenter,
  header,
} from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

import clsx from 'clsx';

import './editor.scss';

const COLUMN_ALIGNMENT_CONTROLS = [
  {
    icon: alignLeft,
    title: __('Align column left', 'pressidium-cookie-consent'),
    align: 'left',
  },
  {
    icon: alignCenter,
    title: __('Align column center', 'pressidium-cookie-consent'),
    align: 'center',
  },
  {
    icon: alignRight,
    title: __('Align column right', 'pressidium-cookie-consent'),
    align: 'right',
  },
];

const COLUMN_NAMES = [
  'name',
  'domain',
  'expiration',
  'path',
  'description',
];

function Edit(props) {
  const {
    attributes,
    setAttributes,
    stripeColor,
    setStripeColor,
    style,
    clientId,
  } = props;

  const blockProps = useBlockProps({
    style: {
      ...style,
      '--cc-block-stripe-color': stripeColor.slug
        ? `var(--wp--preset--color--${stripeColor.slug})`
        : attributes.customStripeColor,
    },
  });
  const colorProps = useColorProps(attributes);
  const borderProps = useBorderProps(attributes);

  const colorGradientSettings = useMultipleOriginColorsAndGradients();

  const [selectedColumn, setSelectedColumn] = useState(null);

  const cookies = useMemo(() => ({
    necessary: pressidiumCookiesBlockData.cookies.necessary || [],
    analytics: pressidiumCookiesBlockData.cookies.analytics || [],
    targeting: pressidiumCookiesBlockData.cookies.targeting || [],
    preferences: pressidiumCookiesBlockData.cookies.preferences || [],
  }), [pressidiumCookiesBlockData]);

  const i18nColumnNames = {
    name: __('Name', 'pressidium-cookie-consent'),
    domain: __('Domain', 'pressidium-cookie-consent'),
    expiration: __('Expiration', 'pressidium-cookie-consent'),
    path: __('Path', 'pressidium-cookie-consent'),
    description: __('Description', 'pressidium-cookie-consent'),
  };

  const selectedCookies = useMemo(
    () => cookies[attributes.cookieCategory],
    [cookies, attributes.cookieCategory],
  );

  const headerAlignment = useMemo(
    () => attributes.headerAlignment || 'center',
    [attributes.headerAlignment],
  );

  const getColumnAlignment = useCallback(() => {
    if (!selectedColumn) {
      return 'left';
    }

    return attributes.columnsAlignment?.[selectedColumn] || 'left';
  }, [attributes.columnsAlignment, selectedColumn]);

  const shouldDisplayColumn = (columnName) => {
    const { columnsVisibility = {} } = attributes;

    if (columnName in columnsVisibility) {
      return columnsVisibility[columnName];
    }

    return true;
  };

  const onChangeCookieCategory = useCallback((cookieCategory) => {
    setAttributes({ cookieCategory });
  }, []);

  const onChangeHeaderAlignment = useCallback((alignment) => {
    setAttributes({
      headerAlignment: alignment,
    });
  }, []);

  const onChangeColumnAlignment = useCallback((alignment) => {
    if (!selectedColumn) {
      return;
    }

    setAttributes({
      columnsAlignment: {
        ...attributes.columnsAlignment,
        [selectedColumn]: alignment,
      },
    });
  }, [attributes.columnsAlignment, selectedColumn]);

  const onChangeColumnVisibility = useCallback((columnName, shouldDisplay) => {
    setAttributes({
      columnsVisibility: {
        ...attributes.columnsVisibility,
        [columnName]: shouldDisplay,
      },
    });
  }, [attributes.columnsVisibility]);

  const onStripeColorChange = (value) => {
    setStripeColor(value);

    setAttributes({
      customStripeColor: value,
    });
  };

  return (
    <>
      <BlockControls>
        <ToolbarDropdownMenu
          icon={header}
          label={__('Change header alignment', 'pressidium-cookie-consent')}
          controls={[
            {
              icon: alignLeft,
              title: __('Align header left', 'pressidium-cookie-consent'),
              align: 'left',
              onClick: () => onChangeHeaderAlignment('left'),
            },
            {
              icon: alignCenter,
              title: __('Align header center', 'pressidium-cookie-consent'),
              align: 'center',
              onClick: () => onChangeHeaderAlignment('center'),
            },
            {
              icon: alignRight,
              title: __('Align header right', 'pressidium-cookie-consent'),
              align: 'right',
              onClick: () => onChangeHeaderAlignment('right'),
            },
          ]}
          value={headerAlignment}
        />
        <AlignmentControl
          label={__('Change column alignment', 'pressidium-cookie-consent')}
          alignmentControls={COLUMN_ALIGNMENT_CONTROLS}
          value={getColumnAlignment()}
          onChange={onChangeColumnAlignment}
        />
      </BlockControls>

      <InspectorControls>
        <PanelBody title={__('Cookie category', 'pressidium-cookie-consent')} initialOpen>
          <PanelRow>
            <RadioControl
              help={__('Select which cookie category to display', 'pressidium-cookie-consent')}
              selected={attributes.cookieCategory}
              options={[
                { label: __('Necessary', 'pressidium-cookie-consent'), value: 'necessary' },
                { label: __('Analytics', 'pressidium-cookie-consent'), value: 'analytics' },
                { label: __('Targeting', 'pressidium-cookie-consent'), value: 'targeting' },
                { label: __('Preferences', 'pressidium-cookie-consent'), value: 'preferences' },
              ]}
              onChange={onChangeCookieCategory}
              disabled={false}
            />
          </PanelRow>
        </PanelBody>
        <PanelBody title={__('Columns visibility', 'pressidium-cookie-consent')} initialOpen>
          <PanelRow>
            <ToggleControl
              label={__('Show name column', 'pressidium-cookie-consent')}
              checked={shouldDisplayColumn('name')}
              onChange={(value) => onChangeColumnVisibility('name', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Show domain column', 'pressidium-cookie-consent')}
              checked={shouldDisplayColumn('domain')}
              onChange={(value) => onChangeColumnVisibility('domain', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Show expiration column', 'pressidium-cookie-consent')}
              checked={shouldDisplayColumn('expiration')}
              onChange={(value) => onChangeColumnVisibility('expiration', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Show path column', 'pressidium-cookie-consent')}
              checked={shouldDisplayColumn('path')}
              onChange={(value) => onChangeColumnVisibility('path', value)}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Show description column', 'pressidium-cookie-consent')}
              checked={shouldDisplayColumn('description')}
              onChange={(value) => onChangeColumnVisibility('description', value)}
            />
          </PanelRow>
        </PanelBody>
        <PanelBody title={__('Wrap columns', 'pressidium-cookie-consent')} initialOpen>
          <PanelRow>
            <ToggleControl
              label={__('Wrap name', 'pressidium-cookie-consent')}
              checked={attributes.wrapName}
              onChange={(value) => {
                setAttributes({ wrapName: value });
              }}
            />
          </PanelRow>
          <PanelRow>
            <ToggleControl
              label={__('Wrap domain', 'pressidium-cookie-consent')}
              checked={attributes.wrapDomain}
              onChange={(value) => {
                setAttributes({ wrapDomain: value });
              }}
            />
          </PanelRow>
        </PanelBody>
      </InspectorControls>

      <InspectorControls group="color">
        <ColorGradientSettingsDropdown
          settings={[{
            label: __('Stripe', 'pressidium-cookie-consent'),
            colorValue: stripeColor.color || attributes.customStripeColor,
            onColorChange: onStripeColorChange,
          }]}
          panelId={clientId}
          hasColorsOrGradients={false}
          disableCustomColors={false}
          __experimentalIsRenderedInSidebar
          {...colorGradientSettings}
        />
      </InspectorControls>

      <div {...blockProps}>
        <table
          data-cookie-category={attributes.cookieCategory}
          className={clsx(colorProps.className, borderProps.className)}
          style={{ ...colorProps.style, ...borderProps.style }}
        >
          <thead>
            <tr>
              {COLUMN_NAMES.map((columnName) => {
                if (!shouldDisplayColumn(columnName)) {
                  return null;
                }

                return (
                  <th
                    onClick={() => setSelectedColumn(columnName)}
                    className={clsx(
                      {
                        selected: selectedColumn === columnName,
                        [`align-${headerAlignment}`]: headerAlignment,
                      },
                    )}
                  >
                    {i18nColumnNames[columnName] || columnName}
                  </th>
                );
              })}
            </tr>
          </thead>
          <tbody>
            {selectedCookies.map((cookie) => (
              <tr>
                {COLUMN_NAMES.map((columnName) => {
                  if (!shouldDisplayColumn(columnName)) {
                    return null;
                  }

                  return (
                    <td
                      data-column={columnName}
                      onClick={() => setSelectedColumn(columnName)}
                      className={clsx(
                        {
                          selected: selectedColumn === columnName,
                          [`align-${attributes.columnsAlignment?.[columnName]}`]:
                            attributes.columnsAlignment?.[columnName],
                          wrap: (columnName === 'name' && attributes.wrapName)
                            || (columnName === 'domain' && attributes.wrapDomain),
                        },
                      )}
                    >
                      {cookie[columnName]}
                    </td>
                  );
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}

export default withColors({ stripeColor: 'stripe-color' })(Edit);
