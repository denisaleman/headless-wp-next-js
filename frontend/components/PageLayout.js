import MainMenu from './MainMenu';

export default function PageLayout({ title, menuItems, children }) {
  return (
    <div className="news-page">
      <header className="news-page__header">
        <h1 className="news-page__title">{title}</h1>
        <MainMenu items={menuItems} />
      </header>
      <main>{children}</main>
      <style jsx>{`
        .news-page {
          display: flex;
          flex-direction: column;
          max-width: 1232px;
          margin: 0 auto;
        }
      `}</style>
    </div>
  );
}
