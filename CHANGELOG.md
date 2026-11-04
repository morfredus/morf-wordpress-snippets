# Changelog

## 2026-11-04 - piwigo-gallery 1.4.0

- New setting `MORF_PIWIGO_CAMERA_FROM_FILENAME` (`true` or `false`) in the settings block: turns the camera-model fallback on the file name on or off. On by default, so behaviour is unchanged. A line removed from the file counts as `true`.
- When the fallback is off, EXIF results are cached under a separate key, so switching the setting never shows stale results. The existing cache stays valid in the default mode.

## 2026-11-04 - documentation

- piwigo-gallery: the camera-model fallback on the file name is now documented (README, guide, troubleshooting, code comment), with tested examples. Behaviour unchanged.
- Link to the companion article added to the root README and the piwigo-gallery README.

## 2026-11-04 - cleanup pass

- piwigo-gallery 1.3.1: removed two dead API parameters (`id` and `f_recursive`, ignored by Piwigo; only `cat_id` counts) and a redundant `sslverify` (already the default). No change in behaviour.
- Documentation: shared installation, update and support sections in the root README; Requirements and Uninstalling sections in every snippet README; the piwigo README no longer duplicates the theming part of the guide.
- Code Snippets removes the opening `<?php` tag itself when saving: the docs no longer ask to remove it by hand.

## 2026-11-04 - documentation

- piwigo-gallery: new complete guide (`GUIDE.md`, `GUIDE.fr.md`): installation, shortcode, settings block, how to find a theme's colours and variables (console scripts tested on a Blocksy site), matching roles, dark mode, troubleshooting.
- All snippets: plugin installation now uses a folder of its own (`wp-content/plugins/<name>/<name>.php`) instead of a loose file.

## 2026-11-04 - piwigo-gallery 1.3.0

- Settings block at the top of the file (`MORF_PIWIGO_STYLE`): every appearance variable listed with its default, as a colour code, a theme variable or a size. The API address sits in the same block.
- An empty value or a deleted line keeps the built-in default, and can still be set from Additional CSS.
- Values are sanitised (no braces, semicolons or angle brackets).

## 2026-11-04 - piwigo-gallery 1.2.0

- Theming: every colour, size and column count of the gallery is now a `--morf-*` CSS variable with a built-in default, so it can be matched to any WordPress theme from the Additional CSS. Documented in the README with theme mappings (Blocksy, Kadence, Astra, GeneratePress, block themes).
- Keyboard focus outline on thumbnails (`--morf-focus`); the title is also shown on focus.
- The `--morf-gap` and `--morf-radius` values are no longer set on the gallery element, so they can be overridden from outside.

## 2026-11-04 - piwigo-gallery 1.1.1

- Fix: a recently created or renamed album was reported as "not found" for up to 10 minutes because the album list is cached. The list is now reloaded once from Piwigo before giving up.

## 2026-11-04 - piwigo-gallery 1.1.0

- Fix: photos from other albums could be mixed into the requested album. The API is now queried with the album id (`cat_id`) and no recursion, and the cache key changed (`v7`).
- Album names are compared after normalisation (case, spaces, dash variants, HTML entities).
- The "no photo" message now names the album.

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
