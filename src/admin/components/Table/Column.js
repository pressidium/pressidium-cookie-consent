import styled from 'styled-components';

const StyledColumn = styled.div`
  padding: 8px 10px;
  text-align: left;
  flex: 1 1 0;
  width: 0;
  min-width: 0;
`;

function Column(props) {
  const {
    children,
    width = 'auto',
    className = '',
    style = {},
  } = props;

  return (
    <StyledColumn className={className} style={{ width, ...style }}>
      {children}
    </StyledColumn>
  );
}

export default Column;
