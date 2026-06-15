import { useRouter } from 'next/router';
import { useState, useEffect } from 'react';
import MainMenu from '../components/MainMenu';
import Title from '../components/typography/Title';

export default function CatchAllPage({ initialMenuItems }) {
  const router = useRouter();
  const { slug } = router.query; // slug is an array of path segments
  const [post, setPost] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [menuItems, setMenuItems] = useState(initialMenuItems || []);

  useEffect(() => {
    if (!slug || slug.length === 0) return;
    const postSlug = slug[0]; // first segment is the post slug
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    fetch(`${wpUrl}/wp-json/headless-news/v1/news/slug/${postSlug}`)
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then(data => {
        setPost(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setError('Article not found');
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

  if (loading) return <div>Loading...</div>;
  if (error) return <div>{error}</div>;
  if (!post) return null;

  return (
    <div className="news-page">
      <header className="news-page__header">
        <MainMenu items={menuItems} />
      </header>
      <article className="news-article">
        {post.featured_image?.url && (
			<figure className="news-article__featured-image">
            <img src={post.featured_image.url} alt={post.title} />
          </figure>
        )}
		<Title tag="h1" size="h1" className="news-article__title">{post.title}</Title>
		<div className="news-article__lead">{post.excerpt}</div>
        <div className="news-article__content" dangerouslySetInnerHTML={{ __html: post.content }} />
        <div className="news-article__meta">
          {post.date && <time dateTime={post.date}>{new Date(post.date).toLocaleDateString()}</time>}
          {post.author && <span>By {post.author}</span>}
          {post.source_url && <a href={post.source_url} target="_blank" rel="noopener noreferrer">Original source</a>}
        </div>
      </article>
      <style jsx>{`
        .news-page {
          max-width: 1232px;
          margin: 0 auto;
          padding: 0 1rem;
        }
        .news-article {
          padding: 0 1rem;
        }
        .news-article__featured-image {
          margin: 1.5rem 0;
        }
        .news-article__featured-image img {
          width: 100%;
          height: auto;
        }
        .news-article__lead {
          font-size: 1.70rem;
          width: 65ch;
        }
        .news-article__content {
          max-width: 80ch;
          font-size: 1.125rem;
          line-height: 1.6;
          margin-bottom: 2rem;
        }
        .news-article__content p {
          margin-bottom: 1rem;
        }
        .news-article__meta {
          display: flex;
          gap: 1rem;
          align-items: center;
          flex-wrap: wrap;
          border-top: 1px solid #ddd;
          padding-top: 1rem;
          color: #666;
          font-size: 0.875rem;
        }
        .news-article__source {
          color: #0070f3;
          text-decoration: none;
        }
      `}</style>
      <style jsx global>{`
        .title.news-article__title {
          font-size: 3rem;
          line-height: 3.2rem;
          margin-bottom: 1.5rem;
        }
      `}</style>
    </div>
  );
}