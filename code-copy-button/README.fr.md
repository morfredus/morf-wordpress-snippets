# morf Code Copy Button

> Fait partie de [morf-wordpress-snippets](../README.fr.md). English : [README.md](README.md)

Habille les blocs de code et ajoute un bouton « Copier » au survol.

## Ce que fait le snippet

- Donne à chaque bloc `<pre>` un fond clair, une bordure et des coins arrondis.
- Ajoute un bouton **Copier** (en haut à droite, visible au survol ou au focus clavier) qui copie le contenu du bloc dans le presse-papiers.
- S'adapte au mode sombre : il cherche la classe `.mf-dark-mode` sur `<html>`. Adaptez le sélecteur à votre propre mode sombre.

## Personnalisation

La couleur du bouton se règle avec une variable CSS. Dans le CSS additionnel de votre thème :

```css
:root { --morf-copy-bg: #0073aa; }
```

## Remarques

- Les styles utilisent `!important` et s'appliquent à **tous** les éléments `<pre>` du site. Vérifiez le rendu avec d'autres extensions de code.
- La copie utilise `navigator.clipboard`, qui exige HTTPS.
- Le bouton est en français (« Copier » / « Copié ! »). Modifiez les textes dans le JavaScript pour une autre langue.

## Installation

Utilisez-le **au choix** :

- **En extension** : placez `morf-code-copy-button.php` dans son propre dossier, `wp-content/plugins/morf-code-copy-button/morf-code-copy-button.php`, et activez-la.
- **En snippet** (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) : collez le code. Code Snippets retire lui-même la balise d'ouverture `<?php` à l'enregistrement (il peut afficher un avertissement, sans conséquence).

## Prérequis

Un site servi en HTTPS (l'API du presse-papiers l'exige).

## Désinstallation

Désactivez ou supprimez le snippet ou l'extension. Rien n'est stocké en base.

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
