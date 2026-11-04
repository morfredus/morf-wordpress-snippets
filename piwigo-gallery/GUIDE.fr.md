# morf Piwigo Gallery : guide complet

> English : [GUIDE.md](GUIDE.md) · Retour au [README court](README.fr.md)

Ce guide couvre tout, de l'installation à l'accord de la galerie avec votre propre thème WordPress, pour n'importe quel site (pas seulement celui de l'auteur).

- [1. Ce qu'il vous faut](#1-ce-quil-vous-faut)
- [2. Installation pas à pas](#2-installation-pas-à-pas)
- [3. Utiliser le shortcode](#3-utiliser-le-shortcode)
- [4. Le bloc de réglages](#4-le-bloc-de-réglages)
- [5. Retrouver les couleurs et les variables de votre thème](#5-retrouver-les-couleurs-et-les-variables-de-votre-thème)
- [6. Accorder la galerie à votre thème](#6-accorder-la-galerie-à-votre-thème)
- [7. Mode sombre](#7-mode-sombre)
- [8. Dépannage](#8-dépannage)

## 1. Ce qu'il vous faut

- Un site WordPress (5.8 ou plus) avec PHP 7.4 ou plus et l'extension PHP `exif` (nécessaire pour les données de prise de vue).
- Une installation Piwigo. Les albums à afficher doivent être **visibles par un visiteur non connecté** : la galerie appelle l'API de Piwigo sans aucun identifiant et ne voit jamais les albums privés.
- L'adresse de l'API de Piwigo : l'adresse de votre Piwigo suivie de `/ws.php`, par exemple `https://photos.exemple.com/ws.php`.

**Vérifiez d'abord votre adresse d'API.** Ouvrez ceci dans un navigateur, en remplaçant l'hôte :

```
https://photos.exemple.com/ws.php?format=json&method=pwg.categories.getList&recursive=true
```

Vous devez obtenir un texte JSON qui commence par `{"stat":"ok"` et liste vos albums. Le `name` de chaque album est ce que vous passerez au shortcode. Si vous obtenez une page d'erreur ou `"stat":"fail"`, corrigez cela avant d'aller plus loin.

## 2. Installation pas à pas

Le fichier `morf-piwigo-gallery.php` s'utilise de deux façons. Choisissez-en une.

### Option A : en snippet (extension Code Snippets)

1. Installez et activez [Code Snippets](https://wordpress.org/plugins/code-snippets/).
2. **Snippets > Ajouter**, donnez-lui un nom comme « Galerie Piwigo », type **PHP**.
3. Collez le contenu de `morf-piwigo-gallery.php`. Vous pouvez garder la première ligne `<?php` : Code Snippets la retire à l'enregistrement (il peut afficher un avertissement, sans conséquence).
4. Modifiez le bloc de réglages (section 4) : au minimum `MORF_PIWIGO_API`.
5. Choisissez une portée qui s'exécute sur le site public (le réglage par défaut « exécuter partout » convient), puis enregistrez et activez.

### Option B : en extension

1. Modifiez `MORF_PIWIGO_API` dans le bloc de réglages de `morf-piwigo-gallery.php`.
2. Créez un dossier nommé `morf-piwigo-gallery` dans `wp-content/plugins/` et placez-y le fichier, pour que le chemin soit `wp-content/plugins/morf-piwigo-gallery/morf-piwigo-gallery.php` (en FTP ou avec le gestionnaire de fichiers de votre hébergeur). Un dossier à lui garde l'extension facile à retrouver, à mettre à jour et à supprimer.
   Autre possibilité : compressez ce dossier en zip et utilisez **Extensions > Ajouter > Téléverser une extension** : le zip doit contenir le dossier `morf-piwigo-gallery`, pas le fichier seul.
3. Activez **morf Piwigo Gallery** dans **Extensions**.

Vos réglages vivent dans ce fichier : si vous le remplacez plus tard par une version plus récente, ils sont écrasés. Gardez une copie de votre bloc de réglages.

## 3. Utiliser le shortcode

```
[morf_piwigo album="Rouille" limit="13" texte="titre"]
```

| Paramètre | Défaut | Rôle |
| --- | --- | --- |
| `album` | *(obligatoire)* | Nom de l'album Piwigo. Les majuscules, les espaces répétés et les variantes de tiret (-, –, —) sont ignorés. |
| `limit` | `0` | Nombre de photographies à afficher. `0` signifie jusqu'à 500. |
| `texte` | `titre` | Légende : `titre` (le titre de la photo) ou `description` (sa description Piwigo, ou le titre si elle est vide). |

**Où le placer.** Dans l'éditeur de blocs, ajoutez un bloc **Shortcode** et tapez la ligne ci-dessus. Dans l'éditeur classique, tapez-la dans le texte. Vous pouvez mettre plusieurs galeries sur une même page : chacune est indépendante.

**Pour afficher le shortcode comme texte** (dans un tutoriel, par exemple), doublez les crochets : `[[morf_piwigo album="Rouille"]]`. Mettez-le dans un bloc de code pour qu'il ne soit pas interprété.

**Sous-albums.** Seules les photographies de l'album lui-même sont affichées, pas celles de ses sous-albums. Utilisez un shortcode par album.

**Modèle d'appareil.** Le modèle affiché dans la visionneuse vient des données EXIF de l'image. S'il n'y en a pas, le code se rabat sur le **nom du fichier** : avec un nom comme `20240512_OM20_001.jpg` (`texte_MODELE_numéro.ext`), il affiche `OM20`. Tout ce qui se trouve entre le premier tiret bas et le dernier `_numéro` est pris, donc `photo_2024_001.jpg` affiche `2024`, qui n'est pas un appareil. Un nom sans ce motif n'affiche rien. Pour maîtriser cela, mettez `MORF_PIWIGO_CAMERA_FROM_FILENAME` à `false` dans le bloc de réglages (seules les données EXIF sont alors utilisées), ou nommez vos fichiers d'après le motif, ou gardez le modèle d'appareil dans les données EXIF.

**Performances.**
- Les réponses de Piwigo sont conservées 10 minutes.
- Les données de prise de vue sont lues dans les fichiers image eux-mêmes, une requête par photographie, avec un délai maximal de 5 secondes. Le premier affichage d'une page avec beaucoup de photographies peut donc être lent. Le résultat est conservé 30 jours : seul le premier visiteur en pâtit. Après avoir publié une page, ouvrez-la une fois vous-même pour préparer le cache.
- Un `limit` plus grand signifie plus de requêtes à ce premier affichage. Gardez une valeur raisonnable pour les longues séries.

## 4. Le bloc de réglages

En haut du fichier PHP, vous trouverez :

```php
define('MORF_PIWIGO_API', 'https://votre-piwigo.exemple/ws.php');
define('MORF_PIWIGO_CAMERA_FROM_FILENAME', true);   // true ou false

define('MORF_PIWIGO_STYLE', array(
    'columns'        => '3',
    'radius'         => '12px',
    'placeholder-bg' => '#eee',
    'caption-bg'     => 'rgba(0, 0, 0, .45)',
    // ... 20 réglages au total
));
```

Chaque ligne est un réglage. La valeur peut être :

| Type de valeur | Exemple |
| --- | --- |
| Un code couleur | `'#D8D2C8'`, `'rgb(216, 210, 200)'`, `'rgba(0, 0, 0, .45)'` |
| Une variable de votre thème | `'var(--theme-palette-color-5)'` |
| Une couleur du thème rendue semi-transparente | `'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)'` |
| Une taille ou un nombre | `'12px'`, `'1.035'`, `'3'` |

Une valeur vide (`''`) ou une ligne supprimée signifie « utiliser la valeur par défaut intégrée ». La liste complète des réglages, avec leurs valeurs par défaut, est dans le [README](README.fr.md#adapter-le-rendu-au-thème-du-site).

**Deux façons de modifier un réglage**
1. Dans le bloc de réglages (ci-dessus). Simple, et tout est au même endroit.
2. Dans **Apparence > Personnaliser > CSS additionnel**, sous forme de variable : `:root { --morf-radius: 4px; }`. Cela ne fonctionne que pour les réglages dont la ligne est vide ou supprimée dans le bloc.

**Ordre de priorité**, du plus fort au plus faible : une règle visant la galerie (`.page-id-123 .morf-piwigo-gallery { --morf-radius: 0; }`), puis le bloc de réglages, puis une règle `:root` du CSS additionnel, puis la valeur par défaut intégrée.

**Essayer un réglage sans rien modifier.** Ouvrez votre page, faites un clic droit sur une photographie, choisissez *Inspecter*, sélectionnez l'élément de classe `morf-piwigo-gallery` et ajoutez `--morf-radius: 0;` dans le panneau *Styles*. La page se met à jour tout de suite. Quand le résultat vous plaît, recopiez la valeur dans le bloc de réglages.

## 5. Retrouver les couleurs et les variables de votre thème

Il y a trois niveaux, du plus robuste au plus élégant. Commencez par le premier : il fonctionne avec tous les thèmes.

### 5.1 Mesurer ce que le thème utilise vraiment (tous les thèmes)

Ouvrez une page de votre site, ouvrez la console du navigateur (`F12`, onglet **Console**) et lancez :

```js
const body = getComputedStyle(document.body);
const link = document.querySelector('main a, .entry-content a');
console.table({
  'couleur du texte': body.color,
  'couleur de fond':  body.backgroundColor,
  'couleur des liens': link ? getComputedStyle(link).color : '(aucun lien trouvé)',
  'police':           body.fontFamily
});
```

Vous obtenez les couleurs sous la forme `rgb(...)`. Le navigateur affiche `rgb(47, 47, 47)` ; l'équivalent hexadécimal est `#2F2F2F`. L'une ou l'autre forme peut être collée dans le bloc de réglages. Ce sont des valeurs fixes : elles ne suivront pas un changement ultérieur de la palette de votre thème, mais elles sont toujours justes.

Si le fond affiche `rgba(0, 0, 0, 0)` (transparent), la couleur est posée sur un conteneur : inspectez un élément plus haut dans la page (l'élément `<html>` ou le conteneur principal).

### 5.2 Lister les variables CSS du thème (recommandé)

La plupart des thèmes publient leur palette sous forme de variables CSS. Pour lister toutes les variables de couleur disponibles sur la page, lancez ceci dans la console :

```js
const cs = getComputedStyle(document.documentElement);
const names = new Set();
for (const sheet of document.styleSheets) {
  try { for (const r of sheet.cssRules) { if (r.style) for (const p of r.style) if (p.startsWith('--')) names.add(p); } }
  catch (e) { /* feuille de style d'un autre domaine : ignorée */ }
}
const rows = [...names]
  .map(n => ({ variable: n, value: cs.getPropertyValue(n).trim() }))
  .filter(r => /^(#|rgb|hsl|color\()/i.test(r.value));
console.table(rows);
```

Cela affiche un tableau avec le nom et la valeur résolue de chaque variable qui contient une couleur (sur le site de l'auteur : 128 variables de couleur sur 544 variables). Cherchez une famille de noms qui suivent un motif avec des numéros, comme `--theme-palette-color-1` à `-8`. Cette famille est la palette.

Pour filtrer, remplacez la dernière ligne par `console.table(rows.filter(r => /palette|global|preset|accent|contrast|base/.test(r.variable)));`.

**Familles connues**

| Thème ou système | Variables à chercher | Remarques |
| --- | --- | --- |
| Blocksy | `--theme-palette-color-1` à `-8` | Vérifié sur un vrai site. Expose aussi des noms sémantiques comme `--theme-text-color` et `--theme-link-initial-color`. |
| Kadence | `--global-palette1` à `9` | D'après la documentation, non vérifié ici. |
| Astra | `--ast-global-color-0` à `8` | D'après la documentation, non vérifié ici. |
| GeneratePress | `--contrast`, `--contrast-2`, `--contrast-3`, `--base`, `--base-2`, `--base-3`, `--accent` | D'après la documentation, non vérifié ici. |
| Thèmes à blocs (`theme.json`) | `--wp--preset--color--<slug>` | Les slugs sont choisis par le thème. Voir 5.3. |
| Tout thème utilisant la palette de l'éditeur WordPress | `--wp--preset--color--<slug>` | Présentes quand le thème déclare une palette de couleurs pour l'éditeur. |

**Un piège à connaître.** Plusieurs systèmes peuvent publier une palette sur un même site. Par exemple, un site qui utilise Blocksy et l'extension Kadence Blocks a à la fois `--theme-palette-color-*` (les vraies couleurs du thème) et `--global-palette*` (les valeurs par défaut de Kadence, des bleus). Comparez les valeurs avec les couleurs que vous voyez réellement sur la page avant de choisir une famille. La mesure du 5.1 vous dit laquelle la page utilise vraiment.

### 5.3 Thèmes à blocs et palette de l'éditeur

- Dans l'**éditeur de site** (Apparence > Éditeur), ouvrez **Styles > Couleurs > Palette**. Les noms listés sont les slugs : une couleur nommée « Contrast » a pour slug `contrast` et pour variable `--wp--preset--color--contrast`.
- Dans les fichiers du thème, la palette est dans `wp-content/themes/<votre-thème>/theme.json`, sous `settings.color.palette`. Chaque entrée a un `slug` et une `color`.
- Avec un thème classique, **Apparence > Personnaliser > Couleurs** (ou la page d'options propre au thème) liste la palette, mais pas toujours les noms de variables : utilisez le 5.2 pour cela.

### 5.4 Variable ou couleur fixe ?

| | `var(--theme-palette-color-5)` | `#D8D2C8` |
| --- | --- | --- |
| Suit un changement ultérieur de la palette | Oui | Non |
| Suit un mode sombre qui échange la palette | Oui (voir section 7) | Non |
| Casse si vous changez de thème | Oui, la variable peut disparaître (la valeur par défaut intégrée s'applique alors) | Non |

Préférez une variable pour que la galerie reste accordée au thème. Préférez une couleur fixe pour qu'elle reste telle quelle.

## 6. Accorder la galerie à votre thème

Décidez pour chaque réglage quel **rôle** il joue dans votre thème, puis choisissez la variable ou la couleur de ce rôle.

| Réglage | Rôle dans le thème | Exemple sur le site Blocksy de l'auteur |
| --- | --- | --- |
| `placeholder-bg` | Une couleur neutre et claire (bordures, séparateurs) | `var(--theme-palette-color-5)` → `#D8D2C8` |
| `focus` | La couleur d'accent (liens, boutons) | `var(--theme-palette-color-1)` → `#5E4B3C` |
| `caption-bg` | La couleur de texte de la page, rendue semi-transparente | `color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)` |
| `caption-color` | La couleur de fond de la page | `var(--theme-palette-color-7)` → `#FAF8F4` |
| `radius`, `gap` | Le rythme de votre thème (arrondi des cartes, espacements) | `12px`, `16px` |
| `font` | Laissez `inherit` : la galerie utilise la police du thème | |

**Exemple complet (Blocksy), dans le bloc de réglages :**

```php
'placeholder-bg' => 'var(--theme-palette-color-5)',
'focus'          => 'var(--theme-palette-color-1)',
// Titre sur la photo : couleur de texte du thème sur couleur de fond du thème, opaque à 55 %.
'caption-bg'     => 'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)',
'caption-color'  => 'var(--theme-palette-color-7)',
```

Ou, en CSS additionnel (laissez vides les lignes correspondantes du bloc de réglages) :

```css
:root {
  --morf-placeholder-bg: var(--theme-palette-color-5);
  --morf-focus: var(--theme-palette-color-1);
  --morf-caption-bg: color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent);
  --morf-caption-color: var(--theme-palette-color-7);
}
```

Comment trouver les rôles : la mesure du 5.1 vous donne les couleurs du texte, du fond et des liens ; comparez-les au tableau du 5.2 pour trouver quelle variable numérotée contient chacune. Sur le site de l'auteur, la mesure a donné `#2F2F2F` (texte), `#FAF8F4` (fond) et `#5E4B3C` (liens), soit `-3`, `-7` et `-1`.

**Pourquoi un couple texte sur fond pour la légende.** Le titre affiché sur une photographie doit rester lisible quelle que soit la photographie. Un bandeau semi-transparent de la couleur du texte, avec la couleur de fond de la page pour les lettres, garde un fort contraste.

**Vérifiez le contraste** chaque fois que vous changez les couleurs de la légende ou de la visionneuse : le texte doit rester lisible. Un rapport d'au moins 4,5:1 est l'objectif habituel.

`color-mix()` est pris en charge par les navigateurs actuels (2023 et après). Sur un navigateur plus ancien, la ligne est ignorée et la valeur par défaut s'applique.

## 7. Mode sombre

Beaucoup de thèmes (et de snippets personnalisés) passent en mode sombre en **échangeant les valeurs des variables de la palette** : la variable qui contenait une couleur de texte sombre en contient alors une claire.

- Pour les **vignettes et la légende**, c'est un avantage tant que vous utilisez un *couple* (couleur de texte et couleur de fond) : les deux s'échangent ensemble, le contraste est donc conservé dans les deux modes.
- Pour la **visionneuse** (`lightbox-*`), n'utilisez pas de couleur du thème si votre site a un mode sombre : le fond deviendrait blanc en mode sombre, ce qui ne convient pas pour regarder des photographies. Gardez les valeurs sombres intégrées, ou utilisez des couleurs fixes.
- La couleur d'accent (`focus`) peut être trop sombre sur fond sombre dans certains thèmes. Vérifiez-la dans les deux modes.

Si votre mode sombre ajoute une classe sur `<html>` (par exemple `.dark` ou `.mf-dark-mode`), vous pouvez donner à la galerie d'autres valeurs dans ce mode, dans le CSS additionnel :

```css
.mf-dark-mode .morf-piwigo-gallery { --morf-placeholder-bg: #3D352E; }
```

(Utilisez la classe de **votre** mode sombre.)

## 8. Dépannage

| Symptôme | Cause probable et remède |
| --- | --- |
| « Album Piwigo introuvable » | Vérifiez l'orthographe par rapport au `name` de la liste de l'API (section 1). L'album doit être visible des visiteurs anonymes. Un album tout juste créé est pris en compte automatiquement (la liste est rechargée une fois avant d'abandonner) à partir de la version 1.1.1. |
| « Erreur Piwigo : … » | L'adresse de l'API est fausse ou injoignable. Ouvrez-la dans un navigateur (section 1). Vérifiez que votre hébergeur autorise les requêtes sortantes. |
| Des photographies d'un autre album apparaissent | Votre version est antérieure à la 1.1.0. Mettez à jour. |
| Pas de données de prise de vue sous la photographie | Le fichier image n'a pas de données EXIF (Piwigo ou votre export les a peut-être retirées), ou le serveur d'images ne permet pas de les lire. Ouvrez le fichier en taille réelle et vérifiez ses métadonnées. |
| Un mauvais modèle d'appareil s'affiche (une année, un mot) | Les données EXIF n'ont pas de modèle, le code l'a donc lu dans le nom du fichier (voir section 3). Mettez `MORF_PIWIGO_CAMERA_FROM_FILENAME` à `false`, ajoutez le modèle aux données EXIF, ou renommez le fichier pour que la partie entre le premier tiret bas et le dernier `_numéro` soit celle voulue. |
| Le premier affichage de la page est très lent | Les données de prise de vue de chaque photographie sont lues une fois, puis conservées 30 jours. Ouvrez la page une fois pour préparer le cache. |
| Un réglage n'a aucun effet | Vérifiez la syntaxe (une apostrophe et une virgule en fin de ligne). Vérifiez que la valeur est une valeur CSS valide. Si vous l'avez mise dans le CSS additionnel, sa ligne dans le bloc de réglages doit être vide ou supprimée. Videz votre extension de cache. Pour voir ce que la page utilise, cherchez `--morf-` dans le code source de la page. |
| Les couleurs sont bleues et ne correspondent pas au thème | Vous avez utilisé les variables Kadence (`--global-palette*`) sur un site qui n'utilise pas le thème Kadence. Voir le piège du 5.2. |
| La visionneuse passe derrière votre menu | Baissez `lightbox-z`, ou augmentez le `z-index` de votre menu. |
| La visionneuse est blanche en mode sombre | Vous avez utilisé une couleur du thème pour `lightbox-bg`. Voir la section 7. |
| La modification n'apparaît pas après avoir changé un snippet | Videz le cache de votre extension de cache et de votre navigateur. |
| Avertissement sur `<?php` à l'enregistrement | Sans conséquence : Code Snippets retire lui-même la balise d'ouverture. |
| Écran blanc après l'enregistrement du snippet | Une erreur de syntaxe. Code Snippets désactive le snippet et affiche l'erreur ; avec l'option extension, restaurez le fichier. Comparez avec l'original. |

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
