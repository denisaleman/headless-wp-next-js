/**
 * Get thumbnail URL for a post from the custom REST API format.
 * @param {object} post - Post object from headless-news/v1/news
 * @param {string} size - Image size slug (default 'news-270x180')
 * @returns {string|null}
 */
export function getThumbnail(post, size = 'news-270x180') {
  return post.featured_image?.[size] || post.featured_image?.url || null;
}