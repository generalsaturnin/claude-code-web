<?php
/**
 * Enable theme features
 *
 * @author     @retlehs
 * @link 	   http://roots.io
 * @editor     Themovation <themovation@gmail.com>
 * @version    1.0
 */
 
add_theme_support('bootstrap-top-navbar');  // Enable Bootstrap's top navbar
add_theme_support('automatic-feed-links'); // Enable post and comment RSS feed links to head.

function thmv_remove_widget_block_editor() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'thmv_remove_widget_block_editor' );

// Custom logo.
$logo_width  = 120;
$logo_height = 100;

// If the retina setting is active, double the recommended width and height.
if ( get_theme_mod( 'themo_retinajs_logo', false ) ) {
    $logo_width  = floor( $logo_width * 2 );
    $logo_height = floor( $logo_height * 2 );
}

add_theme_support(
    'custom-logo',
    array(
        'height'      => $logo_height,
        'width'       => $logo_width,
        'flex-height' => true,
        'flex-width'  => true,
    )
);

/* Declare WooCommerce Support */

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
    //add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}


/**
 * Configuration values
 */
define('POST_EXCERPT_LENGTH', 40); // Length in words for excerpt_length filter (http://codex.wordpress.org/Plugin_API/Filter_Reference/excerpt_length)
define('PORTFOLIO_EXCERPT_LENGTH', 3); // Length in words for excerpt_length filter (http://codex.wordpress.org/Plugin_API/Filter_Reference/excerpt_length)

/**
 * $content_width is a global variable used by WordPress for max image upload sizes
 * and media embeds (in pixels).
 *
 * Example: If the content area is 640px wide, set $content_width = 620; so images and videos will not overflow.
 * Default: 940px is the default Bootstrap container width.
 */
if (!isset($content_width)) { $content_width = 1140; }

/**
 * Define helper constants
 */
$get_theme_name = explode('/themes/', get_template_directory());

define('RELATIVE_PLUGIN_PATH',  str_replace(home_url('/') , '', plugins_url()));
define('RELATIVE_CONTENT_PATH', str_replace(home_url('/'), '', content_url()));
define('THEME_NAME',            next($get_theme_name));
define('THEME_NAME_WHITELABEL','Bellevue');
define('THEME_PATH',            RELATIVE_CONTENT_PATH . '/themes/' . THEME_NAME);
define('ENVATO_THEME_REGISTER_NAME_EX', 'bellevuex');
define('ENVATO_THEME_REGISTER_NAME', 'bellevuex');
// Set minimum PHP version requirements
define( 'TH_REQUIRED_PHP_VERSION', '5.4' );
define( 'TH_PREVENT_BELLEVUE_UPGRADE', true );

// Minimum Header Footer Builder Theme Version
define( 'THMV_MIN_HFE_THEME_VERSION', 3.3 );

define( 'MENU_STRATUS_HOME_ACTIVE', true );
define( 'MENU_STRATUS_HOME', 'aloha_dashboard' );

define( 'MENU_STRATUS_GETTING_STARTED_ACTIVE', false );
define( 'MENU_STRATUS_GETTING_STARTED', 'stratus_getting_started' );
define( 'MENU_STRATUS_GETTING_STARTED_TITLE', 'Getting started' );

define( 'MENU_STRATUS_PLUGINS_ACTIVE', true );
define( 'MENU_STRATUS_PLUGINS', 'stratus_plugins' );
define( 'MENU_STRATUS_PLUGINS_TITLE', 'Plugins' );

define( 'MENU_STRATUS_DOCS', 'stratus_docs' );

define( 'MENU_STRATUS_TEMPLATES_ACTIVE', true );
define( 'MENU_STRATUS_TEMPLATES', 'stratus_templates' );
define( 'MENU_STRATUS_TEMPLATES_TITLE', 'Templates' );

define( 'MENU_STRATUS_UPDATES_ACTIVE', false );
define( 'MENU_STRATUS_UPDATES', 'stratus_updates' );
define( 'MENU_STRATUS_UPDATES_TITLE', 'Updates' );

define( 'ENVATO_STRATUS_ID', 12482898 );
define( 'ENVATO_TOKEN_LINK', 'admin.php?page=envato-market#settings' );
define( 'REST_API_STRATUS', 'https://activate.themovation.com/wp-json/stratus/' );
define( 'REST_API_ACTIVATE', REST_API_STRATUS.'activate');
define( 'REST_API_DEACTIVATE', REST_API_STRATUS.'deactivate');
define( 'REST_API_CHECK_CODE', REST_API_STRATUS.'check_code');
define( 'REST_API_GET_CODE_DETAILS', REST_API_STRATUS.'code_details');
define( 'ENVATO_API_GET_PURCHASE_LIST','https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-themes');

define( 'STATUS_NOT_ACTIVATED', 0 );
define( 'STATUS_ACTIVATED', 1 );
define( 'STATUS_ACTIVATING_SUCCESS', 2 );
define( 'STATUS_ACTIVATING_FAILURE', 3 );
define( 'STATUS_ACTIVATING_ERRORS_CODE_EMPTY', 4 );
define( 'STATUS_ACTIVATING_ERRORS_CODE', 5 );
define( 'STATUS_ACTIVATING_FAILURE_ACTIVATED_EARLY', 6 );
define( 'STATUS_ACTIVATING_FAILURE_CODE_USED', 7 );

define( 'BELLEVUE_CODE_REGISTRY', 'bellevue_code' );
define('STATUS_ALREADY_USED', 'The provided purchase code has already been used');

/**admin constants **/
define( 'BELLEVUE_MAILCHIMP_LIST_ID', 'f0efd501a5' );
define( 'BELLEVUE_OPTION_MAILCHIMP_SUBSCRIPTION', 'bellevue_mailchimp_subscription' );

define( 'BELLEVUE_UPDATE_DOMAIN', 'https://update.bellevuetheme.com/' );
define( 'BELLEVUE_THEME_DETAILS_URL', BELLEVUE_UPDATE_DOMAIN.'bellevue.json' );

define( 'BELLEVUE_SYSTEM_RECOMMENDED_PHP_VERSION', '5.7' );
define( 'BELLEVUE_SYSTEM_RECOMMENDED_MAX_EXECUTION_TIME', '180' );
define( 'BELLEVUE_SYSTEM_RECOMMENDED_MAX_INPUT_TIME', '180' );
define( 'BELLEVUE_SYSTEM_RECOMMENDED_MEMORY_LIMIT', '128M' );
define( 'BELLEVUE_SYSTEM_RECOMMENDED_POST_MAX_SIZE', '32M' );
define( 'BELLEVUE_SYSTEM_RECOMMENDED_UPLOAD_MAX_FILESIZE', '32M' );
if ( function_exists('set_time_limit') ) {
@set_time_limit(BELLEVUE_SYSTEM_RECOMMENDED_MAX_EXECUTION_TIME);
}


define('BELLEVUE_UPDATE_TEMPLATE_LINK', 'https://help.bellevuetheme.com/article/150-how-to-update-the-theme');
define('BELLEVUE_ASSETS_PATH', get_template_directory() . '/assets');
define('BELLEVUE_JS_PATH',BELLEVUE_ASSETS_PATH.'/js');
define('BELLEVUE_IMAGES_PATH',BELLEVUE_ASSETS_PATH.'/images');
define('BELLEVUE_CSS_PATH',BELLEVUE_ASSETS_PATH.'/css');
define('BELLEVUE_ASSETS_URI', get_template_directory_uri() . '/assets');
define('BELLEVUE_JS_URI',BELLEVUE_ASSETS_URI.'/js');
define('BELLEVUE_IMAGES_URI',BELLEVUE_ASSETS_URI.'/images');
define('BELLEVUE_CSS_URI',BELLEVUE_ASSETS_URI.'/css');