const reqSvgs = require.context('../icons', false, /\.svg$/);

const icons = reqSvgs.keys().reduce((acc, filePath) => {
  const iconName = filePath.replace('./', '').replace('.svg', '');
  const IconComponent = reqSvgs(filePath).default;
  acc[iconName] = IconComponent;
  return acc;
}, {});

const Icon = ({ name, colorClass, ...props }) => {
  const Component = icons[name];

  if (!Component) return null;

  const getColor = () => {
    if (colorClass === 'primary') {
      return 'var(--wp--preset--color--primary)';
    }
    return 'var(--wp--preset--color--black)';
  };

  return <Component className="icon" style={{ fill: getColor() }} {...props} />;
};

export default Icon;
