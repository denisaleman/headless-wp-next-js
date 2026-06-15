import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import NewsGrid from '../../components/NewsGrid';
import MainMenu from '../../components/MainMenu';

export default function CategoryPage({ initialMenuItems }) {
  const router = useRouter();
  const { slug } = router.query;

  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [menuItems, setMenuItems] = useState(initialMenuItems || []);

  useEffect(() => {
    if (!slug) return;
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    const url = `${wpUrl}/wp-json/headless-news/v1/news?category_slug=${slug}&per_page=20&orderby=date&order=desc`;
    fetch(url)
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then(data => {
        setPosts(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setError('Failed to load news for this category');
        setLoading(false);
      });
  }, [slug]);

  useEffect(() => {
    if (!initialMenuItems) {
      const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
      fetch(`${wpUrl}/wp-json/headless-news/v1/menu/primary`)
        .then(res => res.json())
        .then(setMenuItems)
        .catch(console.error);
    }
  }, [initialMenuItems]);

  if (loading) return <div>Loading category...</div>;
  if (error) return <div>{error}</div>;

  // Get category name from the first post's categories (or from menu)
  const categoryName = slug ? slug.charAt(0).toUpperCase() + slug.slice(1).replace(/-/g, ' ') : 'Category';

  return (
    <div className="news-page">
      <header className="news-page__header">
        <MainMenu items={menuItems} />
      </header>
      <h1 className="news-page__title">{categoryName}</h1>
      {posts.length === 0 ? (
        <p>No news found in this category.</p>
      ) : (
        <NewsGrid posts={posts} className="news-page__grid" />
      )}
      <style jsx>{`
        .news-page {
          display: flex;
          flex-direction: column;
          max-width: 1232px;
          margin: 0 auto;
        }
        .news-page__title {
          margin: 1rem 0;
        }
      `}</style>
    </div>
  );
}