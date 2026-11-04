# morf Instant Navigation

> Part of [morf-wordpress-snippets](../README.md). Français : [README.fr.md](README.fr.md)

Near-instant page navigation (prefetch) and loading priority for the main image of each post.

## What it does

1. **LCP images.** On single posts, images displayed in `large`, `full`, `post-thumbnail` or `medium_large` size get `loading="eager"` and `fetchpriority="high"`, so the main image is not lazy-loaded.
2. **Prefetching.** Internal pages are fetched just before the click: with the Speculation Rules API (Chrome, Edge, Opera, `moderate` eagerness), and with a hover/touch fallback elsewhere.

`/wp-admin/`, `/wp-login.php` and `/wp-json/` are excluded.

## Notes

- Prefetching increases the number of requests your server receives. On a small shared host, watch the load after enabling it.
- Combine with a page cache for the best result.
- Do not enable it if you have internal links that trigger an action (logout, add to cart) on a simple GET.

## Installation

Use it **either** way:

- **As a plugin**: put `morf-instant-navigation.php` in its own folder, `wp-content/plugins/morf-instant-navigation/morf-instant-navigation.php`, and activate it.
- **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code without the first `<?php` line.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
