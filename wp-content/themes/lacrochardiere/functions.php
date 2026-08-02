<?php
/**
 * La Crochardière — thème enfant de GeneratePress.
 *
 * @package lacrochardiere
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Charger la feuille de style de l'enfant après celle de GeneratePress.
 * GeneratePress charge son propre style ; on ajoute le nôtre par-dessus,
 * avec cache-busting basé sur la date de modification du fichier.
 */
add_action( 'wp_enqueue_scripts', function () {
    $style = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'lacrochardiere',
        get_stylesheet_uri(),
        array( 'generatepress-style' ),
        file_exists( $style ) ? filemtime( $style ) : '1.0.0'
    );
}, 100 );

/**
 * Masquer le titre sur TOUTES les pages (pas les articles ni les archives).
 * Le titre est retiré de la sortie (pas seulement caché en CSS).
 */
add_filter( 'generate_show_title', function ( $show ) {
    if ( is_page() ) {
        return false;
    }
    return $show;
} );

/**
 * ---------------------------------------------------------------------------
 * Vos hooks / filtres personnalisés ci-dessous.
 * GeneratePress expose de nombreux hooks : https://docs.generatepress.com/article/hooks/
 * ---------------------------------------------------------------------------
 */
