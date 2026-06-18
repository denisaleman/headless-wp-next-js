import { useRouter } from 'next/router';
import PageLayout from '../../components/PageLayout';
import NewsGrid from '../../components/NewsGrid';
import {
  useWordPressPosts,
  useWordPressMenu,
  useWordPressFooterMenus,
} from '../../hooks/useWordPressData';

const FOOTER_LOCATIONS = ['footer-categories', 'footer-about', 'footer-legal'];

export default function CategoryPage() {
  const router = useRouter();
  const { slug } = router.query;

  const { posts, loading: postsLoading, error: postsError } = useWordPressPosts(slug || '');
  const { menuItems: headerMenu, loading: menuLoading } = useWordPressMenu('header');
  const { menusData: footerMenus, loading: footerLoading } = useWordPressFooterMenus(FOOTER_LOCATIONS);

  if (!router.isReady) {
    return <div>Loading category...</div>;
  }

  if (postsLoading || menuLoading || footerLoading) return <div>Loading category...</div>;
  if (postsError) return <div>{postsError}</div>;
  if (!posts.length) return <div>No news found.</div>;

  return (
    <PageLayout
      title="Headless WordPress + Next.js"
      headerMenu={headerMenu}
      footerMenus={footerMenus}
    >
      <NewsGrid posts={posts} />
    </PageLayout>
  );
}
