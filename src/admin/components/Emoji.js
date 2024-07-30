function Emoji({ label, symbol, style = {} }) {
  return (
    <span
      role="img"
      aria-label={label || ''}
      aria-hidden={label ? 'false' : 'true'}
      style={{ ...style }}
    >
      {symbol}
    </span>
  );
}

export default Emoji;
