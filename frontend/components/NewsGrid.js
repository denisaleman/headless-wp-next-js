import { getThumbnail } from '../lib/utils';

export default function NewsGrid({ posts, className = '', additionalClasses = [] }) {
  if (!posts || posts.length === 0) return null;

  const combinedClasses = ['news-grid', className, ...additionalClasses].filter(Boolean).join(' ');

  const heroPost = posts[0];
  const column1Posts = posts.slice(1, 4);
  const column2UnderPosts = posts.slice(4, 6);
  const column3Posts = posts.slice(6, 6 + 6);

  return (
    <div className={combinedClasses}>
      {/* Column 1 – text only */}
      <div className="news-grid__col news-grid__col--1">
        {column1Posts.map(post => (
          <article key={post.id} className="news-grid__card news-grid__card--text-only">
            <h3 className="news-grid__card-title">{post.title.rendered}</h3>
            <div className="news-grid__card-excerpt" dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
          </article>
        ))}
      </div>

      {/* Column 2 – hero + subnews-grid */}
      <div className="news-grid__col news-grid__col--2">
        <article className="news-grid__hero">
          {heroPost._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
            <img
              className="news-grid__hero-image"
              src={heroPost._embedded['wp:featuredmedia'][0].source_url}
              alt={heroPost.title.rendered}
            />
          )}
          <h2 className="news-grid__hero-title">{heroPost.title.rendered}</h2>
          <div className="news-grid__hero-excerpt" dangerouslySetInnerHTML={{ __html: heroPost.excerpt.rendered }} />
        </article>

        {/* Subgrid as an element of the main grid block */}
        <div className="news-grid__subgrid">
          {column2UnderPosts.map(post => (
            <article key={post.id} className="news-grid__subgrid-item">
              {post._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
                <img
                  className="news-grid__subgrid-image"
                  src={post._embedded['wp:featuredmedia'][0].source_url}
                  alt={post.title.rendered}
                />
              )}
              <h3 className="news-grid__subgrid-title">{post.title.rendered}</h3>
              <div className="news-grid__subgrid-excerpt" dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
            </article>
          ))}
        </div>
      </div>

      {/* Column 3 – vertical blocks */}
      <div className="news-grid__col news-grid__col--3">
        {column3Posts.map(post => {
          const thumb = getThumbnail(post);
          return (
            <article key={post.id} className="news-grid__vertical-block">
              <div className="news-grid__vertical-inner">
                <h3 className="news-grid__vertical-title">{post.title.rendered}</h3>
                {thumb && (
                  <img className="news-grid__vertical-image" src={thumb} alt={post.title.rendered} />
                )}
              </div>
            </article>
          );
        })}
      </div>

      <style jsx>{`
        /* Block: grid */
        .news-grid {
          display: grid;
          grid-template-columns: 1fr 2fr 1fr;
          gap: 2rem;
          max-width: 1200px;
          margin: 0 auto;
          padding: 1rem;
        }
        .news-grid__col {
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }
        /* Card in column 1 */
        .news-grid__card--text-only {
          border-bottom: 1px solid #eee;
          padding-bottom: 1rem;
        }
        /* Hero in column 2 */
        .news-grid__hero-image {
          width: 100%;
          height: auto;
        }
        /* Subgrid (two‑column layout) */
        .news-grid__subgrid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
        }
        .news-grid__subgrid-image {
          width: 100%;
          height: auto;
        }
        /* Vertical blocks in column 3 */
        .news-grid__vertical-block {
          border-bottom: 1px dotted #ddd;
          padding-bottom: 1rem;
        }
        .news-grid__vertical-inner {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
        }
        .news-grid__vertical-title {
          width: 180px;
          font-size: 1rem;
          margin: 0;
        }
        .news-grid__vertical-image {
          width: 180px;
          height: 120px;
          object-fit: cover;
        }
        @media (max-width: 768px) {
          .news-grid--3cols {
            grid-template-columns: 1fr;
          }
        }
      `}</style>
    </div>
  );
}