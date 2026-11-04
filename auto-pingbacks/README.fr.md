# morf No Self Pingbacks

> Fait partie de [morf-wordpress-snippets](../README.fr.md). English : [README.md](README.md)

Empêche WordPress de s'envoyer des pingbacks à lui-même quand un article contient un lien vers une autre page du site.

## Ce que fait le snippet

Retire les liens internes (ceux qui commencent par l'adresse de votre site) de la liste des liens que WordPress s'apprête à notifier. Les pingbacks vers des sites **externes** continuent de fonctionner.

Il n'agit que sur le crochet `pre_ping` : rien n'est supprimé de la base de données et les pingbacks existants ne sont pas touchés.

## Remarques

- Si vous avez changé l'adresse de votre site récemment, les liens internes avec l'ancienne adresse sont considérés comme externes.
- Pour désactiver complètement les pingbacks, utilisez les réglages de discussion de WordPress.

## Installation

Utilisez-le **au choix** :

- **En extension** : placez `morf-no-self-pingbacks.php` dans son propre dossier, `wp-content/plugins/morf-no-self-pingbacks/morf-no-self-pingbacks.php`, et activez-la.
- **En snippet** (avec [Code Snippets](https://wordpress.org/plugins/code-snippets/) par exemple) : collez le code. Code Snippets retire lui-même la balise d'ouverture `<?php` à l'enregistrement (il peut afficher un avertissement, sans conséquence).

## Prérequis

Aucun prérequis.

## Désinstallation

Désactivez ou supprimez le snippet ou l'extension. Rien n'est stocké en base : WordPress retrouve aussitôt son comportement d'avant.

## Licence

[GPL-3.0-only](../LICENSE) © 2026 Frédéric Biron (morfredus)
