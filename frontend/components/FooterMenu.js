import Link from 'next/link';

export default function FooterMenu({
  menuData,
  className = '',
  additionalClasses = [],
}) {
  const mandatoryClasses = ['footer-menu'];
  const combinedClasses = [...mandatoryClasses, className, ...additionalClasses]
    .filter(Boolean)
    .join(' ');

  if (!menuData || !menuData.items || !menuData.items.length) return null;

  return (
    <div className={combinedClasses}>
      {menuData.name && <h3 className="footer-menu__title">{menuData.name}</h3>}
      <ul className="footer-menu__list">
        {menuData.items.map((item) => (
          <li key={item.id} className="footer-menu__item">
            <Link href={item.url} className="footer-menu__link">
              {item.title}
            </Link>
          </li>
        ))}
      </ul>

      <style jsx>{`
        .footer-menu {
          margin: 0;
        }
        .footer-menu__title {
          font-size: 1rem;
          font-weight: 600;
          margin-bottom: 0.75rem;
        }
        .footer-menu__list {
          list-style: none;
          margin: 0;
          padding: 0;
        }
        .footer-menu__item {
          margin-bottom: 0.5rem;
        }
        .footer-menu__link {
          color: #666;
          text-decoration: none;
          font-size: 0.875rem;
        }
        .footer-menu__link:hover {
          color: #0070f3;
        }
      `}</style>
    </div>
  );
}
