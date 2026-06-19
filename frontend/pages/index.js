import PageLayout from '../components/PageLayout';
import NewsGrid from '../components/NewsGrid';
import { useWordPressPageData } from '../hooks/useWordPressData';

export default function Home() {
  const { data, loading, error } = useWordPressPageData('home');

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
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
