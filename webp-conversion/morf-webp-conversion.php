<?php
/**
 * Plugin Name: morf WebP Conversion
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/webp-conversion
 * Description: Convertit les JPEG/PNG en WebP au téléversement, avec contrôles de sécurité. L'original est conservé par défaut.
 * Version:     1.1.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Utilisable comme extension (dans son propre dossier : wp-content/plugins/<nom>/<nom>.php) ou comme snippet
 * (Code Snippets : coller le code sans la première ligne <?php).
 *
 * Ce que fait le code : après le téléversement, l'image d'origine est convertie en WebP
 * et la médiathèque pointe vers ce fichier WebP.
 *
 * Garde-fous :
 * - rien n'est fait si le serveur ne sait pas écrire du WebP ;
 * - le fichier WebP doit exister et ne pas être vide ;
 * - l'original n'est supprimé que si vous le demandez (voir plus bas).
 *
 * Limite : les miniatures sont créées par WordPress AVANT ce filtre. Elles restent donc
 * au format d'origine (JPEG/PNG). Seule l'image en taille réelle devient WebP.
 *
 * Pour supprimer aussi l'original (gain d'espace disque, sans retour possible) :
 *   add_filter( 'morf_webp_delete_original', '__return_true' );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wp_generate_attachment_metadata', function ( $metadata, $attachment_id, $context ) {

	// Seulement à la création d'un média, pas lors d'une régénération.
	if ( 'create' !== $context ) {
		return $metadata;
	}

	// 1. Fichier d'origine et type : JPEG et PNG uniquement.
	$file = get_attached_file( $attachment_id );
	$type = get_post_mime_type( $attachment_id );

	if ( ! $file || ! file_exists( $file ) || ! in_array( $type, array( 'image/jpeg', 'image/png' ), true ) ) {
		return $metadata;
	}

	// 2. Le serveur sait-il écrire du WebP ?
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp', 'methods' => array( 'save' ) ) ) ) {
		return $metadata;
	}

	// 3. Chemin du fichier WebP.
	$pathinfo  = pathinfo( $file );
	$webp_file = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.webp';

	// Ne jamais écraser un fichier existant.
	if ( file_exists( $webp_file ) ) {
		return $metadata;
	}

	// 4. Conversion avec l'éditeur d'image natif de WordPress.
	$image = wp_get_image_editor( $file );
	if ( is_wp_error( $image ) ) {
		return $metadata;
	}

	$image->set_quality( 82 );
	$saved = $image->save( $webp_file, 'image/webp' );

	// 5. Le WebP doit exister et ne pas être vide, sinon on ne touche à rien.
	if ( is_wp_error( $saved ) || ! file_exists( $webp_file ) || filesize( $webp_file ) === 0 ) {
		if ( file_exists( $webp_file ) ) {
			@unlink( $webp_file );
		}
		return $metadata;
	}

	// 6. La médiathèque pointe désormais vers le WebP.
	update_attached_file( $attachment_id, $webp_file );
	wp_update_post( array(
		'ID'             => $attachment_id,
		'post_mime_type' => 'image/webp',
	) );

	if ( isset( $metadata['file'] ) ) {
		$metadata['file'] = preg_replace( '/\.[^.\/]+$/', '.webp', $metadata['file'] );
	}

	// 7. Suppression de l'original : uniquement si demandée explicitement.
	if ( apply_filters( 'morf_webp_delete_original', false, $attachment_id ) ) {
		@unlink( $file );
	}

	return $metadata;
}, 10, 3 );
