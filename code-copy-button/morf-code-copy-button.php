<?php
/**
 * Plugin Name: morf Code Copy Button
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/code-copy-button
 * Description: Style des blocs de code (<pre>) et bouton « Copier » au survol, avec adaptation au mode sombre.
 * Version:     1.0.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Utilisable comme extension (dans son propre dossier : wp-content/plugins/<nom>/<nom>.php) ou comme snippet
 * (Code Snippets : coller le code sans la première ligne <?php).
 *
 * Personnalisation : la couleur du bouton se règle avec la variable CSS --morf-copy-bg
 * (orange par défaut). Le mode sombre est détecté par la classe .mf-dark-mode sur <html>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<style id="morf-code-blocks-css">
		pre {
			background-color: #f4f4f4 !important;
			border: 1px solid #ddd !important;
			border-radius: 6px !important;
			padding: 15px 20px !important;
			display: block !important;
			overflow-x: auto !important;
			color: #333 !important;
			font-family: monospace !important;
			position: relative !important;
		}

		pre code {
			background-color: transparent !important;
			border: none !important;
			padding: 0 !important;
			color: inherit !important;
		}

		.mf-dark-mode pre {
			background-color: #2a2a2a !important;
			border-color: #444 !important;
			color: #e0e0e0 !important;
		}

		.mf-copy-btn {
			position: absolute;
			top: 5px;
			right: 5px;
			padding: 4px 8px;
			background: var(--morf-copy-bg, #ff4600);
			color: #fff;
			border-radius: 4px;
			font-size: 0.75rem;
			cursor: pointer;
			opacity: 0;
			transition: opacity 0.2s;
			border: none;
		}
		pre:hover .mf-copy-btn,
		.mf-copy-btn:focus { opacity: 1; }
		.mf-dark-mode .mf-copy-btn { background: #444; color: #fff; }
	</style>

	<script id="morf-code-blocks-js">
		document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll('pre').forEach(function (pre) {
				// Le texte est lu AVANT d'ajouter le bouton : il ne contient donc jamais le mot « Copier ».
				var code = pre.textContent;

				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'mf-copy-btn';
				btn.textContent = 'Copier';
				btn.setAttribute('aria-label', 'Copier le code');
				pre.appendChild(btn);

				btn.addEventListener('click', function () {
					if (!navigator.clipboard) return;
					navigator.clipboard.writeText(code).then(function () {
						btn.textContent = 'Copié !';
						setTimeout(function () { btn.textContent = 'Copier'; }, 2000);
					});
				});
			});
		});
	</script>
	<?php
} );
