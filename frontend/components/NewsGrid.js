import { getThumbnail } from '../lib/utils';

export default function NewsGrid({ posts }) {
  if (!posts || posts.length === 0) return null;

  const heroPost = posts[0];
  const column1Posts = posts.slice(1, 4);
  const column2UnderPosts = posts.slice(4, 6);
  const column3Posts = posts.slice(6, 6 + 6);

  return (
    <div className="grid grid--3cols">
      {/* Column 1 – text only */}
      <div className="grid__col grid__col--1">
        {column1Posts.map(post => (
          <article key={post.id} className="card card--text-only">
            <h3 className="card__title">{post.title.rendered}</h3>
            <div className="card__excerpt" dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
          </article>
        ))}
      </div>

      {/* Column 2 – hero + two under */}
      <div className="grid__col grid__col--2">
        <article className="hero-card">
          {heroPost._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
            <img
              className="hero-card__image"
              src={heroPost._embedded['wp:featuredmedia'][0].source_url}
              alt={heroPost.title.rendered}
            />
          )}
          <h2 className="title hero-card__title hero-card__title--h2">{heroPost.title.rendered}</h2>
          <div className="hero-card__excerpt" dangerouslySetInnerHTML={{ __html: heroPost.excerpt.rendered }} />
        </article>

        <div className="subgrid subgrid--2cols">
          {column2UnderPosts.map(post => (
            <article key={post.id} className="subgrid__item">
              {post._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
                <img className="subgrid__image" src={post._embedded['wp:featuredmedia'][0].source_url} alt={post.title.rendered} />
              )}
              <h3 className="title subgrid__title">{post.title.rendered}</h3>
              <div className="subgrid__excerpt" dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
            </article>
          ))}
        </div>
      </div>

      {/* Column 3 – vertical blocks */}
      <div className="grid__col grid__col--3">
        {column3Posts.map(post => {
          const thumb = getThumbnail(post);
          return (
            <article key={post.id} className="vertical-block">
              <div className="vertical-block__inner">
                <h3 className="title vertical-block__title">{post.title.rendered}</h3>
                {thumb && (
                  <img className="vertical-block__image" src={thumb} alt={post.title.rendered} />
                )}
              </div>
            </article>
          );
        })}
      </div>

      <style jsx>{`
        /* BEM‑based styles – identical visual output */
        .grid--3cols {
          display: grid;
          grid-template-columns: 1fr 2fr 1fr;
          gap: 2rem;
          max-width: 1200px;
          margin: 0 auto;
          padding: 1rem;
        }
        .grid__col {
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }
        .card--text-only {
          border-bottom: 1px solid #eee;
          padding-bottom: 1rem;
        }
        .hero-card__image {
          width: 100%;
          height: auto;
        }
        .subgrid--2cols {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
        }
        .subgrid__image {
          width: 100%;
          height: auto;
        }
        .vertical-block {
          border-bottom: 1px dotted #ddd;
          padding-bottom: 1rem;
        }
        .vertical-block__inner {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
        }
        .vertical-block__title {
          width: 180px;
          font-size: 1rem;
          margin: 0;
        }
        .vertical-block__image {
          width: 180px;
          height: 120px;
          object-fit: cover;
        }
        @media (max-width: 768px) {
          .grid--3cols {
            grid-template-columns: 1fr;
          }
        }
      `}</style>
    </div>
  );
}