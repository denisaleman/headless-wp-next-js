import NewsGrid from '../components/NewsGrid';
import MainMenu from '../components/MainMenu';
import { useWordPressPosts, useWordPressMenu } from '../hooks/useWordPressData';

export default function Home() {
  const { posts, loading: postsLoading, error: postsError } = useWordPressPosts('top-news');
  const { menuItems, loading: menuLoading, error: menuError } = useWordPressMenu();

  if (postsLoading || menuLoading) return <div>Loading news...</div>;
  if (postsError) return <div>Error: {postsError}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (
    <div className="news-page">
      <header className="news-page__header">
        <h1 className="news-page__title">Headless WordPress + Next.js</h1>
        <MainMenu items={menuItems} />
      </header>
      <NewsGrid posts={posts} className="news-page__grid" />
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
