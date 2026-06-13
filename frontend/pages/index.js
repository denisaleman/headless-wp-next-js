import { useState, useEffect } from 'react';
import NewsGrid from '../components/NewsGrid';

export default function Home() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    const url = `${wpUrl}/wp-json/wp/v2/news?_embed&per_page=20&order=desc&orderby=date`;

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

  if (loading) return <div>Loading news...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (
    <div className="news-page">
      <h1 className="news-page__title">Headless WordPress + Next.js</h1>
      <NewsGrid posts={posts} />
    </div>
  );
}