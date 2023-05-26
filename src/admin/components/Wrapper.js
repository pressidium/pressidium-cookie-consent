import styled from 'styled-components';

const StyledWrapper = styled.div`
  border: 1px solid rgba(0, 0, 0, 0.1);
  padding: 16px;
`;

function Wrapper(props) {
  const { children, style = {} } = props;

  return (
    <StyledWrapper style={style}>
      {children}
    </StyledWrapper>
  );
}

export default Wrapper;
