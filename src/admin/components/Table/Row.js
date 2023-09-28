import styled from 'styled-components';

const StyledRow = styled.div`
  background-color: white;
  border-left: 1px solid #c3c4c7;
  border-right: 1px solid #c3c4c7;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  &:nth-child(even) {
    background-color: #f6f7f7;
  }
  &:last-of-type {
    border-bottom: 1px solid #c3c4c7;
  }
`;

function Row(props) {
  const { children, style = {} } = props;

  return (
    <StyledRow style={style}>
      {children}
    </StyledRow>
  );
}

export default Row;
