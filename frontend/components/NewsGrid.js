import Title from './typography/Title';
import { getThumbnail } from '../lib/utils';

export default function NewsGrid({ posts, className = '', additionalClasses = [] }) {
  if (!posts || posts.length < 11) return null;

  const article1 = posts[0];
  const heroImagePost = posts[1];
  const article2 = posts[2];
  const article3 = posts[3];
  const article4 = posts[4];
  const article5 = posts[5];
  const rightColumnPosts = posts.slice(6, 12);

  const mandatoryClasses = ['news-grid', 'news-grid--layout', className, ...additionalClasses];
  const combinedClasses = mandatoryClasses.filter(Boolean).join(' ');

  return (
    <div className={combinedClasses}>
      {/* Hero row – spans first two columns */}
      <div className="news-grid__hero">
        <div className="news-grid__hero-text">
          <Title tag="h1" size="h1" className="news-grid__hero-title">
            {article1.title}
          </Title>
		  <p className="news-grid__hero-excerpt">{article1.excerpt}</p>
        </div>
        <figure className="news-grid__hero-image">
          <img
            src={heroImagePost.featured_image?.url}
            alt={heroImagePost.title}
          />
        </figure>
      </div>

      {/* Left bottom column */}
      <div className="news-grid__left-bottom">
        <article className="news-grid__card--vertical">
          <div>
            <Title tag="h3" size="h4" className="news-grid__card-title">
              {article4.title}
            </Title>
			<p className="news-grid__card-excerpt">{article4.excerpt}</p>
          </div>
        </article>
        <article className="news-grid__card--vertical news-grid__card--no-excerpt">
          <Title tag="h3" size="h4" className="news-grid__card-title">
            {article5.title}
          </Title>
        </article>
      </div>

      {/* Center bottom column */}
      <div className="news-grid__center-bottom">
        <article className="news-grid__card">
          {getThumbnail(article2) && (
            <img className="news-grid__thumb-horizontal" src={getThumbnail(article2)} alt={article2.title} />
          )}
          <div>
            <Title tag="h3" size="h4" className="news-grid__card-title">
              {article2.title}
            </Title>
			<p className="news-grid__card-excerpt">{article2.excerpt}</p>
          </div>
        </article>
        <article className="news-grid__card">
          {getThumbnail(article3) && (
            <img className="news-grid__thumb-horizontal" src={getThumbnail(article3)} alt={article3.title} />
          )}
          <div>
            <Title tag="h3" size="h4" className="news-grid__card-title">
              {article3.title}
            </Title>
			<p className="news-grid__card-excerpt">{article3.excerpt}</p>
          </div>
        </article>
      </div>

      {/* Right column (vertical list) */}
      <div className="news-grid__right-col">
        {rightColumnPosts.map((post) => (
          <article key={post.id} className="news-grid__card--inline">
            <Title tag="h4" size="h5" className="news-grid__inline-title">
              {post.title}
            </Title>
            {getThumbnail(post) && (
              <img className="news-grid__thumb-inline" src={getThumbnail(post)} alt={post.title} />
            )}
          </article>
        ))}
      </div>

      <style jsx>{`
        /* ---------- Main grid ---------- */
        .news-grid--layout {
          display: grid;
          grid-template-columns: 1fr 2fr 1.45fr;
          grid-template-areas:
            "hero hero right"
            "left-bottom center-bottom right";
          gap: 2rem;
          row-gap: 1rem;
          max-width: 1200px;
          margin: 0 auto;
          padding: 1rem;
        }

        /* ---------- Hero row (spans first two columns) ---------- */
        .news-grid__hero {
          grid-area: hero;
          display: grid;
          grid-template-columns: 1fr 2fr;
          gap: 2rem;
          height: max-content;
          border-bottom: 1px dotted #ddd;
          padding-bottom: 1rem;
        }

        /* Hero text column – will fill height set by image */
        .news-grid__hero-text {
          display: flex;
          flex-direction: column;
          justify-content: flex-start;
          overflow: hidden;
        }

        /* Truncate title to 3 lines */
        .news-grid__hero-title {
          display: -webkit-box;
          -webkit-line-clamp: 3;
          -webkit-box-orient: vertical;
          overflow: hidden;
        }

        /* Truncate excerpt to 6 lines */
        .news-grid__hero-excerpt {
          display: -webkit-box;
          -webkit-line-clamp: 6;
          -webkit-box-orient: vertical;
          overflow: hidden;
        }

        /* Hero image – defines the row height */
        .news-grid__hero-image {
          margin: 0;
          height: 100%;
        }
        .news-grid__hero-image img {
          width: 100%;
          height: auto;
          display: block;
        }

        /* ---------- Bottom columns ---------- */
        .news-grid__left-bottom {
          grid-area: left-bottom;
          display: flex;
          flex-direction: column;
          gap: 1.5rem;
        }
        .news-grid__center-bottom {
          grid-area: center-bottom;
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1.5rem;
        }
        .news-grid__right-col {
          grid-area: right;
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }

        /* ---------- Card styles ---------- */
        .news-grid__card {

        }
        .news-grid__card--vertical {
          display: flex;
          gap: 1rem;
          border-bottom: 1px dotted #ddd;
          padding-bottom: 1rem;
        }
        .news-grid__thumb-vertical {
          width: 100px;
          height: 80px;
          object-fit: cover;
        }
        .news-grid__card--horizontal {
          display: flex;
          gap: 1rem;
          align-items: flex-start;
        }
        .news-grid__thumb-horizontal {
          width: 100%;
          object-fit: cover;
          margin-bottom: 1rem;
        }
        .news-grid__card--inline {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
          border-bottom: 1px dotted #ddd;
          padding-bottom: 0.75rem;
        }
        .news-grid__thumb-inline {
          flex-basis: 50%;
          width: 180px;
          height: 120px;
          object-fit: cover;
        }
        .news-grid__inline-title {
          font-weight: 600;
          flex: 1;
          flex-basis: 50%;
          font-size: 0.9rem;
          margin: 0;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 768px) {
          .news-grid--layout {
            grid-template-columns: 1fr;
            grid-template-areas:
              "hero"
              "left-bottom"
              "center-bottom"
              "right";
            gap: 1rem;
          }
          .news-grid__hero {
            grid-template-columns: 1fr;
            gap: 1rem;
          }
        }
      `}</style>

      <style jsx global>{`
        .title.news-grid__card-title {
          font-weight: 600;
        }

        .title.news-grid__inline-title {
          font-weight: 600;
          display: -webkit-box;
          -webkit-line-clamp: 5;
          -webkit-box-orient: vertical;
          overflow: hidden;
          word-break: break-word;
          line-height: 1.25;
          max-height: 80%;
        }
        .title.news-grid__hero-title {
          font-size: 1.85rem;
        }
      `}</style>
    </div>
  );
}