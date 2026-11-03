<?php
/**
 * Plugin Name: morf WordPress Cleanup
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/wordpress-cleanup
 * Description: Retire les métadonnées inutiles exposées par WordPress (version, liens RSD/shortlink/oEmbed/REST, emoji, pingbacks XML-RPC) et masque la liste des utilisateurs aux visiteurs.
 * Version:     1.0.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Utilisable comme extension (un seul fichier dans wp-content/plugins/) ou comme snippet
 * (Code Snippets : coller le code sans la première ligne <?php).
 *
 * Chaque bloc est indépendant : supprimez ceux dont vous n'avez pas besoin.
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Liens et en-têtes inutiles
 * ------------------------------------------------------------------------- */

/* Lien RSD (Really Simple Discovery) */
remove_action( 'wp_head', 'rsd_link' );

/* Liens REST dans <head> et en-tête HTTP (l'API REST elle-même reste active) */
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

/* Découverte oEmbed : d'autres sites ne pourront plus intégrer vos articles automatiquement */
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
if ( function_exists( 'wp_oembed_add_host_js' ) ) {
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}

/* Shortlinks */
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

/* Manifeste Windows Live Writer */
remove_action( 'wp_head', 'wlwmanifest_link' );

/* Version de WordPress */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* ---------------------------------------------------------------------------
 * Emoji (scripts et styles)
 * ------------------------------------------------------------------------- */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_mail', 'wp_staticize_emoji_for_email' );
remove_action( 'the_content_feed', 'wp_staticize_emoji' );
remove_action( 'comment_text_rss', 'wp_staticize_emoji' );
add_filter( 'emoji_svg_url', '__return_false' );

/* ---------------------------------------------------------------------------
 * Pingbacks XML-RPC (XML-RPC lui-même n'est pas désactivé)
 * ------------------------------------------------------------------------- */
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'] );
	unset( $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

/* ---------------------------------------------------------------------------
 * Liste des utilisateurs via l'API REST : masquée aux visiteurs non connectés.
 * Les utilisateurs connectés (éditeur de blocs, extensions) la conservent.
 * ------------------------------------------------------------------------- */
add_filter( 'rest_endpoints', function ( $endpoints ) {

	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
} );
