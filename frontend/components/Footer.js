import FooterMenu from './FooterMenu';

export default function Footer({
  className = '',
  additionalClasses = [],
  siteName = 'Headless WordPress',
  menusData = {},
}) {
  const mandatoryClasses = ['footer'];
  const combinedClasses = [...mandatoryClasses, className, ...additionalClasses]
    .filter(Boolean)
    .join(' ');
  const year = new Date().getFullYear();

  const menuLocations = Object.keys(menusData);

  return (
    <footer className={combinedClasses}>
      <div className="footer__inner">
        {menuLocations.length > 0 && (
          <div className="footer__menus">
            {menuLocations.map((location) => (
              <FooterMenu key={location} menuData={menusData[location]} />
            ))}
          </div>
        )}

        <div className="footer__bottom">
          <p className="footer__copyright">
            &copy; {year} {siteName}. All rights reserved.
          </p>
          <div className="footer__social">
            <a href="#" className="footer__social-link" aria-label="Twitter">Twitter</a>
            <a href="#" className="footer__social-link" aria-label="Facebook">Facebook</a>
            <a href="#" className="footer__social-link" aria-label="LinkedIn">LinkedIn</a>
          </div>
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
        }
        .footer__menus {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(160px, max-content));
          gap: 2rem;
          margin-bottom: 1.5rem;
        }
        .footer__bottom {
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          gap: 1rem;
          margin-top: 1rem;
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
          .footer__bottom {
            flex-direction: column;
            text-align: center;
          }
        }
      `}</style>
    </footer>
  );
}
