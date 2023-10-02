import {
  Flex,
  FlexItem,
  Button,
} from '@wordpress/components';
import {
  __,
  _x,
  _n,
  sprintf,
} from '@wordpress/i18n';

import styled from 'styled-components';

const StyledButton = styled(Button)`
  height: auto;
  min-width: 30px;
  min-height: 30px;
  margin: 0;
  padding: 0 4px;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
`;

function Pagination(props) {
  const {
    currentPage,
    numPages,
    changePage,
    totalItems,
    className = '',
    style = {},
  } = props;

  return (
    <Flex className={className} style={style}>
      <FlexItem>
        <span>
          {
            sprintf(
              // translators: %s: Total number of items.
              _n('%s item', '%s items', totalItems),
              totalItems,
            )
          }
        </span>
      </FlexItem>
      <FlexItem>
        <Flex>
          <FlexItem>
            <StyledButton
              variant="secondary"
              onClick={() => changePage(1)}
              disabled={currentPage === 1}
              aria-label={__('First page', 'pressidium-cookie-consent')}
            >
              &laquo;
            </StyledButton>
          </FlexItem>
          <FlexItem>
            <StyledButton
              variant="secondary"
              onClick={() => changePage(currentPage - 1)}
              disabled={currentPage === 1}
              aria-label={__('Previous page', 'pressidium-cookie-consent')}
            >
              &lsaquo;
            </StyledButton>
          </FlexItem>
        </Flex>
      </FlexItem>
      <FlexItem>
        <span>
          {
            sprintf(
              // translators: %1$s: Current page number, %2$s: Total number of pages.
              _x('%1$s of %2$s', 'pagination', 'pressidium-cookie-consent'),
              currentPage,
              numPages,
            )
          }
        </span>
      </FlexItem>
      <FlexItem>
        <Flex>
          <FlexItem>
            <StyledButton
              variant="secondary"
              onClick={() => changePage(currentPage + 1)}
              disabled={currentPage === numPages}
              aria-label={__('Next page', 'pressidium-cookie-consent')}
            >
              &rsaquo;
            </StyledButton>
          </FlexItem>
          <FlexItem>
            <StyledButton
              variant="secondary"
              onClick={() => changePage(numPages)}
              disabled={currentPage === numPages}
              aria-label={__('Last page', 'pressidium-cookie-consent')}
            >
              &raquo;
            </StyledButton>
          </FlexItem>
        </Flex>
      </FlexItem>
    </Flex>
  );
}

export default Pagination;
