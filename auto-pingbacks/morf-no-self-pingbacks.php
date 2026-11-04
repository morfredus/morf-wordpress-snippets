<?php
/**
 * Plugin Name: morf No Self Pingbacks
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/auto-pingbacks
 * Description: Empêche WordPress de se notifier lui-même quand un article contient des liens vers d'autres pages du site. Les pingbacks externes restent actifs.
 * Version:     1.0.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Utilisable comme extension (dans son propre dossier : wp-content/plugins/<nom>/<nom>.php) ou comme snippet
 * (Code Snippets : coller le code sans la première ligne <?php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'pre_ping', function ( &$links ) {

	// Adresse du site (par exemple https://exemple.fr).
	$home = home_url();

	// $links contient la liste des liens que WordPress s'apprête à notifier.
	// Le & signifie que l'on travaille directement sur cette liste.
	foreach ( $links as $key => $link ) {

		// Si le lien commence par l'adresse du site, il est interne : on le retire.
		if ( strpos( $link, $home ) === 0 ) {
			unset( $links[ $key ] );
		}
	}
} );
