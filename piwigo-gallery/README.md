# morf Piwigo Gallery

> Part of [morf-wordpress-snippets](../README.md).

Display a [Piwigo](https://piwigo.org) album inside a WordPress page with one shortcode, without copying the images into the WordPress media library.

> Français : [README.fr.md](README.fr.md)

**Complete guide** (step-by-step installation, theming, how to find your theme's variables, troubleshooting): [GUIDE.md](GUIDE.md).

Piwigo keeps managing the photographs and their albums. WordPress stays the place where you tell the story behind them. The gallery is generated on the fly from the Piwigo API.

Written for [morfredus.fr](https://morfredus.fr), shared as is. The story behind it: [*Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet*](https://morfredus.fr/integrer-piwigo-wordpress/) (in French).

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

1. Edit the settings block at the top of `morf-piwigo-gallery.php`. The address of your Piwigo is required:

   ```php
   define('MORF_PIWIGO_API', 'https://your-piwigo.example/ws.php');
   ```

2. Use it **either** way:
   - **As a plugin**: put `morf-piwigo-gallery.php` in its own folder, `wp-content/plugins/morf-piwigo-gallery/morf-piwigo-gallery.php`, and activate it.
   - **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code. Code Snippets removes the opening `<?php` tag when you save (it may show a warning, which you can ignore).

## Usage

```
[morf_piwigo album="Rouille" limit="13"]
[morf_piwigo album="Rouille" limit="8" texte="description"]
[morf_piwigo album="Rouille" limit="0"]
```

| Parameter | Default | Meaning |
| --- | --- | --- |
| `album` | *(required)* | Name of the Piwigo album. Case, extra spaces and the different dash characters (-, –, —) are ignored. |
| `limit` | `0` | Number of photographs. `0` means up to 500. |
| `texte` | `titre` | Caption shown: `titre` (title) or `description`. |

In the block editor, put the shortcode in a *Shortcode* block. To show it as text in an article, double the brackets: `[[morf_piwigo album="Rouille"]]`.

## How it works

1. The shortcode looks up the album by name with `pwg.categories.getList`, then lists its images with `pwg.categories.getImages`.
2. The Piwigo API response does not contain the shooting data. The code therefore requests the first 64 KB of each image (HTTP `Range`) and reads the EXIF header with PHP's `exif_read_data`.
3. **Camera model fallback.** If the EXIF data contains no camera make or model, the model is read from the **file name** when it follows the pattern `anything_MODEL_number.ext`. For example `20240512_OM20_001.jpg` shows `OM20`. Everything between the first underscore and the last `_number` is used, and it is displayed even when it is not a camera name: `photo_2024_001.jpg` shows `2024`, and `a_b_c_d_042.webp` shows `b_c_d`. A name without that pattern (`IMG_1234.JPG`, `OM20_007.jpg`) shows nothing. It is on by default. To turn it off, set `MORF_PIWIGO_CAMERA_FROM_FILENAME` to `false` in the settings block at the top of the file: then only the EXIF data is used. Otherwise, name your files to match the pattern, or make sure they carry their EXIF camera model.
4. Results are cached with WordPress transients.

## Adapting the look to your theme

Everything visual goes through CSS custom properties named `--morf-*`, and every one of them is listed, with its default value, in a **settings block at the top of the PHP file** (`MORF_PIWIGO_STYLE`). That block is the only part you need to edit.

```php
define('MORF_PIWIGO_STYLE', array(
    'radius'         => '4px',
    'placeholder-bg' => 'var(--theme-palette-color-5)',   // a theme colour
    'caption-bg'     => '#1c1c1c',                        // or a hex code
    // ...
));
```

Each value can be a colour code (`#D8D2C8`, `rgba(0, 0, 0, .45)`), a theme variable (`var(--theme-palette-color-5)`), or a size or number (`12px`, `3`). An empty value (`''`) or a deleted line means "use the built-in default".

**Second option: CSS.** If you prefer not to touch the PHP, empty the line (or delete it) and define the variable in **Appearance > Customize > Additional CSS** instead:

```css
:root {
  --morf-radius: 4px;
  --morf-placeholder-bg: var(--theme-palette-color-5);
}
```

A value that is filled in in the settings block wins over a `:root` rule in Additional CSS. A rule aimed at the gallery itself, such as `.page-id-123 .morf-piwigo-gallery { --morf-radius: 0; }`, always wins over both.

Values are checked before use: braces, semicolons and angle brackets are stripped, so a value cannot break out of its CSS declaration.

### Variables

| Variable | Default | What it controls |
| --- | --- | --- |
| `--morf-columns` | `3` | Columns on large screens. |
| `--morf-columns-tablet` | `2` | Columns up to 900 px wide. |
| `--morf-columns-mobile` | `1` | Columns up to 550 px wide. |
| `--morf-gap` | `16px` | Space between photographs. |
| `--morf-radius` | `12px` | Corner radius of the thumbnails. |
| `--morf-zoom` | `1.035` | Zoom on hover (`1` disables it). |
| `--morf-font` | `inherit` | Font of the gallery (inherits the theme font by default). |
| `--morf-placeholder-bg` | `#eee` | Colour shown behind a thumbnail while it loads. |
| `--morf-caption-bg` | `rgba(0, 0, 0, .45)` | Background of the title shown over a thumbnail. |
| `--morf-caption-color` | `#fff` | Text colour of that title. |
| `--morf-caption-size` | `14px` | Text size of that title. |
| `--morf-focus` | `currentColor` | Outline colour when a thumbnail has keyboard focus. |
| `--morf-lightbox-bg` | `rgba(12, 12, 12, .96)` | Full-screen viewer background. |
| `--morf-lightbox-color` | `#eee` | Viewer text (title). |
| `--morf-lightbox-muted` | `#888` | Viewer counter ("3 / 13"). |
| `--morf-controls-color` | `#fff` | Close, previous and next buttons. |
| `--morf-exif-color` | `#ccc` | Text colour of the EXIF badge. |
| `--morf-exif-bg` | `rgba(255, 255, 255, .08)` | Background of the EXIF badge. |
| `--morf-exif-font` | `monospace, sans-serif` | Font of the EXIF badge. |
| `--morf-lightbox-z` | `999999` | Stacking order of the viewer (lower it if it hides your own menu). |

Not configurable with variables: the 900 px and 550 px breakpoints (CSS media queries cannot read variables). Edit the two `@media` lines in the PHP file if you need other values.

For the theme colours (how to find your theme's variables, which variable for which role, dark mode, an example for Blocksy), see the **[complete guide](GUIDE.md#5-finding-your-themes-colours-and-variables)**.

## Privacy and security notes

- **GPS coordinates.** Only five EXIF fields are displayed, but the viewer opens the original file served by Piwigo. If your originals contain GPS data, a visitor can download it. Strip it in Piwigo or before upload if that matters to you.
- **Public albums only.** No credentials are stored or sent. Private albums are not displayed.
- All output is escaped (`esc_url`, `esc_attr`, `esc_html`) and shortcode attributes are sanitized.

## Limits

- If the image server ignores the `Range` header, the whole file is downloaded once to read the EXIF data, then cached.
- EXIF display depends on the metadata being present in the file.
- After a change in Piwigo, cached data is not refreshed immediately. Wait for the cache to expire, or delete the `morf_piwigo_*` and `morf_strict_exif_*` transients.
- Tested on the author's own site only.

## Uninstalling

Deactivate or delete the snippet or plugin. The cached data (`morf_piwigo_*` and `morf_strict_exif_*` transients) expires by itself after 10 minutes and 30 days; a transient-cleaning plugin can remove it sooner. Shortcodes left in your pages are then displayed as plain text.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
