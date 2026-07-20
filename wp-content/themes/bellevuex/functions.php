<?php
/**
 * Roots includes
 */
DEFINE('THMV_ALOHA_PLUGIN_FILE','aloha-powerpack/aloha-powerpack.php');//aloha-powerpack/aloha-powerpack.php
DEFINE('THMV_ALOHA_PLUGIN_NAME','Aloha Powerpack');
DEFINE('THMV_ALOHA_PLUGIN_SLUG','aloha-powerpack');
DEFINE('THMV_MIN_ALOHA_VERSION_REQUIREMENT','1.2.22');
DEFINE('THMV_ALOHA_VERSION_ERROR', 'Bellevue requires Aloha PowerPack version %1$s%2$s%3$s or newer.');
DEFINE('ALOHA_MORE_INFO_TEXT','More Info');
DEFINE('ALOHA_MORE_INFO_LINK','https://link.bellevuetheme.com/aloha-upgrade');
DEFINE('BELLEUVUE_PLUGINS_REMOTE_URL', 'https://link.bellevuetheme.com');
DEFINE('THMV_OPTION_TREE_FILE','option-tree/ot-loader.php');
DEFINE('THMV_WIDGET_PACK_PLUGIN_FILE','th-widget-pack/th-widget-pack.php');
DEFINE('THMV_WIDGET_PACK_ACTIVATION_ERROR_NOTICE', 'The Widget Pack plugin is no longer supported. Aloha Powerpack has replaced it.');
if(!defined('ALOHA_CURL_ERROR_HELP_URL')){
    DEFINE('ALOHA_CURL_ERROR_HELP_URL','https://help.bellevuetheme.com/article/290-how-to-fix-curl-error-28-connection-timed-out');
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

include( get_template_directory() . '/lib/init.php');            // Initial theme setup and constants
include( get_template_directory() . '/lib/wrapper.php');         // Theme wrapper class
include( get_template_directory() . '/lib/config.php');          // Configuration
include( get_template_directory() . '/lib/titles.php');          // Page titles
include( get_template_directory() . '/lib/cleanup.php');         // Cleanup
include( get_template_directory() . '/lib/nav.php');             // Custom nav modifications
include( get_template_directory() . '/lib/comments.php');        // Custom comments modifications
include( get_template_directory() . '/lib/widgets.php');         // Sidebars and widgets
include( get_template_directory() . '/lib/scripts.php');         // Scripts and stylesheets
include( get_template_directory() . '/lib/custom.php');          // Custom functions

if (is_admin()) {
    include_once( get_template_directory() . '/lib/class-tgm-plugin-activation.php');    // Bundled Plugins
    require_once( get_template_directory() . '/lib/session.php'); //load session manager
    require_once( get_template_directory() . '/lib/thmv_registration_setup.php'); //load session manager
    if (th_aloha_active()) {
        require_once( get_template_directory() . '/lib/aloha_overrides.php');          // Mailchimp functions  
        require_once( get_template_directory() . '/lib/registration_update.php');          // Registration functions
    } else {
        if(get_option('envato_setup_complete', false)){
           th_aloha_installation_setup(); 
        }
    }
     //if installation page, load plugins
    th_template_plugins_installation_setup();
}

if(th_aloha_active()){
    include( get_template_directory() . '/lib/gutenberg-compat.php');  
}

if (th_show_kirki()) {
    add_action('customize_controls_enqueue_scripts', 'th_custom_notification_enqueue_scripts');

    function th_custom_notification_enqueue_scripts() {
        $handle = 'th-options-moved-custom-notification';

        wp_register_script($handle, get_template_directory_uri() . '/assets/js/th-options-moved-custom-notification.js', array('customize-controls'));
        $translated_msg = array(
            'msg' => esc_html('Some theme options have been moved.', 'bellevue') . '<br>'
            . '<a href="https://help.bellevuetheme.com/category/14-theme-settings" target="_blank">'
            . esc_html('Learn More.', 'bellevue') . '</a>',
        );

        wp_localize_script($handle, 'th_customizer_notification', $translated_msg);
        wp_enqueue_script($handle);
    }

}

/**
 * if any old options are there, show kirki
 * @return type
 */
function th_show_kirki(){
    return is_plugin_active('kirki/kirki.php');
}

add_action('after_setup_theme', 'th_after_setup_theme');
/**
 * import old theme mods
 */
function th_after_setup_theme() {
    //check if older theme, if so,
    $old_mods = get_option("theme_mods_bellevue", false);
    //check if older theme mods exist
    if (is_array($old_mods) && count($old_mods)) {
        $theme_slug = get_option('stylesheet');
        if ($theme_slug && 'bellevue' !== $theme_slug) {
            update_option("theme_mods_$theme_slug", $old_mods);
            delete_option("theme_mods_bellevue");
        }
    }
}

// Return theme version
function thmv_get_theme_version() {
    $theme = wp_get_theme();
    $themeToCheck = $theme->parent() ? $theme->parent() : $theme;
    return $themeToCheck->get('Version');
}

function thmv_get_theme_version_history() {
    $thmv_version_history = get_option('thmv_version_history', []);

    
    //BUG FIX - if no thmv_first_activation_log and no thmv_first_activation_version then it means it's an old buggy installation as since 4.0 we set the option correctly
    $legacy_version_to_add = false;
    $child_path = ABSPATH.'wp-content/themes/bellevuex-child/style.css';
        if(file_exists($child_path)){
            $headers = get_file_data( $child_path, ['Version'=> 'Version'], 'theme' );
            if(isset($headers['Version']) && $headers['Version']<4){
                $legacy_version_to_add = $headers['Version'];
        }
    }
    //check for older theme mods
    $old_mods = get_option("theme_mods_bellevue", false);
    
    //if no thmv_first_activation_version OR a legacy version found and is not in the theme history
    if (get_option('thmv_first_activation_version') === false 
            || ($legacy_version_to_add && !in_array($legacy_version_to_add, $thmv_version_history))
            || (is_array($old_mods) && count($old_mods))
            ) {
        if(!$legacy_version_to_add){
             $legacy_version_to_add = '3.5.11';
        }
      
        update_option('thmv_first_activation_log', true);
        update_option('thmv_first_activation_version', $legacy_version_to_add);
        $thmv_version_history = array_merge([$legacy_version_to_add], $thmv_version_history);
    }
    //BUG FIX end
    
    

    $legacy_version = get_option('thmv_first_activation_version');
    $current_version = thmv_get_theme_version();
    $last_history_version = false;

    if (empty($current_version)) {
        return [];
    }
    if (!count($thmv_version_history)) {
        //if an old option is found, keep it
        if ($legacy_version) {
            $thmv_version_history[] = $legacy_version;
        }
        //we must keep the current version in the history so if someone updates via FTP, we still have it in the history
        $thmv_version_history[] = $current_version;
    } else if (count($thmv_version_history)) {
        //the history exists, check if the current version is the latest (at the top)
        $lastIndex = count($thmv_version_history) - 1;
        $last_history_version = $thmv_version_history[$lastIndex];
        if ($last_history_version !== $current_version) {
            $thmv_version_history[] = $current_version;
        }
    }

    update_option('thmv_version_history', $thmv_version_history);

    return $thmv_version_history;
}

/** since 4.0
 * get the version when the template was installed for the first time
 */
function thmv_get_template_first_install_version() {

    $thmv_version_history = thmv_get_theme_version_history();
    if (count($thmv_version_history)) {
        return $thmv_version_history[0];
    }

    return false;
}

/**
 *
 * get the theme version before this current one
 */
function thmv_get_template_previous_install_version(){
    
    $thmv_version_history = thmv_get_theme_version_history();
    //@todo, should the history be ordered?? I don't think so as this could be used for providing support
    //the history is set, now we get the last version (latest - 2) if more than 1 versions exist
    $history_length = count($thmv_version_history);
    if(!$history_length){
        return false;
    }
    if($history_length>1){
        return $thmv_version_history[$history_length-2];
    }
    //otherwise just return the last one (current)
    return $thmv_version_history[$history_length-1];
}

function th_template_plugins_installation_setup(){
    $plugins = [];
    $allPlugins = the_plugin_list($plugins, true);
    $TGMPA_ID = 'bellevue_install';
    $config = array(
        'id' => $TGMPA_ID, // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '', // Default absolute path to bundled plugins.
        'menu' => 'tgmpa-install-plugins', // Menu slug.
        'has_notices' => false, // Show admin notices or not.
        'dismissable' => true, // If false, a user cannot dismiss the nag message.
        'dismiss_msg' => '', // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false, // Automatically activate plugins after installation or not.
        'message' => '', // Message to output right before the plugins table.
    );
    tgmpa($allPlugins, $config);
}
add_action('wp_ajax_is_aloha_installed', 'ajax_is_aloha_installed');
add_action('wp_ajax_is_aloha_active', 'ajax_is_aloha_active');
function ajax_is_aloha_installed(){
    $json = array('result'=>th_aloha_installed());
     wp_send_json($json);
}
function ajax_is_aloha_active(){
    $json = array('result'=>th_aloha_active());
    wp_send_json($json);
}
function th_aloha_installation_setup() {
    include_once( get_template_directory() . '/lib/class-tgm-plugin-activation.php');    // Bundled Plugins
    //register tgmpa for installing aloha powerpack
    $TGMPA_ID = 'aloha';
    $aloha = array(
        'name' => THMV_ALOHA_PLUGIN_NAME,
        'slug' => THMV_ALOHA_PLUGIN_SLUG,
        'required' => true,
        'source' => 'https://link.bellevuetheme.com/'.THMV_ALOHA_PLUGIN_SLUG,
    );
    $config = array(
        'id' => $TGMPA_ID, // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '', // Default absolute path to bundled plugins.
        'menu' => 'tgmpa-install-plugins', // Menu slug.
        'has_notices' => false, // Show admin notices or not.
        'dismissable' => true, // If false, a user cannot dismiss the nag message.
        'dismiss_msg' => '', // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false, // Automatically activate plugins after installation or not.
        'message' => '', // Message to output right before the plugins table.
    );

    tgmpa(array($aloha), $config);
}
function th_aloha_installed() {
   return file_exists(WP_PLUGIN_DIR . '/'.THMV_ALOHA_PLUGIN_FILE);
}
function th_aloha_active() {
   return is_plugin_active(THMV_ALOHA_PLUGIN_FILE); 
}

function th_aloha_version() {
    if (!th_aloha_installed()) {
        return false;
    }

    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . THMV_ALOHA_PLUGIN_FILE, false, false);
    if (empty($plugin_data['Version'])) {
        return false;
    }

    return $plugin_data['Version'];
}

function th_aloha_compatible() {
    $version = th_aloha_version();
    if (!$version) {
        return false;
    }

    return version_compare($version, THMV_MIN_ALOHA_VERSION_REQUIREMENT, '>=');
}

add_action('admin_bar_init', 'th_disallow_widget_pack_activation');
function th_disallow_widget_pack_activation(){
    if(isset($_GET['action']) && $_GET['action']==='activate' && isset($_GET['plugin']) && $_GET['plugin']===THMV_WIDGET_PACK_PLUGIN_FILE){
       add_option('th_widget_pack_activation_error_to_show', true); 
       wp_redirect(admin_url( 'plugins.php' )); 
       exit;
    }
    if(get_option('th_widget_pack_activation_error_to_show',false)){
        delete_option('th_widget_pack_activation_error_to_show'); 
        add_action('admin_notices', 'th_widget_pack_activation_error');
    }
}

function th_widget_pack_activation_error(){
    $class = 'notice-warning notice';
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), __(THMV_WIDGET_PACK_ACTIVATION_ERROR_NOTICE, 'bellevue')); 
}
add_action('admin_init', 'th_aloha_check');

function th_aloha_check() {
    //deactivate the widget pack plugin
    deactivate_plugins(THMV_WIDGET_PACK_PLUGIN_FILE);
    
    if (!th_aloha_active()) {
        add_action('admin_notices', 'th_enable_aloha_message');
    } elseif (!th_aloha_compatible()) {
        add_action('admin_notices', 'th_enable_aloha_update_message');
    }
    if (!th_aloha_installed() && !get_option('aloha_popup_shown', false)) {
        add_option('aloha_popup_shown', true);
        add_action('admin_notices', 'th_aloha_popup');
    }
}
function th_aloha_popup(){
    $class = 'aloha_popup';
    $button_label = __('Install Aloha Powerpack', 'bellevue');
    $message = sprintf(__('This new version of Bellevue requires the free %1$sAloha PowerPack%2$s plugin to be installed and activated.<br>Without the plugin, you can\'t activate the template and access all the features.', 'bellevue'), '<strong>', '</strong>');
    $button = '<p><a id="aloha-installaton-button-popup"  href="#" class="button-primary">' . $button_label . '</a></p><p></p>';

    printf('<div style="display: none;" class="%1$s"><p>%2$s</p><div>%3$s</div><p><a href="%4$s" target="_blank">%5$s</a></p></div>', esc_attr($class), wp_kses_post($message), wp_kses_post($button), ALOHA_MORE_INFO_LINK, ALOHA_MORE_INFO_TEXT );
}

add_action('admin_enqueue_scripts', 'th_aloha_install_script');
function th_aloha_install_script(){
     if(!th_aloha_installed()){         
        wp_enqueue_script('jquery');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'); 
        
        
       
        $timeModified = filemtime(get_template_directory() . '/assets/js/aloha_install.js');
        wp_enqueue_script('th-aloha-install', get_template_directory_uri() . '/assets/js/aloha_install.js', array(
        'jquery','jquery-ui-dialog'), $timeModified, '1'); 
        wp_localize_script('th-aloha-install', 'aloha_params', array(
            'installation_message' => esc_html__('Installing', 'bellevue').'...',
            'activation_message' => esc_html__('Installed. Activating...', 'bellevue'),
            'error_message' => esc_html__('Some error ocurred. Try again.', 'bellevue').'...',
            'active_check' => 'is_aloha_active',
            'install_check' => 'is_aloha_installed',
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'aloha_title' => THMV_ALOHA_PLUGIN_NAME,
            'activation_url' => wp_nonce_url('plugins.php?action=activate&amp;plugin=' . THMV_ALOHA_PLUGIN_FILE . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . THMV_ALOHA_PLUGIN_FILE),
        ));
     }
}

function th_enable_aloha_message() {
    if (!th_aloha_installed()) {
        //missing
        $action_url = wp_nonce_url(
                add_query_arg(
                        array(
                            'plugin' => urlencode(THMV_ALOHA_PLUGIN_SLUG),
                            'tgmpa-' . 'install' => 'install' . '-plugin',
                        ),
                        'themes.php?page=tgmpa-install-plugins'
                ),
                'tgmpa-' . 'install',
                'tgmpa-nonce'
        );
        $button_label = __('Install Aloha Powerpack', 'bellevue');
        $button = '<p><a id="aloha-installaton-button"  href="#" data-href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

    } else {
        //activation message
        $button_label = __('Activate Aloha Powerpack', 'bellevue');
        $action_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . THMV_ALOHA_PLUGIN_FILE . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . THMV_ALOHA_PLUGIN_FILE);
        $button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';
        
    }

    $class = 'notice notice-error';
    $message = sprintf(__('This new version of Bellevue requires the free %1$sAloha PowerPack%2$s plugin to be installed and activated.<br>Without the plugin, you can\'t activate the template and access all the features.', 'bellevue'), '<strong>', '</strong>');

    printf('<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr($class), wp_kses_post($message), wp_kses_post($button));
}

function th_enable_aloha_update_message() {
    $class = 'notice notice-error';
    $message = sprintf(__(THMV_ALOHA_VERSION_ERROR, 'bellevue'), '<strong>', THMV_MIN_ALOHA_VERSION_REQUIREMENT, '</strong>');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
}

/**
 * Define Elementor Partner ID
 */
if ( ! defined( 'ELEMENTOR_PARTNER_ID' ) ) {
    
}

if(th_show_kirki()){
/**
 * Recommend the Kirki plugin
 */
include( get_template_directory() . '/lib/include-kirki.php');          // Customizer options
/**
 * Load the Kirki Fallback class
 */
include( get_template_directory() . '/lib/bellevue-kirki.php');
/**
 * Customizer additions.
 */
include( get_template_directory(). '/lib/customizer.php');

}

// Envato WP Theme Setup Wizard
// Set Envato Username - DISABLED FOR NOW
add_filter('bellevue_theme_setup_wizard_username', 'bellevue_set_theme_setup_wizard_username', 10);
add_filter('bellevuechildtheme_theme_setup_wizard_username', 'bellevue_set_theme_setup_wizard_username', 10);
if( ! function_exists('bellevue_set_theme_setup_wizard_username') ){
    function bellevue_set_theme_setup_wizard_username($username){
        return 'themovation';
    }
}

// Envato WP Theme Setup Wizard
// Set Envato Script URL - DISABLED FOR NOW
add_filter('bellevue_theme_setup_wizard_oauth_script', 'bellevue_set_theme_setup_wizard_oauth_script', 10);
add_filter('bellevuechildtheme_theme_setup_wizard_oauth_script', 'bellevue_set_theme_setup_wizard_oauth_script', 10);
if( ! function_exists('bellevue_set_theme_setup_wizard_oauth_script') ){
    function bellevue_set_theme_setup_wizard_oauth_script($oauth_url){
        return 'https://app.themovation.com/envato/api/server-script.php';
    }
}

// Envato WP Theme Setup Wizard
// Set Custom Default Content Titles and Descriptions
add_filter('bellevue_theme_setup_wizard_default_content', 'bellevue_theme_setup_wizard_default_content_script', 10);
add_filter('bellevuechildtheme_theme_setup_wizard_default_content', 'bellevue_theme_setup_wizard_default_content_script', 10);
if( ! function_exists('bellevue_theme_setup_wizard_default_content_script') ){
    function bellevue_theme_setup_wizard_default_content_script($default){

        // Check all by default
        $default['checked'] = 1;

        // Add user friendly titles and descriptions
        if (isset($default['title'])){
            switch($default['title']) {
                case 'Media':
                    $default['title'] = 'Media Files';
                    $default['description'] = 'Media from the demo, mostly photos and graphics.';
                    break;
                case 'Rooms':
                    $default['title'] = 'Room Pages';
                    $default['description'] = 'Room pages as seen on the demo.';
                    break;
                case 'Posts':
                    $default['title'] = 'Blog Posts';
                    $default['description'] = 'Blog Posts as seen on the demo.';
                    break;
                case 'Pages':
                    $default['description'] = 'Pages as seen on the demo.';
                    break;
                case 'My Library':
                    $default['title'] = 'Templates';
                    $default['description'] = 'Page Builder Templates for rapid page creation.';
                    break;
                case 'Widgets':
                    $default['description'] = 'Widgets as seen on the demo.';
                    break;
                case 'Forms':
                    $default['description'] = 'Formidable Forms as seen on the demo.';
                    break;
            }

        }

        return $default;
    }
}

function th_get_page_by_title($title) {
    $query = new WP_Query(
            array(
        'post_type' => 'page',
        'title' => $title,
        'post_status' => 'all',
        'posts_per_page' => 1,
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'orderby' => 'post_date ID',
        'order' => 'ASC',
            )
    );

    if (!empty($query->post)) {
        $page_got_by_title = $query->post;
    } else {
        $page_got_by_title = null;
    }

    return $page_got_by_title;
}

// Envato WP Theme Setup Wizard
// Update Term IDs for Our Custom Post Stype saved inside _elementor_data Post Meta
/*
 * Takes page elementor widget name, page title and term slugs as an array
 * updates elementor json string to update term(s) during an import.
 */
if( ! function_exists('update_elm_widget_select_term_id') ) {
    function update_elm_widget_select_term_id($elmwidgetname, $pagetitle, $termslug = array())
    {
        // premature exit?
        if (!isset($termslug) || !isset($pagetitle) || !isset($elmwidgetname)) {
            return;
        } else {
            $pageobj = th_get_page_by_title($pagetitle); // get page object
            $pageid = false;
            if(isset($pageobj->ID)){
                $pageid = $pageobj->ID; // get page ID
            }

            // loop through all slugs requested and get terms ids
            foreach ($termslug as $slug) {
                $termid = term_exists($slug); // get term ID
                $termids[] = $termid; // add to array, we'll use this later.
            }

            // premature exit?
            if (!isset($termids) || !isset($pageid)) {
                return;
            } else {

                $data = get_post_meta($pageid, '_elementor_data', TRUE); // get elm json string

                /*if (!is_array($data)){
                    $data = json_decode($data, true); // decode that mofo
                }*/

                // We are looking for something very specific so let's grab it and go.
                // Does key exist? Does it match to the elm widget name passed in?

                if (isset($data[0]['elements'][0]['elements'][0]['widgetType']) && $data[0]['elements'][0]['elements'][0]['widgetType'] = $elmwidgetname) {
                    // make sure there is a term group setting.
                    if (!isset($data[0]['elements'][0]['elements'][0]['settings']['group'])) {
                        return;
                    } else {
                        $data[0]['elements'][0]['elements'][0]['settings']['group'] = $termids; //set updated term ids
                        //$newJsonString = json_encode($data); // encode the json data
                        update_post_meta($pageid, '_elementor_data',$data); // update post meta with new json string.
                    }
                }

            }

        }

    }
}

// Envato WP Theme Setup Wizard
// Hook to find / replace room terms. Fires only during theme import profess.
if( ! function_exists('th_post_content_import_hook') ) {
    function th_post_content_import_hook()
    {
        update_elm_widget_select_term_id('themo-room-grid', 'Home 1', array('packages'));
        update_elm_widget_select_term_id('themo-room-grid', 'Room Index', array('guided','packages','rafting','specials','whitewater'));
    }
}
add_action( 'th_post_content_import', 'th_post_content_import_hook', 10, 2 );

// Envato WP Theme Setup Wizard
//add_filter( 'bellevue_enable_setup_wizard', '__return_true' );
//add_filter( 'bellevuechildtheme_enable_setup_wizard', '__return_true' );


function bellevue_register_elementor_locations( $elementor_theme_manager ) {

    $elementor_theme_manager->register_all_core_location();

}
add_action( 'elementor/theme/register_locations', 'bellevue_register_elementor_locations' );
