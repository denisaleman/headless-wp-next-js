export default function Title({ tag: Tag = 'h1', size, children, className = '', additionalClasses = [] }) {
  const actualSize = size || Tag;
  const sizeClass = `title--${actualSize}`;
  const mandatoryClasses = ['title', sizeClass];
  const combinedClasses = [...mandatoryClasses, className, ...additionalClasses].filter(Boolean).join(' ');

  return (
    <>
      <Tag className={combinedClasses}>{children}</Tag>
      <style jsx>{`
        .title {
          margin: 0;
          line-height: 1.2;
        }
        .title--h1 {
          font-size: 2.5rem;
          font-weight: 700;
        }
        .title--h2 {
          font-size: 2rem;
          font-weight: 600;
        }
        .title--h3 {
          font-size: 1.5rem;
          font-weight: 600;
        }
        .title--h4 {
          font-size: 1.25rem;
          font-weight: 500;
        }
        .title--h5 {
          font-size: 1rem;
          font-weight: 500;
        }
        .title--h6 {
          font-size: 0.875rem;
          font-weight: 500;
        }

        
      `}</style>
    </>
  );
}