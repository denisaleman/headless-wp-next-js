export default function Footer({ children, className = '', additionalClasses = [], siteName = '' }) {
  const mandatoryClasses = ['footer'];
  const combinedClasses = [...mandatoryClasses, className, ...additionalClasses].filter(Boolean).join(' ');
  const year = new Date().getFullYear();

  return (
    <footer className={combinedClasses}>
      <div className="footer__inner">
        <p className="footer__copyright">
          &copy; {year} {siteName}. All rights reserved.
        </p>
        <div className="footer__social">
          {/* Placeholder social icons – replace with actual links/icons */}
          <a href="#" className="footer__social-link" aria-label="Twitter">Twitter</a>
          <a href="#" className="footer__social-link" aria-label="Facebook">Facebook</a>
          <a href="#" className="footer__social-link" aria-label="LinkedIn">LinkedIn</a>
        </div>
      </div>
      <style jsx>{`
        .footer {
          margin-top: 3rem;
          padding: 1.5rem 1rem;
          border-top: 1px solid #ddd;
        }
        .footer__inner {
          max-width: 1232px;
          margin: 0 auto;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          gap: 1rem;
        }
        .footer__copyright {
          margin: 0;
          font-size: 0.875rem;
          color: #000000;
        }
        .footer__social {
          display: flex;
          gap: 1rem;
        }
        .footer__social-link {
          color: #000000;
          text-decoration: none;
          font-size: 0.875rem;
        }
        .footer__social-link:hover {
          color: #0070f3;
        }
        @media (max-width: 600px) {
          .footer__inner {
            flex-direction: column;
            text-align: center;
          }
        }
      `}</style>
    </footer>
  );
}
