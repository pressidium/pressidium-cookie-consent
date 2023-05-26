import styled from 'styled-components';

const StyledPanel = styled.div`
  background-color: #ffffff;
`;

function Panel(props) {
  const { children } = props;

  return (
    <StyledPanel>
      {children}
    </StyledPanel>
  );
}

export default Panel;
