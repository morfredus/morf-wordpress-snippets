# morf WordPress Snippets

Petits snippets WordPress autonomes, issus de [morfredus.fr](https://morfredus.fr) et partagés tels quels.

> English: [README.md](README.md)

Chaque snippet a son dossier et son propre README. La plupart tiennent dans un seul fichier PHP, utilisable comme snippet (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) ou installé comme extension quand le fichier a un en-tête d'extension (voir [Installer un snippet](#installer-un-snippet)). Aucune dépendance, aucune compilation.

## Snippets

| Dossier | Rôle | Article |
| --- | --- | --- |
| [`piwigo-gallery`](piwigo-gallery) | Shortcode `[morf_piwigo]` : affiche un album Piwigo public en grille responsive, avec visionneuse plein écran et données EXIF, sans copier les images dans la médiathèque. | [*Intégrer une galerie Piwigo dans WordPress sans quitter mon carnet*](https://morfredus.fr/integrer-piwigo-wordpress/) |
| [`instant-navigation`](instant-navigation) | Navigation quasi instantanée (préchargement) et priorité de chargement de l'image principale de chaque article. | *Vitesse WordPress : Instant Click et Lazy Loading (sans extension)* |
| [`auto-pingbacks`](auto-pingbacks) | Empêche WordPress de se notifier lui-même quand un article contient un lien interne. | - |
| [`code-copy-button`](code-copy-button) | Habille les blocs de code et ajoute un bouton « Copier ». | - |
| [`webp-conversion`](webp-conversion) | Convertit les JPEG/PNG téléversés en WebP, avec contrôles de sécurité. Original conservé par défaut. | *Performance WordPress : Convertir automatiquement ses images en WebP (sans plugin)* |
| [`wordpress-cleanup`](wordpress-cleanup) | Retire les métadonnées inutiles exposées par WordPress, masque la liste des utilisateurs aux visiteurs. | - |

## Installer un snippet

Chaque snippet s'installe de deux façons. Choisissez-en une.

**A. Avec l'extension Code Snippets (recommandé pour un snippet isolé)**
1. Installez et activez [Code Snippets](https://wordpress.org/plugins/code-snippets/).
2. **Snippets > Ajouter**, choisissez le type **PHP**, donnez-lui un nom.
3. Collez le contenu du fichier `.php` du snippet. Vous pouvez garder la première ligne `<?php` : Code Snippets la retire à l'enregistrement (il peut afficher un avertissement, sans conséquence).
4. Modifiez les valeurs signalées en haut du code si le README du snippet le demande, enregistrez et activez.

**B. En extension**
1. Créez dans `wp-content/plugins/` un dossier portant le nom du fichier, sans `.php` (par exemple `morf-code-copy-button`), et placez-y le fichier : `wp-content/plugins/morf-code-copy-button/morf-code-copy-button.php`.
2. Activez-la dans **Extensions**.
3. Autre possibilité : compressez ce dossier en zip et utilisez **Extensions > Ajouter > Téléverser une extension**. Le zip doit contenir le dossier, pas le fichier seul. (N'utilisez pas pour cela le « Télécharger en ZIP » de GitHub sur tout le dépôt.)

Avec l'option B, vos modifications sont dans le fichier : le remplacer par une version plus récente les écrase. Gardez une copie des valeurs que vous avez changées.

Le README de chaque snippet donne ses prérequis, son utilisation, ses limites et sa désinstallation.

## Mise à jour

Comparez votre copie avec la nouvelle version (le [CHANGELOG](CHANGELOG.md) liste les changements), puis collez le nouveau code par-dessus l'ancien en conservant vos propres valeurs. Sauvegardez d'abord.

## Assistance

Ces snippets sont partagés tels quels, sans garantie. Questions et signalements de bugs : [ouvrez une issue](https://github.com/morfredus/morf-wordpress-snippets/issues). Une réponse n'est pas garantie, et un snippet qui demande beaucoup d'adaptations à votre site vaut parfois mieux qu'on y renonce.

## Avant d'utiliser un snippet

- Lisez le code d'abord. Ces snippets ont été écrits pour un seul site, testés uniquement là, et peuvent demander des adaptations.
- Les valeurs à changer (adresses, identifiants) sont signalées par un commentaire en haut de chaque fichier.
- Sauvegardez votre site avant d'y ajouter du code.

## Licence

[GPL-3.0-only](LICENSE) © 2026 Frédéric Biron (morfredus)
