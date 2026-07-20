<?php
include_once( get_template_directory() . '/lib/plugin-update-checker/plugin-update-checker.php');
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// display custom admin notice

if (defined('ENVATO_HOSTED_SITE')) {
    // this is an envato hosted site so Skip
} else {
    add_action('admin_notices', 'th_admin_envato_market_auth_notice');
}


function th_admin_envato_market_auth_notice() {

    $screen = get_current_screen();

    if ('themes' == $screen->parent_base || 'envato-market' == $screen->parent_base) {



        if (function_exists('envato_market')) {

            $option = envato_market()->get_options();

            if (!$option || empty($option['token'])) {

                delete_option('dtbwp_update_notice');

                // we show an admin notice if it hasn't been dismissed
                $dissmissed_time = get_option('dtbwp_update_notice', false);

                if (!$dissmissed_time || $dissmissed_time < strtotime('-7 days')) {


                    // Added the class "notice-my-class" so jQuery pick it up and pass via AJAX,
                    // and added "data-notice" attribute in order to track multiple / different notices
                    // multiple dismissible notice states 
                    ?>
                    <div class="notice notice-warning notice-dtbwp-themeupdates is-dismissible">
                        <p><?php
                            printf(__('<a href="%s">Please activate</a> ThemeForest updates to ensure you have the latest version of this theme.', 'embark'), esc_url(admin_url('admin.php?page=envato-market')));
                            ?></p>
                        <p>
                            <?php printf(__('<a class="button button-primary" href="%s" target="_blank">Need help?</a>', 'embark'), esc_url('https://help.bellevuetheme.com/article/150-how-to-update-the-theme')); ?>

                        </p>
                    </div>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            $(document).on('click', '.notice-dtbwp-themeupdates .notice-dismiss', function () {
                                $.ajax(ajaxurl,
                                        {
                                            type: 'POST',
                                            data: {
                                                action: 'dtbwp_update_notice_handler',
                                                security: '<?php echo wp_create_nonce("dtnwp-ajax-nonce"); ?>'
                                            }
                                        });
                            });
                        });
                    </script>
                    <?php
                }
            }
        }
    }
}

// Prevent automatic wizard redriect
function filter_woocommerce_prevent_automatic_wizard_redirect() {
    // make filter magic happen here...
    return true;
}

;

// add the filter
add_filter('woocommerce_prevent_automatic_wizard_redirect', 'filter_woocommerce_prevent_automatic_wizard_redirect', 10, 1);

// Remove Rev Slider update notice
function thmv_remove_rev_slider_notice() {
    global $RevSliderAdmin;
    if (class_exists('RevSliderAdmin')) {
        remove_action('after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'add_notice_wrap_pre'));
        remove_action('after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'show_purchase_notice'));
        remove_action('after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'add_notice_wrap_post'));
    }
}

add_action('after_plugin_row_revslider/revslider.php', 'thmv_remove_rev_slider_notice', 9, 3);

// Remove Formidable Redirect after activation
function thmv_remove_formidable_welcome() {
    remove_action('activate_formidable/formidable.php', 'frm_maybe_install');
}

add_action('activate_formidable/formidable.php', 'thmv_remove_formidable_welcome', 1);

// Plugin Activation hook for QuickCal/Booked.

function th_booked_del_redirect() {
    //set_transient( '_booked_welcome_screen_activation_redirect', false, 30 );
    delete_transient('_booked_welcome_screen_activation_redirect');
    delete_transient('_quickcal_welcome_screen_activation_redirect');
}

add_action('admin_init', 'th_booked_del_redirect', 8);

// Remove Elementor Plugin Redirection

function themo_elementor_del_redirect() {
    delete_transient('elementor_activation_redirect');
}

add_action('admin_init', 'themo_elementor_del_redirect', 8);

// Remove Groovy Menu Welcome redirect

remove_action('admin_init', 'groovy_menu_welcome');

// Disable Master Slider Auto Update.
add_filter('masterslider_disable_auto_update', '__return_true');

//we feed our json to PUC
function th_get_json_from_aloha($result) {

    if (
            !is_wp_error($result) && isset($result['http_response']) && $result['http_response'] instanceof \WP_HTTP_Requests_Response && method_exists($result['http_response'], 'get_response_object')
    ) {
        $url = $result['http_response']->get_response_object()->url;
        $slug = str_replace(".json", "", trim(parse_url($url, PHP_URL_PATH), "/"));
        if (!empty($slug) && class_exists('AlohaPlugins') && apply_filters('aloha_plugin_server_test', false)) {
            $plugins_instance = AlohaPlugins::getInstance();
            if (method_exists($plugins_instance, 'fetchPluginList')) {
                $remote_list = $plugins_instance->fetchPluginList();

                if ($remote_list && count($remote_list) && isset($remote_list[$slug])) {
                    $plugin_meta = $remote_list[$slug];
                    $plugin_meta = array_intersect_key($plugin_meta, array_flip(array(
                        'name',
                        'slug',
                        'version',
                        'download_url',
                        'sections',
                    )));
                    $response = [];
                    $response['response'] = ['code' => 200, 'message' => 'OK'];
                    $response['body'] = json_encode($plugin_meta, JSON_UNESCAPED_SLASHES);
                    return $response;
                }
            }
        }
    }
    return $result;
}

function th_get_aloha_update_manifest($force = false) {
    $cache_key = 'thmv_aloha_update_manifest';

    if (!$force) {
        $cached_manifest = get_transient($cache_key);
        if (is_array($cached_manifest)) {
            return $cached_manifest;
        }
    }

    $query_args = array(
        'php' => PHP_VERSION,
        'locale' => get_locale(),
        'checking_for_updates' => 1,
    );

    $installed_version = th_aloha_version();
    if (!empty($installed_version)) {
        $query_args['installed_version'] = $installed_version;
    }

    $request_url = add_query_arg(
        $query_args,
        BELLEVUE_UPDATE_DOMAIN . THMV_ALOHA_PLUGIN_SLUG . '.json'
    );

    $response = wp_remote_get(
        $request_url,
        array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        )
    );

    if (is_wp_error($response)) {
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if (200 !== (int) $response_code) {
        return false;
    }

    $manifest = json_decode(wp_remote_retrieve_body($response), true);
    if (
        !is_array($manifest)
        || empty($manifest['name'])
        || empty($manifest['slug'])
        || empty($manifest['version'])
        || empty($manifest['download_url'])
        || THMV_ALOHA_PLUGIN_SLUG !== $manifest['slug']
    ) {
        return false;
    }

    set_transient($cache_key, $manifest, 5 * MINUTE_IN_SECONDS);

    return $manifest;
}

function th_get_aloha_plugin_info($manifest = null) {
    if (!is_array($manifest)) {
        $manifest = th_get_aloha_update_manifest();
    }

    if (!is_array($manifest)) {
        return false;
    }

    return (object) array(
        'name' => $manifest['name'],
        'slug' => $manifest['slug'],
        'version' => $manifest['version'],
        'author' => isset($manifest['author']) ? $manifest['author'] : '',
        'homepage' => isset($manifest['homepage']) ? $manifest['homepage'] : '',
        'requires' => isset($manifest['requires']) ? $manifest['requires'] : '',
        'tested' => isset($manifest['tested']) ? $manifest['tested'] : '',
        'requires_php' => isset($manifest['requires_php']) ? $manifest['requires_php'] : '',
        'download_link' => $manifest['download_url'],
        'sections' => isset($manifest['sections']) && is_array($manifest['sections'])
            ? array_merge(array('description' => ''), $manifest['sections'])
            : array('description' => ''),
    );
}

function th_inject_aloha_plugin_info($result, $action, $args) {
    if (
        'plugin_information' !== $action
        || !is_object($args)
        || !isset($args->slug)
        || THMV_ALOHA_PLUGIN_SLUG !== $args->slug
    ) {
        return $result;
    }

    $plugin_info = th_get_aloha_plugin_info();
    if ($plugin_info) {
        return $plugin_info;
    }

    return $result;
}
add_filter('plugins_api', 'th_inject_aloha_plugin_info', 15, 3);

function th_inject_aloha_update($updates) {
    if (!th_aloha_installed()) {
        return $updates;
    }

    if (!is_object($updates)) {
        $updates = new stdClass();
    }
    if (!isset($updates->response) || !is_array($updates->response)) {
        $updates->response = array();
    }
    if (!isset($updates->no_update) || !is_array($updates->no_update)) {
        $updates->no_update = array();
    }

    $manifest = th_get_aloha_update_manifest();
    $installed_version = th_aloha_version();
    $plugin_file = THMV_ALOHA_PLUGIN_FILE;

    if (
        !is_array($manifest)
        || empty($installed_version)
        || version_compare($manifest['version'], $installed_version, '<=')
    ) {
        unset($updates->response[$plugin_file]);
        return $updates;
    }

    $update = (object) array(
        'id' => 0,
        'slug' => THMV_ALOHA_PLUGIN_SLUG,
        'plugin' => $plugin_file,
        'new_version' => $manifest['version'],
        'package' => $manifest['download_url'],
        'url' => isset($manifest['homepage']) ? $manifest['homepage'] : '',
        'icons' => isset($manifest['icons']) && is_array($manifest['icons']) ? $manifest['icons'] : array(),
        'tested' => isset($manifest['tested']) ? $manifest['tested'] : '',
        'requires_php' => isset($manifest['requires_php']) ? $manifest['requires_php'] : '',
    );

    $updates->response[$plugin_file] = $update;
    unset($updates->no_update[$plugin_file]);

    return $updates;
}
add_filter('site_transient_update_plugins', 'th_inject_aloha_update');

// Check for plugin updates
// This run the updates under WP Dash / Plugins (not Bellevue dashboard)
function th_puc_update_check() {
    $plugins = [];
    $plugins_list = the_plugin_list($plugins, true);
    if (count($plugins_list)) {
        foreach ($plugins_list as $plugin) {
            $th_plugin_slug = $plugin['slug'];
            $th_plugin_dir = WP_PLUGIN_DIR . '/' . $th_plugin_slug;

            if (isset($plugin['source']) && $plugin['source'] > ""){
                // do this
                if (is_dir($th_plugin_dir)) {
                    if (THMV_ALOHA_PLUGIN_SLUG === $th_plugin_slug) {
                        continue;
                    }
                    //Utilize PUC filter to feed our already fetched json to the remote call made from PUC
                    //the URL fed to to PUC should be a valid domain, the json files have slug names for us to use later in the filter
                    //the files don't have to exist on the remote server.
                    
                    add_filter('puc_request_metadata_http_result-'.$th_plugin_slug, 'th_get_json_from_aloha');
                    // plugin directory found!
                    PucFactory::buildUpdateChecker(
                        BELLEVUE_UPDATE_DOMAIN . $th_plugin_slug.'.json',
                        $th_plugin_dir . '/' . $th_plugin_slug . '.php',
                        $th_plugin_slug
                    );
                }
            }

        }
    }
}

if(is_admin()){
    //if we do it later than this, the update count might not be correct
    add_action('after_setup_theme', 'th_puc_update_check');
}

//-----------------------------------------------------
// BOOKED
//-----------------------------------------------------


/*
 * Unload QuickCal's Booked-compatible text domain, load users .mo first,
 * then ours, then the plugin original.
 *
 */

if (!function_exists('th_load_booked_translations')) {


    function th_load_booked_translations() {

        $text_domain = 'booked';
        $locale = apply_filters('plugin_locale', get_locale(), $text_domain);

        $original_language_file = WP_LANG_DIR . DIRECTORY_SEPARATOR . $text_domain . DIRECTORY_SEPARATOR . $text_domain . '-' . $locale . '.mo';
        $override_language_file = get_template_directory() . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $text_domain . '-' . $locale . '.override.mo';

        // Unload the translation for the text domain of the plugin
        unload_textdomain($text_domain);

        // First load the user's override translation file.
        load_textdomain($text_domain, $original_language_file);

        // Next load our override file.
        load_textdomain($text_domain, $override_language_file);

        // Then load the original file that ships with the plugin.
        load_plugin_textdomain($text_domain, FALSE, plugin_dir_path($text_domain) . '/languages/');
    }

    // remove kirki admin notice
    update_option('kirki_telemetry_no_consent', true);

    add_action('after_setup_theme', 'th_load_booked_translations', 15);
}


// TGMPA Reset dismiss

add_filter("pre_set_theme_mod_themo_tgmpa_hotel_booking", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_hotel_booking_woocommerce_payments", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_hotel_booking_reviews", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_hotel_booking_payment_request", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_woocommerce", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_revslider", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_groovy_menu", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_masterslider", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_formidable", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_simple_page_ordering", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_widget_logic", "th_reset_tgmpa_nag_dismiss", 10, 2);
add_filter("pre_set_theme_mod_themo_tgmpa_header_footer", "th_reset_tgmpa_nag_dismiss", 10, 2);

function th_reset_tgmpa_nag_dismiss($value, $old_value) {
    if ($value == 1) {
        if (class_exists('TGM_Plugin_Activation') && isset($GLOBALS['tgmpa'])) {
            $tgmpa_instance = call_user_func(array(get_class($GLOBALS['tgmpa']), 'get_instance'));
            $tgmpa_instance->id = 'bellevue';
            $tgmpa_instance->update_dismiss();
        } elseif (class_exists('TGM_Plugin_Activation')) {
            $tgmpa_instance = new TGM_Plugin_Activation();
            $tgmpa_instance->update_dismiss();
        }
    }
    return $value;
}

// Envato WP Theme Setup Wizard
// Custom logo for Installer
add_filter('envato_setup_logo_image', 'envato_set_setup_logo_image', 10);
if (!function_exists('envato_set_setup_logo_image')) {

    function envato_set_setup_logo_image($image_url) {
        $logo_main = get_template_directory_uri() . '/assets/images/bellevue_setup_logo.png';
        return $logo_main;
    }

}

// Install Envato plugin with AJAX - part 1
if (!function_exists('th_plugins')) :

    function th_plugins() {
        $instance = call_user_func(array(get_class($GLOBALS['tgmpa']), 'get_instance'));

        $plugins = array(
            'all' => array(), // Meaning: all plugins which still have open actions.
            'install' => array(),
            'update' => array(),
            'activate' => array(),
        );

        foreach ($instance->plugins as $slug => $plugin) {
            if ($slug != 'envato-market' || ( $instance->is_plugin_active($slug) && false === $instance->does_plugin_have_update($slug) )) {
                // No need to display plugins if they are installed, up-to-date and active.
                continue;
            } else {
                $plugins['all'][$slug] = $plugin;

                if (!$instance->is_plugin_installed($slug)) {
                    $plugins['install'][$slug] = $plugin;
                } else {
                    if (false !== $instance->does_plugin_have_update($slug)) {
                        $plugins['update'][$slug] = $plugin;
                    }

                    if ($instance->can_plugin_activate($slug)) {
                        $plugins['activate'][$slug] = $plugin;
                    }
                }
            }
        }
        return $plugins;
    }

endif;

// Set/Get status of Stratus theme Purchased code registration
if (!function_exists('th_theme_register')) :

    function th_theme_register($method, $value = false, $install_status = false) {
        $code = strtolower(THEME_NAME);
        $optionKey = "theme_is_registered_" . $code;

        if ($install_status)
            $optionKey = "theme_is_launched_" . $code;

        if ($method == "set") {
            update_option($optionKey, $value);
        } elseif ($method == "get") {
            return get_option($optionKey);
        }
        return false;
    }

endif;

// If not started install, not add AJAX adding of Envato plugin
$get_install = th_theme_register('get', false, 1);
if (!$get_install) {
    add_action('wp_ajax_envato_setup_plugins', 'th_ajax_plugins');
}

// Install Envato plugin with AJAX - part 2
if (!function_exists('th_ajax_plugins')) :

    function th_ajax_plugins() {
        if (!check_ajax_referer('envato_setup_nonce', 'wpnonce') || empty($_POST['slug'])) {
            wp_send_json_error(array('error' => 1, 'message' => esc_html__('No Slug Found', 'bellevue')));
        }
        $json = array();
        // send back some json we use to hit up TGM

        $plugins = array();

        if (isset($_POST['plugins'])) {
            $plugins = unserialize(stripslashes(stripslashes($_POST['plugins'])));
        }

        // what are we doing with this plugin?
        foreach ($plugins['activate'] as $slug => $plugin) {
            if ($_POST['slug'] == $slug) {
                $json = array(
                    'url' => $GLOBALS['tgmpa']->get_tgmpa_url(),
                    'plugin' => array($slug),
                    'tgmpa-page' => 'tgmpa-install-plugins',
                    'plugin_status' => 'all',
                    '_wpnonce' => wp_create_nonce('bulk-plugins'),
                    'action' => 'tgmpa-bulk-activate',
                    'action2' => - 1,
                    'message' => esc_html__('Activating Plugin', 'bellevue'),
                );
                break;
            }
        }
        foreach ($plugins['update'] as $slug => $plugin) {
            if ($_POST['slug'] == $slug) {
                $json = array(
                    'url' => $GLOBALS['tgmpa']->get_tgmpa_url(),
                    'plugin' => array($slug),
                    'tgmpa-page' => 'tgmpa-install-plugins',
                    'plugin_status' => 'all',
                    '_wpnonce' => wp_create_nonce('bulk-plugins'),
                    'action' => 'tgmpa-bulk-update',
                    'action2' => - 1,
                    'message' => esc_html__('Updating Plugin', 'bellevue'),
                );
                break;
            }
        }
        foreach ($plugins['install'] as $slug => $plugin) {
            if ($_POST['slug'] == $slug) {
                $json = array(
                    'url' => $GLOBALS['tgmpa']->get_tgmpa_url(),
                    'plugin' => array($slug),
                    'tgmpa-page' => 'tgmpa-install-plugins',
                    'plugin_status' => 'all',
                    '_wpnonce' => wp_create_nonce('bulk-plugins'),
                    'action' => 'tgmpa-bulk-install',
                    'action2' => - 1,
                    'message' => esc_html__('Installing Plugin', 'bellevue'),
                );
                break;
            }
        }

        if ($json) {
            $json['hash'] = md5(serialize($json)); // used for checking if duplicates happen, move to next plugin
            wp_send_json($json);
        } else {
            wp_send_json(array('done' => 1, 'message' => esc_html__('Success', 'bellevue')));
        }
        exit;
    }

endif;

if (!function_exists('th_check_envato_market')) :

    function th_check_envato_market() {
        wp_register_script('jquery-blockui-m', get_template_directory_uri() . '/plugins/envato_setup/js/jquery.blockUI.js', array('jquery'), '2.70', true);
        wp_register_script('envato-setup-m', get_template_directory_uri() . '/assets/js/envato-setup-custom.js', array(
            'jquery',
            'jquery-blockui-m',
                ), '2.70');
        wp_localize_script('envato-setup-m', 'envato_setup_params', array(
            'tgm_plugin_nonce' => array(
                'update' => wp_create_nonce('tgmpa-update'),
                'install' => wp_create_nonce('tgmpa-install'),
            ),
            'tgm_bulk_url' => $GLOBALS['tgmpa']->get_tgmpa_url(),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'wpnonce' => wp_create_nonce('envato_setup_nonce'),
            'verify_text' => esc_html__('...verifying', 'bellevue'),
        ));
        wp_enqueue_script('envato-setup-m');

        tgmpa_load_bulk_installer();
        // install plugins with TGM.
        if (!class_exists('TGM_Plugin_Activation') || !isset($GLOBALS['tgmpa'])) {
            die('Failed to find TGM');
        }
        $url = wp_nonce_url(add_query_arg(array('plugins' => 'go')), 'envato-setup');
        $plugins = th_plugins();

        // copied from TGM

        $method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
        $fields = array_keys($_POST); // Extra fields to pass to WP_Filesystem.

        if (false === ( $creds = request_filesystem_credentials(esc_url_raw($url), $method, false, false, $fields) )) {
            return true; // Stop the normal page form from displaying, credential request form will be shown.
        }

        // Now we have some credentials, setup WP_Filesystem.
        if (!WP_Filesystem($creds)) {
            // Our credentials were no good, ask the user for them again.
            request_filesystem_credentials(esc_url_raw($url), $method, true, false, $fields);

            return true;
        }

        /* If we arrive here, we have the filesystem */

        if (count($plugins['all'])) {
            ?>
            <form method="post" id="th-plugins-installed">
                <input type="hidden" name="th_stratus_plugins" value='<?php echo serialize($plugins); ?>' />
                <p class="envato-info-text"><?php esc_html_e('The following essential plugin need to be installed or updated:', 'bellevue'); ?></p>
                <ul class="envato-wizard-plugins">
                    <?php foreach ($plugins['all'] as $slug => $plugin) { ?>
                        <li data-slug="<?php echo esc_attr($slug); ?>"><?php echo esc_html($plugin['name']); ?>
                            <span>
                                <?php
                                $keys = array();
                                if (isset($plugins['install'][$slug])) {
                                    $keys[] = 'Install';
                                }
                                if (isset($plugins['update'][$slug])) {
                                    $keys[] = 'Update';
                                }
                                if (isset($plugins['activate'][$slug])) {
                                    $keys[] = 'Activate';
                                }
                                $plugin_keys_action = implode(' and ', $keys) . ' ' . esc_html($plugin['name']);
                                ?>
                            </span>
                            <div class="spinner"></div>
                        </li>
                    <?php } ?>
                </ul>
                <p class="envato-setup-actions step">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . MENU_STRATUS_HOME)); ?>"
                       class="button-primary button button-large button-next"
                       data-callback="install_plugins"><?php echo $plugin_keys_action; ?></a>
                       <?php wp_nonce_field('envato-setup'); ?>
                </p>
            </form>

            <?php
            return false;
        } else {
            return true;
        }
    }

endif;

// Get Purchased codes based on Envato API Token
if (!function_exists('th_get_envato_codes')) :

    function th_get_envato_codes() {
        $codes = array();
        $type = 'themes';
        $api_url = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-' . $type;
        $response = envato_market()->api()->request($api_url);
        if (isset($response->errors)) {
            return array('errors' => $response->errors);
        } else if (isset($response['results']) && sizeof($response['results']) > 0) {
            $res = array();
            $url = stripslashes(get_site_url());
            foreach ($response['results'] AS $k => $t) {
                if ($t['item']['id'] == ENVATO_STRATUS_ID) {
                    $code = stripslashes($t['code']);
                    $site_data = array(
                        'site' => $url,
                        'code' => $code,
                        'theme' => THEME_NAME,
                    );

                    $dataJson = json_encode($site_data);
                    $activate_url = REST_API_STRATUS . 'check_code';
                    $status = th_get_license_repsonse($activate_url, $dataJson);
                    if ($status == 2) {
                        th_theme_register('set', 1);
                    }
                    $res[] = array(
                        'code' => $code,
                        'status' => $status,
                    );
                }
            }
            return $res;
        }
    }

endif;

add_action('admin_enqueue_scripts','dequeue_envato_setup_script');
function dequeue_envato_setup_script(){
    
    if (isset($_GET['page']) && isset($_GET['action']) && $_GET['page'] == MENU_STRATUS_HOME && $_GET['action'] == "install") {
        wp_dequeue_script('envato-setup-m');
    }
    
    if (!isset($_GET['page']) && isset($_GET['activated']) && !get_option('envato_setup_complete', false)) {
        wp_dequeue_script('envato-setup-m');
    }
}

// Add redirection to Install Stratus page
add_action('wp_loaded', 'th_stratus_redirect');
if (!function_exists('th_stratus_redirect')) :

    function th_stratus_redirect() {
        if (isset($_GET['page']) && isset($_GET['action']) && $_GET['page'] == MENU_STRATUS_HOME && $_GET['action'] == "install") {
            th_theme_register('set', 1, 1);
            //dequeue_envato_setup_script - action is set
            if (is_child_theme()) {
                $thmv_setup_page = 'themes.php?page=bellevuechildtheme-setup';
            } else {
                $thmv_setup_page = 'themes.php?page=bellevue-setup';
            }
            exit(wp_redirect(admin_url($thmv_setup_page)));
        }
    }

endif;

// Send request about Purchased Code Activation/Deactivation
if (!function_exists('th_get_license_repsonse')) :

    function th_get_license_repsonse($url, $json) {
        $response = wp_remote_post(
                $url,
                array(
                    'sslverify'=> false,
                    'method' => 'PUT',
                    'timeout' => 10,
                    'redirection' => 1,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(
                        'Content-Type' => 'application/json; charset=utf-8',
                        'Authorization' => 'StratusActivate',
                    ),
                    'body' => $json, // Payload, text to analyse
                    'data_format' => 'body'
                )
        );

        if(is_wp_error($response)){
            $errorResponse = $response->get_error_message();
            return false;
        }
        $result = wp_remote_retrieve_body($response);
        return $result;
    }

endif;

if (!function_exists('th_stratus_dashboard_checker')) :

    function th_stratus_dashboard_checker() {
        $stratus_register = th_theme_register('get');

        if (isset($_GET['action']) && $_GET['action'] == 'thmv_deactivate' && isset($_POST['url'])) {
            $site_data = array('site' => stripslashes($_POST['url']), 'method' => 'deactivate', 'theme' => THEME_NAME);
            $deactivate_url = REST_API_STRATUS . 'deactivate';
            $dataJson = json_encode($site_data);
            $verify_data = th_get_license_repsonse($deactivate_url, $dataJson);
            th_theme_register('set', 0);
            th_theme_register('set', 0, 1);
            return 0;
        } elseif (isset($_GET['action']) && $_GET['action'] == 'activate' && isset($_POST['url'])) {
            if ($stratus_register)
                return true;

            $message_inner = '';
            $site_data_send = false;
            $site_data = array('site' => stripslashes($_POST['url']), 'theme' => THEME_NAME);

            if (isset($_POST['envato']) && $_POST['envato_token'] != "" && is_plugin_active('envato-market/envato-market.php')) {
                $envato_token = stripslashes($_POST['envato_token']);
                envato_market()->set_option('token', $envato_token);
                return 0;
            }

            if (!isset($_POST['envato'])) {
                $keys = array_keys($_POST);
                $code = '';
                foreach ($keys AS $k => $v) {
                    if (strpos($v, 'submit_code_') !== false) {
                        $mk = str_replace('submit_code_', 'purchase_code_', $v);
                        $code = isset($_POST[$mk]) ? $_POST[$mk] : '';
                    }
                }

                if ($code == '')
                    return STATUS_ACTIVATING_ERRORS_CODE_EMPTY;

                $site_data['code'] = stripslashes($code);

                $activate_url = REST_API_STRATUS . 'activate';
                $dataJson = json_encode($site_data);
                $verify_data = th_get_license_repsonse($activate_url, $dataJson);

                if ($verify_data == 'true') {
                    th_theme_register('set', 1);
                    return STATUS_ACTIVATING_SUCCESS; // activating success
                } elseif ($verify_data == 'false') {
                    return STATUS_ACTIVATING_ERRORS_CODE; // activating errors purchased
                } elseif ($verify_data == '"theme_activated"') {
                    return STATUS_ACTIVATING_FAILURE_ACTIVATED_EARLY;
                } elseif ($verify_data == '"code_used"') {
                    return STATUS_ACTIVATING_FAILURE_CODE_USED;
                }
            }
        } else {
            return $stratus_register; // return current status
        }
    }

endif;

// Add class to Startus Dashboard for show TGMPA notices anout required plugins
add_filter('admin_body_class', 'th_filter_admin_body_class', 10, 100);
if (!function_exists('th_filter_admin_body_class')) :

    function th_filter_admin_body_class($array) {
        $stratus_register = th_theme_register('get');
        if ($stratus_register == STATUS_ACTIVATED)
            return $array . ' th-activated';
        else
            return $array . ' th-not-activated';
    }

    ;
endif;


/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function the_plugin_list($plugins, $loadElementor = false) {
    //@todo, if we wanted to get the updates for plugins coming from themovation,
    //we need to check for their version on 'https://update.bellevuetheme.com/' and then add to the plugins array
    
    array_push($plugins, array(
        'name' => THMV_ALOHA_PLUGIN_NAME,
        'slug' => THMV_ALOHA_PLUGIN_SLUG,
        'required' => true,
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL.'/'.THMV_ALOHA_PLUGIN_SLUG,
    ));

    $aloha_manifest = th_get_aloha_update_manifest();
    if (is_array($aloha_manifest) && isset($plugins[0]['slug']) && THMV_ALOHA_PLUGIN_SLUG === $plugins[0]['slug']) {
        $plugins[0]['version'] = $aloha_manifest['version'];
    }
    
    if ($loadElementor) {
        array_push($plugins, array(
            'name' => 'Elementor Page Builder', // The plugin name.
            'slug' => 'elementor', // The plugin slug (typically the folder name).
            'required' => true,
            'plugin_doc_link' => "https://elementor.com/help/",
            'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/elementor',
        ));
    }
    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    if(th_show_kirki()){
        array_push($plugins, array(
            'name' => 'Kirki',
            'slug' => 'kirki',
            'required' => true,
            'plugin_doc_link' => "https://kirki.org/docs/",
        ));
    }
    
    array_push($plugins, array(
        'name' => 'Envato Market',
        'slug' => 'envato-market',
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/envato-market',
        'required' => true,
        'plugin_doc_link' => "https://www.envato.com/lp/market-plugin/",
    ));

    /*
     * Check theme options to see if we need to install other plugins.
     * Sample code: https://gearside.com/nebula/functions/register_required_plugins/
     */

    // Hotel Booking
    array_push($plugins, array(
        'name' => 'Hotel Booking',
        'slug' => 'motopress-hotel-booking',
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/motopress-hotel-booking',
        'required' => true,
        'plugin_doc_link' => "https://link.bellevuetheme.com/motopress-hotel-booking-help",
    ));

    // Hotel Booking WooCommerce Payments
    array_push($plugins, array(
        'name' => 'Hotel Booking WooCommerce Payments',
        'slug' => 'mphb-woocommerce',
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-woocommerce',
        'required' => false,
        'plugin_doc_link' => "https://link.bellevuetheme.com/mphb-woocommerce-help",
    ));

    // Hotel Booking Payment Request
    array_push($plugins, array(
        'name' => 'Hotel Booking Payment Request',
        'slug' => 'mphb-request-payment',
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-payment-request',
        'required' => false,
        'plugin_doc_link' => "https://link.bellevuetheme.com/mphb-payment-request-help",
    ));

    // Hotel Booking Reviews
    array_push($plugins, array(
        'name' => 'Hotel Booking Reviews',
        'slug' => 'mphb-reviews',
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-reviews',
        'required' => false,
        'plugin_doc_link' => "https://link.bellevuetheme.com/mphb-reviews-help",
    ));

    // WooCommerce
    array_push($plugins, array(
        'name' => 'WooCommerce', // The plugin name.
        'slug' => 'woocommerce', // The plugin slug (typically the folder name)
        'required' => false,
        'plugin_doc_link' => "https://docs.woocommerce.com/documentation/plugins/woocommerce/",
    ));

    // Slider Revolution
    array_push($plugins, array(
        'name' => 'Slider Revolution', // The plugin name.
        'slug' => 'revslider', // The plugin slug (typically the folder name).
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/revslider',
        'required' => false,
        'plugin_doc_link' => "https://www.sliderrevolution.com/help-center/",
    ));

    // Groovy Menu
    array_push($plugins, array(
        'name' => 'Groovy Menu', // The plugin name.
        'slug' => 'groovy-menu', // The plugin slug (typically the folder name).
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/groovy-menu',
        'required' => false,
        'plugin_doc_link' => "https://grooni.com/docs/groovy-menu/",
    ));

    // Master Slider
    array_push($plugins, array(
        'name' => 'Master Slider Pro', // The plugin name.
        'slug' => 'masterslider', // The plugin slug (typically the folder name).
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/masterslider',
        'required' => false,
        'plugin_doc_link' => "http://docs.averta.net/display/mswpdoc/Master+Slider+WordPress+Documentation",
    ));
    // Appointment Booking
    array_push($plugins, array(
        'name' => 'QuickCal',
        'slug' => 'quickcal',
        'source' => 'https://repo.themovation.com/quickcal.zip',
        'required' => false,
    ));
    // Formidable Forms
    array_push($plugins, array(
        'name' => 'Formidable Forms',
        'slug' => 'formidable',
        'required' => true,
        'plugin_doc_link' => "https://formidableforms.com/knowledgebase/",
    ));

    // Simple Page Ordering
    array_push($plugins, array(
        'name' => 'Simple Page Ordering',
        'slug' => 'simple-page-ordering',
        'required' => true,
        'plugin_doc_link' => "https://wordpress.org/plugins/simple-page-ordering/#faq",
    ));

    array_push($plugins, array(
        'name' => 'PDF Invoices',
        'slug' => 'mphb-invoices',
        'required' => false,
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-invoices',
        'plugin_doc_link' => 'https://link.bellevuetheme.com/mphb-invoices-help',
    ));

    array_push($plugins, array(
        'name' => 'Notifier',
        'slug' => 'mphb-notifier',
        'required' => false,
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-notifier',
        'plugin_doc_link' => 'https://link.bellevuetheme.com/mphb-notifier-help',

    ));

    array_push($plugins, array(
        'name' => 'Checkout Fields',
        'slug' => 'mphb-checkout-fields',
        'required' => false,
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-checkout-fields',
        'plugin_doc_link' => 'https://link.bellevuetheme.com/mphb-checkout-fields-help',
    ));

    array_push($plugins, array(
        'name' => 'Multi Currency',
        'slug' => 'mphb-multi-currency',
        'required' => false,
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-multi-currency',
        'plugin_doc_link' => 'https://link.bellevuetheme.com/mphb-multi-currency-help',
    ));

    array_push($plugins, array(
        'name' => 'MailChimp',
        'slug' => 'mphb-mailchimp',
        'required' => false,
        'source' => BELLEUVUE_PLUGINS_REMOTE_URL . '/mphb-mailchimp',
        'plugin_doc_link' => 'https://link.bellevuetheme.com/mphb-mailchimp-help',
    ));


    //we need remote version number for these plugins or update won't be found
    
    if (class_exists('AlohaPlugins') && apply_filters('aloha_plugin_server_test', false)) {
        $plugins_instance = AlohaPlugins::getInstance();
        if (method_exists($plugins_instance, 'fetchPluginList')) {
            $remote_list = $plugins_instance->fetchPluginList();

            if ($remote_list && count($remote_list)) {
                if (is_array($plugins) && $remote_list && count($remote_list)) {
                    foreach ($plugins as &$plugin) {
                        if (isset($remote_list[$plugin['slug']]['version'])) {
                            //check for update - set the version in the instance as the update function works on the instance
                            $plugin['version'] = $remote_list[$plugin['slug']]['version'];
                        }
                    }
                }
            }
        }
    }

    return $plugins;
}

/*
 * The buyer will be redirected to the new dashboard after theme install / activation.
 */

// code to execute on theme activation
if (!function_exists('th_theme_activate')) :

    function th_theme_activate() {
        if (!isset($_GET['page']) && isset($_GET['activated'])) {
            if (get_option('envato_setup_complete', false)) {
                if (th_aloha_active()) {
                    $thmv_setup_page = 'themes.php?page=' . MENU_STRATUS_HOME;
                    exit(wp_redirect(admin_url($thmv_setup_page)));
                }
            } else {
                th_theme_register('set', 1, 1);
                //dequeue_envato_setup_script - action is set

                if (is_child_theme()) {
                    $thmv_setup_page = 'themes.php?page=bellevuechildtheme-setup';
                } else {
                    $thmv_setup_page = 'themes.php?page=bellevue-setup';
                }
                exit(wp_redirect(admin_url($thmv_setup_page)));
            }
        }
    }

endif;

wp_register_theme_deactivation_hook('bellevue');
wp_register_theme_activation_hook('bellevue', 'th_theme_activate');

/**
 * @desc registers a theme activation hook
 * @param string $code : Code of the theme. This can be the base folder of your theme. Eg if your theme is in folder 'mytheme' then code will be 'mytheme'
 * @param callback $function : Function to call when theme gets activated.
 */
function wp_register_theme_activation_hook($code, $function) {
    $optionKey = "theme_is_activated_" . $code;
    if (!get_option($optionKey)) {
        update_option($optionKey, 1);
        call_user_func($function);
    }
}

/**
 * @desc registers deactivation hook
 * @param string $code : Code of the theme. This must match the value you provided in wp_register_theme_activation_hook function as $code
 * @param callback $function : Function to call when theme gets deactivated.
 */
function wp_register_theme_deactivation_hook($code) {
    add_action("switch_theme", function () {
        $code = 'bellevue';
        delete_option("theme_is_activated_" . $code);
    });
}

/*
 * Pre install check.
 * 1. Make sure we are not upgrading from Stratus Classic or at least warn of potential issues. Provide override.
 * 2. Make sure we are using PHP 5.4 +
 *
 * We use after_setup_theme vs after_switch_theme for our primary check
 * because the auto installer uses this hook and we want to make sure
 * everythig is good befor we install.
 *
 */

// do the pre check.
add_action('after_setup_theme', 'th_install_safety_check', 9);
if (!function_exists('th_install_safety_check')) :

    function th_install_safety_check() {
        if (!th_theme_register('get') && !th_theme_register('get', false, 1))
            return false;

        // Check if we may be upgrading from Stratus Classic, exit and warn, provide helpful instructions.
        $th_themes_installed = wp_get_themes();
        foreach ($th_themes_installed as $th_theme) {

            if ($th_theme->get('Name') > "") {
                $th_theme_name_arr = explode("-", $th_theme->get('Name'), 2); // clean up child theme name
                $th_theme_name = trim(strtolower($th_theme_name_arr[0]));

                if ($th_theme_name === 'bellevue' && $th_theme->get('Version') < 2 && $th_theme->stylesheet > "" && TH_PREVENT_BELLEVUE_UPGRADE) {

                    add_action('admin_notices', 'th_admin_notice_noupgrade');

                    function th_admin_notice_noupgrade() {
                        ?>
                        <div class="update-nag">
                            <?php _e('Hello, we ran into a small problem, it looks like you are trying to upgrade from an earlier version of Bellevue, Version 1. You can still upgrade but please be advised that these two versions are not developed under the same framework and so your existing content will not be migrated.', 'bellevue'); ?> <?php _e('If you need help, please contact the <a href="https://themovation.ticksy.com/" target="_blank">Bellevue support team here.</a> or <a href="https://themovation.ticksy.com/article/12248/" target="_blank">read the guide on updating Bellevue V1.</a>', 'bellevue'); ?> <br />
                        </div>
                        <?php
                    }

                    switch_theme($th_theme->stylesheet);
                    return false;
                }
            };
        }

        // Compare versions, just exit as after_switch_theme will do the fancy stuff.
        if (version_compare(PHP_VERSION, TH_REQUIRED_PHP_VERSION, '<')) : //PHP_VERSION
            return false;
        endif;

        // If it all looks good, run Envato WP Theme Setup Wizard
        include( get_template_directory() . '/plugins/envato_setup/envato_setup_init.php');     // Custom functions
        include( get_template_directory() . '/plugins/envato_setup/envato_setup.php');          // Custom functions
    }

endif;

// Only one fist activation, log theme version number.
add_action('after_switch_theme', 'thmv_first_activation');

function thmv_first_activation() {
    if (get_option('thmv_first_activation_log') === false) {
        // Set a flag if the theme activation happened
        add_option('thmv_first_activation_log', true, '', false);

        // stuff here only runs once, when the theme is activated for the 1st time;
        add_option('thmv_first_activation_version', thmv_get_theme_version(), '', false);
    }
}

/**
 * Stratus admin notice for need activate license.
 */
add_action('admin_notices', 'th_need_register');
if (!function_exists('th_need_register')) {

    function th_need_register() {
        if (isset($_GET['page']) && in_array($_GET['page'], array(MENU_STRATUS_HOME, MENU_STRATUS_PLUGINS)))
            return false;
        $stratus_register = th_theme_register('get');
        if ($stratus_register)
            return false;

        $logo_setup = get_template_directory_uri() . '/assets/images/Bellevue-Icon.png';

        $class = 'notice is-dismissible';
        $message = '<div class="stratus-notice"><div class="stratus-link">' . sprintf('<a href="%s">Register</a>', __(admin_url('admin.php?page=' . MENU_STRATUS_HOME))) . '</div><div class="stratus-message">' . __('Automatic updates, premium support and one-click template imports.', 'bellevue') . '</div><div class="stratus-message-icon"><img src="' . $logo_setup . '"></div></div>';

        global $wp_version;
        if (version_compare($wp_version, '4.2') < 0) {
            $message .= '<a id="stratus-dismiss-notice" href="javascript: stratus_dismiss_notice();">' . __('Dismiss this notice.') . '</a>';
        }
        echo '<div id="stratus-notice" class="' . $class . '">' . $message . '</div>';
        echo "<script>
                function stratus_dismiss_notice() {
                    jQuery( '#stratus-notice' ).hide();
                }
        
                jQuery( document ).ready( function() {
                    jQuery( 'body' ).on( 'click', '#stratus-notice .notice-dismiss', function() {
                        stratus_dismiss_notice();
                    } );
                } );
                </script>";
    }

}

add_action('after_switch_theme', 'check_theme_setup', 10, 2);

function check_theme_setup($old_theme_name, $old_theme = false) {

    // Log theme version number
    // Deactivate OLD Shortcode Plugin
    if (is_plugin_active('bellevue-shortcodes/bellevue-shortcodes.php')) {
        deactivate_plugins('bellevue-shortcodes/bellevue-shortcodes.php');
    }

    // Deactivate OLD Custom Post Type
    if (is_plugin_active('bellevue-custom-post-types/bellevue-custom-post-types.php')) {
        deactivate_plugins('bellevue-custom-post-types/bellevue-custom-post-types.php');
    }

    // Compare versions.
    if (version_compare(PHP_VERSION, TH_REQUIRED_PHP_VERSION, '<')) :

        // Theme not activated info message.
        add_action('admin_notices', 'th_admin_notice_phpversion');

        function th_admin_notice_phpversion() {
            ?>
            <div class="update-nag">
                <?php _e('Hello, we ran into a small problem, but it\'s an easy fix. Your version of <strong>PHP</strong>', 'bellevue'); ?> <strong><?php echo PHP_VERSION; ?></strong> <?php _e('is unsupported. We recommend <strong>PHP 7+</strong>, however, the theme should work with <strong>PHP</strong>', 'embark') ?> <strong><?php echo TH_REQUIRED_PHP_VERSION; ?>+</strong>. <?php _e('Please ask your web host to upgrade your version of PHP before activating this theme. If you need help, please contact the <a href="https://themovation.ticksy.com/" target="_blank">Embark support team here.</a>', 'embark'); ?> <br />
            </div>
            <?php
        }

        // Switch back to previous theme.
        switch_theme($old_theme->stylesheet);
        return false;

    endif;
}
