<?php
/**
 * Plugin Name: morf Piwigo Gallery
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/piwigo-gallery
 * Description: Shortcode [morf_piwigo] : affiche un album Piwigo (public) dans WordPress, avec visionneuse et EXIF, sans copier les images.
 * Version:     1.4.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: morf-piwigo-gallery
 *
 * Utilisable comme extension (dans son propre dossier : wp-content/plugins/<nom>/<nom>.php) ou comme snippet
 * (Code Snippets : coller le code ; il retire lui-même la balise d'ouverture <?php à l'enregistrement).
 *
 * Ne lit que les albums publics de Piwigo. Les EXIF sont lus dans les fichiers servis
 * par Piwigo : si vos originaux contiennent des coordonnées GPS, elles restent
 * téléchargeables par un visiteur (le shortcode n'affiche que modèle, focale,
 * ouverture, durée d'exposition et ISO).
 *
 * Shortcode :
 * [morf_piwigo album="Rouille" limit="13" texte="titre"]
 *
 * Paramètres :
 * album  : nom de l'album Piwigo (majuscules, accents des tirets et espaces multiples tolérés)
 * limit  : nombre de photos (0 = jusqu'à 500)
 * texte  : titre ou description
 */

if (!defined('ABSPATH')) {
    exit;
}

/* =============================================================================
 * RÉGLAGES : c'est la seule partie à modifier.
 * ========================================================================== */

// Adresse de l'API de VOTRE Piwigo (ws.php à la racine de l'installation).
define('MORF_PIWIGO_API', 'https://votre-piwigo.exemple/ws.php');

// Modèle d'appareil lu dans le NOM DU FICHIER quand l'EXIF n'en contient pas.
// Motif « texte_MODELE_numéro.ext » : 20240512_OM20_001.jpg affiche « OM20 ».
// Attention : tout ce qui est entre le premier « _ » et le dernier « _numéro » est pris
// (photo_2024_001.jpg affiche « 2024 »). true = activé (par défaut), false = désactivé.
define('MORF_PIWIGO_CAMERA_FROM_FILENAME', true);

/*
 * Apparence de la galerie. Une ligne = un réglage. La valeur peut être :
 *   - un code couleur            : '#D8D2C8', 'rgb(216, 210, 200)', 'rgba(0, 0, 0, .45)'
 *   - une variable de votre thème : 'var(--theme-palette-color-5)'   (voir le README pour votre thème)
 *   - une couleur du thème rendue semi-transparente :
 *                                   'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)'
 *   - une taille ou un nombre    : '12px', '1.035', '3'
 * Une valeur vide ('') ou une ligne supprimée = valeur par défaut intégrée (les valeurs ci-dessous).
 * Un réglage laissé vide peut alors être défini dans le CSS additionnel du site (:root { --morf-radius: 4px; }).
 *
 * Exemple pour un site Blocksy (décommentez et adaptez) :
 *   'placeholder-bg' => 'var(--theme-palette-color-5)',
 *   'focus'          => 'var(--theme-palette-color-1)',
 *   'caption-bg'     => 'color-mix(in srgb, var(--theme-palette-color-3) 55%, transparent)',
 *   'caption-color'  => 'var(--theme-palette-color-7)',
 */
define('MORF_PIWIGO_STYLE', array(

    // --- Disposition ---------------------------------------------------------
    'columns'        => '3',                       // colonnes sur grand écran
    'columns-tablet' => '2',                       // colonnes jusqu'à 900 px de large
    'columns-mobile' => '1',                       // colonnes jusqu'à 550 px de large
    'gap'            => '16px',                    // espace entre les photos
    'radius'         => '12px',                    // arrondi des vignettes
    'zoom'           => '1.035',                   // zoom au survol (1 = aucun)
    'font'           => 'inherit',                 // police ('inherit' = celle du thème)

    // --- Vignettes -----------------------------------------------------------
    'placeholder-bg' => '#eee',                    // fond derrière la vignette pendant le chargement
    'caption-bg'     => 'rgba(0, 0, 0, .45)',      // fond du titre affiché sur la vignette
    'caption-color'  => '#fff',                    // couleur du texte de ce titre
    'caption-size'   => '14px',                    // taille du texte de ce titre
    'focus'          => 'currentColor',            // contour au focus clavier

    // --- Visionneuse plein écran ----------------------------------------------
    'lightbox-bg'    => 'rgba(12, 12, 12, .96)',   // fond (gardez-le sombre, voir le README)
    'lightbox-color' => '#eee',                    // texte (titre)
    'lightbox-muted' => '#888',                    // compteur « 3 / 13 »
    'controls-color' => '#fff',                    // boutons fermer, précédent, suivant
    'exif-color'     => '#ccc',                    // texte du badge EXIF
    'exif-bg'        => 'rgba(255, 255, 255, .08)', // fond du badge EXIF
    'exif-font'      => 'monospace, sans-serif',   // police du badge EXIF
    'lightbox-z'     => '999999',                  // ordre d'empilement (baissez-le si elle masque votre menu)
));

/* ============================================================================
 * FIN DES RÉGLAGES
 * ========================================================================== */

/**
 * Transforme MORF_PIWIGO_STYLE en variables CSS --morf-* pour une galerie.
 * :where() donne une spécificité nulle : un CSS de page (.page-id-123 .morf-piwigo-gallery { ... })
 * garde toujours la priorité. Les valeurs vides ne produisent rien.
 */
function morf_piwigo_style_vars($gallery_id) {

    $declarations = '';

    foreach ((array) MORF_PIWIGO_STYLE as $name => $value) {

        $name  = preg_replace('/[^a-z0-9-]/', '', strtolower((string) $name));
        // Une valeur ne peut pas sortir de sa déclaration : ni accolade, ni point-virgule, ni balise.
        $value = trim(preg_replace('/[<>{};]/', '', (string) $value));

        if ($name === '' || $value === '') {
            continue;
        }

        $declarations .= '--morf-' . $name . ': ' . $value . '; ';
    }

    if ($declarations === '') {
        return '';
    }

    return ':where(#' . esc_attr($gallery_id) . ') { ' . $declarations . '}';
}

/**
 * Interrogation de l'API Piwigo avec cache.
 */
function morf_piwigo_api($params, $cache_time = 600, $force = false) {

    $params['format'] = 'json';

    // Clé de cache basée sur l'intégralité des paramètres de requête
    $cache_key = 'morf_piwigo_v7_' . md5(wp_json_encode($params));

    $cached = get_transient($cache_key);

    if ($cached !== false && !$force) {
        return $cached;
    }

    $url = add_query_arg($params, MORF_PIWIGO_API);

    $response = wp_remote_get($url, array(
        'timeout'     => 20,
        'redirection' => 3,
        'headers'     => array(
            'Accept' => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $status = wp_remote_retrieve_response_code($response);

    if ($status !== 200) {
        return new WP_Error(
            'piwigo_http_error',
            'Piwigo a répondu avec le code HTTP ' . $status
        );
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (!is_array($data) || ($data['stat'] ?? '') !== 'ok') {
        return new WP_Error(
            'piwigo_api_error',
            'Réponse invalide de l’API Piwigo.'
        );
    }

    set_transient($cache_key, $data, $cache_time);

    return $data;
}

/**
 * Récupération des albums.
 */
function morf_piwigo_get_albums($force = false) {

    $response = morf_piwigo_api(array(
        'method'    => 'pwg.categories.getList',
        'recursive' => 'true',
    ), 600, $force);

    if (is_wp_error($response)) {
        return $response;
    }

    $result = $response['result'] ?? array();

    if (isset($result['categories'])) {
        return $result['categories'];
    }

    return is_array($result) ? $result : array();
}

/**
 * Récupération stricte des photos d'un album donné (sans récursion).
 */
function morf_piwigo_get_images($album_id, $limit) {

    $response = morf_piwigo_api(array(
        'method'    => 'pwg.categories.getImages',
        'cat_id'    => intval($album_id),   // seul 'cat_id' est lu par Piwigo : un simple 'id' renverrait les photos de tous les albums
        'recursive' => 'false',
        'per_page'  => ($limit > 0) ? $limit : 500,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $result = $response['result'] ?? array();

    if (isset($result['images'])) {
        return $result['images'];
    }

    return is_array($result) ? $result : array();
}

/**
 * Extraction stricte et fiable des 5 paramètres EXIF :
 * Modèle • Focale • Ouverture • Durée d'exposition • ISO
 *
 * Repli pour le modèle (réglage MORF_PIWIGO_CAMERA_FROM_FILENAME) : si l'EXIF ne contient ni marque ni modèle,
 * il est lu dans le NOM DU FICHIER quand celui-ci suit le motif « texte_MODELE_numéro.ext » (ex. 20240512_OM20_001.jpg donne « OM20 »).
 * Tout ce qui se trouve entre le premier « _ » et le dernier « _numéro.ext » est pris, même si ce n'est
 * pas un appareil (photo_2024_001.jpg donne « 2024 »). Voir le README, section « How it works ».
 */
function morf_piwigo_get_strict_exif($image_url, $file_name = '') {
    if (empty($image_url)) {
        return '';
    }

    // Réglage absent (ligne supprimée) = activé, comme par défaut.
    $camera_from_file = !defined('MORF_PIWIGO_CAMERA_FROM_FILENAME') || (bool) MORF_PIWIGO_CAMERA_FROM_FILENAME;

    // La clé ne change que si le repli est désactivé : le cache existant reste valable en mode par défaut,
    // et basculer le réglage ne laisse pas d'anciens résultats en cache pendant 30 jours.
    $cache_key = 'morf_strict_exif_' . md5($image_url) . ($camera_from_file ? '' : '_nofile');
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $camera   = '';
    $focal    = '';
    $aperture = '';
    $shutter  = '';
    $iso      = '';

    $response = wp_remote_get($image_url, array(
        'timeout' => 5,
        'headers' => array('Range' => 'bytes=0-65535'),
    ));

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) < 400) {
        $body = wp_remote_retrieve_body($response);
        if (!empty($body)) {
            $stream = 'data://image/jpeg;base64,' . base64_encode($body);
            $exif = @exif_read_data($stream);

            if ($exif && is_array($exif)) {
                $make = trim($exif['Make'] ?? '');
                $model = trim($exif['Model'] ?? '');
                if ($make && $model && stripos($model, $make) !== false) {
                    $camera = $model;
                } else {
                    $camera = trim($make . ' ' . $model);
                }

                if (!empty($exif['FocalLength'])) {
                    $parts = explode('/', (string)$exif['FocalLength']);
                    $val = (count($parts) === 2 && (float)$parts[1] > 0)
                        ? round((float)$parts[0] / (float)$parts[1], 1)
                        : (float)$parts[0];
                    if ($val > 0) {
                        $focal = $val . 'mm';
                    }
                }

                if (!empty($exif['FNumber'])) {
                    $parts = explode('/', (string)$exif['FNumber']);
                    $val = (count($parts) === 2 && (float)$parts[1] > 0)
                        ? round((float)$parts[0] / (float)$parts[1], 1)
                        : (float)$parts[0];
                    if ($val > 0) {
                        $aperture = 'f/' . $val;
                    }
                }

                if (!empty($exif['ExposureTime'])) {
                    $shutter = (string)$exif['ExposureTime'] . 's';
                }

                if (!empty($exif['ISOSpeedRatings'])) {
                    $iso_val = is_array($exif['ISOSpeedRatings']) ? reset($exif['ISOSpeedRatings']) : $exif['ISOSpeedRatings'];
                    if (!empty($iso_val)) {
                        $iso = 'ISO ' . $iso_val;
                    }
                }
            }
        }
    }

    if ($camera_from_file && empty($camera) && !empty($file_name)) {
        if (preg_match('/_(.+?)_\d+\.[a-zA-Z0-9]+$/', $file_name, $matches)) {
            $camera = trim($matches[1]);
        }
    }

    $exif_items = array_filter(array($camera, $focal, $aperture, $shutter, $iso));
    $result = implode(' • ', $exif_items);

    set_transient($cache_key, $result, 30 * DAY_IN_SECONDS);

    return $result;
}

/**
 * Fonction de nettoyage et normalisation de chaînes pour comparaison d'albums
 */
function morf_clean_string($str) {
    $str = html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Remplacement des tirets spéciaux (demi-cadratin, cadratin) par un tiret simple
    $str = str_replace(array('–', '—', '−'), '-', $str);
    // Suppression des espaces multiples et espaces insécables
    $str = preg_replace('/\s+/u', ' ', $str);
    return mb_strtolower(trim($str), 'UTF-8');
}

/**
 * Shortcode principal.
 */
function morf_piwigo_shortcode($atts) {

    $atts = shortcode_atts(array(
        'album' => '',
        'limit' => 0,
        'texte' => 'titre',
    ), $atts, 'morf_piwigo');

    $raw_album_name = (string)$atts['album'];
    $limit          = max(0, intval($atts['limit']));
    $texte          = strtolower(sanitize_key($atts['texte']));

    if (trim($raw_album_name) === '') {
        return '<p>Erreur : aucun album Piwigo indiqué.</p>';
    }

    if (!in_array($texte, array('titre', 'description'), true)) {
        $texte = 'titre';
    }

    $target_clean = morf_clean_string($raw_album_name);
    $album_id     = 0;

    // Album introuvable dans la liste en cache (album créé ou renommé récemment) :
    // on recharge la liste une fois depuis Piwigo avant de conclure.
    foreach (array(false, true) as $force) {
        $albums = morf_piwigo_get_albums($force);

        if (is_wp_error($albums)) {
            return '<p>Erreur Piwigo : ' . esc_html($albums->get_error_message()) . '</p>';
        }

        foreach ($albums as $album) {
            if (isset($album['id'], $album['name']) && morf_clean_string($album['name']) === $target_clean) {
                $album_id = intval($album['id']);
                break 2;
            }
        }
    }

    if (!$album_id) {
        return '<p>Album Piwigo introuvable : ' . esc_html($raw_album_name) . '</p>';
    }

    $images = morf_piwigo_get_images($album_id, $limit);

    if (is_wp_error($images)) {
        return '<p>Erreur Piwigo : ' . esc_html($images->get_error_message()) . '</p>';
    }

    if (empty($images)) {
        return '<p>Aucune photographie dans cet album (' . esc_html($raw_album_name) . ').</p>';
    }

    if ($limit > 0) {
        $images = array_slice($images, 0, $limit);
    }

    $gallery_id = wp_unique_id('morf-piwigo-');

    ob_start();
    ?>

    <div id="<?php echo esc_attr($gallery_id); ?>" class="morf-piwigo-gallery">

        <div class="morf-gallery-grid">

            <?php foreach ($images as $image) :

                $image_id = intval($image['id'] ?? 0);

                if (!$image_id) {
                    continue;
                }

                $title = trim($image['name'] ?? '');
                $description = trim($image['comment'] ?? '');
                $label = ($texte === 'description' && $description !== '') ? $description : $title;

                $derivatives = $image['derivatives'] ?? array();

                $thumbnail =
                    $derivatives['medium']['url'] ??
                    $derivatives['small']['url'] ??
                    $derivatives['thumb']['url'] ??
                    $image['element_url'] ??
                    '';

                $full = $image['element_url'] ?? '';

                if (!$thumbnail || !$full) {
                    continue;
                }

                $exif_text = morf_piwigo_get_strict_exif($full, $image['file'] ?? '');

                ?>

                <a class="morf-photo"
                   href="<?php echo esc_url($full); ?>"
                   data-full="<?php echo esc_url($full); ?>"
                   data-title="<?php echo esc_attr($label); ?>"
                   data-exif="<?php echo esc_attr($exif_text); ?>">

                    <img
                        src="<?php echo esc_url($thumbnail); ?>"
                        alt="<?php echo esc_attr($title); ?>"
                        loading="lazy"
                        decoding="async"
                    >

                    <span class="morf-photo-title">
                        <?php echo esc_html($label); ?>
                    </span>

                </a>

            <?php endforeach; ?>

        </div>

        <div class="morf-lightbox" aria-hidden="true">

            <button class="morf-close" aria-label="Fermer">&times;</button>

            <button class="morf-prev" aria-label="Photo précédente">&#10094;</button>

            <figure>
                <img src="" alt="">

                <figcaption>
                    <div class="morf-meta">
                        <span class="morf-caption"></span>
                        <span class="morf-exif"></span>
                    </div>
                    <span class="morf-counter"></span>
                </figcaption>
            </figure>

            <button class="morf-next" aria-label="Photo suivante">&#10095;</button>

        </div>

    </div>

    <style>
    /*
     * Variables --morf-* : valeurs issues de MORF_PIWIGO_STYLE (en haut du fichier).
     * Le reste de la feuille de style les lit avec une valeur de repli intégrée.
     */
    <?php echo morf_piwigo_style_vars($gallery_id); ?>

    #<?php echo esc_attr($gallery_id); ?> {
        width: 100%;
        margin: 0 auto;
        font-family: var(--morf-font, inherit);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-gallery-grid {
        columns: var(--morf-columns, 3);
        column-gap: var(--morf-gap, 16px);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo {
        position: relative;
        display: block;
        width: 100%;
        margin: 0 0 var(--morf-gap, 16px);
        break-inside: avoid;
        overflow: hidden;
        cursor: zoom-in;
        background: var(--morf-placeholder-bg, #eee);
        border-radius: var(--morf-radius, 12px);
        -webkit-mask-image: -webkit-radial-gradient(white, black);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo:focus-visible {
        outline: 2px solid var(--morf-focus, currentColor);
        outline-offset: 3px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo img {
        display: block;
        width: 100%;
        height: auto;
        border-radius: inherit;
        transition: transform .35s ease;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo:hover img {
        transform: scale(var(--morf-zoom, 1.035));
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 19px 14px 10px;
        color: var(--morf-caption-color, #fff);
        background: var(--morf-caption-bg, rgba(0, 0, 0, .45));
        font-size: var(--morf-caption-size, 14px);
        line-height: 1.3;
        font-weight: 400;
        clip-path: polygon(0 0, 100% 18%, 100% 100%, 0 100%);
        border-bottom-left-radius: var(--morf-radius, 12px);
        border-bottom-right-radius: var(--morf-radius, 12px);
        pointer-events: none;
        opacity: 0;
        transition: opacity .3s ease;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo:hover .morf-photo-title,
    #<?php echo esc_attr($gallery_id); ?> .morf-photo:focus-visible .morf-photo-title {
        opacity: 1;
    }

    @media (hover: none) {
        #<?php echo esc_attr($gallery_id); ?> .morf-photo-title {
            opacity: 1;
        }
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: var(--morf-lightbox-z, 999999);
        background: var(--morf-lightbox-bg, rgba(12, 12, 12, .96));
        align-items: center;
        justify-content: center;
        padding: 30px 60px 45px;
        box-sizing: border-box;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox.is-open {
        display: flex;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox figure {
        margin: 0;
        max-width: 100%;
        max-height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox figure img {
        display: block;
        max-width: 100%;
        max-height: calc(100vh - 180px);
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 6px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox figcaption {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        width: 100%;
        max-width: 90vw;
        color: var(--morf-lightbox-color, #eee);
        font-size: 14px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
        text-align: left;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-caption {
        font-weight: 500;
        font-size: 15px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-exif {
        display: inline-block;
        font-family: var(--morf-exif-font, monospace, sans-serif);
        font-size: 12px;
        color: var(--morf-exif-color, #ccc);
        background: var(--morf-exif-bg, rgba(255, 255, 255, 0.08));
        padding: 3px 8px;
        border-radius: 4px;
        width: fit-content;
        letter-spacing: 0.3px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-counter {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
        color: var(--morf-lightbox-muted, #888);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox button {
        position: absolute;
        border: 0;
        background: transparent;
        color: var(--morf-controls-color, #fff);
        cursor: pointer;
        padding: 10px;
        line-height: 1;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-close {
        top: 15px;
        right: 25px;
        font-size: 38px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-prev,
    #<?php echo esc_attr($gallery_id); ?> .morf-next {
        top: 50%;
        transform: translateY(-50%);
        font-size: 32px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-prev {
        left: 15px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-next {
        right: 15px;
    }

    @media (max-width: 900px) {
        #<?php echo esc_attr($gallery_id); ?> .morf-gallery-grid {
            columns: var(--morf-columns-tablet, 2);
        }
    }

    @media (max-width: 550px) {
        #<?php echo esc_attr($gallery_id); ?> .morf-gallery-grid {
            columns: var(--morf-columns-mobile, 1);
        }

        #<?php echo esc_attr($gallery_id); ?> .morf-lightbox {
            padding: 55px 25px 35px;
        }

        #<?php echo esc_attr($gallery_id); ?> .morf-prev {
            left: 0;
        }

        #<?php echo esc_attr($gallery_id); ?> .morf-next {
            right: 0;
        }

        #<?php echo esc_attr($gallery_id); ?> .morf-lightbox figcaption {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }
    </style>

    <script>
    (function () {
        const root = document.getElementById(<?php echo wp_json_encode($gallery_id); ?>);
        if (!root || root.dataset.initialized) return;
        root.dataset.initialized = '1';

        const lightbox = root.querySelector('.morf-lightbox');
        const largeImage = lightbox.querySelector('figure img');
        const caption = root.querySelector('.morf-caption');
        const exifBadge = root.querySelector('.morf-exif');
        const counter = root.querySelector('.morf-counter');
        const links = Array.from(root.querySelectorAll('.morf-photo'));

        let current = 0;

        function update() {
            const link = links[current];
            largeImage.src = link.dataset.full;
            largeImage.alt = link.querySelector('img').alt;
            caption.textContent = link.dataset.title || '';

            const exifData = link.dataset.exif || '';
            exifBadge.textContent = exifData;
            exifBadge.style.display = exifData ? 'inline-block' : 'none';

            counter.textContent = (current + 1) + ' / ' + links.length;
        }

        function open(index) {
            current = index;
            update();
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function move(direction) {
            current = (current + direction + links.length) % links.length;
            update();
        }

        links.forEach((link, index) => {
            link.addEventListener('click', event => {
                event.preventDefault();
                open(index);
            });
        });

        root.querySelector('.morf-close').addEventListener('click', close);
        root.querySelector('.morf-prev').addEventListener('click', () => move(-1));
        root.querySelector('.morf-next').addEventListener('click', () => move(1));

        lightbox.addEventListener('click', event => {
            if (event.target === lightbox) {
                close();
            }
        });

        document.addEventListener('keydown', event => {
            if (!lightbox.classList.contains('is-open')) return;
            if (event.key === 'Escape') close();
            if (event.key === 'ArrowLeft') move(-1);
            if (event.key === 'ArrowRight') move(1);
        });
    })();
    </script>

    <?php
    return ob_get_clean();
}

add_shortcode('morf_piwigo', 'morf_piwigo_shortcode');
