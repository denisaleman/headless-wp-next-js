import { useRouter } from 'next/router';
import PageLayout from '../../components/PageLayout';
import NewsGrid from '../../components/NewsGrid';
import { useWordPressPosts, useWordPressMenu } from '../../hooks/useWordPressData';

export default function CategoryPage() {
  const router = useRouter();
  const { slug } = router.query;

  const { posts, loading: postsLoading, error: postsError } = useWordPressPosts(slug || '');
  const { menuItems, loading: menuLoading } = useWordPressMenu();

  if (postsLoading || menuLoading) return <div>Loading category...</div>;
  if (postsError) return <div>{postsError}</div>;

  return (
    <PageLayout title="Headless WordPress + Next.js" menuItems={menuItems}>
      <NewsGrid posts={posts} className="news-page__grid" />
    </PageLayout>
  );
}
