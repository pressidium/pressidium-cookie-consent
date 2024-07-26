import { createInterpolateElement } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  PanelRow,
  Flex,
  FlexItem,
  Button,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { file as FileIcon, link as LinkIcon } from '@wordpress/icons';

import Table, { Header, Row, Column } from './Table';

function TagManagerGuide() {
  const repoUrl = 'https://github.com/pressidium/pressidium-cookie-consent-gtm-template/';
  const communityTemplateGalleryUrl = 'https://tagmanager.google.com/gallery/#/owners/pressidium/templates/pressidium-cookie-consent-gtm-template';

  const {
    screenshots = {},
    gtm_template_url: templateUrl = repoUrl,
  } = pressidiumCCAdminDetails.assets;

  return (
    <Panel>
      <PanelBody
        title={__('Adding the Google Tag Manager template', 'pressidium-cookie-consent')}
        initialOpen
      >
        <PanelRow>
          <Button
            icon={LinkIcon}
            href={communityTemplateGalleryUrl}
            target="_blank"
            isPrimary
          >
            {__('Community Template Gallery', 'pressidium-cookie-consent')}
          </Button>
        </PanelRow>
        <PanelRow>
          <img
            src={screenshots.gallery}
            alt={__('Adding a template via the Community Template Gallery screenshot', 'pressidium-cookie-consent')}
            style={{ width: '100%' }}
          />
        </PanelRow>
        <PanelRow>
          <ol>
            <li>
              {
                createInterpolateElement(
                  __('Navigate to <a>Google Tag Manager</a>', 'pressidium-cookie-consent'),
                  {
                    a: (
                      // eslint-disable-next-line max-len
                      // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                      <a
                        href="https://tagmanager.google.com/"
                        target="_blank"
                        rel="noreferrer noopener"
                      />
                    ),
                  },
                )
              }
            </li>
            <li>
              {__('Select your workspace', 'pressidium-cookie-consent')}
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Navigate to the <strong>Templates</strong> tab', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>Search Gallery</strong>, under the Tag Templates section', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Search for <strong>Pressidium Cookie Consent</strong>', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Select it and click <strong>Add to workspace</strong>', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
          </ol>
        </PanelRow>
      </PanelBody>
      <PanelBody
        title={__('Manually importing the Google Tag Manager template', 'pressidium-cookie-consent')}
        initialOpen={false}
      >
        <PanelRow>
          <Flex justify="flex-start">
            <FlexItem>
              <Button
                icon={FileIcon}
                href={templateUrl}
                isPrimary
              >
                {__('GTM custom template', 'pressidium-cookie-consent')}
              </Button>
            </FlexItem>
            <FlexItem>
              <Button
                icon={LinkIcon}
                href={repoUrl}
                target="_blank"
                isSecondary
              >
                {__('Template GitHub repository', 'pressidium-cookie-consent')}
              </Button>
            </FlexItem>
          </Flex>
        </PanelRow>
        <PanelRow>
          <img
            src={screenshots.import}
            alt={__('Google Tag Manager importing a template screenshot', 'pressidium-cookie-consent')}
            style={{ width: '100%' }}
          />
        </PanelRow>
        <PanelRow>
          <Flex
            direction="column"
            gap={0}
          >
            <FlexItem>
              <p>
                {__('Alternatively, to manually import the template, follow these steps:', 'pressidium-cookie-consent')}
              </p>
            </FlexItem>
            <FlexItem>
              <ol>
                <li>
                  {
                    createInterpolateElement(
                      __('Download the GTM <a>custom template</a>', 'pressidium-cookie-consent'),
                      {
                        a: (
                          // eslint-disable-next-line max-len
                          // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                          <a
                            href={templateUrl}
                            target="_blank"
                            rel="noreferrer noopener"
                          />
                        ),
                      },
                    )
                  }
                </li>
                <li>
                  {
                    createInterpolateElement(
                      __('Navigate to <a>Google Tag Manager</a>', 'pressidium-cookie-consent'),
                      {
                        a: (
                          // eslint-disable-next-line max-len
                          // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                          <a
                            href="https://tagmanager.google.com/"
                            target="_blank"
                            rel="noreferrer noopener"
                          />
                        ),
                      },
                    )
                  }
                </li>
                <li>
                  {__('Select your workspace', 'pressidium-cookie-consent')}
                </li>
                <li>
                  {
                    createInterpolateElement(
                      __('Navigate to the <strong>Templates</strong> tab', 'pressidium-cookie-consent'),
                      {
                        strong: <strong/>,
                      },
                    )
                  }
                </li>
                <li>
                  {
                    createInterpolateElement(
                      __('Click <strong>New</strong>, under the Tag Templates section', 'pressidium-cookie-consent'),
                      {
                        strong: <strong/>,
                      },
                    )
                  }
                </li>
                <li>
                  {__('Select the kebab menu (three dots) located at the top-right corner of the Template Editor', 'pressidium-cookie-consent')}
                </li>
                <li>
                  {
                    createInterpolateElement(
                      __('Click <strong>Import</strong>', 'pressidium-cookie-consent'),
                      {
                        strong: <strong/>,
                      },
                    )
                  }
                </li>
                <li>
                  {
                    createInterpolateElement(
                      __('Select the previously downloaded <code>template.tpl</code> file', 'pressidium-cookie-consent'),
                      {
                        code: <code/>,
                      },
                    )
                  }
                </li>
                <li>
                  {
                    createInterpolateElement(
                      __('Click <strong>Save</strong>', 'pressidium-cookie-consent'),
                      {
                        strong: <strong/>,
                      },
                    )
                  }
                </li>
                <li>
                  {__('Close the Template Editor', 'pressidium-cookie-consent')}
                </li>
              </ol>
            </FlexItem>
          </Flex>
        </PanelRow>
      </PanelBody>
      <PanelBody
        title={__('Creating a new tag', 'pressidium-cookie-consent')}
        initialOpen={false}
      >
        <PanelRow>
          <img
            src={screenshots.tag}
            alt={__('Google Tag Manager creating a tag screenshot', 'pressidium-cookie-consent')}
            style={{ width: '100%' }}
          />
        </PanelRow>
        <PanelRow>
          <ol>
            <li>
              {
                createInterpolateElement(
                  __('Navigate to <a>Google Tag Manager</a>', 'pressidium-cookie-consent'),
                  {
                    a: (
                      // eslint-disable-next-line max-len
                      // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                      <a
                        href="https://tagmanager.google.com/"
                        target="_blank"
                        rel="noreferrer noopener"
                      />
                    ),
                  },
                )
              }
            </li>
            <li>
              {__('Select your workspace', 'pressidium-cookie-consent')}
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Navigate to the <strong>Tags</strong> tab', 'pressidium-cookie-consent'),
                  {
                    strong: <strong/>,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>New</strong> to create a new tag', 'pressidium-cookie-consent'),
                  {
                    strong: <strong/>,
                  },
                )
              }
            </li>
            <li>
              {__('Give the tag a name (e.g. Cookie Consent)', 'pressidium-cookie-consent')}
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>Choose a tag type to begin setup</strong>, under Tag Configuration', 'pressidium-cookie-consent'),
                  {
                    strong: <strong/>,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Select the <strong>Pressidium Cookie Consent</strong> template, under the Custom section', 'pressidium-cookie-consent'),
                  {
                    strong: <strong/>,
                  },
                )
              }
            </li>
            <li>
              {__('Configure the tag as needed', 'pressidium-cookie-consent')}
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>Choose a trigger to make this tag fire</strong>, under the Triggering section', 'pressidium-cookie-consent'),
                  {
                    strong: <strong/>,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Select <strong>Consent Initialization - All Pages</strong>', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>New</strong> to create a new trigger', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>Save</strong>', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Click <strong>Submit</strong> to submit your changes', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
          </ol>
        </PanelRow>
      </PanelBody>
      <PanelBody
        title={__('Configuring the Pressidium Cookie Consent tag', 'pressidium-cookie-consent')}
        initialOpen={false}
      >
        <PanelRow>
          <img
            src={screenshots.config}
            alt={__('Google Tag Manager configuring a tag screenshot', 'pressidium-cookie-consent')}
            style={{ width: '100%' }}
          />
        </PanelRow>
        <PanelRow>
          <ol style={{ width: 'calc(100% - 2em)' }}>
            <li>
              {
                createInterpolateElement(
                  __('Navigate to <a>Google Tag Manager</a>', 'pressidium-cookie-consent'),
                  {
                    a: (
                      // eslint-disable-next-line max-len
                      // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                      <a
                        href="https://tagmanager.google.com/"
                        target="_blank"
                        rel="noreferrer noopener"
                      />
                    ),
                  },
                )
              }
            </li>
            <li>
              {__('Select your workspace', 'pressidium-cookie-consent')}
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Navigate to the <strong>Tags</strong> tab', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
            <li>
              {__('Select the previously created Cookie Consent tag', 'pressidium-cookie-consent')}
            </li>
            <li>
              <p>
                {__('Under the Tag Configuration section, you can configure the following settings:', 'pressidium-cookie-consent')}
              </p>
              <p>
                <ul style={{ listStyleType: 'square', marginLeft: '2em' }}>
                  <li>
                    {__('Default consent states', 'pressidium-cookie-consent')}
                  </li>
                  <li>
                    {__('Ads data redaction', 'pressidium-cookie-consent')}
                  </li>
                  <li>
                    {__('URL passthrough', 'pressidium-cookie-consent')}
                  </li>
                </ul>
              </p>
              <p>
                {
                  createInterpolateElement(
                    __('Under Default consent states, click <strong>Add Row</strong> to add a new row for each region. For each row, you can set the regions as a comma-separated list of <a>ISO 3166-2</a> codes (leave blank to have your selection apply to all regions) and the default consent state for each consent type', 'pressidium-cookie-consent'),
                    {
                      strong: <strong />,
                      a: (
                        // eslint-disable-next-line max-len
                        // eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/control-has-associated-label
                        <a
                          href="https://en.wikipedia.org/wiki/ISO_3166-2"
                          target="_blank"
                          rel="noreferrer noopener"
                        />
                      ),
                    },
                  )
                }
              </p>
              <p>
                {
                  createInterpolateElement(
                    __('If two rows are set with values for a region and subregion, the one with a more specific region will take effect. For example, if you have <code>ad_storage</code> set to <code>true</code> for <code>US</code>, and <code>ad_storage</code> set to <code>false</code> for <code>US-CA</code>, a visitor from California will have the more specific <code>US-CA</code> setting take effect. For this example, that would mean a visitor from California would have <code>ad_storage</code> set to <code>false</code>.', 'pressidium-cookie-consent'),
                    {
                      code: <code />,
                    },
                  )
                }
              </p>
              <p style={{ overflowX: 'scroll' }}>
                <Table style={{ width: '600px' }}>
                  <Header>
                    <Column style={{ maxWidth: '120px' }}>
                      {__('Region', 'pressidium-cookie-consent')}
                    </Column>
                    <Column style={{ maxWidth: '120px' }}>
                      {__('Ad storage', 'pressidium-cookie-consent')}
                    </Column>
                    <Column>
                      {__('Behavior', 'pressidium-cookie-consent')}
                    </Column>
                  </Header>
                  <Row>
                    <Column style={{ maxWidth: '120px' }}>
                      <code>{__('US', 'pressidium-cookie-consent')}</code>
                    </Column>
                    <Column style={{ maxWidth: '120px' }}>
                      <code>{__('true', 'pressidium-cookie-consent')}</code>
                    </Column>
                    <Column>
                      {__('Applies to all US states except California', 'pressidium-cookie-consent')}
                    </Column>
                  </Row>
                  <Row>
                    <Column style={{ maxWidth: '120px' }}>
                      <code>{__('US-CA', 'pressidium-cookie-consent')}</code>
                    </Column>
                    <Column style={{ maxWidth: '120px' }}>
                      <code>{__('false', 'pressidium-cookie-consent')}</code>
                    </Column>
                    <Column>
                      {__('Applies to California', 'pressidium-cookie-consent')}
                    </Column>
                  </Row>
                  <Row>
                    <Column style={{ maxWidth: '120px' }}>
                      {__('Unspecified', 'pressidium-cookie-consent')}
                    </Column>
                    <Column style={{ maxWidth: '120px' }}>
                      <code>{__('true', 'pressidium-cookie-consent')}</code>
                    </Column>
                    <Column>
                      {__('Applies to all other regions', 'pressidium-cookie-consent')}
                    </Column>
                  </Row>
                </Table>
              </p>
            </li>
            <li>
              {
                createInterpolateElement(
                  __('Once you are done configuring the tag, click <strong>Submit</strong> to submit your changes', 'pressidium-cookie-consent'),
                  {
                    strong: <strong />,
                  },
                )
              }
            </li>
          </ol>
        </PanelRow>
      </PanelBody>
    </Panel>
  );
}

export default TagManagerGuide;
