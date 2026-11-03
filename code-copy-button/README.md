# morf Code Copy Button

> Part of [morf-wordpress-snippets](../README.md). Français : [README.fr.md](README.fr.md)

Styles code blocks and adds a "Copy" button that appears on hover.

## What it does

- Gives every `<pre>` block a light background, a border and rounded corners.
- Adds a **Copy** button (top right, visible on hover or keyboard focus) that copies the block content to the clipboard.
- Adapts to dark mode: it looks for the `.mf-dark-mode` class on `<html>`. Adapt the selector to your own dark mode.

## Customize

The button colour is set with a CSS variable. In your theme's additional CSS:

```css
:root { --morf-copy-bg: #0073aa; }
```

## Notes

- The styles use `!important` and apply to **all** `<pre>` elements of the site. Check the rendering of other code plugins.
- Copying uses `navigator.clipboard`, which requires HTTPS.
- The button is French ("Copier" / "Copié !"). Change the strings in the JavaScript for another language.

## Installation

Use it **either** way:

- **As a plugin**: copy `morf-code-copy-button.php` into `wp-content/plugins/` and activate it.
- **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code without the first `<?php` line.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
