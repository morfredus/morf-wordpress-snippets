# morf WordPress Snippets

Petits snippets WordPress autonomes, issus de [morfredus.fr](https://morfredus.fr) et partagés tels quels.

> English: [README.md](README.md)

Chaque snippet a son dossier et son propre README. La plupart tiennent dans un seul fichier PHP, utilisable comme snippet (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) ou déposé dans `wp-content/plugins/` quand le fichier a un en-tête d'extension. Aucune dépendance, aucune compilation.

## Snippets

| Dossier | Rôle | Article |
| --- | --- | --- |
| [`piwigo-gallery`](piwigo-gallery) | Shortcode `[morf_piwigo]` : affiche un album Piwigo public en grille responsive, avec visionneuse plein écran et données EXIF, sans copier les images dans la médiathèque. | *Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet* |
| [`instant-navigation`](instant-navigation) | Navigation quasi instantanée (préchargement) et priorité de chargement de l'image principale de chaque article. | *Vitesse WordPress : Instant Click et Lazy Loading (sans extension)* |
| [`auto-pingbacks`](auto-pingbacks) | Empêche WordPress de se notifier lui-même quand un article contient un lien interne. | - |
| [`code-copy-button`](code-copy-button) | Habille les blocs de code et ajoute un bouton « Copier ». | - |
| [`webp-conversion`](webp-conversion) | Convertit les JPEG/PNG téléversés en WebP, avec contrôles de sécurité. Original conservé par défaut. | *Performance WordPress : Convertir automatiquement ses images en WebP (sans plugin)* |
| [`wordpress-cleanup`](wordpress-cleanup) | Retire les métadonnées inutiles exposées par WordPress, masque la liste des utilisateurs aux visiteurs. | - |

## Avant d'utiliser un snippet

- Lisez le code d'abord. Ces snippets ont été écrits pour un seul site, testés uniquement là, et peuvent demander des adaptations.
- Les valeurs à changer (adresses, identifiants) sont signalées par un commentaire en haut de chaque fichier.
- Sauvegardez votre site avant d'y ajouter du code.

## Licence

[GPL-3.0-only](LICENSE) © 2026 Frédéric Biron (morfredus)
