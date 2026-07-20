<?php
/**
 * _s Theme Customizer.
 *
 * @package _s
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function _s_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
//add_action( 'customize_register', '_s_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function _s_customize_preview_js() {
	wp_enqueue_script( '_s_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
//add_action( 'customize_preview_init', '_s_customize_preview_js' );


// Add the theme configuration
Bellevue_Kirki::add_config( 'bellevue_theme', array(
    'capability'    => 'edit_theme_options',
    'option_type'   => 'theme_mod',
) );

// Create a Panel for our theme options.
Bellevue_Kirki::add_panel( 'th_options', array(
    'priority'    => 10,
    'title'       => __( 'Theme Options', 'bellevue' ),
    'description' => __( 'My Description', 'bellevue' ),
) );

// HEADER & FOOTER SECTION

//remove_theme_mod( 'thmv_standard_header_switch');

$thmv_standard_header = 'on'; // Default to on for backwards compatibility.

// Turn off Standard for new versions.
if(thmv_get_theme_version() >= THMV_MIN_HFE_THEME_VERSION){
    // If upgrading from a previous theme version, enable show standard options.
    $thmv_previous_theme_version = thmv_get_template_first_install_version();
    if($thmv_previous_theme_version > 0 && $thmv_previous_theme_version >= thmv_get_theme_version()){
        $thmv_standard_header = '';
    }
}

Bellevue_Kirki::add_section( 'header_footer', array(
    'title'      => esc_attr__( 'Header & Footer', 'bellevue' ),
    'priority'   => 2,
    'panel'          => 'th_options',
    'capability' => 'edit_theme_options',
) );


// HEADER & FOOTER SECTION - HFB Notice
Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'thmv_hfb_notice',
    'label'       => esc_html__( 'Global Templates', 'bellevue' ),
    'section'     => 'header_footer',
    'default' => '<div class="th-theme-support">'.esc_html__('You can now build your header and footer in Elementor. Go to Dashboard > Bellevue > Global Templates.', 'bellevue').'</div><div class="th-theme-support">' . sprintf(__('<p class=""> <a href="%1$s" target="_blank">Learn more</p>', 'bellevue'), 'https://help.bellevuetheme.com/article/65-header-footer') . '</div>',
    'priority'    => 10,

] );

// HEADER & FOOTER SECTION - Standard Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'thmv_standard_header_switch',
    'label'       => esc_html__( 'Standard Header & Footer', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => $thmv_standard_header,
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Showing Settings', 'bellevue' ),
        'off' => esc_attr__( 'Hiding Settings', 'bellevue' ),
    ),
) );

// HEADER & FOOTER SECTION - Standard Header Switch Callback
$thmv_standard_header_switch_callback = array(
        array(
            'setting'  => 'thmv_standard_header_switch',
            'operator' => '==',
            'value'    => true,
        ),
        array(
            'setting'  => 'thmv_standard_header_switch',
            'operator' => '==',
            'value'    => 'on',
        )
);


Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'thmv_standard_header_notice',
    'section'     => 'header_footer',
    'default' => '<div class="th-theme-support">'.esc_html__('Applies to standard header & footer only.', 'bellevue').'</div><div class="th-theme-support">' . sprintf(__('<p class=""> <a href="%1$s" target="_blank">Learn more</p>', 'bellevue'), 'https://help.bellevuetheme.com/article/65-header-footer#standard-header-footer') . '</div>',
    'priority'    => 10,
    'active_callback'    => array(
        array(
            $thmv_standard_header_switch_callback
        )
    ),
] );



// HEADER & FOOTER SECTION - Logo
Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'thmv_customizer_header_logo',
    'section'     => 'header_footer',
    'default'     => '<div class="thmv-customizer-heading">' . __('Logo', 'bellevue') . '</div><div class="thmv-customizer-divider"><span></span></div>',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
] );


// HEADER & FOOTER SECTION - Logo - Enable Retina Support.
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_retinajs_logo',
    'label'       => esc_html__( 'High-resolution/Retina Logo Support', 'bellevue' ),
    'description' => esc_html__( 'Automatically serve up your high-resolution logo to devices that support them.', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Logo - Height
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'number',
    'settings'    => 'themo_logo_height',
    'label'       => esc_html__( 'Logo Height', 'bellevue' ),
    'description' => esc_html__( 'Set height and then \'Publish\' BEFORE uploading your logo.', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 100,
    'choices'     => array(
        'min'  => '10',
        'max'  => '300',
        'step' => '1',
    ),
    'output' => array(
        array(
            'element'  => '#logo img',
            'property' => 'max-height',
            'units'    => 'px',
        ),
        array(
            'element'  => '#logo img',
            'property' => 'width',
            'value_pattern' => 'auto'
        ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'themo_logo_resize_help',
    'label'       => esc_html__('To increase logo size', 'bellevue'),
    'section'     => 'header_footer',
    'default'     => '<div class="th-theme-support">' . __('Set height, publish, remove and re-select your logos.', 'bellevue') . '</div>',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
] );

// HEADER & FOOTER SECTION - Logo - Logo Image
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'image',
    'settings'    => 'themo_logo',
    'label'       => esc_html__( 'Logo', 'bellevue' ),
    'description' => esc_html__( 'For retina support, upload a logo that is twice the height set above.', 'bellevue' ) ,
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Logo - Transparent Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_logo_transparent_header_enable',
    'label'       => esc_html__( 'Alternative logo', 'bellevue' ),
    'description'       => esc_html__( 'Used as an option for transparency header', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Logo - Transparent Logo
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'image',
    'settings'    => 'themo_logo_transparent_header',
    'label'       => esc_html__( 'Alternative logo upload', 'bellevue' ),
    'description' => esc_html__( 'For retina support, upload a logo that is twice the height set above.', 'bellevue' ) ,
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_logo_transparent_header_enable',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_logo_transparent_header_enable',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback,
    ),
) );


// HEADER & FOOTER SECTION - Menu
Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'thmv_customizer_header_menu',
    'section'     => 'header_footer',
    'default'     => '<div class="thmv-customizer-heading">' . __('Menu', 'bellevue') . '</div><div class="thmv-customizer-divider"><span></span></div>',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
] );

// HEADER & FOOTER SECTION - Menu - Top Menu Margin

Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'number',
    'settings'    => 'themo_nav_top_margin',
    'label'       => esc_html__( 'Navigation Top Margin', 'bellevue' ),
    'description' => esc_html__( 'Set top margin value for the navigation bar', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 19,
    'choices'     => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
    ),
    'output' => array(
        array(
            'element'  => '.navbar .navbar-nav',
            'property' => 'margin-top',
            'units'    => 'px',
        ),
        array(
            'element'  => '.navbar .navbar-toggle',
            'property' => 'top',
            'units'    => 'px',
        ),
        array(
            'element'  => '.themo_cart_icon',
            'property' => 'margin-top',
            'value_pattern' => 'calc($px + 10px)'
        ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Menu - Menu Text
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'typography',
    'settings'    => 'header_footer',
    'label'       => esc_attr__( 'Menu Typography', 'bellevue' ),
    'description' => esc_attr__( 'Select the typography options for your Menu.', 'bellevue' ),
    'help'        => esc_attr__( 'The typography options you set here will override the Typography options for the main menu on your site.', 'bellevue' ),
    'section'     => 'header_footer',
    'priority'    => 10,
    'default'     => array(
        'font-family'    => 'Lato',
        'variant'        => 'regular',
        'font-size'      => '15px',
        'color'          => '#333333',
    ),
    'output' => array(
        array(
            'element' => array( '.navbar .navbar-nav > li > a, .navbar .navbar-nav > li > a:hover, .navbar .navbar-nav > li.active > a, .navbar .navbar-nav > li.active > a:hover, .navbar .navbar-nav > li.active > a:focus,.banner[data-transparent-header="true"].headhesive--clone .navbar-nav > li > a, .navbar .navbar-nav > li.th-accent' ),
        ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// Menu : Dropdown Style
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'radio',
    'settings'    => 'themo_dropdown_style',
    'label'       => esc_html__( 'Dropdown Style', 'uplands' ),
    'section'     => 'header_footer',
    'default'     => 'dark',
    'priority'    => 10,
    'choices'     => array(
        'dark'  => esc_attr__( 'Dark', 'uplands' ),
        'light' => esc_attr__( 'Light', 'uplands' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Header
Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'thmv_customizer_header_header',
    'section'     => 'header_footer',
    'default'     => '<div class="thmv-customizer-heading">' . __('Header', 'bellevue') . '</div><div class="thmv-customizer-divider"><span></span></div>',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
] );

// HEADER & FOOTER SECTION - Header - Enable Dark Header
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'radio',
    'settings'    => 'themo_header_style',
    'label'       => esc_html__( 'Style Header', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'dark',
    'priority'    => 10,
    'choices'     => array(
        'dark'  => esc_attr__( 'Dark', 'bellevue' ),
        'light' => esc_attr__( 'Light', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );



// Menu : Social Icno Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_nav_social_switch',
    'label'       => esc_html__( 'Social Icons', 'uplands' ),
    'section'     => 'header_footer',
    'description' => '<div class="th-theme-support">' . __('Add icons under Appearance / Customize / Theme Options / Widgets', 'bellevue') . '</div>',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'uplands' ),
        'off' => esc_attr__( 'Disable', 'uplands' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// Menu : Widget Title Underland
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'transparent_header_border_color_switch',
    'label'       => esc_html__( 'Transparent Header Border', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'output' => array(
        array(
            'element'  => '.navbar-default[data-transparent-header="true"]',
            'property' => 'border-bottom',
            'value_pattern' => '1px solid',
            'exclude' => array( false ),
        ),

        //
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),

) );

// Menu : Header Border
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'transparent_header_border_color',
    'label'       => esc_attr__( 'Border Color', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'rgba(255,255,255,.3)',
    'priority'    => 10,
    'choices'     => array(
        'alpha' => true,
    ),
    'output' => array(

        array(
            'element'  => '.navbar-default[data-transparent-header="true"]',
            'property' => 'border-color',
        ),

    ),
    //padding-bottom: 20px
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'transparent_header_border_color_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'transparent_header_border_color_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback,
    ),
) );

// HEADER & FOOTER SECTION - Header - Sticky Header
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_sticky_header',
    'label'       => esc_html__( 'Sticky Header', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Header - Top Nav Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_top_nav_switch',
    'label'       => esc_html__( 'Top Bar', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Header - Top Nav Text
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'     => 'textarea',
    'settings' => 'themo_top_nav_text',
    'label'    => esc_html__( 'Top Bar Text', 'bellevue' ),
    'section'  => 'header_footer',
    'default'  => esc_attr__( 'Welcome', 'bellevue' ),
    'priority' => 10,
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_top_nav_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_top_nav_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback,
    ),
) );

// HEADER & FOOTER SECTION - Header - Icon Block
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'repeater',
    'label'       => esc_attr__( 'Top Bar Icons', 'bellevue' ),
    'description' => esc_html__( 'Use any', 'bellevue' ). ' <a href="http://fontawesome.io/icons/" target="_blank">Font Awesome</a> icon (e.g.: fa fa-twitter). <a href="http://fontawesome.io/icons/" target="_blank">'.esc_html__( 'Full List Here', 'bellevue' ).'</a>',
    'section'     => 'header_footer',
    'priority'    => 10,
    'row_label' => array(
        'type' => 'text',
        'value' => esc_attr__('Icon Block', 'bellevue' ),
    ),
    'settings'    => 'themo_top_nav_icon_blocks',
    'default'     => array(
        array(
            'title' => esc_attr__( 'Contact Us', 'bellevue' ),
            'themo_top_nav_icon'  => 'fa fa-envelope-open-o',
            'themo_top_nav_icon_url'  => 'mailto:contact@themovation.com',
            'themo_top_nav_icon_url_target'  => '',
        ),
        array(
            'title' => esc_attr__( 'How to Find Us', 'bellevue' ),
            'themo_top_nav_icon'  => 'fa fa-map-o',
            'themo_top_nav_icon_url'  => '#',
            'themo_top_nav_icon_url_target'  => '',
        ),
        array(
            'title' => esc_attr__( '250-555-5555', 'bellevue' ),
            'themo_top_nav_icon'  => 'fa fa-mobile',
            'themo_top_nav_icon_url'  => 'tel:250-555-5555',
            'themo_top_nav_icon_url_target'  => '',
        ),
        array(
            'themo_top_nav_icon'  => 'fa fa-twitter',
            'themo_top_nav_icon_url'  => 'http://twitter.com',
            'themo_top_nav_icon_url_target'  => '1',
        ),
    ),
    'fields' => array(
        'title' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Link Text', 'bellevue' ),
            'default'     => '',
        ),
        'themo_top_nav_icon' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Icon', 'bellevue' ),
            'default'     => '',
        ),
        'themo_top_nav_icon_url' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Link URL', 'bellevue' ),
            'default'     => '',
        ),
        'themo_top_nav_icon_url_target' => array(
            'type'        => 'checkbox',
            'label'       => esc_attr__( 'Open Link in New Window', 'bellevue' ),
            'default'     => '',
        ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_top_nav_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_top_nav_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback,
    ),
) );

if(th_is_woocommerce_activated()) {
    // HEADER & FOOTER SECTION - Header - Cart
    Bellevue_Kirki::add_field( 'bellevue_theme', [
        'type'        => 'custom',
        'settings'    => 'thmv_customizer_header_cart',
        'section'     => 'header_footer',
        'default'     => '<div class="thmv-customizer-heading">' . __('Cart', 'bellevue') . '</div><div class="thmv-customizer-divider"><span></span></div>',
        'priority'    => 10,
        'active_callback'    => array(
            $thmv_standard_header_switch_callback
        ),
    ] );

    // HEADER & FOOTER SECTION - Header - Cart Switch
    Bellevue_Kirki::add_field( 'bellevue_theme', array(
        'type'        => 'switch',
        'settings'    => 'themo_woo_show_cart_icon',
        'label'       => esc_html__( 'Show Cart Icon', 'bellevue' ),
        'description' => __( 'Show / Hide shopping cart icon in header', 'bellevue' ),
        'section'     => 'header_footer',
        'default'     => 'on',
        'priority'    => 10,
        'choices'     => array(
            'on'  => esc_attr__( 'Enable', 'bellevue' ),
            'off' => esc_attr__( 'Disable', 'bellevue' ),
        ),
        'active_callback'    => array(
            $thmv_standard_header_switch_callback
        ),
    ) );

    // Woo. : Disable Quantity from cart
    Bellevue_Kirki::add_field('bellevue_theme', array(
        'type' => 'switch',
        'settings' => 'themo_disable_cart_qty',
        'label' => esc_html__('Hide Cart Icon Counter', 'bellevue'),
        'section' => 'header_footer',
        'default' => 'on',
        'priority' => 10,
        'choices' => array(
            'on' => esc_attr__('Enable', 'bellevue'),
            'off' => esc_attr__('Disable', 'bellevue'),
        ),
        'output' => array(
            array(
                'element' => 'span.themo_cart_item_count',
                'property' => 'display',
                'value_pattern' => 'none',
                'exclude' => array(false)
            ),

        ),
        'active_callback'    => array(
            array(
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => true,
                ),
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => 'on',
                )
            ),
            $thmv_standard_header_switch_callback,
        ),
    ));

    // Woo. : Hide Quantity from cart
    Bellevue_Kirki::add_field('bellevue_theme', array(
        'type' => 'switch',
        'settings' => 'themo_hide_cart_qty',
        'label' => esc_html__('Remove Item Quantity from Shopping Cart', 'bellevue'),
        'section' => 'header_footer',
        'default' => '',
        'priority' => 10,
        'choices' => array(
            'on' => esc_attr__('Enable', 'bellevue'),
            'off' => esc_attr__('Disable', 'bellevue'),
        ),
        'output' => array(
            array(
                'element' => '.woocommerce-cart td.product-quantity, .woocommerce-cart th.product-quantity',
                'property' => 'display',
                'value_pattern' => 'none',
                'exclude' => array(false)
            ),
            array( /* Remove controls from Safari and Chrome */
                'element' => '.woocommerce-cart td.product-quantity input[type=number]',
                'property' => '-moz-appearance:textfield; pointer-events:none;',
                'value_pattern' => 'none',
                'exclude' => array(false)
            ),
            array( /* Remove controls from Safari and Chrome */
                'element' => '.woocommerce-cart td.product-quantity input[type=number]::-webkit-inner-spin-button, .woocommerce-cart td.product-quantity input[type=number]::-webkit-outer-spin-button ',
                'property' => '-webkit-appearance: none; -moz-appearance: none; appearance: none; margin: 0;',
                'value_pattern' => 'none',
                'exclude' => array(false)
            ),
        ),
        'active_callback'    => array(
            array(
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => true,
                ),
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => 'on',
                )
            ),
            $thmv_standard_header_switch_callback,
        ),
    ));

    // Woo. : Hide Quantity from checkout
    Bellevue_Kirki::add_field('bellevue_theme', array(
        'type' => 'switch',
        'settings' => 'themo_hide_checkout_qty',
        'label' => esc_html__('Remove Item Quantity from Checkout', 'bellevue'),
        'section' => 'header_footer',
        'default' => '',
        'priority' => 10,
        'choices' => array(
            'on' => esc_attr__('Enable', 'bellevue'),
            'off' => esc_attr__('Disable', 'bellevue'),
        ),
        'output' => array(
            array(
                'element' => '.woocommerce-checkout strong.product-quantity',
                'property' => 'display',
                'value_pattern' => 'none',
                'exclude' => array(false)
            ),

        ),
        'active_callback'    => array(
            array(
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => true,
                ),
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => 'on',
                )
            ),
            $thmv_standard_header_switch_callback,
        ),
    ));

    // HEADER & FOOTER SECTION - Header - Cart Icon
    Bellevue_Kirki::add_field( 'bellevue_theme', array(
        'type'        => 'radio-buttonset',
        'settings'    => 'themo_woo_cart_icon',
        'label'       => esc_html__( 'Cart Icon', 'bellevue' ),
        'description'        => esc_html__( 'Choose your shopping cart icon', 'bellevue' ),
        'section'     => 'header_footer',
        'default'     => 'th-i-cart',
        'priority'    => 10,
        'choices'     => array(

            'th-i-cart'   => array(
                esc_attr__( 'Bag', 'bellevue' ),
            ),
            'th-i-cart2'   => array(
                esc_attr__( 'Cart', 'bellevue' ),
            ),
            'th-i-cart3'   => array(
                esc_attr__( 'Cart 2', 'bellevue' ),
            ),
            'th-i-card'   => array(
                esc_attr__( 'Card', 'bellevue' ),
            ),
            'th-i-card2'   => array(
                esc_attr__( 'Card 2', 'bellevue' ),
            ),

        ),
        'active_callback'    => array(
            array(
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => true,
                ),
                array(
                    'setting'  => 'themo_woo_show_cart_icon',
                    'operator' => '==',
                    'value'    => 'on',
                )
            ),
            $thmv_standard_header_switch_callback,
        ),
    ) );
}

// HEADER & FOOTER SECTION - Footer
Bellevue_Kirki::add_field( 'bellevue_theme', [
    'type'        => 'custom',
    'settings'    => 'thmv_customizer_header_footer',
    'section'     => 'header_footer',
    'default'     => '<div class="thmv-customizer-heading">' . __('Footer', 'bellevue') . '</div><div class="thmv-customizer-divider"><span></span></div>',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
] );



// HEADER & FOOTER SECTION - Upper Footer - Widget Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_footer_widget_switch',
    'label'       => esc_html__( 'Footer 1', 'bellevue' ),
    'description' => esc_html__( 'Show / Hide Footer widgets area', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Footer - Footer Columns
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'radio',
    'settings'    => 'themo_footer_columns',
    'label'       => esc_html__( 'How many columns?', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '3',
    'priority'    => 10,
    'choices'     => array(
        '1'   => esc_attr__( '1 Column', 'bellevue' ),
        '2' => esc_attr__( '2 Columns', 'bellevue' ),
        '3'  => esc_attr__( '3 Columns', 'bellevue' ),
        '4'  => esc_attr__( '4 Columns', 'bellevue' ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback,
    ),
) );

// Footer : Title Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer_widget_title_colour',
    'label'       => __( 'Title', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '#FFFFFF',
    'output' => array(
        array(
            'element'  => '.th-upper-footer h1.widget-title, .th-upper-footer h2.widget-title, 
            .th-upper-footer h3.widget-title, .th-upper-footer h4.widget-title, .th-upper-footer h5.widget-title,
            .th-upper-footer h6.widget-title, .th-upper-footer a:hover',
            'property' => 'color',
            'exclude' => array( false )
        ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback,
    ),
) );

// Footer : Widget Title Underline
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_footer_remove_title_underline',
    'label'       => esc_html__( 'Underline', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'output' => array(
        array(
            'element'  => '.footer .widget-title',
            'property' => 'border-bottom',
            'value_pattern' => 'none',
            'exclude' => array( true )
        ),
        array(
            'element'  => '.footer .widget-title',
            'property' => 'padding-bottom',
            'value_pattern' => '0px',
            'exclude' => array( true )
        ),
        array(
            'element'  => '.footer .widget-title, .footer h3.widget-title',
            'property' => 'padding-bottom',
            'value_pattern' => '0px',
            'exclude' => array( true ),
            'suffix' => '!important',
        ),
        array(
            'element'  => '.footer .widget-title, .footer h3.widget-title',
            'property' => 'margin-bottom',
            'value_pattern' => '18px',
            'exclude' => array( true )
        ),
        array(
            'element'  => '.footer .widget-title:after',
            'property' => 'display',
            'value_pattern' => 'none',
            'exclude' => array( true )
        ),
        //
    ),
    //padding-bottom: 20px
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer : Text Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer_widget_text_colour',
    'label'       => __( 'Text', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '#d2d2d2',
    'output' => array(
        array(
            'element'  => '.th-upper-footer p, .th-upper-footer a, .th-upper-footer ul li, .th-upper-footer ol li, .th-upper-footer .soc-widget i',
            'property' => 'color',
            'exclude' => array( false )
        ),
        array(
            'element'  => '.footer label, .footer .frm_forms .frm_description',
            'property' => 'color',
            'exclude' => array( false ),
            'suffix' => '!important',
        ),
        array(
            'element'  => '.footer input[type=text], .footer input[type=email], 
            .footer input[type=url], .footer input[type=password], .footer input[type=number], 
            .footer input[type=tel], .footer textarea, .footer select',
            'property' => 'color',
            'exclude' => array( false ),
            'suffix' => '!important',
        ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer : Background Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer_background_colour',
    'label'       => __( 'Background', 'textdomain' ),
    'section'     => 'header_footer',
    'default'     => '#292e31',
    'choices'     => array(
        'alpha' => true,
    ),
    'output' => array(

        array(
            'element'  => '.th-upper-footer',
            'property' => 'background',
        ),

    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer : Accent Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer_widget_border_colour',
    'label'       => __( 'Accent', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'rgba(255,255,255,0.12)',
    'choices'     => array(
        'alpha' => true,
    ),
    'output' => array(
        array(
            'element'  => '.footer input[type=text], .footer input[type=email],
            .footer input[type=url], .footer input[type=password],
            .footer input[type=number], .footer input[type=tel],
            .footer textarea, .footer select, .th-payment-no-img',
            'property' => 'border-color',
            'exclude' => array( false ),
            'suffix' => '!important',
        ),
        array(
            'element'  => '.footer .meta-border, .footer ul li, .footer .widget ul li,
            .footer .widget-title,
            .footer .widget.widget_categories li a, .footer .widget.widget_pages li a, .footer .widget.widget_nav_menu li a',
            'property' => 'border-bottom-color',
            'exclude' => array( false )
        ),
        array(
            'element'  => '.footer .widget-title:after',
            'property' => 'background-color',
            'exclude' => array( false )
        ),
        //

    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer 2 : Widget Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_footer2_widget_switch',
    'label'       => esc_html__( 'Footer 2', 'bellevue' ),
    //'description' => esc_html__( 'Show / hide lower footer widgets area', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// Footer : Widget Title Underline
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_footer2_divder',
    'label'       => esc_html__( 'Divider', 'bellevue' ),
    //'description' => esc_html__( 'Show / Hide section divider', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'output' => array(
        array(
            'element'  => '.th-lower-footer .th-separator',
            'property' => 'border-top',
            'value_pattern' => '1px solid #dcdcdc',
            'exclude' => array( false )
        ),
        array(
            'element'  => '.th-lower-footer .th-widget-area',
            'property' => 'padding-top',
            'value_pattern' => '50px',
            'exclude' => array( false )
        ),
        array(
            'element'  => '.th-lower-footer',
            'property' => 'padding-top',
            'value_pattern' => '0px',
            'exclude' => array( false ),
            'suffix' => '!important'
        ),
    ),
    //padding-bottom: 20px
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Lower Footer : Text Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer2_divider_colour',
    'label'       => __( 'Divider Color', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '#888888',
    'choices'     => array(
        'alpha' => true,
    ),
    'output' => array(
        array(
            'element'  => '.th-lower-footer .th-separator',
            'property' => 'border-top-color',
            'exclude' => array( false )
        ),

    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer 2 : Footer Columns
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'radio',
    'settings'    => 'themo_footer2_columns',
    'label'       => esc_html__( 'How many columns?', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '2',
    'priority'    => 10,
    'choices'     => array(
        '1'   => esc_attr__( '1 Column', 'bellevue' ),
        '2' => esc_attr__( '2 Columns', 'bellevue' ),
        '3'  => esc_attr__( '3 Columns', 'bellevue' ),
        '4'  => esc_attr__( '4 Columns', 'bellevue' ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer : Title Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer2_widget_title_colour',
    'label'       => __( 'Title', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '#FFFFFF',
    'output' => array(
        array(
            'element'  => '.th-lower-footer h1.widget-title, .th-lower-footer h2.widget-title, .th-lower-footer h3.widget-title, .th-lower-footer h4.widget-title,
             .th-lower-footer h5.widget-title, .th-lower-footer h6.widget-title, .th-lower-footer a:hover',
            'property' => 'color',
            'exclude' => array( false )
        ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// Footer : Text Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer2_widget_text_colour',
    'label'       => __( 'Text', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '#d2d2d2',
    'output' => array(
        array(
            'element'  => '.th-lower-footer p, .th-lower-footer a, .th-lower-footer ul li, .th-lower-footer ol li, .th-lower-footer .soc-widget i',
            'property' => 'color',
            'exclude' => array( false )
        ),
    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );



// Footer : Background Colour
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'color',
    'settings'    => 'themo_footer2_background_colour',
    'label'       => __( 'Background', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => '#212E31',
    'choices'     => array(
        'alpha' => true,
    ),
    'output' => array(

        array(
            'element'  => '.th-lower-footer',
            'property' => 'background',
        ),

    ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer2_widget_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Footer - Footer Logo (Widget)
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'image',
    'settings'    => 'themo_footer_logo',
    'label'       => esc_html__( 'Footer Logo', 'bellevue' ),
    'description' => '<p>' . esc_html__( 'Upload the logo you would like to use in your footer widget.', 'bellevue' ) . '</p>' ,
    'section'     => 'header_footer',
    'default'     => '',
    'priority'    => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );


// HEADER & FOOTER SECTION - Footer - Footer Logo URL
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'     => 'text',
    'settings' =>  'themo_footer_logo_url',
    'label'       => esc_html__( 'Footer Logo Link', 'bellevue' ),
    'description' => esc_html__( 'e.g. mailto:hello@themovation.com, /contact, http://google.com:', 'bellevue' ),
    'section'     => 'header_footer',
    'priority' => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );


// HEADER & FOOTER SECTION - Footer - Footer Logo URL
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'     => 'checkbox',
    'settings' =>  'themo_footer_logo_url_target',
    'label'       => esc_html__( 'Open Link in New Window', 'bellevue' ),
    'section'     => 'header_footer',
    'priority' => 10,
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// Footer Copyright : Widget Switch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_footer_copyright_switch',
    'label'       => esc_html__( 'Footer Copyright', 'bellevue' ),
    'section'     => 'header_footer',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
    'active_callback'    => array(
        $thmv_standard_header_switch_callback
    ),
) );

// HEADER & FOOTER SECTION - Footer - Copyright
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'     => 'textarea',
    'settings' => 'themo_footer_copyright',
    'label'       => esc_html__( 'Footer Copyright', 'bellevue' ),
    'section'     => 'header_footer',
    'priority' => 10,

    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_copyright_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_copyright_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );


// HEADER & FOOTER SECTION - Footer - Credit
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'     => 'textarea',
    'settings' => 'themo_footer_credit',
    'label'       => esc_html__( 'Footer Credit', 'bellevue' ),
    'section'     => 'header_footer',
    'priority' => 10,
    'default' => __( 'Made with <i class="fa fa-heart-o"></i> by <a href="http://themovation.com">Themovation</a>', 'bellevue' ),
    'active_callback'    => array(
        array(
            array(
                'setting'  => 'themo_footer_copyright_switch',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'themo_footer_copyright_switch',
                'operator' => '==',
                'value'    => 'on',
            )
        ),
        $thmv_standard_header_switch_callback
    ),
) );

if(th_is_woocommerce_activated()) {
// WOOCOMMERCE SECTION
Bellevue_Kirki::add_section( 'woo', array(
    'title'      => esc_attr__( 'WooCommerce', 'bellevue' ),
    'priority'   => 2,
    'panel'          => 'th_options',
    'capability' => 'edit_theme_options',
) );


    // Woo : Header Switch
    Bellevue_Kirki::add_field('bellevue_theme', array(
        'type' => 'switch',
        'settings' => 'themo_woo_show_header',
        'label' => esc_html__('Page Header', 'bellevue'),
        'description' => esc_html__('Show / Hide page header for woo categories, tags, taxonomies', 'bellevue'),
        'section' => 'woo',
        'default' => 'on',
        'priority' => 10,
        'choices' => array(
            'on' => esc_attr__('Enable', 'bellevue'),
            'off' => esc_attr__('Disable', 'bellevue'),
        ),
    ));

    // Woo : Header Align
    Bellevue_Kirki::add_field('bellevue_theme', array(
        'type' => 'radio-buttonset',
        'settings' => 'themo_woo_header_float',
        'label' => esc_html__('Align Page Header', 'bellevue'),
        'section' => 'woo',
        'default' => 'centered',
        'priority' => 10,
        'choices' => array(

            'left' => array(
                esc_attr__('Left', 'bellevue'),
            ),
            'centered' => array(
                esc_attr__('Centered', 'bellevue'),
            ),
            'right' => array(
                esc_attr__('Right', 'bellevue'),
            ),
        ),
        'active_callback'  => array(
            array(
                array(
                    'setting'  => 'themo_woo_show_header',
                    'operator' => '==',
                    'value'    => 1,
                ),
                array(
                    'setting'  => 'themo_woo_show_header',
                    'operator' => '==',
                    'value'    => 'on',
                )
            )
        )
    ));

    // Woo : Sidebar Position
    Bellevue_Kirki::add_field('bellevue_theme', array(
        'type' => 'radio-buttonset',
        'settings' => 'themo_woo_sidebar',
        'label' => esc_html__('Sidebar Position for Woo categories', 'bellevue'),
        'section' => 'woo',
        'default' => 'right',
        'priority' => 10,
        'choices' => array(

            'left' => array(
                esc_attr__('Left', 'bellevue'),
            ),
            'full' => array(
                esc_attr__('None', 'bellevue'),
            ),
            'right' => array(
                esc_attr__('Right', 'bellevue'),
            ),

        ),
    ));
}


// SLIDER SECTION
Bellevue_Kirki::add_section( 'slider', array(
    'title'      => esc_attr__( 'Slider', 'bellevue' ),
    'priority'   => 2,
    'capability' => 'edit_theme_options',
    'panel'          => 'th_options',
) );

// Slider : Autoplay
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_autoplay',
    'label'       => esc_attr__( 'Auto Play', 'bellevue' ),
    'description' => esc_attr__( 'Start slider automatically', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Animation
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'radio',
    'settings'    => 'themo_flex_animation',
    'label'       => esc_html__( 'Animation', 'bellevue' ),
    'description'        => esc_html__( 'Controls the animation type, "fade" or "slide".', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'fade',
    'priority'    => 10,
    'choices'     => array(
        'fade'   => array(
            esc_attr__( 'Fade', 'bellevue' ),
        ),
        'slide' => array(
            esc_attr__( 'Slide', 'bellevue' ),
        ),
    ),
) );

// Slider : Easing
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'radio',
    'settings'    => 'themo_flex_easing',
    'label'       => esc_html__( 'Easing', 'bellevue' ),
    'description'        => esc_html__( 'Determines the easing method used in jQuery transitions.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'swing',
    'priority'    => 10,
    'choices'     => array(
        'swing'   => array(
            esc_attr__( 'Swing', 'bellevue' ),
        ),
        'linear' => array(
            esc_attr__( 'Linear', 'bellevue' ),
        ),
    ),
) );

// Slider : Animation Loop
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_animationloop',
    'label'       => esc_attr__( 'Animation Loop', 'bellevue' ),
    'description' => esc_attr__( 'Gives the slider a seamless infinite loop.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Smooth Height
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_smoothheight',
    'label'       => esc_attr__( 'Smooth Height', 'bellevue' ),
    'description' => esc_attr__( 'Animate the height of the slider smoothly for slides of varying height.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Slide Speed
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'slider',
    'settings'    => 'themo_flex_slideshowspeed',
    'label'       => esc_html__( 'Slideshow Speed', 'bellevue' ),
    'description'        => esc_html__( 'Set the speed of the slideshow cycling, in milliseconds', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 4000,
    'choices'     => array(
        'min'  => '0',
        'max'  => '15000',
        'step' => '100',
    ),
) );

// Slider : Animation Speed
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'slider',
    'settings'    => 'themo_flex_animationspeed',
    'label'       => esc_html__( 'Animation Speed', 'bellevue' ),
    'description' => esc_html__( 'Set the speed of animations, in milliseconds', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 550,
    'choices'     => array(
        'min'  => '0',
        'max'  => '1200',
        'step' => '50',
    ),
) );

// Slider : Randomize
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_randomize',
    'label'       => esc_attr__( 'Randomize', 'bellevue' ),
    'description' => esc_attr__( 'Randomize slide order, on load', 'bellevue' ),
    'section'     => 'slider',
    'default'     => '',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Puse on hover
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_pauseonhover',
    'label'       => esc_attr__( 'Pause on Hover', 'bellevue' ),
    'description' => esc_attr__( 'Pause the slideshow when hovering over slider, then resume when no longer hovering.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Touch
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_touch',
    'label'       => esc_attr__( 'Touch', 'bellevue' ),
    'description' => esc_attr__( 'Allow touch swipe navigation of the slider on enabled devices.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Dir Nav
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_directionnav',
    'label'       => esc_attr__( 'Direction Nav', 'bellevue' ),
    'description' => esc_attr__( 'Create previous/next arrow navigation.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// Slider : Paging Control
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'switch',
    'settings'    => 'themo_flex_controlNav',
    'label'       => esc_attr__( 'Paging Control', 'bellevue' ),
    'description' => esc_attr__( 'Create navigation for paging control of each slide.', 'bellevue' ),
    'section'     => 'slider',
    'default'     => 'on',
    'priority'    => 10,
    'choices'     => array(
        'on'  => esc_attr__( 'Enable', 'bellevue' ),
        'off' => esc_attr__( 'Disable', 'bellevue' ),
    ),
) );

// MISC. SECTION
Bellevue_Kirki::add_section( 'misc', array(
    'title'      => esc_attr__( 'Misc.', 'bellevue' ),
    'priority'   => 2,
    'panel'          => 'th_options',
    'capability' => 'edit_theme_options',
) );

// WIDGET SECTION
Bellevue_Kirki::add_section( 'th_widgets', array(
    'title'      => esc_attr__( 'Widgets', 'bellevue' ),
    'priority'   => 2,
    'panel'      => 'th_options',
    'capability' => 'edit_theme_options',
) );



// Footer : Footer Social
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'repeater',
    'label'       => esc_html__( 'Social Media Accounts', 'bellevue' ),
    'description'        => esc_html__( 'For use with the "Social Icons" Widget. Add your social media accounts here. Use any', 'bellevue' ). ' Social icon (e.g.: fa fa-twitter). <a href="http://fontawesome.io/icons/" target="_blank">'.esc_html__( 'Full List Here', 'bellevue' ).'</a>',
    'section'     => 'th_widgets',
    'priority'    => 10,
    'row_label' => array(
        'type' => 'text',
        'value' => esc_attr__('Social Icon', 'bellevue' ),
    ),
    'settings'    => 'themo_social_media_accounts',
    'default'     => array(
        array(
            'title' => esc_attr__( 'Facebook', 'bellevue' ),
            'themo_social_font_icon'  => 'fa fa-facebook',
            'themo_social_url'  => 'https://www.facebook.com',
            'themo_social_url_target'  => 1,
        ),
        array(
            'title' => esc_attr__( 'Twitter', 'bellevue' ),
            'themo_social_font_icon'  => 'fa fa-twitter',
            'themo_social_url'  => 'https://twitter.com',
            'themo_social_url_target'  => 1,
        ),
        array(
            'title' => esc_attr__( 'Instagram', 'bellevue' ),
            'themo_social_font_icon'  => 'fa fa-instagram',
            'themo_social_url'  => '#',
            'themo_social_url_target'  => 1,
        ),

    ),
    'fields' => array(
        'title' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Name', 'bellevue' ),
            'default'     => '',
        ),
        'themo_social_font_icon' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Social Icon', 'bellevue' ),
            'default'     => '',
        ),
        'themo_social_url' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Social Link', 'bellevue' ),
            'default'     => '',
        ),
        'themo_social_url_target' => array(
            'type'        => 'checkbox',
            'label'       => esc_attr__( 'Open Link in New Window', 'bellevue' ),
            'default'     => '',
        ),
    )
) );

// Footer : Footer Payments Accepted
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'repeater',
    'label'       => esc_html__( 'Payments Accepted', 'bellevue' ),
    'description' => esc_html__( 'For use with the "Payments Accepted" Widget. Add your accepted payments types here.', 'bellevue' ),
    'section'     => 'th_widgets',
    'priority'    => 10,
    'row_label' => array(
        'type' => 'text',
        'value' => esc_attr__('Payment Info', 'bellevue' ),
    ),
    'settings'    => 'themo_payments_accepted',
    'default'     => array(
        array(
            'title' => esc_attr__( 'Visa', 'bellevue' ),
            'themo_payments_accepted_logo'  => '',
            'themo_payment_url'  => 'https://visa.com',
            'themo_payment_url_target'  => 1,
        ),
        array(
            'title' => esc_attr__( 'PayPal', 'bellevue' ),
            'themo_payments_accepted_logo'  => '',
            'themo_payment_url'  => 'https://paypal.com',
            'themo_payment_url_target'  => 1,
        ),
        array(
            'title' => esc_attr__( 'MasterCard', 'bellevue' ),
            'themo_payments_accepted_logo'  => '',
            'themo_payment_url'  => 'https://mastercard.com',
            'themo_payment_url_target'  => 1,
        ),
    ),
    'fields' => array(
        'title' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Name', 'bellevue' ),
            'default'     => '',
        ),
        'themo_payments_accepted_logo' => array(
            'type'        => 'image',
            'label'       => esc_attr__( 'Logo', 'bellevue' ),
            'default'     => '',
        ),
        'themo_payment_url' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Link', 'bellevue' ),
            'default'     => '',
        ),
        'themo_payment_url_target' => array(
            'type'        => 'checkbox',
            'label'       => esc_attr__( 'Open Link in New Window', 'bellevue' ),
            'default'     => '',
        ),
    )
) );

// Footer : Footer Contact Details
Bellevue_Kirki::add_field( 'bellevue_theme', array(
    'type'        => 'repeater',
    'label'       => esc_html__( 'Contact Details', 'bellevue' ),
    'description' => esc_html__( 'For use with the "Contact Info" Widget. Add your contact info here. Use any', 'bellevue' ). ' <a href="http://fontawesome.io/icons/" target="_blank">Font Awesome</a> icon (e.g.: fa fa-twitter). <a href="http://fontawesome.io/icons/" target="_blank">'.esc_html__( 'Full List Here', 'bellevue' ).'</a>',
    'section'     => 'th_widgets',
    'priority'    => 10,
    'row_label' => array(
        'type' => 'text',
        'value' => esc_attr__('Contact Info', 'bellevue' ),
    ),
    'settings'    => 'themo_contact_icons',
    'default'     => array(
        array(
            'title' => esc_attr__( 'contact@bellevue.com', 'bellevue' ),
            'themo_contact_icon'  => 'fa fa-envelope-open-o',
            'themo_contact_icon_url'  => 'mailto:contact@ourdomain.com',
            'themo_contact_icon_url_target'  => 1,
        ),
        array(
            'title' => esc_attr__( '1-800-222-4545', 'bellevue' ),
            'themo_contact_icon'  => 'fa fa-mobile',
            'themo_contact_icon_url'  => 'tel:800-222-4545',
            'themo_contact_icon_url_target'  => 1,
        ),
        array(
            'title' => esc_attr__( 'Location', 'bellevue' ),
            'themo_contact_icon'  => 'fa fa-map-o',
            'themo_contact_icon_url'  => '#',
            'themo_contact_icon_url_target'  => 0,
        ),

    ),
    'fields' => array(
        'title' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Name', 'bellevue' ),
            'default'     => '',
        ),
        'themo_contact_icon' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Icon', 'bellevue' ),
            'default'     => '',
        ),
        'themo_contact_icon_url' => array(
            'type'        => 'text',
            'label'       => esc_attr__( 'Link', 'bellevue' ),
            'default'     => '',
        ),
        'themo_contact_icon_url_target' => array(
            'type'        => 'checkbox',
            'label'       => esc_attr__( 'Open Link in New Window', 'bellevue' ),
            'default'     => '',
        ),
    )
) );