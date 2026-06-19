import { useRouter } from 'next/router';
import PageLayout from '../../components/PageLayout';
import NewsGrid from '../../components/NewsGrid';
import { useWordPressPageData } from '../../hooks/useWordPressData';

export default function CategoryPage() {
  const router = useRouter();
  const { slug } = router.query;

  // Build the endpoint only when slug is available
  const endpoint = slug ? `category/${slug}` : null;
  const { data, loading, error } = useWordPressPageData(endpoint);

  if (!router.isReady || loading) return <div>Loading category...</div>;
  if (error) return <div>{error}</div>;
  if (!data || !data.posts || !data.posts.length) return <div>No news found.</div>;

  return (
    <PageLayout
      title="Headless WordPress + Next.js"
      headerMenu={data.header}
      footerMenus={data.footer}
    >
      <NewsGrid posts={data.posts} />
    </PageLayout>
  );
}
