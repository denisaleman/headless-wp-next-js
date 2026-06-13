export function getThumbnail(post, size = 'news-180x120') {
  const media = post._embedded?.['wp:featuredmedia']?.[0];
  const src = media?.media_details?.sizes?.[size]?.source_url || media?.source_url;
  return src || null;
}