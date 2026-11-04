# morf Piwigo Gallery: complete guide

> Français : [GUIDE.fr.md](GUIDE.fr.md) · Back to the [short README](README.md)

This guide covers everything from installation to matching the gallery with your own WordPress theme, for any site (not only the author's).

- [1. What you need](#1-what-you-need)
- [2. Installation, step by step](#2-installation-step-by-step)
- [3. Using the shortcode](#3-using-the-shortcode)
- [4. The settings block](#4-the-settings-block)
- [5. Finding your theme's colours and variables](#5-finding-your-themes-colours-and-variables)
- [6. Matching the gallery to your theme](#6-matching-the-gallery-to-your-theme)
- [7. Dark mode](#7-dark-mode)
- [8. Troubleshooting](#8-troubleshooting)

## 1. What you need

- A WordPress site (5.8 or later) with PHP 7.4 or later and the PHP `exif` extension (needed for the shooting data).
- A Piwigo installation. The albums you want to show must be **visible to a visitor who is not logged in**: the gallery calls the Piwigo API without any credentials and never sees private albums.
- The address of the Piwigo API: your Piwigo address followed by `/ws.php`, for example `https://photos.example.com/ws.php`.

**Check your API address first.** Open this in a browser, replacing the host:

```
https://photos.example.com/ws.php?format=json&method=pwg.categories.getList&recursive=true
```

You should get a JSON text starting with `{"stat":"ok"` and listing your albums. The `name` of each album is what you will pass to the shortcode. If you get an error page or `"stat":"fail"`, fix that before going further.

## 2. Installation, step by step

The file `morf-piwigo-gallery.php` can be used in two ways. Pick one.

### Option A: as a snippet (Code Snippets plugin)

1. Install and activate [Code Snippets](https://wordpress.org/plugins/code-snippets/).
2. **Snippets > Add New**, give it a name such as "Piwigo gallery", type **PHP**.
3. Paste the content of `morf-piwigo-gallery.php` **without its first line** `<?php`.
4. Edit the settings block (section 4): at least `MORF_PIWIGO_API`.
5. Choose a scope that runs on the front end of the site (the default "run everywhere" is fine), then save and activate.

### Option B: as a plugin

1. Edit `MORF_PIWIGO_API` in the settings block of `morf-piwigo-gallery.php`.
2. Create a folder named `morf-piwigo-gallery` in `wp-content/plugins/` and put the file inside, so that the path is `wp-content/plugins/morf-piwigo-gallery/morf-piwigo-gallery.php` (over FTP or the file manager of your host). A folder of its own keeps the plugin easy to find, to update and to delete.
   Alternatively, zip that folder and use **Plugins > Add New > Upload Plugin**: the zip must contain the `morf-piwigo-gallery` folder, not the bare file.
3. Activate **morf Piwigo Gallery** in **Plugins**.

Note that your settings live in this file: if you later replace it with a newer version, they are overwritten. Keep a copy of your settings block.

## 3. Using the shortcode

```
[morf_piwigo album="Rouille" limit="13" texte="titre"]
```

| Parameter | Default | Meaning |
| --- | --- | --- |
| `album` | *(required)* | Name of the Piwigo album. Case, repeated spaces and the dash variants (-, –, —) are ignored. |
| `limit` | `0` | Number of photographs to show. `0` means up to 500. |
| `texte` | `titre` | Caption: `titre` (the photo title) or `description` (its Piwigo description, falling back to the title when empty). |

**Where to put it.** In the block editor, add a **Shortcode** block and type the line above. In the classic editor, type it in the text. You can put several galleries on one page: each one is independent.

**To show the shortcode as text** (in a tutorial, for instance) double the brackets: `[[morf_piwigo album="Rouille"]]`. Put it in a code block so that it is not interpreted.

**Subalbums.** Only the photographs of the album itself are shown, not those of its subalbums. Use one shortcode per album.

**Performance.**
- The Piwigo answers are cached for 10 minutes.
- The shooting data are read from the image files themselves, one request per photograph, with a 5-second timeout. The first display of a page with many photographs can therefore be slow. The result is cached for 30 days, so only the first visitor pays. After publishing a page, open it once yourself to warm the cache.
- A larger `limit` means more requests on that first display. Keep it reasonable for long series.

## 4. The settings block

At the top of the PHP file, you will find:

```php
define('MORF_PIWIGO_API', 'https://your-piwigo.example/ws.php');

define('MORF_PIWIGO_STYLE', array(
    'columns'        => '3',
    'radius'         => '12px',
    'placeholder-bg' => '#eee',
    'caption-bg'     => 'rgba(0, 0, 0, .45)',
    // ... 20 settings in total
));
```

Each line is one setting. The value can be:

| Kind of value | Example |
| --- | --- |
| A colour code | `'#D8D2C8'`, `'rgb(216, 210, 200)'`, `'rgba(0, 0, 0, .45)'` |
| A variable of your theme | `'var(--theme-palette-color-5)'` |
| A theme colour made semi-transparent | `'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)'` |
| A size or a number | `'12px'`, `'1.035'`, `'3'` |

An empty value (`''`) or a deleted line means "use the built-in default". The full list of settings, with their defaults, is in the [README](README.md#adapting-the-look-to-your-theme).

**Two ways to change a setting**
1. In the settings block (above). Simple and everything is in one place.
2. In **Appearance > Customize > Additional CSS**, as a variable: `:root { --morf-radius: 4px; }`. This only works for settings whose line is empty or deleted in the block.

**Order of priority**, strongest first: a rule aimed at the gallery (`.page-id-123 .morf-piwigo-gallery { --morf-radius: 0; }`), then the settings block, then a `:root` rule in Additional CSS, then the built-in default.

**Try a setting without editing anything.** Open your page, right-click a photograph, choose *Inspect*, select the element with the class `morf-piwigo-gallery` and add `--morf-radius: 0;` in the *Styles* panel. The page updates immediately. When you like the result, copy the value into the settings block.

## 5. Finding your theme's colours and variables

There are three layers, from most robust to most elegant. Start with the first: it works with every theme.

### 5.1 Measure what the theme really uses (works with any theme)

Open a page of your site, open the browser console (`F12`, tab **Console**) and run:

```js
const body = getComputedStyle(document.body);
const link = document.querySelector('main a, .entry-content a');
console.table({
  'text colour':       body.color,
  'background colour': body.backgroundColor,
  'link colour':       link ? getComputedStyle(link).color : '(no link found)',
  'font':              body.fontFamily
});
```

You get the colours in `rgb(...)` form. Browsers show `rgb(47, 47, 47)`; the equivalent hex is `#2F2F2F`. Either form can be pasted in the settings block. This gives you fixed values: they will not follow if you later change your theme's palette, but they are always correct.

If the background shows `rgba(0, 0, 0, 0)` (transparent), the colour is set on a wrapper: inspect an element higher in the page (the `<html>` element or the main container).

### 5.2 List the CSS variables of the theme (recommended)

Most themes publish their palette as CSS variables. To list every colour variable available on the page, run this in the console:

```js
const cs = getComputedStyle(document.documentElement);
const names = new Set();
for (const sheet of document.styleSheets) {
  try { for (const r of sheet.cssRules) { if (r.style) for (const p of r.style) if (p.startsWith('--')) names.add(p); } }
  catch (e) { /* style sheet from another domain: skipped */ }
}
const rows = [...names]
  .map(n => ({ variable: n, value: cs.getPropertyValue(n).trim() }))
  .filter(r => /^(#|rgb|hsl|color\()/i.test(r.value));
console.table(rows);
```

This prints a table with the name and the resolved value of every variable holding a colour (on the author's site: 128 colour variables out of 544 variables). Look for a family of names that follow a pattern with numbers, such as `--theme-palette-color-1` to `-8`. That family is the palette.

To search it, replace the last line with `console.table(rows.filter(r => /palette|global|preset|accent|contrast|base/.test(r.variable)));`.

**Known families**

| Theme or system | Variables to look for | Notes |
| --- | --- | --- |
| Blocksy | `--theme-palette-color-1` to `-8` | Checked on a real site. Also exposes semantic names such as `--theme-text-color` and `--theme-link-initial-color`. |
| Kadence | `--global-palette1` to `9` | From documentation, not checked here. |
| Astra | `--ast-global-color-0` to `8` | From documentation, not checked here. |
| GeneratePress | `--contrast`, `--contrast-2`, `--contrast-3`, `--base`, `--base-2`, `--base-3`, `--accent` | From documentation, not checked here. |
| Block themes (`theme.json`) | `--wp--preset--color--<slug>` | The slugs are chosen by the theme. See 5.3. |
| Any theme using the WordPress editor palette | `--wp--preset--color--<slug>` | Present when the theme declares an editor colour palette. |

**A trap to know.** Several systems can publish a palette on the same site. For example, a site using Blocksy and the Kadence Blocks plugin has both `--theme-palette-color-*` (the real theme colours) and `--global-palette*` (Kadence's own defaults, blue ones). Compare the values with the colours you actually see on the page before choosing a family. The measure in 5.1 tells you which one the page really uses.

### 5.3 Block themes and the editor palette

- In the **Site Editor** (Appearance > Editor), open **Styles > Colors > Palette**. The names listed there are the slugs: a colour named "Contrast" has the slug `contrast` and the variable `--wp--preset--color--contrast`.
- In the files of the theme, the palette is in `wp-content/themes/<your-theme>/theme.json`, under `settings.color.palette`. Each entry has a `slug` and a `color`.
- With a classic theme, **Appearance > Customize > Colors** (or the theme's own options page) lists the palette, but not always the variable names: use 5.2 for those.

### 5.4 Variable or fixed colour?

| | `var(--theme-palette-color-5)` | `#D8D2C8` |
| --- | --- | --- |
| Follows a later change of your palette | Yes | No |
| Follows a dark mode that swaps the palette | Yes (see section 7) | No |
| Breaks if you change theme | Yes, the variable may disappear (the built-in default is used instead) | No |

Prefer a variable when you want the gallery to stay in tune with the theme. Prefer a fixed colour when you want it to stay as it is.

## 6. Matching the gallery to your theme

Decide for each setting which **role** in your theme it plays, then pick the variable or the colour of that role.

| Setting | Role in the theme | Example on the author's Blocksy site |
| --- | --- | --- |
| `placeholder-bg` | A neutral, light colour (borders, separators) | `var(--theme-palette-color-5)` → `#D8D2C8` |
| `focus` | The accent colour (links, buttons) | `var(--theme-palette-color-1)` → `#5E4B3C` |
| `caption-bg` | The text colour of the page, made semi-transparent | `color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)` |
| `caption-color` | The background colour of the page | `var(--theme-palette-color-7)` → `#FAF8F4` |
| `radius`, `gap` | The rhythm of your theme (its card radius, its spacing) | `12px`, `16px` |
| `font` | Leave at `inherit`: the gallery uses the font of the theme | |

How to find the roles: the measure in 5.1 gives you the text, background and link colours; compare them with the table of 5.2 to find which numbered variable holds each one. On the author's site, the measure gave `#2F2F2F` (text), `#FAF8F4` (background) and `#5E4B3C` (links), which are `-3`, `-7` and `-1`.

**Why a text-on-background pair for the caption.** The title shown over a photograph needs to be readable whatever the photograph is. A semi-transparent band of the text colour, with the page background colour for the lettering, keeps a strong contrast.

**Check the contrast** whenever you change the colours of the caption or of the viewer: the text must stay readable. A ratio of at least 4.5:1 is the usual target.

Keep in mind that `color-mix()` is supported by current browsers (2023 and later). On an older browser, the line is ignored and the default value applies.

## 7. Dark mode

Many themes (and many custom snippets) switch to a dark mode by **swapping the values of the palette variables**: the variable that held a dark text colour then holds a light one.

- For **thumbnails and caption**, this is an advantage as long as you use a *pair* (text colour and background colour): both swap together, so the contrast is kept in both modes.
- For the **viewer** (`lightbox-*`), do not use a theme colour if your site has a dark mode: the background would turn white in dark mode, which is wrong for viewing photographs. Keep the built-in dark values, or use fixed colours.
- The accent colour (`focus`) may be too dark on a dark background in some themes. Check it in both modes.

If your dark mode adds a class on `<html>` (for example `.dark` or `.mf-dark-mode`), you can give the gallery different values in that mode, in Additional CSS:

```css
.mf-dark-mode .morf-piwigo-gallery { --morf-placeholder-bg: #3D352E; }
```

(Use the class of **your** dark mode.)

## 8. Troubleshooting

| Symptom | Likely cause and fix |
| --- | --- |
| "Album Piwigo introuvable" | Check the spelling against the `name` in the API list (section 1). The album must be visible to anonymous visitors. A newly created album is picked up automatically (the list is reloaded once before giving up) from version 1.1.1. |
| "Erreur Piwigo : …" | The API address is wrong or unreachable. Open it in a browser (section 1). Check that your host can make outgoing requests. |
| Photographs from another album appear | You are using a version older than 1.1.0. Update. |
| No shooting data under the photograph | The image file has no EXIF data (Piwigo or your export may have removed it), or the image server does not allow reading it. Open the full-size file and check its metadata. |
| First display of the page is very slow | The shooting data of each photograph are read once, then cached for 30 days. Open the page once to warm the cache. |
| A setting has no effect | Check the syntax (a quote and a comma at the end of each line). Check that the value is a valid CSS value. If you set it in Additional CSS, its line in the settings block must be empty or deleted. Clear your cache plugin. To see what the page uses, search the page source for `--morf-`. |
| Colours are blue and do not match the theme | You used the Kadence variables (`--global-palette*`) on a site that does not use the Kadence theme. See the trap in 5.2. |
| The viewer is hidden behind your menu | Lower `lightbox-z`, or raise your menu's `z-index`. |
| The viewer is white in dark mode | You used a theme colour for `lightbox-bg`. See section 7. |
| The change is not visible after editing a snippet | Clear the cache of your caching plugin and of your browser. |
| White screen after saving the snippet | A syntax error. Code Snippets deactivates the snippet and shows the error; with the plugin option, restore the file. Compare with the original. |

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
