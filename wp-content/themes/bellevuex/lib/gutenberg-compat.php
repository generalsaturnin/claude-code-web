<?php

//add colors for gutenberg editor
if (is_admin()) {
    add_action('after_setup_theme', 'aloha_guteberg_compat');
}
//if a page is not done in elementor, elementor colors won't be present.
//we need to load them ourselves
add_action('wp_enqueue_scripts', 'aloha_add_global_colors');
add_action('admin_enqueue_scripts', 'aloha_add_global_colors');
add_action('admin_enqueue_scripts', 'aloha_gutenberg_styles');

add_theme_support('align-wide');

function is_gutenberg_page() {

    global $post;

    if (function_exists('has_blocks') && isset($post->ID) && has_blocks($post->ID)) {
        return true;
    } else {
        return false;
    }
}

add_filter('body_class', function ($classes) {
    if (!is_gutenberg_page()) {
        return $classes;
    }

    return array_merge($classes, array('gutenberg-page'));
});

function aloha_gutenberg_styles() {
    
    $css = '';
    ob_start();
    ?>

    <style>
        :root{
            --bv--spacing-horizontal: 15px;
            --global--spacing-vertical: 30px;
            --bv--wide-width: 1300px;
            --bv--default-width: 1140px;
            --responsive--aligndefault-width: calc(100vw - var(--bv--spacing-horizontal));
            --responsive--alignwide-width: calc(100vw - var(--bv--spacing-horizontal));
        }

        .editor-styles-wrapper [data-block] {
            margin-top: var(--global--spacing-vertical);
            margin-bottom: var(--global--spacing-vertical);
        }

        @media only screen and (min-width: 482px){
            :root {
                --responsive--aligndefault-width: min(calc(100vw - 4 * var(--bv--spacing-horizontal)), var(--bv--default-width));
                --responsive--alignwide-width: calc(100vw - 4 * var(--bv--spacing-horizontal));
            }
        }

        @media only screen and (min-width: 822px){
            :root {
                --responsive--aligndefault-width: min(calc(100vw - 8 * var(--bv--spacing-horizontal)), var(--bv--default-width));
                --responsive--alignwide-width: min(calc(100vw - 8 * var(--bv--spacing-horizontal)), var(--bv--wide-width));
            }
        }
        .editor-styles-wrapper .wp-block {
            max-width: var(--responsive--aligndefault-width);
            box-sizing: border-box;/**very important **/
        }
        .editor-styles-wrapper .wp-block[data-align=full], .editor-styles-wrapper .wp-block.alignfull {
            max-width: none;
        }
        .editor-styles-wrapper .wp-block[data-align=wide], .editor-styles-wrapper .wp-block.alignwide {
            max-width: var(--responsive--alignwide-width);
        }

    </style>        
    <?php

    echo ob_get_clean();
}

function aloha_add_global_colors() {
    $elementor_page = get_post_meta(get_the_ID(), '_elementor_edit_mode', true); 
    //we need the colors in non elementor pages
    if ($elementor_page) {
        return;
    }
    
    if (function_exists('aloha_get_elementor_tab_prefix') && is_callable('Elementor\Plugin::instance')) {

        $kit_id = get_option(Elementor\Core\Kits\Manager::OPTION_ACTIVE);
        $key = Elementor\Core\Base\Document::PAGE_META_KEY;
        $custom_colors_css = $colors_css = $bg_colors_css = '';
        if ($kit_id) {
            $meta = get_post_meta($kit_id, $key, true);
            if (is_array($meta) && isset($meta['system_colors'])) {
                foreach ($meta['system_colors'] as $system_color) {
                    if (strpos($system_color['_id'], 'thmv_') !== false) {
                        $custom_colors_css .= '--e-global-color-' . $system_color['_id'] . ': ' . $system_color['color'] . ';';
                        $colors_css .= '.has-' . str_replace("_", "-", $system_color['_id']) . '-color{color: var(--e-global-color-' . $system_color['_id'] . ');}';
                        $bg_colors_css .= '.has-' . str_replace("_", "-", $system_color['_id']) . '-background-color{background-color: var(--e-global-color-' . $system_color['_id'] . ');}';
                    }
                }
            }
        }
        if (!empty($custom_colors_css)) {
            echo '<style>'
            . ':root{' . $custom_colors_css . '}'
            . $colors_css
            . $bg_colors_css
            . '</style>';
        }
    }
}

function aloha_guteberg_compat() {

    if (function_exists('aloha_get_elementor_tab_prefix') && (defined('ELEMENTOR_VERSION') && is_callable('Elementor\Plugin::instance'))) {
        $name = aloha_get_elementor_tab_prefix();
        $kit_id = get_option(Elementor\Core\Kits\Manager::OPTION_ACTIVE);
        $key = Elementor\Core\Base\Document::PAGE_META_KEY;
        $custom_colors_array = [];
        if ($kit_id) {
            $meta = get_post_meta($kit_id, $key, true);
            if (is_array($meta) && isset($meta['system_colors'])) {
                foreach ($meta['system_colors'] as $system_color) {
                    if (strpos($system_color['_id'], 'thmv_') !== false) {
                        $temp = ['slug' => $system_color['_id'], 'name' => $system_color['title'], 'color' => 'var(--e-global-color-' . $system_color['_id'] . ')'];
                        $custom_colors_array[] = $temp;
                    }
                }
            }
        }


        if (count($custom_colors_array)) {
            add_theme_support(
                    'editor-color-palette',
                    $custom_colors_array
            );
        }
    }
}
