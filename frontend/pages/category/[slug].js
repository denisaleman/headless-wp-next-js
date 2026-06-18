import { useRouter } from 'next/router';
import PageLayout from '../../components/PageLayout';
import NewsGrid from '../../components/NewsGrid';
import { useWordPressPosts, useWordPressMenu } from '../../hooks/useWordPressData';

export default function CategoryPage() {
  const router = useRouter();
  const { slug } = router.query;

  if (!router.isReady) {
    return <div>Loading category...</div>; // or a skeleton
  }

  const { posts, loading: postsLoading, error: postsError } = useWordPressPosts(slug);
  const { menuItems, loading: menuLoading } = useWordPressMenu('header');

  if (postsLoading || menuLoading) return <div>Loading category...</div>;
  if (postsError) return <div>{postsError}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (
    <PageLayout title="Headless WordPress + Next.js" menuItems={menuItems}>
      <NewsGrid posts={posts} className="news-page__grid" />
    </PageLayout>
  );
}
