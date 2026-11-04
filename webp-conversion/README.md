# morf WebP Conversion

> Part of [morf-wordpress-snippets](../README.md). Français : [README.fr.md](README.fr.md)

Converts uploaded JPEG/PNG images to WebP, with safety checks. The original file is kept by default.

## What it does

After an upload, the original image is converted to WebP (quality 82) with WordPress's own image editor, and the media library then points to the WebP file.

## Safety checks

- Nothing happens if the server cannot write WebP.
- The WebP file must exist and not be empty, otherwise nothing is changed.
- An existing `.webp` file is never overwritten.
- It only runs when a media is created, not when thumbnails are regenerated.
- **The original file is kept.** To delete it (saves disk space, cannot be undone):

```php
add_filter( 'morf_webp_delete_original', '__return_true' );
```

## Limits, please read

- WordPress creates the thumbnails **before** this code runs. They stay in the original format (JPEG/PNG). Only the full-size image becomes WebP.
- It does not convert images already in your library.
- If you want WebP for all sizes, WordPress 5.8+ can do it natively with the `image_editor_output_format` filter, and you may not need this snippet.
- Changing the file format of existing uploads can break hard-coded links to the old file. Test on a copy of your site first.
- **Back up your site before using it.**

## Installation

Use it **either** way:

- **As a plugin**: put `morf-webp-conversion.php` in its own folder, `wp-content/plugins/morf-webp-conversion/morf-webp-conversion.php`, and activate it.
- **As a snippet** (for example with [Code Snippets](https://wordpress.org/plugins/code-snippets/)): paste the code. Code Snippets removes the opening `<?php` tag when you save (it may show a warning, which you can ignore).

## Requirements

A server whose image library (GD or Imagick) can write WebP. The snippet checks this and does nothing otherwise.

## Uninstalling

Deactivating stops new conversions. **Images already converted stay in WebP**: they are not converted back, and the originals are gone if you had enabled their deletion.

## License

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
