<?php
/**
 * Plugin Name: morf Instant Navigation
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/instant-navigation
 * Description: Navigation quasi instantanée (Speculation Rules + repli au survol) et priorité de chargement des images LCP sur les articles.
 * Version:     1.0.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Utilisable comme extension (un seul fichier dans wp-content/plugins/) ou comme snippet
 * (Code Snippets : coller le code sans la première ligne <?php).
 *
 * 1. Les images de l'article affichées en grande taille sont chargées en priorité (LCP).
 * 2. Les pages internes sont préchargées quand le visiteur s'apprête à cliquer
 *    (Speculation Rules sur Chrome/Edge/Opera, repli au survol ou au toucher ailleurs).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Priorité de chargement des images LCP sur les articles.
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
	if ( is_single() ) {
		$lcp_sizes = array( 'large', 'full', 'post-thumbnail', 'medium_large' );

		if ( in_array( $size, $lcp_sizes, true ) ) {
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
		}
	}
	return $attr;
}, 10, 3 );

// 2. Préchargement des liens internes.
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<script id="morf-instant-navigation-js">
		// A. Speculation Rules API (Chrome, Edge, Opera)
		const speculationScript = document.createElement('script');
		speculationScript.type = 'speculationrules';
		speculationScript.textContent = JSON.stringify({
			"prefetch": [{
				"source": "document",
				"where": {
					"and": [
						{ "href_matches": "\\/*" },
						{ "not": { "href_matches": [ "\\/wp-admin\\/*", "\\/wp-login.php*", "\\/wp-json\\/*" ] } }
					]
				},
				"eagerness": "moderate"
			}]
		});
		document.head.appendChild(speculationScript);

		// B. Repli au survol / au toucher (Safari et anciens navigateurs)
		document.addEventListener('DOMContentLoaded', () => {
			const prefetchLink = (url) => {
				if (document.querySelector(`link[href="${url}"]`)) return;
				const link = document.createElement('link');
				link.rel = 'prefetch';
				link.href = url;
				document.head.appendChild(link);
			};

			const isInternal = (anchor) =>
				anchor && anchor.hostname === window.location.hostname &&
				!anchor.href.includes('wp-admin') && !anchor.href.includes('wp-login');

			// Ordinateur : survol de la souris
			document.body.addEventListener('mouseover', (e) => {
				const anchor = e.target.closest('a');
				if (isInternal(anchor)) prefetchLink(anchor.href);
			});

			// Mobile : début du toucher
			document.body.addEventListener('touchstart', (e) => {
				const anchor = e.target.closest('a');
				if (isInternal(anchor)) prefetchLink(anchor.href);
			}, { passive: true });
		});
	</script>
	<?php
} );
