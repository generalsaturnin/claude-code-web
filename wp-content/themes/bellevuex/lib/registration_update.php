<?php

/**
 * admin lib for processing backend events
 *
 */
DEFINE('BV_ENVATO_PLUGIN_SLUG', 'envato-market');
DEFINE('BV_WIDGETPACK_PLUGIN_SLUG', 'th-widget-pack');
DEFINE('BV_PREMIUM_KEY_PREFIX', 'bv_premium_status_');
//loading the two necessary files here as aloha will call the isThemeRegistered from this class and the files wouldn't have loaded
require_once( get_template_directory() . '/lib/session.php'); //load session manager
require_once( get_template_directory() . '/lib/thmv_registration_setup.php'); //load registration functions
class BelleVueRegistrationUpdate {

    static $instance;
    static $themeVersion = null;
    static $remoteThemeVersion = null;
    static $widgetPackVersion = null;
    static $remotewidgetPackVersion = null;

    public static function getInstance() {
        static $instance;
        $class = __CLASS__;
        if (!$instance instanceof $class) {
            $instance = new $class;
            $instance->init();
        }
        return $instance;
    }

    function init() {
        //call this function to register the status of the theme that is set in a cookie
//        add_action('after_setup_theme', [self::getInstance(), 'getThemeRemoteVersion']);
//        add_action('after_setup_theme', [self::getInstance(), 'isPremiumStatusValid']);
        //register stuff, load css js
        add_action('wp_ajax_bellevue_unregister_theme', [self::getInstance(), 'unRegisterThemeAjax']);
        add_action('wp_ajax_bellevue_register_theme', [self::getInstance(), 'registerThemeAjax']);
        add_action('admin_enqueue_scripts', [self::getInstance(), 'loadRegistrationAssets']);
    }

    function loadRegistrationAssets() {
        // Admin Scripts
        $jsTimeModified = filemtime(get_template_directory() . '/assets/js/registration.js');
        wp_register_script('registration', get_template_directory_uri() . '/assets/js/registration.js', array('jquery'), $jsTimeModified, true);
        wp_enqueue_script('registration');
    }

    function getEnvatoMarketStatus() {

        if (is_plugin_active(BV_ENVATO_PLUGIN_SLUG . '/' . BV_ENVATO_PLUGIN_SLUG . '.php')) {
            return true;
        }
        return false;
    }

    function activateThePurchaseCode($code) {
        $url = stripslashes(get_site_url());
        $site_data = array(
            'site' => $url,
            'code' => stripslashes($code),
            'theme' => ENVATO_THEME_REGISTER_NAME_EX,
        );
        $dataJson = json_encode($site_data);
        $verify_data = th_get_license_repsonse(REST_API_ACTIVATE, $dataJson);

        if ($verify_data == 'true' || $verify_data == '"theme_activated"') {
            return true; // activating success
        } elseif ($verify_data == '"code_used"') {
            return STATUS_ALREADY_USED;
        }

//        $count1 = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name where site_url = '" . $site . "' AND active = 1 AND theme = '" . $theme . "';" );
//        $count2 = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name where site_url <> '" . $site ."' AND code = '" . $code . "' AND active = 1;" );
//
//                if ( $count1 > 0 ) {
//                    return 'theme_activated';
//                } elseif ( $count2 > 0 ) {
//                    return 'code_used';
//                }
        //if found response is true;
        //if not found: false

        return false;
    }

    function validateAndRegisterTheCode($code) {
        //if being requested to validate, first unregister for this site as there could be some old registration hanging
        $this->unRegisterTheme();
        THMVSession::delete(BV_PREMIUM_KEY_PREFIX . $code);

        $isEnvatoCode = (strpos($code, '-') === false) ? true : false;
        $result = false;
        $whichCodeUsed = $code; //finally which code was used

        if (!$isEnvatoCode) {
            $status = $this->getPurchaseCodeStatus($code);
            if (in_array($status, array(1, 2))) {
                if ($status == 1) {
                    $result = $this->activateThePurchaseCode($code);
                }
            }
        } else {
            $envato_token = stripslashes($code);
            envato_market()->set_option('token', $envato_token);
            $envato_codes = $this->getPurchaseCodesFromEenvatoCode($code);
            if (is_array($envato_codes['errors'])) {
                foreach ($envato_codes['errors'] as $errorCode => $error) {
                    if ($errorCode == '401') {
                        $error = 'The code is incorrect';
                    } else {
                        $error = $errorCode . ' - ' . $error[0];
                    }

                    break;
                }
                return $error;
            } else if (is_array($envato_codes)) {
                //see if one is registered
                //2 already registered, 1 not registered yet
                foreach ($envato_codes as $purchaseCode) {
                    $status = $this->getPurchaseCodeStatus($purchaseCode);
                    if (in_array($status, array(1, 2))) {
                        if ($status == 1) {
                            //valid but not registered?
                            $result = $this->activateThePurchaseCode($purchaseCode);
                            if ($result === true) {
                                $whichCodeUsed = $purchaseCode;
                                break; //break the loop, we found a valid code
                            }
                        }
                    }
                }
            }
        }

        if ($result === true) {
            update_option(BELLEVUE_CODE_REGISTRY, $whichCodeUsed);
            th_theme_register('set', 1);
        }
        return $result;
    }

    function getPurchaseCodeStatus($purchaseCode) {
        $url = stripslashes(get_site_url());
        $site_data = array(
            'site' => $url,
            'code' => $purchaseCode,
            'theme' => ENVATO_THEME_REGISTER_NAME_EX,
        );

        $dataJson = json_encode($site_data);
        $status = th_get_license_repsonse(REST_API_CHECK_CODE, $dataJson);
        //status for any garbage code is 1! lol
        //
        //$count1 = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE code = '" . $code . "' AND active = 1;" );
        //$count2 = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE site_url = '" . $site . "' AND code = '" . $code . "' AND active = 1 AND theme = '" . $theme . "';" );
        //if ( $count1 == 0 ) {
        //    return 1; //okay to activate, not used before
        //    }
        //    if ( $count2 == 1 ) {
        //    return 2; //valid but already registered to this (better deactivate before trying)
        //   }
        //   otherwise 0, means not valid
        //
//        $headers = array(
//                'Content-type: application/json; charset=utf-8',
//                'Authorization: StratusActivate');
//        $result = Requests::put(REST_API_CHECK_CODE, $headers, $site_data, array());
//        $resultBody = $result->body;

        return $status;
    }

    function installActivateEnvatoMarket() {
        $plugins = th_plugins();
        $_POST['slug'] = BV_ENVATO_PLUGIN_SLUG;
        $_REQUEST['_wpnonce'] = wp_create_nonce('envato_setup_nonce');
        $_POST['plugins'] = serialize($plugins);
        th_ajax_plugins();
        exit;
    }

    function unRegisterTheme() {

        $url = stripslashes(get_site_url());
        $site_data = array(
            'site' => $url,
            'method' => 'deactivate',
            'theme' => ENVATO_THEME_REGISTER_NAME_EX,
        );
        $dataJson = json_encode($site_data);
        $verify_data = th_get_license_repsonse(REST_API_DEACTIVATE, $dataJson);
        th_theme_register('set', 0);
        th_theme_register('set', 0, 1);
        update_option(BELLEVUE_CODE_REGISTRY, false);
    }

    function unRegisterThemeAjax() {
        
        if (!current_user_can('manage_options')) {
            $response = array(
                'success' => false,
            );
            wp_send_json($response);
        }
        
        $ERROR = esc_html__('Some error occurred, try again later', 'bellevue');
        $SUCCESS = esc_html__('Unregistered successfully', 'bellevue');
        $is_sucess = true;
        $message = $SUCCESS;

        $this->unRegisterTheme();
        $response = array(
            'success' => $is_sucess,
            'message' => $message,
        );
        wp_send_json($response);
    }

    function registerThemeAjax() {

        
        if (!current_user_can('manage_options')) {
            $response = array(
                'success' => false,
            );
            wp_send_json($response);
        }
        
        
        $ERROR = esc_html__('Some error occurred, try again later', 'bellevue');
        $SUCCESS = esc_html__('Registered successfully', 'bellevue');
        $MISSING = esc_html__('Invalid or empty code.', 'bellevue');
        $is_sucess = false;
        $stopExe = false;
        $registerSuccess = false;

        $message = 'Some error ocurred';

        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        $nextStage = isset($_POST['nextStage']) ? $_POST['nextStage'] : 0;
        $totalStages = 3;
        if ($nextStage <= $totalStages) {
            if (empty($code) || strlen($code) < 20) {
                $message = $MISSING;
                $stopExe = true;
            } else {
                $is_sucess = true;
                switch ($nextStage) {
                    case 3:
                        $returnMessage = $this->validateAndRegisterTheCode($code);
                        $stopExe = true;
                        $registerSuccess = $returnMessage === true;
                        $is_sucess = $registerSuccess;
                        if ($registerSuccess) {
                            $message = 'Regisration Successful';
                        } else {
                            $message = $returnMessage === false ? 'Registration failed' : $returnMessage;
                        }
                        break;
                    case 2:
                        $result = $this->installActivateEnvatoMarket();
                        //this doesn't return from here.
                        break;
                    case 1:
                        $status = $this->getEnvatoMarketStatus();
                        if (!$status) {
                            $nextStage++;
                            $message = 'Envato market plugin not installed or activated. Installing it...';
                        } else {
                            $nextStage += 2;
                            $message = 'Envato market plugin installed... registering...';
                        }

                        break;
                    default:
                        $nextStage = 1;
                        $message = 'Checking Envato Market plugin...';
                        break;
                }
            }
        } else {
            $nextStage = 0;
            $message = 'Some error ocurred';
            $stopExe = true;
        }
        $response = array(
            'success' => $is_sucess,
            'message' => $message,
            'data' => array('stop_exe' => $stopExe, 'nextStage' => $nextStage, 'registerSuccess' => $registerSuccess)
        );
        wp_send_json($response);
    }

    function getThemeCurrentVersion() {
        if (empty(self::$themeVersion)) {
            $theme = wp_get_theme();
            $themeToCheck = $theme->parent() ? $theme->parent() : $theme;
            self::$themeVersion = $themeToCheck->get('Version');
        }
        return self::$themeVersion;
    }

    function getRemotePluginsVersion() {
        return $this->getPluginsVersion();
    }

    /**
     * Retrieves theme version - checks once per session
     * @staticvar float $remoteThemeVersion
     * @return float
     */
    function getThemeRemoteVersion() {
        if(!apply_filters('aloha_plugin_server_test', false)){
            return false;
        }
        
        if (empty(self::$remoteThemeVersion)) {
            if (THMVSession::get('bv_version_check')) {
                return self::$remoteThemeVersion = THMVSession::get('bv_version_check');
            }
            
            self::$remoteThemeVersion = $this->getThemeovationVersions(BELLEVUE_THEME_DETAILS_URL);
            if (self::$remoteThemeVersion) {
                
                THMVSession::set('bv_version_check', self::$remoteThemeVersion);
                return self::$remoteThemeVersion;
            }
        }
        
        return $this->getThemeCurrentVersion();
    }

    function getThemeovationVersions($url) {

        $options = array(
            'timeout' => 10, //seconds
            'headers' => array(
                'Accept' => 'application/json',
            ),
        );
        $result = wp_remote_get($url, $options);
        if (is_array($result) && !is_wp_error($result)) {
            $resultBody = wp_remote_retrieve_body($result);
            $details = (array) json_decode($resultBody);
            return $details['version'];
        }

        return false;
    }

    function getEnvatoThemeDetails() {
        $token = envato_market()->get_option('token', false);
        if ($token) {
            // envato_market()->set_themes( false, true );
            envato_market()->items()->set_themes();
            $premium = envato_market()->items()->themes('active');
            if (is_array($premium) && isset($premium[ENVATO_THEME_REGISTER_NAME])) {
                return $premium[ENVATO_THEME_REGISTER_NAME];
            }
        }

        return false;
    }

    function getThemeUpdateLink() {
        //if envato plugin is active and code is entered, then use the update link otherwise external link
        $envato = $this->getEnvatoMarketStatus();
        if ($envato) {
            //check if there is a token
            $token = envato_market()->get_option('token', false);
            //if the token is wrong, I think the below could fail.
            if ($token) {
                //use the ajax methods
                $jsTimeModified = filemtime(BELLEVUE_JS_PATH . '/update_theme.js');
                wp_register_script('update-theme-script', BELLEVUE_JS_URI . '/update_theme.js', array('jquery'), $jsTimeModified, true);
                wp_enqueue_script('update-theme-script');
                wp_localize_script('update-theme-script', 'bv', array(
                    '_ajax_nonce' => wp_create_nonce('updates'),
                    'slug' => ENVATO_THEME_REGISTER_NAME,
                ));
                //and fallback to the link
                $upgrade_link = add_query_arg(
                        array(
                            'action' => 'upgrade-theme',
                            'theme' => esc_attr(ENVATO_THEME_REGISTER_NAME),
                            'return' => admin_url('admin.php?page=' . MENU_STRATUS_HOME),
                        ),
                        self_admin_url('update.php')
                );
                return wp_nonce_url($upgrade_link, 'upgrade-theme_' . ENVATO_THEME_REGISTER_NAME);
            }
        }

        return BELLEVUE_UPDATE_TEMPLATE_LINK;
    }

    function isRegistered() {
        //check if envato active
        if($this->getEnvatoMarketStatus()){
            $token = envato_market()->get_option('token', false);
        if ($token) {
            // envato_market()->set_themes( false, true );
            envato_market()->items()->set_themes();
            $premium = envato_market()->items()->themes('active');
            if (is_array($premium) && isset($premium[ENVATO_THEME_REGISTER_NAME])) {
                return true;
            }
        }
        }
        

        //fallback in case registered through a purchase code - this will not exist otherwise
        $checker = th_theme_register('get');
        if ($checker == STATUS_ACTIVATED || $checker == STATUS_ACTIVATING_SUCCESS || $checker == STATUS_ACTIVATING_FAILURE_ACTIVATED_EARLY) {
            return true;
        }

        return false;
    }

    function getPurchaseCodesFromEenvatoCode($code) {
        $codes = array();
        $response = envato_market()->api()->request(ENVATO_API_GET_PURCHASE_LIST);
        if (isset($response->errors)) {
            return array('errors' => $response->errors);
        } else if (isset($response['results']) && sizeof($response['results']) > 0) {
            foreach ($response['results'] AS $k => $t) {
                if ($t['item']['id'] == ENVATO_STRATUS_ID) {
                    $codes[] = stripslashes($t['code']);
                }
            }
        }
        return $codes;
    }

    function getExpirationDate($code) {
        $site_data = array(
            'code' => $code,
        );

        $dataJson = json_encode($site_data);
        $json = th_get_license_repsonse(REST_API_GET_CODE_DETAILS, $dataJson); //th_get_license_repsonse(REST_API_GET_CODE_DETAILS, $dataJson);
        $verify_obj = json_decode($json);
        if (
                isset($verify_obj->supported_until) && !empty($verify_obj->supported_until)  && $verify_obj->item_id == ENVATO_STRATUS_ID
        ) {
            return $verify_obj->supported_until;
        }
        return false;
    }


    /** see if any plugin needs updating * */
    function isPremiumStatusValid() {
        //echo "<pre>debug isPremiumStatusValid</pre>";
        $code = get_option(BELLEVUE_CODE_REGISTRY, false);
        if (!empty($code)) {
            //echo "<pre>debug code ".$code."</pre>";
            //echo "<pre>debug prefix ".BV_PREMIUM_KEY_PREFIX."</pre>";
            if (THMVSession::get(BV_PREMIUM_KEY_PREFIX . $code)!==null) {
                return THMVSession::get(BV_PREMIUM_KEY_PREFIX . $code);
            }
           
            $date = $this->getExpirationDate($code);

            //echo "<pre>debug date".$date."</pre>";

            if ($date !== false && empty($date)) {
                //empty data - maybe the server failed for some reason - wait for it?
                return false; //PENDING_STATUS; returning false for now, but not save in the session to keep trying
            } else if (!empty($date)) {
                //check if greater than today
                $expirationDate = new DateTime($date);
                $todayDate = new DateTime();
                $interval = date_diff($todayDate, $expirationDate);
                $remaining = $interval->format('%R%a');
                $status = $remaining > 0;
                THMVSession::set(BV_PREMIUM_KEY_PREFIX . $code, $status );
                return $status;
            }
        } else {
            //older users, the value doesn't exist. We don't know which code they used.
            //echo 'DEBUG FAILED';
        }

        return false;
    }

}

//keep this on,so the function is registered
$instance = BelleVueRegistrationUpdate::getInstance();
function thmv_is_registered() {
    return BelleVueRegistrationUpdate::getInstance()->isRegistered();
}