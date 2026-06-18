import { useState, useEffect } from 'react';

export function useWordPressPosts(category = 'top-news') {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    const url = `${wpUrl}/wp-json/headless-news/v1/news?category_slug=${category}&per_page=20&orderby=date&order=desc`;

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
  }, [category]);

  return { posts, loading, error };
}

export function useWordPressMenu(location) {
  const [menuItems, setMenuItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!location || typeof location !== 'string') {
      setLoading(false);
      setMenuItems([]);
      setError(null);
      return;
    }

    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    fetch(`${wpUrl}/wp-json/headless-news/v1/menu/${location}`)
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then(data => {
        setMenuItems(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setError('Failed to load menu');
        setLoading(false);
      });
  }, [location]);

  return { menuItems, loading, error };
}

export function useWordPressPost(slug) {
  const [post, setPost] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!slug) return;
    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    fetch(`${wpUrl}/wp-json/headless-news/v1/news/slug/${slug}`)
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

  return { post, loading, error };
}

/**
 * Fetch multiple footer menus in one request.
 *
 * @param {string[]} locations - Array of menu location slugs.
 * @returns {object} { menusData, loading, error }
 */
export function useWordPressFooterMenus(locations = []) {
  const [menusData, setMenusData] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!locations.length) {
      setLoading(false);
      setMenusData({});
      setError(null);
      return;
    }

    const wpUrl = process.env.NEXT_PUBLIC_WP_URL || 'http://localhost';
    const locationsParam = locations.join(',');
    fetch(`${wpUrl}/wp-json/headless-news/v1/menus/${locations.join(',')}`)
      .then(res => {
        if (!res.ok) throw new Error(`Failed to fetch footer menus`);
        return res.json();
      })
      .then(data => {
        setMenusData(data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setError('Failed to load footer menus');
        setLoading(false);
      });
  }, [locations]);

  return { menusData, loading, error };
}
