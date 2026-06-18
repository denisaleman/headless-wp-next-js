import { useRouter } from 'next/router';
import PageLayout from '../components/PageLayout';
import Title from '../components/typography/Title';
import Lead from '../components/typography/Lead';
import {
  useWordPressPost,
  useWordPressMenu,
  useWordPressFooterMenus,
} from '../hooks/useWordPressData';

export default function CatchAllPage() {
  const router = useRouter();
  const { slug } = router.query;

  const postSlug = slug && slug.length > 0 ? slug[0] : null;
  const { post, loading: postLoading, error: postError } = useWordPressPost(postSlug);
  const { menuItems: headerMenu, loading: menuLoading } = useWordPressMenu('header');
  const { menusData: footerMenus, loading: footerLoading } = useWordPressFooterMenus(['footer-categories', 'footer-about', 'footer-legal']);

  if (!router.isReady || postLoading || menuLoading || footerLoading) {
    return <div>Loading...</div>;
  }

  if (postError) return <div>{postError}</div>;
  if (!post) return null;

  return (
    <PageLayout
      title="Headless WordPress + Next.js"
      headerMenu={headerMenu}
      footerMenus={footerMenus}
    >
      <article className="news-article">
        {post.featured_image?.url && (
          <figure className="news-article__featured-image">
            <img src={post.featured_image.url} alt={post.title} />
          </figure>
        )}
        <Title tag="h1" size="h1" className="news-article__title">{post.title}</Title>
        <Lead className="news-article__lead">{post.excerpt}</Lead>
        <div className="news-article__content" dangerouslySetInnerHTML={{ __html: post.content }} />
        <div className="news-article__meta">
          {post.date && <time dateTime={post.date}>{new Date(post.date).toLocaleDateString()}</time>}
          {post.author && <span>By {post.author}</span>}
          {post.source_url && <a href={post.source_url} target="_blank" rel="noopener noreferrer">Original source</a>}
        </div>
      </article>
      <style jsx>{`
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
        .news-article__content {
          max-width: 80ch;
          font-size: 1.125rem;
          line-height: 1.6;
          margin-bottom: 2rem;
        }
        .news-article__content :global(p) {
          margin-bottom: 1rem;
          margin-top: 1rem;
          font-size: 1.25rem;
        }
        .news-article__meta {
          display: flex;
          gap: 1rem;
          align-items: center;
          flex-wrap: wrap;
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
    </PageLayout>
  );
}
