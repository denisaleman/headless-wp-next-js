import { useState, useEffect } from 'react';

export default function Home() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    // Add _embed to include featured images and other embedded data
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

  return (
    <div>
      <h1>Headless WordPress + Next.js</h1>
      <div className="news-grid">
        {posts.map(post => {
          // Featured image URL from _embedded
          const featuredImage = post._embedded?.['wp:featuredmedia']?.[0]?.source_url;

          return (
            <article key={post.id} className="news-card">
              {featuredImage && (
                <img src={featuredImage} alt={post.title.rendered} className="featured-image" />
              )}
              <h2>{post.title.rendered}</h2>
              <div dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
            </article>
          );
        })}
      </div>
    </div>
  );
}