# morf Instant Navigation

> Fait partie de [morf-wordpress-snippets](../README.fr.md). English : [README.md](README.md)

Navigation quasi instantanée (préchargement) et priorité de chargement de l'image principale de chaque article.

## Ce que fait le snippet

1. **Images LCP.** Sur les articles, les images affichées en taille `large`, `full`, `post-thumbnail` ou `medium_large` reçoivent `loading="eager"` et `fetchpriority="high"` : l'image principale n'est donc pas chargée en différé.
2. **Préchargement.** Les pages internes sont récupérées juste avant le clic : avec l'API Speculation Rules (Chrome, Edge, Opera, niveau `moderate`), et avec un repli au survol ou au toucher ailleurs.

`/wp-admin/`, `/wp-login.php` et `/wp-json/` sont exclus.

## Remarques

- Le préchargement augmente le nombre de requêtes reçues par votre serveur. Sur un petit hébergement mutualisé, surveillez la charge après l'activation.
- Associez-le à un cache de pages pour un meilleur résultat.
- Ne l'activez pas si certains liens internes déclenchent une action (déconnexion, ajout au panier) par un simple GET.

## Installation

Utilisez-le **au choix** :

- **En extension** : copiez `morf-instant-navigation.php` dans `wp-content/plugins/` et activez-la.
- **En snippet** (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) : collez le code sans la première ligne `<?php`.

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
