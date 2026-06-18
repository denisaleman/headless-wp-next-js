import PageLayout from '../components/PageLayout';
import NewsGrid from '../components/NewsGrid';
import { useWordPressPosts, useWordPressMenu, useWordPressFooterMenus } from '../hooks/useWordPressData';

export default function Home() {
  const { posts, loading: postsLoading, error: postsError } = useWordPressPosts('top-news');
  const { menuItems: headerMenu, loading: menuLoading } = useWordPressMenu('header');
  const { menusData: footerMenus, loading: footerLoading } = useWordPressFooterMenus(['footer-categories', 'footer-about', 'footer-legal']);

  if (postsLoading || menuLoading || footerLoading) return <div>Loading...</div>;
  if (postsError) return <div>Error: {postsError}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (

    <PageLayout
      title="Headless WordPress + Next.js"
      headerMenu={headerMenu}
      footerMenus={footerMenus}
    >
      <NewsGrid posts={posts} />
    </PageLayout>
  );
}
