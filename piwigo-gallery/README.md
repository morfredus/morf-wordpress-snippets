# morf Piwigo Gallery

> Part of [morf-wordpress-snippets](../README.md).

Display a [Piwigo](https://piwigo.org) album inside a WordPress page with one shortcode, without copying the images into the WordPress media library.

> Français : [README.fr.md](README.fr.md)

**Complete guide** (step-by-step installation, theming, how to find your theme's variables, troubleshooting): [GUIDE.md](GUIDE.md).

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

1. Edit the settings block at the top of `morf-piwigo-gallery.php`. The address of your Piwigo is required:

   ```php
   define('MORF_PIWIGO_API', 'https://your-piwigo.example/ws.php');
   ```

2. Use it **either** way:
   - **As a plugin**: put `morf-piwigo-gallery.php` in its own folder, `wp-content/plugins/morf-piwigo-gallery/morf-piwigo-gallery.php`, and activate it.
   - **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code without the first `<?php` line.

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
3. Results are cached with WordPress transients.

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

### Use your theme's colours

Most themes publish their palette as CSS variables on `:root`. Use them with `var(...)`:

| Theme | Variables to look for |
| --- | --- |
| Blocksy | `--theme-palette-color-1` to `-8` (checked on the author's site) |
| Kadence | `--global-palette1` to `9` |
| Astra | `--ast-global-color-0` to `8` |
| GeneratePress | `--contrast`, `--contrast-2`, `--contrast-3`, `--base`, `--base-2`, `--base-3`, `--accent` |
| Block themes (theme.json) | `--wp--preset--color--<slug>`, slugs are listed in the Site Editor under Styles > Colors |

Only Blocksy was checked on a real site. For the others, confirm the names in your browser: open the developer tools, select the `<html>` element and look at the *Styles* panel, or run this in the console:

```js
[...getComputedStyle(document.documentElement)].filter(k => /palette|global-color|preset--color/.test(k))
```

**Pitfall.** On a Blocksy site that also runs Kadence Blocks, the `--global-palette*` variables exist too, with Kadence's default **blue** colours, not your theme's. Use the variables of the theme that actually styles your pages.

### Example (Blocksy)

In the settings block:

```php
'placeholder-bg' => 'var(--theme-palette-color-5)',
'focus'          => 'var(--theme-palette-color-1)',
// Title over the photo: theme text colour on theme background colour, 55 % opaque.
'caption-bg'     => 'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)',
'caption-color'  => 'var(--theme-palette-color-7)',
```

Or, as Additional CSS:

```css
:root {
  --morf-placeholder-bg: var(--theme-palette-color-5);
  --morf-focus: var(--theme-palette-color-1);
  /* Title over the photo: theme text colour on theme background colour, 55 % opaque. */
  --morf-caption-bg: color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent);
  --morf-caption-color: var(--theme-palette-color-7);
}
```

`color-mix()` is needed to make a theme colour semi-transparent. It works in current Chrome, Edge, Safari and Firefox (2023 and later); in an older browser the declaration is ignored and the default is used.

### Good to know

- **Keep the viewer dark.** The full-screen viewer is meant to show photographs on a dark background. If your theme has a dark mode, its palette usually swaps light and dark colours, so a theme colour used for `--morf-lightbox-bg` would turn the viewer white in dark mode. Only use theme colours there if your site has no dark mode.
- **Dark mode on the title.** The pair "text colour on background colour" (for example Blocksy `-3` on `-7`) swaps together, so the title stays readable in both modes.
- **One page only.** WordPress adds the page id to the `<body>` class, so you can scope a setting: `.page-id-123 .morf-piwigo-gallery { --morf-radius: 0; }`.
- **Order of rules.** Variables are read at display time: for the CSS option, set them anywhere (`:root`, `body`, or a wrapper), no need to load them before the gallery.
- **Caching plugins.** If you use a CSS minifier or cache, clear it after changing your Additional CSS.

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
