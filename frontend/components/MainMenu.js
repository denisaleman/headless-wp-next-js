import Link from 'next/link';

export default function MainMenu({ items = [] }) {
  if (!items.length) return null;

  return (
    <nav className="main-menu">
      <ul className="main-menu__list">
        {items.map(item => (
          <li key={item.id} className="main-menu__item">
            <Link href={item.url} className="main-menu__link">
              {item.title}
            </Link>
          </li>
        ))}
      </ul>

      <style jsx>{`
        .main-menu {
          background: #f8f9fa;
          padding: 0.75rem 1rem;
          border-bottom: 1px solid #e9ecef;
        }
        .main-menu__list {
          display: flex;
          gap: 1.5rem;
          list-style: none;
          margin: 0;
          padding: 0;
          align-items: center;
          justify-content: flex-start;
          flex-wrap: wrap;
        }
        .main-menu__item {
          margin: 0;
          padding: 0;
        }
        .main-menu__link {
          text-decoration: none;
          color: #333;
          font-weight: 500;
          transition: color 0.2s ease;
        }
        .main-menu__link:hover {
          color: #0070f3;
        }
        @media (max-width: 768px) {
          .main-menu__list {
            gap: 1rem;
          }
        }
      `}</style>
    </nav>
  );
}