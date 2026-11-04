# morf Piwigo Gallery

> Fait partie de [morf-wordpress-snippets](../README.fr.md).

Affichez un album [Piwigo](https://piwigo.org) dans une page WordPress avec un simple shortcode, sans copier les images dans la médiathèque.

> English: [README.md](README.md)

Piwigo continue de gérer les photographies et leurs albums. WordPress reste l'endroit où l'on raconte ce qu'il y a derrière. La galerie est générée à la volée à partir de l'API de Piwigo.

Écrit pour [morfredus.fr](https://morfredus.fr), partagé tel quel. L'histoire derrière : *Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet*.

## Fonctionnalités

- Grille responsive : trois colonnes sur grand écran, deux sur écran intermédiaire, une sur petit écran.
- Visionneuse plein écran : flèches du clavier, `Échap` pour fermer, clic en dehors de l'image, compteur.
- Informations de prise de vue sous chaque photographie : boîtier, focale, ouverture, durée d'exposition, ISO.
- Pas de seconde copie des images : miniatures et fichiers en pleine taille viennent directement de Piwigo.
- Deux caches : réponses de l'API Piwigo (10 minutes) et données EXIF (30 jours).
- Un seul fichier PHP, sans dépendance ni compilation.

## Prérequis

- WordPress 5.8 ou plus, PHP 7.4 ou plus avec l'extension `exif`.
- Une installation Piwigo dont les albums sont **publics** (l'API est appelée sans authentification).

## Installation

1. Modifiez la constante en haut de `morf-piwigo-gallery.php` :

   ```php
   define('MORF_PIWIGO_API', 'https://votre-piwigo.exemple/ws.php');
   ```

2. Utilisez-le **au choix** :
   - **En extension** : copiez `morf-piwigo-gallery.php` dans `wp-content/plugins/` et activez-la.
   - **En snippet** (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) : collez le code sans la première ligne `<?php`.

## Utilisation

```
[morf_piwigo album="Rouille" limit="13"]
[morf_piwigo album="Rouille" limit="8" texte="description"]
[morf_piwigo album="Rouille" limit="0"]
```

| Paramètre | Défaut | Rôle |
| --- | --- | --- |
| `album` | *(obligatoire)* | Nom de l'album Piwigo. Les majuscules, les espaces multiples et les différents tirets (-, –, —) sont ignorés. |
| `limit` | `0` | Nombre de photographies. `0` signifie jusqu'à 500. |
| `texte` | `titre` | Légende affichée : `titre` ou `description`. |

Dans l'éditeur de blocs, placez le shortcode dans un bloc *Shortcode*. Pour l'afficher comme texte dans un article, doublez les crochets : `[[morf_piwigo album="Rouille"]]`.

## Fonctionnement

1. Le shortcode cherche l'album par son nom avec `pwg.categories.getList`, puis liste ses images avec `pwg.categories.getImages`.
2. Les réponses de l'API Piwigo ne contiennent pas les données de prise de vue. Le code demande donc les 64 premiers Ko de chaque image (en-tête HTTP `Range`) et lit l'en-tête EXIF avec `exif_read_data`.
3. Les résultats sont conservés avec les transients de WordPress.

## Vie privée et sécurité

- **Coordonnées GPS.** Seuls cinq champs EXIF sont affichés, mais la visionneuse ouvre le fichier d'origine servi par Piwigo. Si vos originaux contiennent des coordonnées GPS, un visiteur peut les récupérer. Supprimez-les dans Piwigo ou avant l'envoi si cela compte pour vous.
- **Albums publics uniquement.** Aucun identifiant n'est stocké ni envoyé. Les albums privés ne s'affichent pas.
- Toutes les sorties sont échappées (`esc_url`, `esc_attr`, `esc_html`) et les attributs du shortcode sont nettoyés.

## Limites

- Si le serveur d'images ignore l'en-tête `Range`, le fichier entier est téléchargé une fois pour lire l'EXIF, puis mis en cache.
- L'affichage des EXIF dépend de la présence des métadonnées dans le fichier.
- Après une modification dans Piwigo, les données en cache ne sont pas rafraîchies tout de suite. Attendez l'expiration du cache ou supprimez les transients `morf_piwigo_*` et `morf_strict_exif_*`.
- Testé uniquement sur le site de l'auteur.

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
