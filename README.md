# morf WordPress Snippets

Small, self-contained WordPress snippets from [morfredus.fr](https://morfredus.fr), shared as is.

> Français : [README.fr.md](README.fr.md)

Each snippet lives in its own folder, with its own README. Most are a single PHP file, usable as a snippet (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)) or dropped into `wp-content/plugins/` when the file has a plugin header. No dependency, no build step.

## Snippets

| Folder | What it does | Article (French) |
| --- | --- | --- |
| [`piwigo-gallery`](piwigo-gallery) | `[morf_piwigo]` shortcode: shows a public Piwigo album as a responsive grid with a full-screen viewer and EXIF data, without copying images into the media library. | *Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet* |
| [`instant-navigation`](instant-navigation) | Near-instant navigation (prefetch) and loading priority for the main image of each post. | *Vitesse WordPress : Instant Click et Lazy Loading (sans extension)* |
| [`auto-pingbacks`](auto-pingbacks) | Stops WordPress from pinging itself when a post links to the same site. | - |
| [`code-copy-button`](code-copy-button) | Styles code blocks and adds a "Copy" button. | - |
| [`webp-conversion`](webp-conversion) | Converts uploaded JPEG/PNG images to WebP, with safety checks. Original kept by default. | *Performance WordPress : Convertir automatiquement ses images en WebP (sans plugin)* |
| [`wordpress-cleanup`](wordpress-cleanup) | Removes useless metadata exposed by WordPress, hides the user list from anonymous visitors. | - |

## Before you use a snippet

- Read the code first. These snippets were written for one site, tested there only, and may need adapting.
- Values to change (addresses, identifiers) are marked with a comment at the top of each file.
- Back up your site before adding code to it.

## License

[GPL-3.0-only](LICENSE) © 2026 Frédéric Biron (morfredus)
