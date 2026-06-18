import Title from './typography/Title';
import Link from 'next/link';
import { getThumbnail } from '../lib/utils';

export default function NewsGrid({ posts, className = '', additionalClasses = [] }) {
  if (!posts || posts.length < 11) return null;

  const article1 = posts[0];
  const article2 = posts[1];
  const article3 = posts[2];
  const article4 = posts[3];
  const article5 = posts[4];
  const rightColumnPosts = posts.slice(6, 12);

  const mandatoryClasses = ['news-grid', 'news-grid--layout', className, ...additionalClasses];
  const combinedClasses = mandatoryClasses.filter(Boolean).join(' ');

  return (
    <div className={combinedClasses}>
      {/* Hero row – spans first two columns */}
      <Link href={`/${article1.slug}`} legacyBehavior>
        <div className="news-grid__hero">
          <div className="news-grid__hero-text">

            <Title tag="h1" size="h1" className="news-grid__hero-title">
              <a className="news-grid__link" href={`/${article1.slug}`}>{article1.title}</a>
            </Title>
            <p className="news-grid__hero-excerpt">{article1.excerpt}</p>
          </div>
          <figure className="news-grid__hero-image">
            <a className="news-grid__link" href={`/${article1.slug}`}>
              <img
                src={article1.featured_image?.url}
                alt={article1.title}
              />
            </a>
          </figure>
        </div>
      </Link>

      {/* Left bottom column */}
      <div className="news-grid__left-bottom">
        <Link href={`/${article4.slug}`} legacyBehavior>
          <article className="news-grid__card--vertical">
            <div>
              <Title tag="h3" size="h4" className="news-grid__card-title">
                <a className="news-grid__link" href={`/${article4.slug}`}>{article4.title}</a>
              </Title>
              <p className="news-grid__card-excerpt">{article4.excerpt}</p>
            </div>
          </article>
        </Link>
        <Link href={`/${article5.slug}`} legacyBehavior>
          <article className="news-grid__card--vertical news-grid__card--no-excerpt">
            <Title tag="h3" size="h4" className="news-grid__card-title">
              <a className="news-grid__link" href={`/${article5.slug}`}>{article5.title}</a>
            </Title>
          </article>
        </Link>
      </div>

      {/* Center bottom column */}
      <div className="news-grid__center-bottom">
        <Link href={`/${article2.slug}`} legacyBehavior>
          <article className="news-grid__card">
            {getThumbnail(article2) && (
              <a className="news-grid__link" href={`/${article2.slug}`}>
                <img className="news-grid__thumb-horizontal" src={getThumbnail(article2)} alt={article2.title} />
              </a>
            )}
            <div>
              <Title tag="h3" size="h4" className="news-grid__card-title">
                <a className="news-grid__link" href={`/${article2.slug}`}>{article2.title}</a>
              </Title>
              <p className="news-grid__card-excerpt">{article2.excerpt}</p>
            </div>
          </article>
        </Link>
        <Link href={`/${article3.slug}`} legacyBehavior>
        <article className="news-grid__card">
          {getThumbnail(article3) && (
            <a className="news-grid__link" href={`/${article3.slug}`}>
              <img className="news-grid__thumb-horizontal" src={getThumbnail(article3)} alt={article3.title} />
            </a>
          )}
          <div>
            <Title tag="h3" size="h4" className="news-grid__card-title">
              <a className="news-grid__link" href={`/${article3.slug}`}>{article3.title}</a>
            </Title>
			      <p className="news-grid__card-excerpt">{article3.excerpt}</p>
          </div>
        </article>
        </Link>
      </div>

      {/* Right column (vertical list) */}
      <div className="news-grid__right-col">
        {rightColumnPosts.map((post) => (
          <Link key={post.id} href={`/${post.slug}`} legacyBehavior>
            <article className="news-grid__card--inline">
              <Title tag="h4" size="h5" className="news-grid__inline-title">
                <a className="news-grid__link" href={`/${post.slug}`}>{post.title}</a>
              </Title>
              {getThumbnail(post) && (
                <a className="news-grid__link" href={`/${post.slug}`}>
                  <img className="news-grid__thumb-inline" src={getThumbnail(post)} alt={post.title} />
                </a>
              )}
            </article>
          </Link>
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
        .news-grid__link {
          color: #000;
          text-decoration: none;
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
