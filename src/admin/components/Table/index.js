import styled from 'styled-components';

import Header from './Header';
import Row from './Row';
import Column from './Column';
import Pagination from './Pagination';

const StyledTable = styled.div`
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

export {
  Header,
  Row,
  Column,
  Pagination,
};

export default Table;
