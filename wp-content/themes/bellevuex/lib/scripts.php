<?php
/**
 * Enqueue scripts and stylesheets
 */ 
function roots_scripts() {

	if (is_single() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
	wp_enqueue_script('jquery');

	/********************************
	Bootstrap + Vendor CSS / JS
	 ********************************/
    wp_deregister_script( 'mphb-flexslider' ); // // Deregister MotoPress Flex Slider JS.
	wp_register_script('t_vendor_footer', get_template_directory_uri() . '/assets/js/vendor/vendor_footer.min.js', array(), '1.3', true);
	wp_enqueue_script('t_vendor_footer');

        
    /********************************
		Main JS - Theme helpers
	********************************/  
        $main_edit_time = filemtime(get_template_directory() . '/assets/js/main.js');
  	wp_register_script('roots_main', get_template_directory_uri() . '/assets/js/main.js', array(), $main_edit_time, true);
	wp_enqueue_script('roots_main');



	
	/********************************
		Main Stylesheet
	********************************/  
        $base_css_time = filemtime(get_template_directory() . '/assets/css/base.css');
	wp_register_style('base_app',  get_template_directory_uri() . '/assets/css/base.css', array(), $base_css_time);
	wp_enqueue_style('base_app');
        $app_css_time = filemtime(get_template_directory() . '/assets/css/app.css');
	wp_register_style('roots_app',  get_template_directory_uri() . '/assets/css/app.css', array(), $app_css_time);
	wp_enqueue_style('roots_app');

        
    /********************************
    Styling for MPHB/WP Booking System
     ********************************/

    if ( function_exists( 'get_theme_mod' ) && is_plugin_active('motopress-hotel-booking/motopress-hotel-booking.php')) {
        $themo_mphb_styling = get_theme_mod( 'themo_mphb_use_theme_styling', true );
        if ($themo_mphb_styling == true){
            $hotel_booking_file_time = filemtime(get_template_directory() . '/assets/css/hotel-booking.css');
            wp_register_style('hotel_booking',  get_template_directory_uri() . '/assets/css/hotel-booking.css', array(), $hotel_booking_file_time);
            wp_enqueue_style('hotel_booking');
        }
    }

    /********************************
      Standard Header CSS
     ********************************/
    //if elementor header footer is not used and standard header is on with sticky option set to yes, then load the sticky header
    $thhf_header_enabled = false;
    if (function_exists('thhf_header_enabled')) {
        $thhf_header_enabled = thhf_header_enabled() ? true : false;
    }

    if (!$thhf_header_enabled && th_show_kirki()) {
        //there's a bug that we still want the sticky to work even if the switch is off.
        //&& get_theme_mod('thmv_standard_header_switch')
        if (get_theme_mod('themo_sticky_header', true)) {
            $headhesive_time = filemtime(get_template_directory() . '/assets/js/headhesive.js');
            wp_register_script('t_headhesive', get_template_directory_uri() . '/assets/js/headhesive.js', array('jquery'), $headhesive_time, true);
            wp_enqueue_script('t_headhesive');

            $headhesive_css_time = filemtime(get_template_directory() . '/assets/css/headhesive.css');
            wp_register_style('t_headhesive', get_template_directory_uri() . '/assets/css/headhesive.css', array(), $headhesive_css_time);
            wp_enqueue_style('t_headhesive');
        }
            $header_css_time = filemtime(get_template_directory() . '/assets/css/header.css');
            wp_register_style('t_header', get_template_directory_uri() . '/assets/css/header.css', array(), $header_css_time);
            wp_enqueue_style('t_header');
    }

    /********************************
      Preloader
     ********************************/
    if (get_theme_mod('themo_preloader', true)) {
        $preloader_css_time = filemtime(get_template_directory() . '/assets/css/preloader.css');
        wp_register_style('t_preloader', get_template_directory_uri() . '/assets/css/preloader.css', array(), $preloader_css_time);
        wp_enqueue_style('t_preloader');
    }

    /********************************
      Forms
     ********************************/
    if (function_exists('load_formidable_forms') || class_exists('HotelBookingPlugin')) {
        $forms_css_time = filemtime(get_template_directory() . '/assets/css/forms.css');
        wp_register_style('t_forms', get_template_directory_uri() . '/assets/css/forms.css', array(), $forms_css_time);
        wp_enqueue_style('t_forms');
    }
    /********************************
      WooCommerce
     ********************************/
    if (is_plugin_active('woocommerce/woocommerce.php') && class_exists( 'woocommerce' )) {
        $woocommerce_css_time = filemtime(get_template_directory() . '/assets/css/woocommerce.css');
        wp_register_style('t_woocommerce', get_template_directory_uri() . '/assets/css/woocommerce.css', array(), $woocommerce_css_time);
        wp_enqueue_style('t_woocommerce');
    }
    
    /********************************
      QuickCal / Booked Calendar
     ********************************/
    if (is_plugin_active('quickcal/quickcal.php') || is_plugin_active('booked/booked.php')) {
        $booked_calendar_css_time = filemtime(get_template_directory() . '/assets/css/booked-calendar.css');
        wp_register_style('t_booked-calendar', get_template_directory_uri() . '/assets/css/booked-calendar.css', array(), $booked_calendar_css_time);
        wp_enqueue_style('t_booked-calendar');
    }
    /********************************
      Groovy Menu
     ********************************/
    if (defined('GROOVY_MENU_VERSION')) {
        $groovy_menu_css_time = filemtime(get_template_directory() . '/assets/css/groovy-menu.css');
        wp_register_style('t_groovy-menu', get_template_directory_uri() . '/assets/css/groovy-menu.css', array(), $groovy_menu_css_time);
        wp_enqueue_style('t_groovy-menu');
    }
    
    //echo get_template_directory_uri() . '/assets/css/hotel-booking.css';

    /********************************
    WooCommerce
     ********************************/
    // If woocommerce enabled then ensure shortcodes are respected inside our html metaboxes.
    if ( class_exists( 'woocommerce' ) ) {
        global $post;
        if(isset($post->ID) && $post->ID > 0){
            $themo_meta_data = get_post_meta($post->ID); // get all post meta data
            foreach ( $themo_meta_data as $key => $value ){ // loop
                $pos_html = strpos($key, 'themo_html_'); // Get position of 'themo_html_' in each key.
                $pos_content = strpos($key, '_content'); // Get position of '_content' in each key.
                if($pos_html == 0 && $pos_content > 0 && isset($value) && is_array($value) && isset($value[0]) && strstr( $value[0], '[product_page' )){
                    global $woocommerce;
                    $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                    wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
                    wp_enqueue_script( 'prettyPhoto-init', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
                    wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
                }
            }
        }
    }
		
	/********************************
		Child Theme
	********************************/
	if (is_child_theme()) {
		wp_register_style('roots_child', get_stylesheet_uri());
		wp_enqueue_style('roots_child');
	}

  
}
add_action('wp_enqueue_scripts', 'roots_scripts', 100);
