# morf WordPress Cleanup

> Fait partie de [morf-wordpress-snippets](../README.fr.md). English : [README.md](README.md)

Retire les métadonnées inutiles exposées par WordPress et masque la liste des utilisateurs aux visiteurs anonymes.

## Ce que fait le snippet

| Bloc | Effet |
| --- | --- |
| RSD, manifeste WLW, shortlinks | Retire les liens du `<head>` et des en-têtes HTTP. |
| Version de WordPress | Retire la balise generator et sa sortie dans les flux. |
| Liens de découverte REST | Retire les liens seulement. **L'API REST elle-même reste active.** |
| Découverte oEmbed | Les autres sites ne peuvent plus intégrer automatiquement vos articles. |
| Emoji | Retire le script de détection et les styles emoji. |
| Pingbacks XML-RPC | Désactive les deux méthodes de pingback. XML-RPC reste disponible (application mobile, Jetpack). |
| Liste des utilisateurs REST | `/wp/v2/users` est masquée aux visiteurs **non connectés** seulement : l'éditeur de blocs continue de fonctionner. |

Chaque bloc est indépendant : supprimez ceux dont vous n'avez pas besoin.

## Remarques

- C'est un durcissement cosmétique, pas une solution de sécurité. Masquer la version ne corrige pas un site qui n'est pas à jour.
- Masquer la liste REST des utilisateurs n'empêche pas toutes les techniques d'énumération (les archives d'auteurs, par exemple).
- Certaines extensions lisent la liste REST des utilisateurs pour les visiteurs anonymes. Si quelque chose casse, retirez d'abord le dernier bloc.

## Installation

Utilisez-le **au choix** :

- **En extension** : placez `morf-wordpress-cleanup.php` dans son propre dossier, `wp-content/plugins/morf-wordpress-cleanup/morf-wordpress-cleanup.php`, et activez-la.
- **En snippet** (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) : collez le code. Code Snippets retire lui-même la balise d'ouverture `<?php` à l'enregistrement (il peut afficher un avertissement, sans conséquence).

## Prérequis

Aucun prérequis.

## Désinstallation

Désactivez ou supprimez le snippet ou l'extension. Rien n'est stocké en base : les liens et en-têtes retirés reviennent.

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
