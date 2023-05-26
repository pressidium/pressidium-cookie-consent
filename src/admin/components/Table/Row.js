import styled from 'styled-components';

const StyledRow = styled.div`
  background-color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  &:nth-child(even) {
    background-color: #f6f7f7;
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
