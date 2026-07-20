<?php
/**
 * General Custom Functions
 *
 * @author     Themovation <themovation@gmail.com>
 * @copyright  2014 Themovation
 * @license    http://themeforest.net/licenses/regular
 * @version    1.0.5
 */

# 100 - Helper Functions
# 200 - WordPress Actions & Filters
# 300 - 3rd Party Plugins - Actions & Filters
# 400 - Option Tree FunctionsOption Tree Functions, Hooks, Filters
# 500 - Core / Special Functions
# 600 - Development Functions - to be removed.



//======================================================================
// 100 - Helper Functions
//======================================================================

if(th_show_kirki()){
// Regenerate Logo image size on save.
    add_filter( "pre_set_theme_mod_themo_logo_height", "th_refresh_logo_size", 2, 2);

    function th_refresh_logo_size($value, $old_value){

        //error_log('custom.php line 35 New value. ');

        if($value !== $old_value){

            if (class_exists( 'Kirki_Helper' ) ) {

                add_image_size('themo-logo', 9999, $value);
                //error_log('NEW Size: '.$value);

                // REG LOGO
                $th_logo = get_theme_mod( 'themo_logo');
                //error_log('Logo URL: '.$th_logo);

                if($th_logo > "") {
                    $th_logo_id = Kirki_Helper::get_image_id($th_logo);
                    //error_log('Logo ID : '.$th_logo_id);

                    $th_logo_path = get_attached_file($th_logo_id); // Full path
                    //error_log('Logo PATH : '.$th_logo_path);

                    $th_attach_data = wp_generate_attachment_metadata($th_logo_id, $th_logo_path);
                    wp_update_attachment_metadata($th_logo_id, $th_attach_data);
                }

                // ALT LOGO
                $th_alt_logo = get_theme_mod( 'themo_logo_transparent_header');
                //error_log('Alt Logo URL: '.$th_alt_logo);

                if($th_alt_logo > ""){
                    $th_alt_logo_id = Kirki_Helper::get_image_id($th_alt_logo);
                    //error_log('Alt Logo ID : '.$th_alt_logo_id);

                    $th_alt_logo_path = get_attached_file( $th_alt_logo_id ); // Full path
                    //error_log('Alt Logo PATH : '.$th_alt_logo_path);

                    $th_attach_data = wp_generate_attachment_metadata( $th_alt_logo_id, $th_alt_logo_path );
                    wp_update_attachment_metadata( $th_alt_logo_id, $th_attach_data );
                }

                //error_log( print_r($th_attach_data, TRUE) );
                //error_log('FILE DATA : '.$th_attach_data['width']);
            }
        }
        return $value;
    }
}
// Check for empty conotent.
function th_empty_content($str) {
    return trim(str_replace('&nbsp;','',strip_tags($str))) == '';
}

/*
 * Gets the first image in a post content.
 * Used for helping missing featured images in blog posts.
 */

function th_catch_that_image() {
    global $post, $posts;
    $first_img = '';
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    $first_img = $matches [1] [0];

    if(empty($first_img)){ //Defines a default image
        //$first_img = "/images/default.jpg";
        $first_img = false;
    }
    return $first_img;
}

// Pagination

if ( ! function_exists( 'th_bittersweet_pagination' ) ) {
    function th_bittersweet_pagination()
    {
        global $wp_query;
        $total = $wp_query->max_num_pages;

        if (get_option('permalink_structure')) {
            $format = '?paged=%#%';
        }

        $pages = paginate_links(array(
            'base' => get_pagenum_link(1) . '%_%',
            'format' => $format,
            'current' => max(1, get_query_var('paged')),
            'total' => $total,
            'type' => 'array',
            'prev_text' => esc_html__('Newer posts &rarr;', 'bellevue'),
            'next_text' => esc_html__('&larr; Older posts', 'bellevue'),
        ));

        if (is_array($pages)) {
            foreach ($pages as $page) {
                if (strpos($page, 'Newer posts') !== false) {
                    echo "<li class='next'>".wp_kses_post($page)."</li>";
                } elseif (strpos($page, 'Older posts') !== false) {
                    echo "<li class='previous'>".wp_kses_post($page)>"</li>";
                }
            }
        }
    }
}


/*
 * backward compatible with pre-4.1
 * */

if ( ! function_exists( '_wp_render_title_tag' ) ) :
    function theme_slug_render_title() {
        ?>
        <title><?php wp_title('|', true, 'right'); ?></title>
        <?php
    }
    add_action( 'wp_head', 'theme_slug_render_title' );
endif;


/*
 * If WooCommerce isn’t activated, return false.
 */

if ( ! function_exists( 'th_is_woocommerce_activated' ) ) {
    function th_is_woocommerce_activated() {
        if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
    }
}

//-----------------------------------------------------
// return woo page IDs
//-----------------------------------------------------
function themo_return_woo_page_ID(){
    if(th_is_woocommerce_activated() && is_woocommerce()){
        // Get the shop page ID, so we can get the custom header and sidebar options for Categories, archieve etc.
        if(get_option( 'woocommerce_shop_page_id' )){
            $woo_shop_page_id = get_option( 'woocommerce_shop_page_id' );
        }
        if(is_product()){
            return false;
        }elseif ((is_product_tag() || is_product_category() || is_shop()) && isset($woo_shop_page_id) && $woo_shop_page_id > ""){
            return $woo_shop_page_id;
        }
    }
    return false;
}



//-----------------------------------------------------
// Check if retina version of an image exists
// Takes attachecment ID
//-----------------------------------------------------
function themo_retina_version_exists($id){
    $post_id = (int) $id;

    if ( !$post = get_post( $post_id ) )
        return false;

    if ( !is_array( $imagedata = wp_get_attachment_metadata( $post->ID ) ) )
        return false;
    $file = get_attached_file( $post->ID );

    if ( !empty($imagedata['sizes']['themo-logo']['file']) && ($thumbfile = str_replace(basename($file), $imagedata['sizes']['themo-logo']['file'], $file)) && file_exists($thumbfile) ) {

        $path_parts = pathinfo($thumbfile);
        $image_find = $path_parts['dirname'].'/'.$path_parts['filename'].'@2x.'.$path_parts['extension'];

        if (file_exists ( $image_find )){
            return true;
        }
    }
    return false;
}

//-----------------------------------------------------
// Return Retina Logo src, heigh, width
// Takes attachecment ID
//-----------------------------------------------------

function themo_return_retina_logo($id){
    if(themo_retina_version_exists($id)){ // If we have a valid retina version, continue.

        $image_attributes  = wp_get_attachment_image_src( $id, 'themo-logo' );

        if(isset($image_attributes) && !empty( $image_attributes ) )
        {
            $logo_src = $image_attributes[0];
            $logo_height = $image_attributes[2];
            $logo_width = $image_attributes[1];;

            // Split up the URL so we can create the retina version.
            $logo_src_scheme = parse_url($logo_src,PHP_URL_SCHEME);
            $logo_src_host = parse_url($logo_src,PHP_URL_HOST);
            $logo_src_path = pathinfo(parse_url($logo_src,PHP_URL_PATH),PATHINFO_DIRNAME);
            $logo_src_filename = pathinfo(parse_url($logo_src,PHP_URL_PATH),PATHINFO_FILENAME);
            $logo_src_extension = pathinfo(parse_url($logo_src,PHP_URL_PATH),PATHINFO_EXTENSION);


            $retina_file_part = '@2x';
            $logo_retina_src = $logo_src_scheme . '://' . $logo_src_host . $logo_src_path . '/' . $logo_src_filename . $retina_file_part . '.' . $logo_src_extension;
            $logo_retina_height = $logo_height * 2;
            $logo_retina_width = $logo_width * 2;

            return array($logo_retina_src, $logo_retina_height, $logo_retina_width);

        }
    }
    return false;
}

//-----------------------------------------------------
// themo_content
//-----------------------------------------------------
function themo_content($content,$return_content=false){
    $content = wp_kses_post($content);
    $content = apply_filters( 'the_content', $content );
    $content = str_replace( ']]>', ']]&gt;', $content );
    if($return_content){
        return $content;
    }else{
        echo $content; // Sanitized just above. Retain Shortocde formatting / output.
    }
}




//-----------------------------------------------------
// returns an image via attachmentID
// @attachment_id - WordPress Media Library POST ID
// @classes - any classes to be inserted into tag if using tag mode
// @image_size - specify image size already created by add_image_size()
// @return_src - if you want to return the src only vs the img tag.
//-----------------------------------------------------
function themo_return_metabox_image($attachment_id = 0, $classes = null, $image_size = 'th_img_xxl', $return_src = false, &$alt=""){
    if(!$attachment_id > "" ){
        return false;
    }

    if(!is_numeric($attachment_id)){ // We might be dealing with an URL vs ID, look up URL and get ID.
        $attachment_url = $attachment_id; // put URL in a local var
        $attachment_id = themo_return_attachment_id_from_url($attachment_url); // Search DB for URL and return ID.
    }

    if(!$attachment_id > "" ){
        return false;
    }

    $attachment_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

    if( ! empty( $attachment_alt ) && is_array($attachment_alt)) {
        $alt = trim(strip_tags($attachment_alt[0]));
    }else{
        $alt = $attachment_alt;
    }

    $image_attr = array(
        'class'	=> $classes,
        'alt'   => $alt
    );
    if ($return_src){
        $image_attributes = wp_get_attachment_image_src( $attachment_id, $image_size) ;
        if( $image_attributes ) {
            return $image_attributes[0];
        }else{
            return false;
        }

    }else{
        return wp_get_attachment_image( $attachment_id, $image_size, 0, $image_attr ) ;
    }

}

//-----------------------------------------------------
// themo_return_header_sidebar_settings
// Gets header and sidebar settings based on type page
//-----------------------------------------------------

function themo_return_header_sidebar_settings($post_type = false) {
    if (th_is_woocommerce_activated() && is_woocommerce()) { // Handle all Woo stuff...
        $key = 'themo_woo';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }elseif($post_type > ""){
        $key = $post_type."_layout";
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        $masonry = get_theme_mod( $key.'_masonry', "off" );
        return array ($key, $show_header, $page_header_float,$masonry);
    }elseif (is_home()) {
        $key = 'themo_blog_index_layout';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        $masonry = get_theme_mod( $key.'_masonry', false );
        return array ($key, $show_header, $page_header_float,$masonry);
    }elseif (is_single()) {
        $key = 'themo_single_post_layout';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }elseif(is_post_type_archive( 'mphb_room_service')){
        $key = 'themo_mphp_service';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }elseif(is_tax( 'mphb_room_type_tag')){
        $key = 'themo_mphp_tag';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }elseif(is_tax( 'mphb_room_type_facility' )){
        $key = 'themo_mphp_amenities';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }elseif(is_tax( 'mphb_room_type_category' )){
        $key = 'themo_mphp_category';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }
    elseif (is_archive()) {
        $key = 'themo_default_layout';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    } elseif (is_search()) {
        $key = 'themo_default_layout';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    } elseif (is_404()) {
        $key = 'themo_default_layout';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    } else {
        $key = 'themo_default_layout';
        $show_header = get_theme_mod( $key.'_show_header', true );
        $page_header_float = get_theme_mod( $key.'_header_float', "centered" );
        return array ($key, $show_header, $page_header_float,false);
    }
}


//-----------------------------------------------------
// themo_is_element_empty
// returns true / falase
//-----------------------------------------------------
function themo_is_element_empty($element) {
    $element = trim($element);
    return empty($element) ? false : true;
}


//-----------------------------------------------------
// themo_return_attachment_id_from_url
// returns an image via attachmentID
// @attachment_id - WordPress Media Library POST ID
// @classes - any classes to be inserted into tag if using tag mode
// @image_size - specify image size already created by add_image_size()
// @return_src - if you want to return the src only vs the img tag.
//-----------------------------------------------------
function themo_return_attachment_id_from_url( $attachment_url = '' ) {
    // Sanitization
    $attachment_url = esc_url($attachment_url);
    global $wpdb;
    $attachment_id = false;
    // If there is no url, return.
    if ( '' == $attachment_url )
        return;
    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();
    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
        // If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
    }
    return $attachment_id;
}


//-----------------------------------------------------
// Get Attachment ID from URL
// Use the following code to get the image you want, Please note that your image
// will have to be uploaded through WordPress in order for this to work.
// Adapt code as needed:
//-----------------------------------------------------

function themo_custom_get_attachment_id( $guid ) {
    // Prepare & Sanitization
    $guid = esc_url($guid);

    global $wpdb;

    /* nothing to find return false */
    if ( ! $guid )
        return false;

    /* get the ID */
    $id = $wpdb->get_var( $wpdb->prepare("SELECT p.ID FROM $wpdb->posts p WHERE p.guid = %s AND p.post_type = %s", $guid, 'attachment'));

    /* the ID was not found, try getting it the expensive WordPress way */
    if ( $id == 0 )
        $id = url_to_postid( $guid );

    return $id;
}


//-----------------------------------------------------
// Create retina-ready images
// Referenced via retina_support_attachment_meta().
//-----------------------------------------------------

function themo_retina_support_create_images( $file, $width, $height, $crop = false ) {
    if ( $width || $height ) {
        $resized_file = wp_get_image_editor( $file );
        if ( ! is_wp_error( $resized_file ) ) {
            $filename = $resized_file->generate_filename( $width . 'x' . $height . '@2x' );

            $resized_file->resize( $width * 2, $height * 2, $crop );
            $resized_file->save( $filename );

            $info = $resized_file->get_size();

            return array(
                'file' => wp_basename( $filename ),
                'width' => $info['width'],
                'height' => $info['height'],
            );
        }
    }
    return false;
}


//-----------------------------------------------------
// themo_return_outer_tag
// Returns output if $bool is true
//-----------------------------------------------------
function themo_return_outer_tag($output,$bool){
    if($bool){
        return $output;
    }
}

//-----------------------------------------------------
// themo_return_inner_tag
// Returns output if $bool is false
//-----------------------------------------------------
function themo_return_inner_tag($output,$bool){
    if(!$bool){
        return $output;
    }
}

//-----------------------------------------------------
// themo_has_sidebar
// Returns a boolean value if the page has a sidebar
// Takes pagelayout (full, right, left)
// Returns true there is a sidebar (left or right), false if anything else.
//-----------------------------------------------------
function themo_has_sidebar($pagelayout){
    if($pagelayout == 'right' ||  $pagelayout == 'left'){
        return true;
    }else{
        return false;
    }
}





//-----------------------------------------------------
// themo_return_social_icons
// Return background styling and html markup for
// Social Media Icons
//-----------------------------------------------------

function themo_return_social_icons() {
    $output = "";
    if ( function_exists( 'get_theme_mod' ) ) {
        /* get the slider array */
        $social_icons = get_theme_mod( 'themo_social_media_accounts', array() );
        //print_r($social_icons);
        if ( ! empty( $social_icons ) ) {
            foreach( $social_icons as $social_icon ) {
                if (isset($social_icon["themo_social_url"]) && $social_icon["themo_social_url"] >""){

                    // Link Target
                    $link_target = $social_icon["themo_social_url_target"];
                    $link_target_att = false;
                    if (isset($link_target) && $link_target) {
                        $link_target_att = "target=_blank ";
                    }

                    $output .= "<a ".$link_target_att." href='".$social_icon["themo_social_url"]."'><i class='".$social_icon["themo_social_font_icon"]."'></i></a>";
                }else{
                    $output .= "<i class='".$social_icon["themo_social_font_icon"]."'></i>";
                }

            }
        }
    }
    return $output;
}

//-----------------------------------------------------
// themo_return_payments_accepted
// Return background styling and html markup for
// Payments Accepted
//-----------------------------------------------------

function themo_return_payments_accepted() {
    $output = "";
    if ( function_exists( 'get_theme_mod' ) ) {
        /* get the slider array */
        $payments_accepted = get_theme_mod( 'themo_payments_accepted', array() );
        //print_r($social_icons);
        if ( ! empty( $payments_accepted ) ) {
            foreach( $payments_accepted as $payment_info ) {

                // Image
                $payment_logo_src = false;
                $payment_logo_width = false;
                $payment_logo_height = false;
                $payment_logo = $payment_info["themo_payments_accepted_logo"];
                if(isset($payment_logo) && $payment_logo > ""){
                    $img_id = $payment_logo ;// themo_custom_get_attachment_id( $payment_logo );
                    if($img_id > ""){
                        $image_attributes = wp_get_attachment_image_src( $img_id, 'th_img_xs');
                        if( $image_attributes ) {
                            $payment_logo_src = $image_attributes[0];
                            $payment_logo_width = $image_attributes[1];
                            $payment_logo_height = $image_attributes[2];
                            if(isset($payment_logo_width) && $payment_logo_width > ""){
                                $payment_logo_width = "width='".esc_attr($payment_logo_width)."'";
                            }
                            if(isset($payment_logo_height) && $payment_logo_height > ""){
                                $payment_logo_height = "height='".esc_attr($payment_logo_height)."'";
                            }
                        }
                    }
                }

                // Link Target
                if (isset($payment_info["themo_payment_url_target"])) {
                    $link_target = $payment_info["themo_payment_url_target"];
                }

                $link_target_att = false;
                if (isset($link_target) && is_array($link_target)  && !empty($link_target)) {
                    $link_target = $link_target[0];
                    if($link_target == '_blank'){
                        $link_target_att = "target='_blank'";
                    }
                }elseif(isset($link_target) && $link_target){
                    $link_target_att = "target=_blank";
                }

                // Link
                $href_open = false;
                $href_close = false;
                $payment_link = $payment_info["themo_payment_url"];
                if(isset($payment_link) && $payment_link > ""){
                    $href_open = "<a ".$link_target_att." href='".esc_url($payment_link)."'>";
                    $href_close = '</a>';
                }
                if(isset($payment_logo_src) && $payment_logo_src > ""){
                    $output .= $href_open . "<img src='".esc_url($payment_logo_src)."' alt='".esc_attr($payment_info["title"])."' " .$payment_logo_width ." ". $payment_logo_height. ">" . $href_close;
                }else{
                    if (isset($payment_info["title"])) {
                        $output .= $href_open . "<span class='th-payment-no-img'>" . $payment_info["title"] . "</span>" . $href_close;
                    }
                }
            }
        }
    }
    return $output;
}



//-----------------------------------------------------
// themo_return_contact_info
// Return background styling and html markup for
// Contact Info Widget
//-----------------------------------------------------
function themo_return_contact_info(){
    $output = "";


    $defaults = [
        [
            'title' => esc_html__( 'contact@bellevue.com', 'bellevue' ),
            'themo_contact_icon'  => 'fa fa-envelope-open-o',
            'themo_contact_icon_url'  => 'mailto:contact@ourdomain.com',
            'themo_contact_icon_url_target'  => 1,
        ]
    ];

    if ( function_exists( 'get_theme_mod' ) ) {
        // Get icon block array from OT
        $icon_block = get_theme_mod( 'themo_contact_icons', $defaults );

        if (isset($icon_block) && is_array($icon_block)  && !empty($icon_block)) {

            $output .= "<div class='icon-blocks'>";

            foreach( $icon_block as $icon ) {
                $glyphicon_type = $substring = substr($icon["themo_contact_icon"], 0, strpos($icon["themo_contact_icon"], '-'));
                if (isset($icon["themo_contact_icon_url_target"])) {
                    $link_target = $icon["themo_contact_icon_url_target"];
                }

                $link_target_att = false;
                if (isset($link_target)  && $link_target) {
                    $link_target_att = "target='_blank'";
                }
                // Link
                $href_open = false;
                $href_close = false;
                $contact_url = $icon["themo_contact_icon_url"];
                if(isset($contact_url) && $contact_url > ""){
                    $href_open = "<a ".$link_target_att." href='".esc_url($contact_url)."'>";
                    $href_close = '</a>';
                }

                $output .= '<div class="icon-block">';
                $output .= "<p>".$href_open."<i class='".esc_attr($icon["themo_contact_icon"])."'></i><span>".wp_kses_post($icon["title"])."</span>".$href_close."</p>";
                $output .= '</div>';
            }
            $output .= "</div>";
        }
    }
    return $output;
}


//-----------------------------------------------------
// themo_return_footer_logo
// Return background styling and html markup for
// Footer Logo
//-----------------------------------------------------

function themo_return_footer_logo() {
    $output = "";
    if ( function_exists( 'get_theme_mod' ) ) {
        /* get the slider array */

        // Image
        $payment_logo_src = false;
        $payment_logo_width = false;
        $payment_logo_height = false;
        $footer_logo = get_theme_mod( 'themo_footer_logo', false );

        if(isset($footer_logo) && $footer_logo > ""){
            $img_id = themo_custom_get_attachment_id( $footer_logo );
            if($img_id > ""){
                $image_attributes = wp_get_attachment_image_src( $img_id, 'themo_featured');
                if( $image_attributes ) {
                    $footer_logo_src = $image_attributes[0];
                    $footer_logo_width = $image_attributes[1];
                    $footer_logo_height = $image_attributes[2];
                    if(isset($footer_logo_width) && $footer_logo_width > ""){
                        $footer_logo_width = "width='".esc_attr($footer_logo_width)."'";
                    }
                    if(isset($footer_logo_height) && $footer_logo_height > ""){
                        $footer_logo_height = "height='".esc_attr($footer_logo_height)."'";
                    }
                }
            }
        }


        // Link Target
        $link_target = get_theme_mod( 'themo_footer_logo_url_target', false );
        $link_target_att = false;
        if (isset($link_target) && !empty($link_target)) {
            $link_target_att = "target=_blank";
        }


        // Link
        $href_open = false;
        $href_close = false;
        $logo_link = get_theme_mod( 'themo_footer_logo_url', false );
        if(isset($logo_link) && $logo_link > ""){
            $href_open = "<a ".$link_target_att." href='".esc_url($logo_link)."'>";
            $href_close = '</a>';
        }

        if(isset($footer_logo_src) && $footer_logo_src > ""){
            $output .= $href_open . "<img src='".esc_url($footer_logo_src)."' " .$footer_logo_width ." ". $footer_logo_height. ">" . $href_close;
        }

    }
    return $output;
}


//======================================================================
// 200 - WordPress Actions & Filters
//======================================================================

# Actions
# Filters
# Plugins Actiosn and Filters

// Use our comments template.
// Check child theme first, fallback to parent theme.
function thmv_load_comments_template( $comment_template ) {
    /*global $post;
    if ( !( is_singular() && ( have_comments() || 'open' == $post->comment_status ) ) ) {
        return;
    }
    if($post->post_type == 'business'){ // assuming there is a post type called business
        return dirname(__FILE__) . '/reviews.php';
    }*/
    $thmv_child_theme_comments = get_stylesheet_directory() . '/templates/comments.php';
    if ( file_exists( $thmv_child_theme_comments ) ) {
        return $thmv_child_theme_comments;
    }
    return get_template_directory() . '/templates/comments.php';
}

add_filter( "comments_template", "thmv_load_comments_template" );

// RetinaJS
// Add data attribute for retina images.
if ( function_exists( 'get_theme_mod' ) ) {
    if (get_theme_mod('themo_retinajs', false)) {
        add_filter('the_content', 'themo_add_retina_tags', 99999);
    }
}

function themo_add_retina_tags($content)
{
    $themo_retina_size = '2';
    preg_match_all('/<img (.*?)\/>/', $content, $images);
    if(!is_null($images))
    {
        foreach($images[1] as $index => $value)
        {
            if(!preg_match('/data-rjs=/', $value))
            {
                $new_img = str_replace('<img', '<img data-rjs="'.$themo_retina_size.'"', $images[0][$index]);
                $content = str_replace($images[0][$index], $new_img, $content);
            }
        }
    }
    return $content;
}

// Add MPHB CPTs into editable by elemetnor array.
//add_action( 'after_setup_theme', 'themo_plugin_overrides' );

function themo_plugin_overrides()
{
    // MotoPress Hotel Booking
    if (!get_option('themo_mphb_cpt_elementor_support_check')) {
        update_option('themo_mphb_cpt_elementor_support_check', 1);
        if (class_exists('HotelBookingPlugin') && defined('ELEMENTOR_PATH')) {
            // Check for our custom post type, if it's not included, include it.
            $elementor_cpt_support = get_option('elementor_cpt_support');
            if (empty($elementor_cpt_support)) {
                $elementor_cpt_support = array();
            }
            if (!in_array("mphb_room_type", $elementor_cpt_support)) {
                array_push($elementor_cpt_support, "mphb_room_type");
                update_option('elementor_cpt_support', $elementor_cpt_support);
            }

            if (!in_array("mphb_room_service", $elementor_cpt_support)) {
                array_push($elementor_cpt_support, "mphb_room_service");
                update_option('elementor_cpt_support', $elementor_cpt_support);
            }
        }

    }
}

/* Admin notice for HFE */



if ( ! function_exists( 'thmv_hfe_help_notice' ) ){

    // display custom admin notice
    function thmv_hfe_help_notice() {

        if ( is_admin()){
            $th_screen = get_current_screen();

            if ($th_screen->id === 'edit-elementor-thhf') {

                $user_id = get_current_user_id();

                if ( get_user_meta( $user_id, 'thmv_set_hfe_help_dismissed', true ) !== '1' ) { ?>
                    <div class="notice notice-info is-dismissible thmv_hfe_edit">
                        <p><strong><?php _e('Get started quickly with our <a href="https://help.bellevuetheme.com/article/38-header-setup" target="_blank">templates and help guide!</a>', 'stratus'); ?> <span><a href="https://help.bellevuetheme.com/article/38-header-setup" target="_blank">Learn more</a> | <a class="th-dismiss" href="edit.php?post_type=elementor-thhf&thmv-hfe-help-dismissed">Dismiss this notice</a></span></strong></p>
                    </div>
                    <?php

                }
                // delete_user_meta($user_id, 'thmv_set_hfe_help_dismissed');
            }
        }
    }
}
//add_action('admin_notices', 'thmv_hfe_help_notice');

if ( ! function_exists( 'thmv_set_hfe_help_dismissed' ) ){
    function thmv_set_hfe_help_dismissed() {
        $user_id = get_current_user_id();
        if ( isset( $_GET['thmv-hfe-help-dismissed'] ) ){
            update_user_meta( $user_id, 'thmv_set_hfe_help_dismissed', '1');
        }
    }
}

add_action( 'admin_init', 'thmv_set_hfe_help_dismissed' );

/* Admin notice for Master Slider */

// display custom admin notice
function th_master_slider_install_notice() {

    $th_screen = get_current_screen();

    if ($th_screen->id === 'toplevel_page_masterslider') {

        $user_id = get_current_user_id();

        if ( get_user_meta( $user_id, 'th_ms_install_dismissed', true ) !== '1' ) { ?>
            <div class="notice notice-info">
                <p><?php _e('Looking for the sliders from the live demo? <a href="https://help.bellevuetheme.com/article/210-master-slider---help-and-support" target="_blank">Check out this article for how to import them</a>.', 'bellevue'); ?> <a class="th-dismiss" href="?page=masterslider&th-ms-install-dismissed">Dismiss</a></p>
            </div>
            <?php

        }
        delete_user_meta($user_id, 'th_ms_install_dismissed');

    }
}
add_action('admin_notices', 'th_master_slider_install_notice');


function th_set_ms_install_dismissed() {
    $user_id = get_current_user_id();
    if ( isset( $_GET['th-ms-install-dismissed'] ) ){
        update_user_meta( $user_id, 'th_ms_install_dismissed', '1');
    }
}
add_action( 'admin_init', 'th_set_ms_install_dismissed' );

/**
 * Loads the child theme textdomain.
 */
function themo_child_theme_setup() {
    load_child_theme_textdomain( 'bellevue', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'themo_child_theme_setup' );


/**
 * Customize Adjacent Post Link Order
 */


/**
 * Check if WPML is installed, add in Menu Classes to support dropdowns.
 *
 */

function th_wpml_new_submenu_class($menu) {
    $menu = preg_replace('/ class="sub-menu submenu-languages"/','/ class="dropdown-menu sub-menu submenu-languages"/',$menu);
    $menu = preg_replace('/ class="menu-item menu-item-language menu-item-language-current menu-item-has-children"/','/ class="dropdown menu-item menu-item-language menu-item-language-current menu-item-has-children"/',$menu);
    return $menu;
}

if ( function_exists('icl_object_id') ) {
    add_filter('wp_nav_menu_items','th_wpml_new_submenu_class');
}




function themo_adjacent_post_where($sql) {

    if ( !is_main_query() || !is_singular() )
        return $sql;

    $the_post = get_post( get_the_ID() );
    $patterns = array();
    $patterns[] = '/post_date/';
    $patterns[] = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\'/';
    $replacements = array();
    $replacements[] = 'menu_order';
    $replacements[] = $the_post->menu_order;
    return preg_replace( $patterns, $replacements, $sql );
}


function themo_adjacent_post_sort($sql) {
    if ( !is_main_query() || !is_singular() )
        return $sql;

    $pattern = '/post_date/';
    $replacement = 'menu_order';
    return preg_replace( $pattern, $replacement, $sql );
}

if ( isset($_GET['portorder']) && $_GET['portorder'] == 'menu' ) {

    add_filter( 'get_next_post_where', 'themo_adjacent_post_where' );
    add_filter( 'get_previous_post_where', 'themo_adjacent_post_where' );
    add_filter( 'get_next_post_sort', 'themo_adjacent_post_sort' );
    add_filter( 'get_previous_post_sort', 'themo_adjacent_post_sort' );
}

function themo_add_query_vars_filter( $vars ){
    $vars[] = "portorder";
    return $vars;
}
add_filter( 'query_vars', 'themo_add_query_vars_filter' );

/**
 * Adds a pretty "Continue Reading" link to post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 */
function themo_custom_excerpt_more( $output ) {
    if ( (has_excerpt() || themo_has_more()) && ! is_attachment() && get_post_type() != 'themo_room' && get_post_type() != 'mphb_room_type') {
        $output .= ' &hellip; <a href="' . esc_url(get_permalink()) . '">' . esc_html__('Read More', 'bellevue') . '</a>';
    }
    return $output;
}
add_filter( 'get_the_excerpt', 'themo_custom_excerpt_more' );



function themo_read_more_link() {
    if (get_post_type() != 'themo_room' && get_post_type() != 'mphb_room_type') {
        return ' &hellip; <a href="' . esc_url(get_permalink()) . '">' . esc_html__('Read More', 'bellevue') . '</a>';
    }

}

add_filter( 'the_content_more_link', 'themo_read_more_link' );



function themo_has_more()
{
    global $post;
    if ( empty( $post ) ) return;

    if (isset($post->post_content) && $pos=strpos($post->post_content, '<!--more-->')) {
        return true;
    } else {
        return false;
    }
}


add_action('wp_head', 'themo_load_html5shiv_respond');
function themo_load_html5shiv_respond(){
    echo '<!--[if lt IE 9]>'."\n".'<script src="'.get_template_directory_uri() .'/assets/js/vendor/html5shiv.min.js"></script>'."\n".'<script src="'.get_template_directory_uri().'/assets/js/vendor/respond.min.js"></script>'."\n".'<![endif]-->'."\n";
}

/**
 * prefetch google fonts
 */
function dns_prefetch() {
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" />';
}
add_action('wp_head', 'dns_prefetch', 0);
//-----------------------------------------------------
// admin_enqueue_scripts - action
// Support for Meta Boxes (show / hide)
// Whenever a page template selected value changes,
// instantly hide/show the related metaboxs.
//-----------------------------------------------------
add_action('admin_enqueue_scripts', 'themo_admin_meta_show');

function themo_admin_meta_show()
{

    // Admin Styles
    wp_register_style( 'themo_admin_css', get_template_directory_uri() . '/assets/css/admin-styles.css', false, '1' );
    wp_enqueue_style( 'themo_admin_css' );


    // Admin dashboard Styles
    $cssTimeModified = filemtime(get_template_directory().'/assets/css/admin-dashboard.css');
    wp_register_style( 'themo_admin_dashboard', get_template_directory_uri() . '/assets/css/admin-dashboard.css', false, $cssTimeModified );
    wp_enqueue_style( 'themo_admin_dashboard' );

    wp_register_style( 'google-font-inter','https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap', false, '1' );
    wp_enqueue_style( 'google-font-inter' );

    // Admin Scripts
    $jsTimeModified = filemtime(get_template_directory().'/assets/js/admin-scripts.js');
    wp_register_script('themo_admin_js', get_template_directory_uri() . '/assets/js/admin-scripts.js', array(), $jsTimeModified, true);
    wp_enqueue_script('themo_admin_js');

}

//-----------------------------------------------------
// clean_url - Filter
// Defer JS
// Adapted from https://gist.github.com/toscho/1584783
//-----------------------------------------------------
if ( ! function_exists( 'themo_add_defer_to_js' ) )
{
    function themo_add_defer_to_js( $url )
    {
        if (strpos($url, '#deferload')===false)
            return $url;
        else if (is_admin())
            return str_replace('#deferload', '', $url);
        else
            return str_replace('#deferload', '', $url)."' defer='defer";
    }
    add_filter( 'clean_url', 'themo_add_defer_to_js', 11, 1 );
}


//-----------------------------------------------------
// prepend_attachment - filter
// Set default image size on the attachment pages
//-----------------------------------------------------
add_filter('prepend_attachment', 'themo_prepend_attachment');
function themo_prepend_attachment($p) {
    return wp_get_attachment_link(0, 'th_img_xl', false);
}

//-----------------------------------------------------
// delete_attachment - filter
// Delete retina-ready images
// This function is attached to the 'delete_attachment' filter hook.
//-----------------------------------------------------
add_filter( 'delete_attachment', 'themo_delete_retina_support_images' );

function themo_delete_retina_support_images( $attachment_id ) {
    $meta = wp_get_attachment_metadata( $attachment_id );
    $upload_dir = wp_upload_dir();
    if (isset($meta['file']) && $meta['file'] > ""){
        $path = pathinfo( $meta['file'] );
        foreach ( $meta as $key => $value ) {
            if ( 'sizes' === $key ) {
                foreach ( $value as $sizes => $size ) {
                    $original_filename = $upload_dir['basedir'] . '/' . $path['dirname'] . '/' . $size['file'];
                    $retina_filename = substr_replace( $original_filename, '@2x.', strrpos( $original_filename, '.' ), strlen( '.' ) );
                    if ( file_exists( $retina_filename ) )
                        unlink( $retina_filename );
                }
            }
        }
    }
}

//-----------------------------------------------------
// wp_generate_attachment_metadata - filter
// Retina Support for Logo
// This function is attached to the 'wp_generate_attachment_metadata' filter hook.
//-----------------------------------------------------

// We can only add retina support after_setup_theme, when ot_get_option is available.
// We want to check if the user has disabled retina support before adding it automatically.
function themo_add_retina_support() {

    add_filter( 'wp_generate_attachment_metadata', 'themo_retina_support_attachment_meta', 10, 2 );

}
add_action( 'after_setup_theme', 'themo_add_retina_support' );

function themo_retina_support_attachment_meta( $metadata, $attachment_id ) {

    $retina_support = false; // Default to no retina support.
    if ( function_exists( 'get_theme_mod' ) ) {
        $retina_support = get_theme_mod( 'themo_retina_support', 'off' );
    }
    foreach ( $metadata as $key => $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $image => $attr ) {
                if(is_array( $attr )){
                    if ($retina_support == 'on' || $image == 'themo-logo'){ // Always use retina for logo.
                        themo_retina_support_create_images( get_attached_file( $attachment_id ), $attr['width'], $attr['height'], true );
                    }
                }
            }
        }
    }
    return $metadata;
}

//-----------------------------------------------------
// wp_get_attachment_link - filter
// Lightbox Support
//-----------------------------------------------------
add_filter( 'wp_get_attachment_link' , 'themo_add_lighbox_data' );

function themo_add_lighbox_data ($content) {

    $postid = get_the_ID();
    $content = str_replace('<a', '<a class="thumbnail img-thumbnail"', $content);

    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = FALSE;
    //$doc->loadHTML($content);
    // Prepend an XML encoding hint so DOMDocument parses UTF-8 content
    // without relying on deprecated HTML-ENTITIES conversion.
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $content);

    $tags = $doc->getElementsByTagName('img');

    foreach ($tags as $tag) {
        $alt = $tag->getAttribute('alt');
    }

    $a_tag = $doc->getElementsByTagName('a');

    foreach ($a_tag as $tag) {
        $href = $tag->getAttribute('href');
        $image_large_src = "";
        // We need to get the ID by href
        // Check if this ID has a th_img_xl size, if so replace href.


        if ($href > ""){ // If href is captured
            $image_ID = themo_return_attachment_id_from_url($href); // Get the attachment ID
            if ($image_ID > 0){ // If id has been captured, check for image size.
                $image_large_attributes = wp_get_attachment_image_src( $image_ID, "th_img_xl") ;

                if( $image_large_attributes ) { //  If there is th_img_xl size, use it.
                    $image_large_src = $image_large_attributes[0];
                }else{
                    $image_large_src = wp_get_attachment_url( $image_ID );
                }
            }
        }

        // If a large size has been found, replace the original size.
        if ($image_large_src > ""){
            $content = str_replace($href, $image_large_src, $content);
        }
    }

    if (false !== strpos($href,'.jpg') || false !== strpos($href,'.jpeg') || false !== strpos($href,'.png') || false !== strpos($href,'.gif')) {
        // data-footer=\"future title / caption \"

        // Disable global lightbox by default.
        $elementor_global_image_lightbox = get_option('elementor_global_image_lightbox');
        if (!empty($elementor_global_image_lightbox) && $elementor_global_image_lightbox == 'yes') {
            $content = preg_replace("/<a/","<a data-title=\"$alt\" ",$content,1);
        }else{
            $content = preg_replace("/<a/","<a data-toggle=\"lightbox\" data-gallery=\"multiimages\" data-title=\"$alt\" ",$content,1);
        }


    }

    return $content;
}


function themo_portfolio_template_options( $query ) {

    if ( is_admin() || ! $query->is_main_query() )
        return;

    //http://codex.wordpress.org/Plugin_API/Action_Reference/pre_get_posts

}
//add_action( 'pre_get_posts', 'themo_portfolio_template_options', 1 );




//======================================================================
// 300 - 3rd Party Plugins - Actions & Filters
//======================================================================


//-----------------------------------------------------
// MotoPress Hotel Booking
//-----------------------------------------------------

// Set default MPHB datepicker options
function mphb_public_js_data_callback( $data) {
    //echo "<pre>";
    //echo print_r($data);
    //echo "</pre>";

    if(isset($data['_data']['settings']['numberOfMonthCalendar'])){
        $data['_data']['settings']['numberOfMonthCalendar'] = 1;
        $data['_data']['settings']['numberOfMonthDatepicker'] = 1;
    }

    return $data;
}
//add_filter( 'mphb_public_js_data', 'mphb_public_js_data_callback', 10, 3 );

// Turn off extension links. Some buyers are getting confused because we bundle some of these extensions.
add_filter( 'mphb_show_extension_links', '__return_false');

// Remove mphb install pages admin notice if page have been imported already.
if(!get_option( 'mphb_wizard_passed', false )){

    $themo_mphb_booking_page = th_get_page_by_title( 'Booking Confirmation' , OBJECT );

    if ( isset($themo_mphb_booking_page) ) {

        update_option('mphb_wizard_passed', true);
    }
}

// Auto set template mode, just onece
if(!get_option( 'themo_mphb_default_settings', false )){
    update_option('mphb_template_mode', 'plugin'); // Template Mode
    update_option('mphb_confirmation_mode', 'manual'); // Confirmation Mode
    update_option('mphb_direct_booking', 1); // Skip search results
    update_option('themo_mphb_default_settings', true); // Only run this once.
}

// Auto set MPHB page settings, just onece
if ( ! function_exists( 'themo_mphb_hide_license_notice_if_supported' ) ) {
    function themo_mphb_hide_license_notice_if_supported( $mphb_license_settings ) {
        if (
            is_object( $mphb_license_settings )
            && method_exists( $mphb_license_settings, 'needHideNotice' )
            && method_exists( $mphb_license_settings, 'setNeedHideNotice' )
            && ! $mphb_license_settings->needHideNotice()
        ) {
            $mphb_license_settings->setNeedHideNotice( true );
        }
    }
}

if(get_option( 'envato_setup_complete', false ) && !get_option( 'themo_mphb_page_settings', false )){
    //  MotoPress Object exist? Set config pages if not set already.
    if(function_exists('MPHB') && is_object(MPHB())){

        //
        themo_mphb_hide_license_notice_if_supported( MPHB()->settings()->license() );

        // Search Results Page
        if(!MPHB()->settings()->pages()->getSearchResultsPageId()){
            if ( $searchPage = get_page_by_path( 'booking-confirmation/search-results', OBJECT, 'page' ) ) {
                if (is_object($searchPage) && (count(get_object_vars($searchPage)) > 0)) {
                    $searchPageID = $searchPage->ID;
                    MPHB()->settings()->pages()->setSearchResultsPage($searchPageID);
                }
            }elseif ( $searchPage = get_page_by_path( 'search-results', OBJECT, 'page' ) ) {
                if (is_object($searchPage) && (count(get_object_vars($searchPage)) > 0)) {
                    $searchPageID = $searchPage->ID;
                    MPHB()->settings()->pages()->setSearchResultsPage($searchPageID);
                }
            }
        }
        // Checkout Page
        if(!MPHB()->settings()->pages()->getCheckoutPageId()){
            if ( $checkoutPage = get_page_by_path( 'booking-confirmation', OBJECT, 'page' ) ) {
                if (is_object($checkoutPage) && (count(get_object_vars($checkoutPage)) > 0)) {
                    $checkoutPageID = $checkoutPage->ID;
                    MPHB()->settings()->pages()->setCheckoutPage($checkoutPageID);
                }
            }
        }
        // Terms & Conditions
        // Booking Confirmed Page
        if(!MPHB()->settings()->pages()->getBookingConfirmedPageId()){
            if ( $bookingConfirmedPage = get_page_by_path( 'booking-confirmation/booking-confirmed', OBJECT, 'page' ) ) {
                if (is_object($bookingConfirmedPage) && (count(get_object_vars($bookingConfirmedPage)) > 0)) {
                    $bookingConfirmedPageeID = $bookingConfirmedPage->ID;
                    MPHB()->settings()->pages()->setBookingConfirmPage($bookingConfirmedPageeID);
                }
            }
        }
        // Cancellation Page
        if(!MPHB()->settings()->pages()->getUserCancelRedirectPageId()){
            if ( $userCancelPage = get_page_by_path( 'booking-confirmation/booking-canceled', OBJECT, 'page' ) ) {
                if (is_object($userCancelPage) && (count(get_object_vars($userCancelPage)) > 0)) {
                    $userCancelPageID = $userCancelPage->ID;
                    MPHB()->settings()->pages()->setUserCancelRedirectPage($userCancelPageID);
                }
            }
        }
        // Reservation Received Page
        if(!MPHB()->settings()->pages()->getReservationReceivedPageId()){
            if ( $reservationReceivedPage = get_page_by_path( 'booking-confirmation/payment-success', OBJECT, 'page' ) ) {
                if (is_object($reservationReceivedPage) && (count(get_object_vars($reservationReceivedPage)) > 0)) {
                    $reservationReceivedPageID = $reservationReceivedPage->ID;
                    MPHB()->settings()->pages()->setPaymentSuccessPage($reservationReceivedPageID);
                }
            }
        }
        // Failed Transaction Page
        if(!MPHB()->settings()->pages()->getPaymentFailedPageId()){
            if ( $failedPaymentPage = get_page_by_path( 'booking-confirmation/transaction-failed', OBJECT, 'page' ) ) {
                if (is_object($failedPaymentPage) && (count(get_object_vars($failedPaymentPage)) > 0)) {
                    $failedPaymentPageID = $failedPaymentPage->ID;
                    MPHB()->settings()->pages()->setPaymentFailedPage($failedPaymentPageID);
                }
            }
        }
    }
    update_option('themo_mphb_page_settings', true); // Only run this once.
}

/*
 * MotoPress - SEARCH RESULTS & WIDGET
 * Hooks for Wrappers
 */

// Search results Recommendation Wrap
function themo_mphb_search_recommend_before( $foo ){
    echo '<div class="themo_mphb_search_recommend_wrapper">';
}
add_action( 'mphb_sc_search_results_recommendation_before', 'themo_mphb_search_recommend_before' );

function themo_mphb_search_recommend_after( $foo){
    echo '</div>';
}
add_action( 'mphb_sc_search_results_recommendation_after', 'themo_mphb_search_recommend_after' );

// Search Results Cart Wrap
function themo_mphb_search_cart_before( $foo ){
    echo '<div class="themo_mphb_search_cart_wrapper">';
}
add_action( 'mphb_sc_search_results_reservation_cart_before', 'themo_mphb_search_cart_before' );

function themo_mphb_search_cart_after( $foo){
    echo '</div>';
}
add_action( 'mphb_sc_search_results_reservation_cart_after', 'themo_mphb_search_cart_after' );

/*
 * MotoPress - ADMIN & NAG NOTICES
 */

// Reset
// update_option( 'themo_mphb_admin_notice_dismissed', false );

// ajax call from admin-scripts.js
add_action( 'wp_ajax_themo_admin_notice_dismissed', 'themo_dismiss_admin_notice' );

function themo_dismiss_admin_notice() {
    echo "Processing Ajax request...";
    update_option( 'themo_mphb_admin_notice_dismissed', true );
    wp_die();
}

// If dismissed option is set, don't display admin notice.
$themo_mphb_admin_notice_dismissed = get_option( 'themo_mphb_admin_notice_dismissed' );
if(!$themo_mphb_admin_notice_dismissed){
    add_action( 'admin_notices', 'th_admin_notice_mphb_theme_mode' );
}

function th_admin_notice_mphb_theme_mode() {
    //getTemplateMode
    if ( class_exists( 'HotelBookingPlugin' ) ) {
        // Check for our MPHB custom post types are supported in Elementor settings.
        $themo_mpb_nag_notice = false;
        if(defined('ELEMENTOR_PATH')){
            $elementor_cpt_support = get_option('elementor_cpt_support');
            if (!empty($elementor_cpt_support)) {
                if (!in_array("mphb_room_type", $elementor_cpt_support) || !in_array("mphb_room_service", $elementor_cpt_support)) {
                    $themo_mpb_nag_notice = sprintf( wp_kses_post( __( '<span>Check \'Accommodation Types\' and \'Services\' Post Types options inside %1$s.</span>', 'bellevue' )), '<a href="'.admin_url( 'admin.php?page=elementor#tab-general').'">' . esc_html__( 'Elementor / Settings / General ', 'bellevue' ) .'</a>' );

                }
            }
        }
        $themo_mphb_template_mode = get_option( 'mphb_template_mode');
        if('plugin' !== $themo_mphb_template_mode) {
            $themo_mpb_nag_notice .= sprintf( wp_kses_post( __( '<span>Set \'Template Mode\' to \'Developer Mode\' inside the %1$s.</span>', 'bellevue' )), '<a href="'.admin_url( 'edit.php?post_type=mphb_room_type&page=mphb_settings#mphb-mphb_enable_coupons').'">' . esc_html__( 'MotoPress Hotel Booking settings', 'bellevue' ) .'</a>' );

        }
        if(!empty($themo_mpb_nag_notice)){
            $themo_mpb_nag_notice_header = '<span><strong>MotoPress Hotel Booking setup</strong></span>';
            ?>
            <div class="notice-warning settings-error notice is-dismissible themo-notice-warning">
                <?php echo $themo_mpb_nag_notice_header .$themo_mpb_nag_notice; ?>
            </div>
            <?php

        }
    }
}

/**
 * Filter whether comments are open for a given post type.
 *
 * @param string $status       Default status for the given post type,
 *                             either 'open' or 'closed'.
 * @param string $post_type    Post type. Default is `post`.
 * @param string $comment_type Type of comment. Default is `comment`.
 * @return string (Maybe) filtered default status for the given post type.
 */

function wpdocs_open_comments_for_myposttype( $status, $post_type, $comment_type ) {
    if ( 'mphb_room_service' !== $post_type && 'themo_room' !== $post_type
        && 'mphb_room_type' !== $post_type ) {
        return $status;
    }

    // You could be more specific here for different comment types if desired
    return 'closed';
}
add_filter( 'get_default_comment_status', 'wpdocs_open_comments_for_myposttype', 10, 3 );

/********************************
WooCommerce Extras
 ********************************/
/*
WooCommerce Booking Support
WooCommerce Custom Booking Fields to checkout.
Comment these actions and filters if of you wish to use.

TO ACTIVATE UNCOMMENT the add_action lines at the bottom of this file (Scroll Down).
*/


// If woocommerce enabled then ensure shortcodes are respected inside our html metaboxes.
if(!function_exists('wdm_enque_scripts')) {
    function wdm_enque_scripts(){
        wp_register_script('th_addtocart', get_template_directory_uri() . '/assets/js/th_single_product_page.js', array('jquery'), '1', true);
        wp_enqueue_script('th_addtocart');
        $array_to_be_sent = array('ajaxurl' => admin_url('admin-ajax.php'));
        wp_localize_script('th_addtocart', 'th_ajax', $array_to_be_sent);
    }
}



if(!function_exists('wdm_get_cart_items_from_session'))
{
    function wdm_get_cart_items_from_session($item,$values,$key)
    {

        if (array_key_exists( 'custom_data_1', $values ) && $values['custom_data_1'] > "")
        {
            $item['custom_data_1'] = $values['custom_data_1'];
        }

        if (array_key_exists( 'custom_data_2', $values )  && $values['custom_data_2'] > "")
        {
            $item['custom_data_2'] = $values['custom_data_2'];
        }

        if (array_key_exists( 'custom_data_3', $values )  && $values['custom_data_3'] > "")
        {
            $item['custom_data_3'] = $values['custom_data_3'];
        }

        if (array_key_exists( 'custom_data_4', $values )  && $values['custom_data_4'] > "")
        {
            $item['custom_data_4'] = $values['custom_data_4'];
        }

        if (array_key_exists( 'custom_data_5', $values )  && $values['custom_data_5'] > "")
        {
            $item['custom_data_5'] = $values['custom_data_5'];
        }

        return $item;
    }
}


// step 4



if(!function_exists('wdm_add_user_custom_option_from_session_into_cart'))
{
    function wdm_add_user_custom_option_from_session_into_cart($product_name, $values, $cart_item_key )
    {
        /*code to add custom data on Cart & checkout Page*/
        if(isset($values['custom_data_1']) && count($values['custom_data_1']) > 0)
        {
            $return_string = $product_name . "</a><dl class='variation'>";
            $return_string .= "<table class='wdm_options_table' id='" . $values['product_id'] . "'>";
            $return_string .= "<tr><td>".esc_html__('Check-in', 'BELLEVUE')." : " . $values['custom_data_1'] . "</td></tr>";
            if(isset($values['custom_data_2']) && $values['custom_data_2'] != 'false') {
                $return_string .= "<tr><td>" . esc_html__('Check-out', 'BELLEVUE') . " : " . $values['custom_data_2'] . "</td></tr>";
            }
            if(isset($values['custom_data_3']) && $values['custom_data_3'] != 'false') {
                $return_string .= "<tr><td>" . esc_html__('Custom 3', 'BELLEVUE') . " : " . $values['custom_data_3'] . "</td></tr>";
            }
            if(isset($values['custom_data_4']) && $values['custom_data_4'] != 'false') {
                $return_string .= "<tr><td>" . esc_html__('Custom 4', 'BELLEVUE') . " : " . $values['custom_data_4'] . "</td></tr>";
            }
            if(isset($values['custom_data_5']) && $values['custom_data_5'] != 'false') {
                $return_string .= "<tr><td>" . esc_html__('Custom 5', 'BELLEVUE') . " : " . $values['custom_data_5'] . "</td></tr>";
            }
            $return_string .= "</table></dl>";

            return $return_string;
        }
        else
        {
            return $product_name;
        }
    }
}


// step 5


if(!function_exists('wdm_add_values_to_order_item_meta'))
{
    function wdm_add_values_to_order_item_meta($item_id, $values)
    {
        global $woocommerce,$wpdb;


        $custom_data_1 = $values['custom_data_1'];

        if(!empty($custom_data_1) && $custom_data_1 > '')
        {
            wc_add_order_item_meta($item_id,'Check-in',$custom_data_1);
        }


        $custom_data_2 = $values['custom_data_2'];

        if(!empty($custom_data_2)  && $custom_data_2 > '')
        {
            wc_add_order_item_meta($item_id,'Check-out',$custom_data_2);
        }

        $custom_data_3 = $values['custom_data_3'];

        if(!empty($custom_data_3)  && $custom_data_3 > '')
        {
            wc_add_order_item_meta($item_id,'custom_data_3',$custom_data_3);
        }

        $custom_data_4 = $values['custom_data_4'];

        if(!empty($custom_data_4)  && $custom_data_4 > '')
        {
            wc_add_order_item_meta($item_id,'custom_data_4',$custom_data_4);
        }

        $custom_data_5 = $values['custom_data_5'];

        if(!empty($custom_data_5)  && $custom_data_5 > '')
        {
            wc_add_order_item_meta($item_id,'custom_data_5',$custom_data_5);
        }
    }
}


// step 6


if(!function_exists('wdm_remove_user_custom_data_options_from_cart'))
{
    function wdm_remove_user_custom_data_options_from_cart($cart_item_key)
    {
        global $woocommerce;
        // Get cart
        $cart = $woocommerce->cart->get_cart();
        // For each item in cart, if item is upsell of deleted product, delete it
        foreach( $cart as $key => $values)
        {
            if ( $values['wdm_user_custom_data_value'] == $cart_item_key )
                unset( $woocommerce->cart->cart_contents[ $key ] );
        }
    }
}

// UNCOMMENT ALL OF THE LINKS BELOW
// This will provide sample code for showing WP Booking fields in the Woo Cart.


/*
if ( class_exists( 'woocommerce' ) ) {
    add_action( 'wp_enqueue_scripts', 'wdm_enque_scripts' );
    add_action('wp_ajax_nopriv_wdm_add_user_custom_data_options', 'wdm_add_user_custom_data_options_callback');
    add_filter('woocommerce_get_cart_item_from_session', 'wdm_get_cart_items_from_session', 1, 3 );
    add_filter('woocommerce_checkout_cart_item_quantity','wdm_add_user_custom_option_from_session_into_cart',1,3);
    add_filter('woocommerce_cart_item_price','wdm_add_user_custom_option_from_session_into_cart',1,3);
    add_action('woocommerce_add_order_item_meta','wdm_add_values_to_order_item_meta',1,2);
    add_action('woocommerce_before_cart_item_quantity_zero','wdm_remove_user_custom_data_options_from_cart',1,1);
}*/



// REMOVE OPTION TREE Theme Options Links

function th_remove_admin_bar_links() {
    global $wp_admin_bar, $current_user;

    $wp_admin_bar->remove_menu('ot-theme-options');          // Remove the updates link

    if ($current_user->ID != 1) {

    }
}
add_action( 'wp_before_admin_bar_render', 'th_remove_admin_bar_links' );


// WooCommerce Actions
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 11 );

function themo_is_yoast_seo_active() {
    return defined( 'WPSEO_VERSION' ) || defined( 'WPSEO_FILE' ) || class_exists( 'WPSEO_Options' );
}

function themo_disable_woocommerce_breadcrumblist_schema_when_yoast_active( $markup ) {
    if ( themo_is_yoast_seo_active() ) {
        return array();
    }

    return $markup;
}
add_filter( 'woocommerce_structured_data_breadcrumblist', 'themo_disable_woocommerce_breadcrumblist_schema_when_yoast_active', 10, 1 );

function themo_remove_woocommerce_breadcrumblist_schema_type_when_yoast_active( $types ) {
    if ( themo_is_yoast_seo_active() ) {
        return array_values( array_diff( $types, array( 'breadcrumblist' ) ) );
    }

    return $types;
}
add_filter( 'woocommerce_structured_data_type_for_page', 'themo_remove_woocommerce_breadcrumblist_schema_type_when_yoast_active', 10, 1 );

// Hide Shop Title
function th_filter_woocommerce_show_page_title( $bool )
{
    // make filter magic happen here...
    return false;
};

// add the filter
add_filter( 'woocommerce_show_page_title', 'th_filter_woocommerce_show_page_title', 10, 1 );

//Exclude AddThis widgets from anything other than posts
add_filter('addthis_post_exclude', 'themo_addthis_post_exclude');
function themo_addthis_post_exclude($display) {
    return false;
    echo 'HELLO';
    if ( !is_singular( 'post' ) )
        $display = false;
    return $display;
}


//-----------------------------------------------------
// themo_search_meta - filter
// Enhance Search to include Meta Boxes
//-----------------------------------------------------
add_filter('posts_search', 'themo_search_function', 10, 2);
function themo_search_function($search, $query) {
    global $wpdb, $pagenow;
    if(!$query->is_main_query() || !$query->is_search || $pagenow=='post.php'){
        return($search); //determine if we are modifying the right query
    }


    $search_term = $query->get('s'); // Get Search Terms
    $search = ' AND (';

    // Query Content
    $search .=  $wpdb->prepare("($wpdb->posts.post_content LIKE '%%%s%%')",$wpdb->esc_like( $search_term ));

    // add an OR between search conditions
    $search .= " OR ";

    // Query Title
    $search .=  $wpdb->prepare("($wpdb->posts.post_title LIKE '%%%s%%')",$wpdb->esc_like( $search_term ));

    // add an OR between search conditions
    $search .= " OR ";

    // Sub Query Custom Meta Boxes
    $search .=  $wpdb->prepare("( $wpdb->posts.ID IN (SELECT DISTINCT $wpdb->postmeta.post_id FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key like 'themo_%%' AND $wpdb->postmeta.meta_value LIKE '%%%s%%'))",$wpdb->esc_like( $search_term ));

    // add the filter to join tables if needed.
    // add_filter('posts_join', 'join_tables');
    return $search . ') ';
}

//-----------------------------------------------------
// themo_ajax_loader - filter
// Replace the Contact Form 7 Ajax Loading Image with our Own
//-----------------------------------------------------
if ( function_exists( 'wpcf7_ajax_loader' ) ) {
    add_filter( 'wpcf7_ajax_loader', 'themo_wap8_wpcf7_ajax_loader' );

    function themo_wap8_wpcf7_ajax_loader() {
        $url = "asdfa"; //get_template_directory_uri() . '/images/ajax-loader.gif';
        return $url;
    }
}

//-----------------------------------------------------
// activate_formidable/formidable.php - Filter
// When the formidable plugin is active set an option to
// print an admin message
//-----------------------------------------------------

add_action('activate_formidable/formidable.php', 'themo_formidable_set_notice');
function themo_formidable_set_notice() {
    add_option('formidable_do_activation_message', true);
}


/*
 * Change Meta Box visibility according to Page Template
 *
 * Observation: this example swaps the Featured Image meta box visibility
 *
 * Usage:
 * - adjust $('#postimagediv') to your meta box
 * - change 'page-portfolio.php' to your template's filename
 * - remove the console.log outputs
 */

add_action('admin_head', 'themo_wpse_50092_script_enqueuer');

function themo_wpse_50092_script_enqueuer() {
    global $current_screen;
    if(isset($current_screen->id) && 'page' != $current_screen->id) return;

    $iswooshoppage = 0;
    // Find out the shop page id for woo and hide the meta box builder.
    if(th_is_woocommerce_activated()){
        $post_ID = get_the_ID();
        $shop_page_id = wc_get_page_id( 'shop' );


        if(isset($post_ID) && isset($shop_page_id) && $post_ID == $shop_page_id){
            $iswooshoppage = 1;
        }
    }

    echo <<<HTML
        <script type="text/javascript">
        jQuery(document).ready( function($) {
		"use strict";
        var excludeTemplates = [ "templates/portfolio-standard.php","templates/blog-masonry.php","templates/blog-masonry-wide.php","templates/blog-standard.php","templates/blog-category-masonry.php"];
        var currentTemplate = $('#page_template').val();
        var excludeFound = $.inArray(currentTemplate, excludeTemplates);
            /**
             * Adjust visibility of the meta box at startup
            */
            if($iswooshoppage) {
                $('#themo_meta_box_builder_meta_box').hide();
            }
            if( excludeFound !== -1 && !excludeFound > -1) {
                // hide your meta box
                $('#themo_meta_box_builder_meta_box').hide();
                $('#themo_blog_category_meta_box').show();
            } else {
                // show the meta box
                $('#themo_meta_box_builder_meta_box').show();
                $('#themo_tour_options_meta_box').hide();
                $('#themo_blog_category_meta_box').hide();
            }
            if( currentTemplate ==  "templates/portfolio-standard.php") {
            	$('#themo_tour_options_meta_box').show();
            }else{
            	$('#themo_tour_options_meta_box').hide();
            }



            // Debug only
            // - outputs the template filename
            // - checking for console existance to avoid js errors in non-compliant browsers
            /*
            if (typeof console == "object")
                console.log ('default value = ' + $('#page_template').val());
                */

            /**
             * Live adjustment of the meta box visibility
            */
            $('.postbox').on('change','#page_template', function(){
                var currentTemplate = $(this).val();
                var excludeFound = $.inArray(currentTemplate, excludeTemplates);

                if( excludeFound !== -1 && !excludeFound > -1) {
                     // hide your meta box
                    $('#themo_meta_box_builder_meta_box').hide();
					$('#themo_blog_category_meta_box').show();
					//$('#themo_tour_options_meta_box').show();
                } else {
                    // show the meta box
                    $('#themo_meta_box_builder_meta_box').show();
                    //$('#themo_tour_options_meta_box').hide();
					$('#themo_blog_category_meta_box').hide();
                }

                if( currentTemplate ==  "templates/portfolio-standard.php") {
					$('#themo_tour_options_meta_box').show();
					$('#themo_blog_category_meta_box').hide();
				}else{
					$('#themo_tour_options_meta_box').hide();
				}

                // Debug only
               /* if (typeof console == "object")
                    console.log ('live change value = ' + $(this).val()); */
            });
        });
        </script>
HTML;
}



//======================================================================
// Metabox Plugin Functions
//======================================================================
// Remove BR tag from checkbox list output.
add_filter('rwmb_themo_meta_box_builder_meta_boxes_html','themo_test');
function themo_test($html){
    return strip_tags($html,'<label><input>');
}

//-----------------------------------------------------
// print_google_font_link from OT settings.
// Print Google Font link tag for inline styling.
//-----------------------------------------------------
function themo_print_google_font_link(){

    // check for custom google fonts, add them.
    if ( function_exists( 'get_theme_mod' ) ) {

        /* get the slider array */
        $google_fonts = get_theme_mod( 'themo_google_fonts', array() );

        if ( ! empty( $google_fonts ) ) {
            foreach( $google_fonts as $google_font ) {
                //$google_font_family = $google_font["themo_google_font_family"];
                if($google_font["themo_google_font_url"] > ""){
                    ?>
                    <!-- GOOGLE FONTS -->
                    <link href='<?php echo esc_url($google_font["themo_google_font_url"]); ?>' rel='stylesheet' type='text/css'>
                    <?php
                }
            }
        }
    }
}


//======================================================================
// 500 - Core / Special Functions
//======================================================================


//======================================================================
// CATEGORY LARGE FONT
//======================================================================

//-----------------------------------------------------
// Sub-Category Smaller Font
//-----------------------------------------------------

/* Title Here Notice the First Letters are Capitalized note from from WIN */

# Option 1
# Option 2
# Option 3

/*
 * This is a detailed explanation
 * of something that should require
 * several paragraphs of information.
 */

// This is a single line quote.
