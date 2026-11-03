# morf Piwigo Gallery

> Part of [morf-wordpress-snippets](../README.md).

Display a [Piwigo](https://piwigo.org) album inside a WordPress page with one shortcode, without copying the images into the WordPress media library.

> Français : [README.fr.md](README.fr.md)

Piwigo keeps managing the photographs and their albums. WordPress stays the place where you tell the story behind them. The gallery is generated on the fly from the Piwigo API.

Written for [morfredus.fr](https://morfredus.fr), shared as is. The story behind it: *Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet* (French).

## Features

- Responsive grid: 3 columns on large screens, 2 on medium, 1 on small.
- Full-screen viewer: arrow keys, `Esc` to close, click outside the image, counter.
- Shooting information under each photograph: camera model, focal length, aperture, exposure time, ISO.
- No second copy of your images: thumbnails and full-size files come straight from Piwigo.
- Two caches: Piwigo API responses (10 minutes) and EXIF data (30 days).
- Single PHP file, no dependency, no build step.

## Requirements

- WordPress 5.8 or later, PHP 7.4 or later with the `exif` extension.
- A Piwigo installation whose albums are **public** (the API is called without authentication).

## Installation

1. Edit the constant at the top of `morf-piwigo-gallery.php`:

   ```php
   define('MORF_PIWIGO_API', 'https://your-piwigo.example/ws.php');
   ```

2. Use it **either** way:
   - **As a plugin**: copy `morf-piwigo-gallery.php` into `wp-content/plugins/` and activate it.
   - **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code without the first `<?php` line.

## Usage

```
[morf_piwigo album="Rouille" limit="13"]
[morf_piwigo album="Rouille" limit="8" texte="description"]
[morf_piwigo album="Rouille" limit="0"]
```

| Parameter | Default | Meaning |
| --- | --- | --- |
| `album` | *(required)* | Exact name of the Piwigo album (case-insensitive). |
| `limit` | `0` | Number of photographs. `0` means up to 500. |
| `texte` | `titre` | Caption shown: `titre` (title) or `description`. |

In the block editor, put the shortcode in a *Shortcode* block. To show it as text in an article, double the brackets: `[[morf_piwigo album="Rouille"]]`.

## How it works

1. The shortcode looks up the album by name with `pwg.categories.getList`, then lists its images with `pwg.categories.getImages`.
2. The Piwigo API response does not contain the shooting data. The code therefore requests the first 64 KB of each image (HTTP `Range`) and reads the EXIF header with PHP's `exif_read_data`.
3. Results are cached with WordPress transients.

## Privacy and security notes

- **GPS coordinates.** Only five EXIF fields are displayed, but the viewer opens the original file served by Piwigo. If your originals contain GPS data, a visitor can download it. Strip it in Piwigo or before upload if that matters to you.
- **Public albums only.** No credentials are stored or sent. Private albums are not displayed.
- All output is escaped (`esc_url`, `esc_attr`, `esc_html`) and shortcode attributes are sanitized.

## Limits

- If the image server ignores the `Range` header, the whole file is downloaded once to read the EXIF data, then cached.
- EXIF display depends on the metadata being present in the file.
- After a change in Piwigo, cached data is not refreshed immediately. Wait for the cache to expire, or delete the `morf_piwigo_*` and `morf_strict_exif_*` transients.
- Tested on the author's own site only.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
