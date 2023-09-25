import styled from 'styled-components';

const StyledHeader = styled.div`
  background-color: white;
  border: 1px solid #c3c4c7;
  display: flex;
  justify-content: space-between;
  padding: 8px 10px;
`;

function Header(props) {
  const { children } = props;

  return (
    <StyledHeader>
      {children}
    </StyledHeader>
  );
}

export default Header;
