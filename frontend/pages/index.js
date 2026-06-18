import PageLayout from '../components/PageLayout';
import NewsGrid from '../components/NewsGrid';
import { useWordPressPosts, useWordPressMenu } from '../hooks/useWordPressData';

export default function Home() {
  const { posts, loading: postsLoading, error: postsError } = useWordPressPosts('top-news');
  const { menuItems, loading: menuLoading } = useWordPressMenu();

  if (postsLoading || menuLoading) return <div>Loading news...</div>;
  if (postsError) return <div>Error: {postsError}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (
    <PageLayout title="Headless WordPress + Next.js" menuItems={menuItems}>
      <NewsGrid posts={posts} className="news-page__grid" />
    </PageLayout>
  );
}
