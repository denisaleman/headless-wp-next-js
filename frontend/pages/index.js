import { useState, useEffect } from 'react';
import NewsGrid from '../components/NewsGrid';
import MainMenu from '../components/MainMenu';

export default function Home({ initialMenuItems }) {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [menuItems, setMenuItems] = useState(initialMenuItems || []);

  useEffect(() => {
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    const url = `${wpUrl}/wp-json/headless-news/v1/news?category_slug=top-news&per_page=20&orderby=date&order=desc`;

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
        setError('Failed to load news');
        setLoading(false);
      });
  }, []);

  useEffect(() => {
    if (!initialMenuItems) {
      const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
      fetch(`${wpUrl}/wp-json/headless-news/v1/menu/primary`)
        .then(res => res.json())
        .then(setMenuItems)
        .catch(console.error);
    }
  }, [initialMenuItems]);

  if (loading) return <div>Loading news...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (
    <>
        <div className="news-page">
          <h1 className="news-page__title">Headless WordPress + Next.js</h1>
          <header className="news-page__header">
            <MainMenu items={menuItems} />
          </header>
          <NewsGrid posts={posts} className="news-page__grid" />
        </div>

        <style jsx>{`
            /* ---------- Main grid ---------- */
            .news-page {
                display: flex;
                flex-direction: column;
                max-width: 1232px;
                margin: 0 auto;
            }
        `}</style>
    </>
  );
}