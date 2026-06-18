export default function Lead({ children, className = '', additionalClasses = [] }) {
  const mandatoryClasses = ['lead'];
  const combinedClasses = [...mandatoryClasses, className, ...additionalClasses].filter(Boolean).join(' ');

  return (
    <div className={combinedClasses}>
      {children}
      <style jsx>{`
        .lead {
          font-size: 1.70rem;
          max-width: 65ch;
          margin: 0;
        }
      `}</style>
    </div>
  );
}
