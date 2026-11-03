# morf WebP Conversion

> Fait partie de [morf-wordpress-snippets](../README.fr.md). English : [README.md](README.md)

Convertit les images JPEG/PNG téléversées en WebP, avec des contrôles de sécurité. Le fichier d'origine est conservé par défaut.

## Ce que fait le snippet

Après un téléversement, l'image d'origine est convertie en WebP (qualité 82) avec l'éditeur d'image de WordPress, et la médiathèque pointe ensuite vers le fichier WebP.

## Contrôles de sécurité

- Rien n'est fait si le serveur ne sait pas écrire du WebP.
- Le fichier WebP doit exister et ne pas être vide, sinon rien n'est modifié.
- Un fichier `.webp` existant n'est jamais écrasé.
- Il ne s'exécute qu'à la création d'un média, pas lors d'une régénération des miniatures.
- **Le fichier d'origine est conservé.** Pour le supprimer (gain d'espace disque, sans retour possible) :

```php
add_filter( 'morf_webp_delete_original', '__return_true' );
```

## Limites, à lire

- WordPress crée les miniatures **avant** l'exécution de ce code. Elles restent au format d'origine (JPEG/PNG). Seule l'image en taille réelle devient WebP.
- Il ne convertit pas les images déjà présentes dans votre médiathèque.
- Si vous voulez du WebP pour toutes les tailles, WordPress 5.8+ sait le faire nativement avec le filtre `image_editor_output_format`, et ce snippet n'est peut-être pas nécessaire.
- Changer le format de fichiers existants peut casser des liens écrits en dur vers l'ancien fichier. Testez d'abord sur une copie de votre site.
- **Sauvegardez votre site avant de l'utiliser.**

## Installation

Utilisez-le **au choix** :

- **En extension** : copiez `morf-webp-conversion.php` dans `wp-content/plugins/` et activez-la.
- **En snippet** (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) : collez le code sans la première ligne `<?php`.

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
