# morf Piwigo Gallery

> Fait partie de [morf-wordpress-snippets](../README.fr.md).

Affichez un album [Piwigo](https://piwigo.org) dans une page WordPress avec un simple shortcode, sans copier les images dans la médiathèque.

> English: [README.md](README.md)

**Guide complet** (installation pas à pas, adaptation au thème, comment retrouver les variables de votre thème, dépannage) : [GUIDE.fr.md](GUIDE.fr.md).

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

1. Modifiez le bloc de réglages en haut de `morf-piwigo-gallery.php`. L'adresse de votre Piwigo est obligatoire :

   ```php
   define('MORF_PIWIGO_API', 'https://votre-piwigo.exemple/ws.php');
   ```

2. Utilisez-le **au choix** :
   - **En extension** : placez `morf-piwigo-gallery.php` dans son propre dossier, `wp-content/plugins/morf-piwigo-gallery/morf-piwigo-gallery.php`, et activez-la.
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

## Adapter le rendu au thème du site

Tout ce qui est visuel passe par des variables CSS nommées `--morf-*`, et chacune est listée, avec sa valeur par défaut, dans un **bloc de réglages en tête du fichier PHP** (`MORF_PIWIGO_STYLE`). Ce bloc est la seule partie à modifier.

```php
define('MORF_PIWIGO_STYLE', array(
    'radius'         => '4px',
    'placeholder-bg' => 'var(--theme-palette-color-5)',   // une couleur du thème
    'caption-bg'     => '#1c1c1c',                        // ou un code hexadécimal
    // ...
));
```

Chaque valeur peut être un code couleur (`#D8D2C8`, `rgba(0, 0, 0, .45)`), une variable du thème (`var(--theme-palette-color-5)`), ou une taille ou un nombre (`12px`, `3`). Une valeur vide (`''`) ou une ligne supprimée signifie « utiliser la valeur par défaut intégrée ».

**Seconde possibilité : le CSS.** Si vous préférez ne pas toucher au PHP, videz la ligne (ou supprimez-la) et définissez la variable dans **Apparence > Personnaliser > CSS additionnel** :

```css
:root {
  --morf-radius: 4px;
  --morf-placeholder-bg: var(--theme-palette-color-5);
}
```

Une valeur renseignée dans le bloc de réglages l'emporte sur une règle `:root` du CSS additionnel. Une règle visant la galerie elle-même, comme `.page-id-123 .morf-piwigo-gallery { --morf-radius: 0; }`, l'emporte toujours sur les deux.

Les valeurs sont contrôlées avant usage : accolades, points-virgules et chevrons sont retirés, une valeur ne peut donc pas sortir de sa déclaration CSS.

### Variables

| Variable | Défaut | Ce qu'elle règle |
| --- | --- | --- |
| `--morf-columns` | `3` | Colonnes sur grand écran. |
| `--morf-columns-tablet` | `2` | Colonnes jusqu'à 900 px de large. |
| `--morf-columns-mobile` | `1` | Colonnes jusqu'à 550 px de large. |
| `--morf-gap` | `16px` | Espace entre les photographies. |
| `--morf-radius` | `12px` | Arrondi des vignettes. |
| `--morf-zoom` | `1.035` | Zoom au survol (`1` le désactive). |
| `--morf-font` | `inherit` | Police de la galerie (celle du thème par défaut). |
| `--morf-placeholder-bg` | `#eee` | Couleur affichée derrière une vignette pendant son chargement. |
| `--morf-caption-bg` | `rgba(0, 0, 0, .45)` | Fond du titre affiché sur une vignette. |
| `--morf-caption-color` | `#fff` | Couleur du texte de ce titre. |
| `--morf-caption-size` | `14px` | Taille du texte de ce titre. |
| `--morf-focus` | `currentColor` | Couleur du contour quand une vignette a le focus clavier. |
| `--morf-lightbox-bg` | `rgba(12, 12, 12, .96)` | Fond de la visionneuse plein écran. |
| `--morf-lightbox-color` | `#eee` | Texte de la visionneuse (titre). |
| `--morf-lightbox-muted` | `#888` | Compteur de la visionneuse (« 3 / 13 »). |
| `--morf-controls-color` | `#fff` | Boutons fermer, précédent et suivant. |
| `--morf-exif-color` | `#ccc` | Couleur du texte du badge EXIF. |
| `--morf-exif-bg` | `rgba(255, 255, 255, .08)` | Fond du badge EXIF. |
| `--morf-exif-font` | `monospace, sans-serif` | Police du badge EXIF. |
| `--morf-lightbox-z` | `999999` | Ordre d'empilement de la visionneuse (à baisser si elle masque votre propre menu). |

Non réglables par variable : les seuils de 900 px et 550 px (les media queries CSS ne lisent pas les variables). Modifiez les deux lignes `@media` dans le fichier PHP si vous voulez d'autres valeurs.

### Utiliser les couleurs de votre thème

La plupart des thèmes publient leur palette sous forme de variables CSS sur `:root`. Utilisez-les avec `var(...)` :

| Thème | Variables à chercher |
| --- | --- |
| Blocksy | `--theme-palette-color-1` à `-8` (vérifié sur le site de l'auteur) |
| Kadence | `--global-palette1` à `9` |
| Astra | `--ast-global-color-0` à `8` |
| GeneratePress | `--contrast`, `--contrast-2`, `--contrast-3`, `--base`, `--base-2`, `--base-3`, `--accent` |
| Thèmes à blocs (theme.json) | `--wp--preset--color--<slug>`, les slugs sont listés dans l'éditeur de site, section Styles > Couleurs |

Seul Blocksy a été vérifié sur un vrai site. Pour les autres, confirmez les noms dans votre navigateur : ouvrez les outils de développement, sélectionnez l'élément `<html>` et regardez le panneau *Styles*, ou lancez ceci dans la console :

```js
[...getComputedStyle(document.documentElement)].filter(k => /palette|global-color|preset--color/.test(k))
```

**Piège.** Sur un site Blocksy qui utilise aussi Kadence Blocks, les variables `--global-palette*` existent également, avec les couleurs **bleues** par défaut de Kadence, et non celles de votre thème. Utilisez les variables du thème qui habille réellement vos pages.

### Exemple (Blocksy)

Dans le bloc de réglages :

```php
'placeholder-bg' => 'var(--theme-palette-color-5)',
'focus'          => 'var(--theme-palette-color-1)',
// Titre sur la photo : couleur de texte du thème sur couleur de fond du thème, opaque à 55 %.
'caption-bg'     => 'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)',
'caption-color'  => 'var(--theme-palette-color-7)',
```

Ou, en CSS additionnel :

```css
:root {
  --morf-placeholder-bg: var(--theme-palette-color-5);
  --morf-focus: var(--theme-palette-color-1);
  /* Titre sur la photo : couleur de texte du thème sur couleur de fond du thème, opaque à 55 %. */
  --morf-caption-bg: color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent);
  --morf-caption-color: var(--theme-palette-color-7);
}
```

`color-mix()` est nécessaire pour rendre une couleur du thème semi-transparente. Il fonctionne dans les versions actuelles de Chrome, Edge, Safari et Firefox (2023 et après) ; dans un navigateur plus ancien, la déclaration est ignorée et la valeur par défaut s'applique.

### Bon à savoir

- **Gardez la visionneuse sombre.** La visionneuse plein écran est faite pour montrer des photographies sur fond sombre. Si votre thème a un mode sombre, sa palette échange en général clairs et sombres : une couleur du thème utilisée pour `--morf-lightbox-bg` rendrait la visionneuse blanche en mode sombre. N'y mettez une couleur du thème que si votre site n'a pas de mode sombre.
- **Mode sombre et titre.** Le couple « couleur de texte sur couleur de fond » (par exemple Blocksy `-3` sur `-7`) s'échange en bloc : le titre reste lisible dans les deux modes.
- **Une seule page.** WordPress ajoute l'identifiant de la page à la classe du `<body>` : vous pouvez limiter un réglage, par exemple `.page-id-123 .morf-piwigo-gallery { --morf-radius: 0; }`.
- **Ordre des règles.** Les variables sont lues à l'affichage : pour l'option CSS, placez-les où vous voulez (`:root`, `body`, ou un conteneur), sans souci d'ordre avec la galerie.
- **Extensions de cache.** Si vous utilisez un minificateur ou un cache de CSS, videz-le après avoir modifié votre CSS additionnel.

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
