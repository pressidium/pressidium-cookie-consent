import styled from 'styled-components';

import Header from './Header';
import Row from './Row';
import Column from './Column';

const StyledTable = styled.div`
  border: 1px solid #c3c4c7;
  display: grid;
`;

function Table(props) {
  const { children, width = 'auto', style = {} } = props;

  return (
    <StyledTable style={{ width, ...style }}>
      {children}
    </StyledTable>
  );
}

export { Header, Row, Column };

export default Table;
