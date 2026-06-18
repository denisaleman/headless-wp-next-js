import MainMenu from './MainMenu';
import Footer from './Footer';

export default function PageLayout({ title, menuItems, children }) {
  return (
    <div className="page-layout">
      <header className="page-layout__header">
        <h1 className="page-layout__title">{title}</h1>
        <MainMenu items={menuItems} />
      </header>
      <main className="page-layout__main">{children}</main>
      <Footer className="page-layout__footer" siteName="Headless WordPress" />
      <style jsx>{`
        .page-layout {
          display: flex;
          flex-direction: column;
          min-height: 100vh;
          max-width: 1232px;
          margin: 0 auto;
        }
        .page-layout__main {
          flex: 1;
        }
      `}</style>
    </div>
  );
}
