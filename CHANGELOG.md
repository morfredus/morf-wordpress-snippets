# Changelog

## 2026-11-04 - first wave of snippets

- `instant-navigation` 1.0.0: prefetching and LCP image priority.
- `auto-pingbacks` 1.0.0: no more self pingbacks.
- `code-copy-button` 1.0.0: code block styles and "Copy" button. The copied text is read before the button is added.
- `webp-conversion` 1.1.0: safety checks added (WebP support, non-empty output, no overwrite), original file kept by default, thumbnails are no longer relabelled as WebP.
- `wordpress-cleanup` 1.0.0: merge of three site snippets; the REST user list is now hidden from logged-out visitors only.

## 2026-11-03 - piwigo-gallery 1.0.0

First snippet of the collection, extracted from the snippet used on morfredus.fr.

- Shortcode `[morf_piwigo]` with `album`, `limit` and `texte`.
- Responsive grid and full-screen viewer.
- EXIF read from the first 64 KB of each image, cached for 30 days.
- API address configurable through the `MORF_PIWIGO_API` constant.
- Packaged as a single-file plugin, also usable as a snippet.
