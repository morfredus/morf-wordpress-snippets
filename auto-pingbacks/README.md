# morf No Self Pingbacks

> Part of [morf-wordpress-snippets](../README.md). Français : [README.fr.md](README.fr.md)

Stops WordPress from sending pingbacks to itself when a post links to another page of the same site.

## What it does

Removes internal links (those starting with your site address) from the list of links WordPress is about to ping. Pingbacks to **external** sites keep working.

It only acts on the `pre_ping` hook: nothing is deleted from the database, and existing pingbacks are untouched.

## Notes

- If you changed your site address recently, internal links using the old address are treated as external.
- To also disable pingbacks entirely, use the Discussion settings in WordPress.

## Installation

Use it **either** way:

- **As a plugin**: put `morf-no-self-pingbacks.php` in its own folder, `wp-content/plugins/morf-no-self-pingbacks/morf-no-self-pingbacks.php`, and activate it.
- **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code without the first `<?php` line.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
