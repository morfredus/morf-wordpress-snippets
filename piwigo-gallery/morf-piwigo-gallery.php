<?php
/**
 * Plugin Name: morf Piwigo Gallery
 * Plugin URI:  https://github.com/morfredus/morf-wordpress-snippets/tree/main/piwigo-gallery
 * Description: Shortcode [morf_piwigo] : affiche un album Piwigo (public) dans WordPress, avec visionneuse et EXIF, sans copier les images.
 * Version:     1.0.0
 * Author:      morfredus
 * Author URI:  https://morfredus.fr
 * License:     GPL-3.0-only
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: morf-piwigo-gallery
 *
 * Utilisable comme extension (un seul fichier dans wp-content/plugins/) ou comme snippet
 * (Code Snippets : coller le code sans la première ligne <?php).
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
 * album  : nom exact de l'album Piwigo
 * limit  : nombre de photos (0 = jusqu'à 500)
 * texte  : titre ou description
 */

if (!defined('ABSPATH')) {
    exit;
}

// À adapter : adresse de l'API de VOTRE Piwigo (ws.php à la racine de l'installation).
define('MORF_PIWIGO_API', 'https://votre-piwigo.exemple/ws.php');

/**
 * Interrogation de l'API Piwigo avec cache.
 */
function morf_piwigo_api($params, $cache_time = 600) {

    $params['format'] = 'json';

    $cache_key = 'morf_piwigo_v6_' . md5(wp_json_encode($params));

    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $url = add_query_arg($params, MORF_PIWIGO_API);

    $response = wp_remote_get($url, array(
        'timeout'     => 20,
        'redirection' => 3,
        'sslverify'   => true,
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
function morf_piwigo_get_albums() {

    $response = morf_piwigo_api(array(
        'method'    => 'pwg.categories.getList',
        'recursive' => 'true',
    ));

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
 * Récupération des photos d'un album.
 */
function morf_piwigo_get_images($album_id, $limit) {

    $response = morf_piwigo_api(array(
        'method'    => 'pwg.categories.getImages',
        'id'        => $album_id,
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
 */
function morf_piwigo_get_strict_exif($image_url, $file_name = '') {
    if (empty($image_url)) {
        return '';
    }

    $cache_key = 'morf_strict_exif_' . md5($image_url);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $camera   = '';
    $focal    = '';
    $aperture = '';
    $shutter  = '';
    $iso      = '';

    // Téléchargement sécurisé des 64 premiers Ko de l'image (suffisant pour le header EXIF)
    $response = wp_remote_get($image_url, array(
        'timeout' => 5,
        'headers' => array('Range' => 'bytes=0-65535'),
    ));

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) < 400) {
        $body = wp_remote_retrieve_body($response);
        if (!empty($body)) {
            // Lecture du flux mémoire via data-wrapper PHP
            $stream = 'data://image/jpeg;base64,' . base64_encode($body);
            $exif = @exif_read_data($stream);

            if ($exif && is_array($exif)) {
                // 1. Modèle
                $make = trim($exif['Make'] ?? '');
                $model = trim($exif['Model'] ?? '');
                if ($make && $model && stripos($model, $make) !== false) {
                    $camera = $model;
                } else {
                    $camera = trim($make . ' ' . $model);
                }

                // 2. Focale
                if (!empty($exif['FocalLength'])) {
                    $parts = explode('/', (string)$exif['FocalLength']);
                    $val = (count($parts) === 2 && (float)$parts[1] > 0)
                        ? round((float)$parts[0] / (float)$parts[1], 1)
                        : (float)$parts[0];
                    if ($val > 0) {
                        $focal = $val . 'mm';
                    }
                }

                // 3. Ouverture (FNumber)
                if (!empty($exif['FNumber'])) {
                    $parts = explode('/', (string)$exif['FNumber']);
                    $val = (count($parts) === 2 && (float)$parts[1] > 0)
                        ? round((float)$parts[0] / (float)$parts[1], 1)
                        : (float)$parts[0];
                    if ($val > 0) {
                        $aperture = 'f/' . $val;
                    }
                }

                // 4. Durée d'exposition
                if (!empty($exif['ExposureTime'])) {
                    $shutter = (string)$exif['ExposureTime'] . 's';
                }

                // 5. ISO
                if (!empty($exif['ISOSpeedRatings'])) {
                    $iso_val = is_array($exif['ISOSpeedRatings']) ? reset($exif['ISOSpeedRatings']) : $exif['ISOSpeedRatings'];
                    if (!empty($iso_val)) {
                        $iso = 'ISO ' . $iso_val;
                    }
                }
            }
        }
    }

    // Repli pour le modèle si la lecture d'en-tête échoue
    if (empty($camera) && !empty($file_name)) {
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
 * Shortcode principal.
 */
function morf_piwigo_shortcode($atts) {

    $atts = shortcode_atts(array(
        'album' => '',
        'limit' => 0,
        'texte' => 'titre',
    ), $atts, 'morf_piwigo');

    $album_name = sanitize_text_field($atts['album']);
    $limit      = max(0, intval($atts['limit']));
    $texte      = strtolower(sanitize_key($atts['texte']));

    if ($album_name === '') {
        return '<p>Erreur : aucun album Piwigo indiqué.</p>';
    }

    if (!in_array($texte, array('titre', 'description'), true)) {
        $texte = 'titre';
    }

    $albums = morf_piwigo_get_albums();

    if (is_wp_error($albums)) {
        return '<p>Erreur Piwigo : ' . esc_html($albums->get_error_message()) . '</p>';
    }

    $album_id = 0;

    foreach ($albums as $album) {
        if (
            isset($album['name'], $album['id']) &&
            mb_strtolower(trim($album['name'])) === mb_strtolower(trim($album_name))
        ) {
            $album_id = intval($album['id']);
            break;
        }
    }

    if (!$album_id) {
        return '<p>Album Piwigo introuvable : ' . esc_html($album_name) . '</p>';
    }

    $images = morf_piwigo_get_images($album_id, $limit);

    if (is_wp_error($images)) {
        return '<p>Erreur Piwigo : ' . esc_html($images->get_error_message()) . '</p>';
    }

    if (empty($images)) {
        return '<p>Aucune photographie dans cet album.</p>';
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

                // Extraction stricte des 5 paramètres EXIF
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
    #<?php echo esc_attr($gallery_id); ?> {
        --morf-gap: 16px;
        --morf-radius: 12px;
        width: 100%;
        margin: 0 auto;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-gallery-grid {
        columns: 3;
        column-gap: var(--morf-gap);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo {
        position: relative;
        display: block;
        width: 100%;
        margin: 0 0 var(--morf-gap);
        break-inside: avoid;
        overflow: hidden;
        cursor: zoom-in;
        background: #eee;
        border-radius: var(--morf-radius);
        -webkit-mask-image: -webkit-radial-gradient(white, black);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo img {
        display: block;
        width: 100%;
        height: auto;
        border-radius: inherit;
        transition: transform .35s ease;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo:hover img {
        transform: scale(1.035);
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 19px 14px 10px;
        color: #fff;
        background: rgba(0, 0, 0, .45);
        font-size: 14px;
        line-height: 1.3;
        font-weight: 400;
        clip-path: polygon(0 0, 100% 18%, 100% 100%, 0 100%);
        border-bottom-left-radius: var(--morf-radius);
        border-bottom-right-radius: var(--morf-radius);
        pointer-events: none;
        opacity: 0;
        transition: opacity .3s ease;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-photo:hover .morf-photo-title {
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
        z-index: 999999;
        background: rgba(12, 12, 12, .96);
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
        color: #eee;
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
        font-family: monospace, sans-serif;
        font-size: 12px;
        color: #ccc;
        background: rgba(255, 255, 255, 0.08);
        padding: 3px 8px;
        border-radius: 4px;
        width: fit-content;
        letter-spacing: 0.3px;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-counter {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
        color: #888;
    }

    #<?php echo esc_attr($gallery_id); ?> .morf-lightbox button {
        position: absolute;
        border: 0;
        background: transparent;
        color: white;
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
            columns: 2;
        }
    }

    @media (max-width: 550px) {
        #<?php echo esc_attr($gallery_id); ?> .morf-gallery-grid {
            columns: 1;
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

        const root = document.getElementById(
            <?php echo wp_json_encode($gallery_id); ?>
        );

        if (!root || root.dataset.initialized) return;
        root.dataset.initialized = '1';

        const lightbox = root.querySelector('.morf-lightbox');
        const largeImage = lightbox.querySelector('figure img');
        const caption = root.querySelector('.morf-caption');
        const exifBadge = root.querySelector('.morf-exif');
        const counter = root.querySelector('.morf-counter');

        const links = Array.from(
            root.querySelectorAll('.morf-photo')
        );

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

            current = (
                current + direction + links.length
            ) % links.length;

            update();
        }

        links.forEach((link, index) => {

            link.addEventListener('click', event => {

                event.preventDefault();
                open(index);

            });

        });

        root.querySelector('.morf-close')
            .addEventListener('click', close);

        root.querySelector('.morf-prev')
            .addEventListener('click', () => move(-1));

        root.querySelector('.morf-next')
            .addEventListener('click', () => move(1));

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
