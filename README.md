# morf WordPress Snippets

Small, self-contained WordPress snippets from [morfredus.fr](https://morfredus.fr), shared as is.

> Français : [README.fr.md](README.fr.md)

Each snippet lives in its own folder, with its own README. Most are a single PHP file, usable as a snippet (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)) or installed as a plugin when the file has a plugin header (see [Installing a snippet](#installing-a-snippet)). No dependency, no build step.

## Snippets

| Folder | What it does | Article (French) |
| --- | --- | --- |
| [`piwigo-gallery`](piwigo-gallery) | `[morf_piwigo]` shortcode: shows a public Piwigo album as a responsive grid with a full-screen viewer and EXIF data, without copying images into the media library. | [*Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet*](https://morfredus.fr/integrer-piwigo-wordpress/) |
| [`instant-navigation`](instant-navigation) | Near-instant navigation (prefetch) and loading priority for the main image of each post. | *Vitesse WordPress : Instant Click et Lazy Loading (sans extension)* |
| [`auto-pingbacks`](auto-pingbacks) | Stops WordPress from pinging itself when a post links to the same site. | - |
| [`code-copy-button`](code-copy-button) | Styles code blocks and adds a "Copy" button. | - |
| [`webp-conversion`](webp-conversion) | Converts uploaded JPEG/PNG images to WebP, with safety checks. Original kept by default. | *Performance WordPress : Convertir automatiquement ses images en WebP (sans plugin)* |
| [`wordpress-cleanup`](wordpress-cleanup) | Removes useless metadata exposed by WordPress, hides the user list from anonymous visitors. | - |

## Installing a snippet

Every snippet is installed in one of two ways. Pick one.

**A. With the Code Snippets plugin (recommended for a single snippet)**
1. Install and activate [Code Snippets](https://wordpress.org/plugins/code-snippets/).
2. **Snippets > Add New**, choose the type **PHP**, give it a name.
3. Paste the content of the snippet's `.php` file. You can keep the first line `<?php`: Code Snippets removes it when you save (it may show a warning, which you can ignore).
4. Edit the values marked at the top of the code if the snippet's README asks for it, save and activate.

**B. As a plugin**
1. Create a folder in `wp-content/plugins/` named after the file, without `.php` (for example `morf-code-copy-button`), and put the file inside: `wp-content/plugins/morf-code-copy-button/morf-code-copy-button.php`.
2. Activate it in **Plugins**.
3. Alternatively, zip that folder and use **Plugins > Add New > Upload Plugin**. The zip must contain the folder, not the bare file. (Do not use GitHub's "Download ZIP" of the whole repository for this.)

With option B, your edits live in the file: replacing it with a newer version overwrites them. Keep a copy of the values you changed.

Each snippet's own README gives its requirements, how to use it, its limits and how to uninstall it.

## Updating

Compare your copy with the new version (the [CHANGELOG](CHANGELOG.md) lists the changes), then paste the new code over the old one, keeping your own values. Back up first.

## Support

These snippets are shared as is, with no warranty. Questions and bug reports: [open an issue](https://github.com/morfredus/morf-wordpress-snippets/issues). Replies are not guaranteed, and a snippet that needs a lot of adapting to your site may be better left alone.

## Before you use a snippet

- Read the code first. These snippets were written for one site, tested there only, and may need adapting.
- Values to change (addresses, identifiers) are marked with a comment at the top of each file.
- Back up your site before adding code to it.

## License

[GPL-3.0-only](LICENSE) © 2026 Frédéric Biron (morfredus)
