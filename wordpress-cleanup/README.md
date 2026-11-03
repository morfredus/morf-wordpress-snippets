# morf WordPress Cleanup

> Part of [morf-wordpress-snippets](../README.md). Français : [README.fr.md](README.fr.md)

Removes useless metadata exposed by WordPress and hides the user list from anonymous visitors.

## What it does

| Block | Effect |
| --- | --- |
| RSD, WLW manifest, shortlinks | Removes the links from `<head>` and HTTP headers. |
| WordPress version | Removes the generator tag and its feed output. |
| REST discovery links | Removes the links only. **The REST API itself stays active.** |
| oEmbed discovery | Other sites can no longer auto-embed your posts. |
| Emoji | Removes the emoji detection script and styles. |
| XML-RPC pingbacks | Disables the two pingback methods. XML-RPC itself stays available (mobile app, Jetpack). |
| REST user list | `/wp/v2/users` is hidden from **logged-out** visitors only, so the block editor keeps working. |

Each block is independent: delete the ones you do not want.

## Notes

- This is cosmetic hardening, not a security solution. Hiding the version does not fix an outdated site.
- Hiding the REST user list does not stop every user enumeration technique (author archives, for example).
- Some plugins read the REST user list for anonymous visitors. If something breaks, remove the last block first.

## Installation

Use it **either** way:

- **As a plugin**: copy `morf-wordpress-cleanup.php` into `wp-content/plugins/` and activate it.
- **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code without the first `<?php` line.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
