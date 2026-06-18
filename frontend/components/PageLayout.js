import HeaderMenu from './HeaderMenu';
import Footer from './Footer';

export default function PageLayout({
  title,
  headerMenu = {},
  footerMenus = {},
  children,
}) {
  return (
    <div className="page-layout">
      <header className="page-layout__header">
        <h1 className="page-layout__title">{title}</h1>
        <HeaderMenu menuData={headerMenu} />
      </header>
      <main className="page-layout__main">{children}</main>
      <Footer siteName="Headless WordPress" menusData={footerMenus} />
      <style jsx>{`
        .page-layout {
          display: flex;
          flex-direction: column;
          min-height: 100vh;
          max-width: 1232px;
          margin: 0 auto;
        }
        .page-layout__title {
          padding-left: 1rem;
          padding-right: 1rem;
        }
        .page-layout__main {
          flex: 1;
        }
      `}</style>
    </div>
  );
}
