<?php
/**
 * Bellevue Child — functions.php
 *
 * Point d'entrée pour TOUTES vos personnalisations PHP.
 * Ajoutez ici vos hooks, filtres et fonctions au lieu de modifier le thème
 * parent « bellevuex » (qui est écrasé à chaque mise à jour).
 *
 * NB : le style.css du parent ET celui de l'enfant sont déjà mis en file
 * d'attente par le thème parent (bellevuex/lib/scripts.php, via
 * is_child_theme()). Inutile donc de ré-enqueue le style parent ici.
 *
 * @package Bellevue Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Pas d'accès direct.
}

/**
 * Exemple — décommentez pour charger un JS personnalisé, avec cache-busting
 * automatique basé sur la date de modification du fichier.
 */
// add_action( 'wp_enqueue_scripts', function () {
//     $path = get_stylesheet_directory() . '/assets/js/custom.js';
//     if ( file_exists( $path ) ) {
//         wp_enqueue_script(
//             'bellevue-child-custom',
//             get_stylesheet_directory_uri() . '/assets/js/custom.js',
//             array( 'jquery' ),
//             filemtime( $path ),
//             true
//         );
//     }
// }, 110 );

/**
 * ---------------------------------------------------------------------------
 * Vos personnalisations ci-dessous.
 * ---------------------------------------------------------------------------
 */
