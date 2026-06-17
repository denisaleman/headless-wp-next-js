import { useRouter } from 'next/router';
import NewsGrid from '../../components/NewsGrid';
import MainMenu from '../../components/MainMenu';
import { useWordPressPosts, useWordPressMenu } from '../../hooks/useWordPressData';

export default function CategoryPage() {
  const router = useRouter();
  const { slug } = router.query;

  const {
    posts,
    loading: postsLoading,
    error: postsError,
  } = useWordPressPosts(slug || '');

  const { menuItems, loading: menuLoading, error: menuError } = useWordPressMenu();

  if (postsLoading || menuLoading) return <div>Loading category...</div>;
  if (postsError) return <div>{postsError}</div>;

  const categoryName = slug
    ? slug.charAt(0).toUpperCase() + slug.slice(1).replace(/-/g, ' ')
    : 'Category';

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