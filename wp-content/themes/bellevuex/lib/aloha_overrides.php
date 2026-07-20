<?php
require_once( get_template_directory() . '/lib/registration_update.php');
$registrationInstance = BelleVueRegistrationUpdate::getInstance();
add_filter('aloha_is_theme_registered', [$registrationInstance, 'isRegistered']);
add_filter('aloha_is_premium_status_valid', [$registrationInstance, 'isPremiumStatusValid']);
add_filter('aloha_get_theme_current_version', [$registrationInstance, 'getThemeCurrentVersion']);
add_filter('aloha_get_theme_remote_version', [$registrationInstance, 'getThemeRemoteVersion']);
add_filter('aloha_get_theme_update_link', [$registrationInstance, 'getThemeUpdateLink']);
add_filter('aloha_theme_registration_form', 'th_registration_form');

//add_action('tgmpa_register', 'th_register_required_plugins');
add_filter('aloha_mailchimp_tags', 'th_aloha_mailchimp_tag');

function th_aloha_mailchimp_tag($tags) {
    $tags[] = 'bellevue';
    return $tags;
}

add_filter('aloha_dashboard_title', 'th_aloha_menu_name');
add_filter('aloha_menu_name', 'th_aloha_menu_name');

function th_aloha_menu_name($name) {
    return 'Bellevue';
}

add_filter('aloha_tgmpa_id', 'th_tgmpa_id');

function th_tgmpa_id() {
    return 'bellevue';
}
add_filter('aloha_plugins_list', 'the_plugin_list');

add_action('aloha_admin_init', function () {
    wp_enqueue_script('th-stratus-activate', get_template_directory_uri() . '/assets/js/stratus-activate.js', array(
        'jquery',
            ), '1');
});
add_filter('aloha_dashboard_setup_guide', 'bv_override_getting_started');

function bv_override_getting_started($output) {
    $installText = get_option('envato_setup_complete', false) ? esc_html('Rerun Setup') : esc_html('Launch Setup');
    $install_action = esc_url(admin_url('admin.php?page=' . MENU_STRATUS_HOME . '&action=install'));
    ob_start();
    ?>
    <h3 class="thmv-dash-title"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#0C1015" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 8V12" stroke="#0C1015" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 16H12.01" stroke="#0C1015" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>Setup Guide</h3>
    <p class="thmv-dash-paragraph">Import demo content and activate required plugins.</p>

    <button class="thmv-launch-setup"  onclick="window.location.href = '<?= $install_action ?>'"><?= $installText ?></button>

    <div class="thmv-help-guide">
        <a href="https://help.bellevuetheme.com/category/13-install" target="_blank">Help guide</a>
    </div>
    <?php
    return ob_get_clean();
}

function th_registration_form() {
    $isRegistered = apply_filters('aloha_is_theme_registered', false);
    ?>
    <!-- Registration form -->
    <section class="thmv-dash-registration">
        <div class="thmv-dash-row">
            <div class="thmv-dash-registration-box">
                <div class="thmv-column-3 thmv-registration-info">
                    <h3 class="thmv-dash-title">Registration

                        <svg <?php if ($isRegistered): ?> style="display: none;"<?php endif; ?> xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M7.7 7.7H6.3V3.5H7.7V7.7ZM7.7 10.5H6.3V9.1H7.7V10.5ZM7 0C6.08075 0 5.17049 0.18106 4.32122 0.532843C3.47194 0.884626 2.70026 1.40024 2.05025 2.05025C0.737498 3.36301 0 5.14348 0 7C0 8.85651 0.737498 10.637 2.05025 11.9497C2.70026 12.5998 3.47194 13.1154 4.32122 13.4672C5.17049 13.8189 6.08075 14 7 14C8.85651 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85651 14 7C14 6.08075 13.8189 5.17049 13.4672 4.32122C13.1154 3.47194 12.5998 2.70026 11.9497 2.05025C11.2997 1.40024 10.5281 0.884626 9.67878 0.532843C8.8295 0.18106 7.91925 0 7 0Z" fill="#EB4D4D"/>
                        </svg>

                    </h3>
                    <p class="thmv-dash-paragraph">Get full access to the Visual Template Library and Premium Plugins.</p>
                </div>
                <div class="thmv-column-3 thmv-dash-subform">
                    <form class="thmv_email_form" <?= ($isRegistered ? 'style="display:none"' : '') ?> id="thmv_registration_form" >
                        <input type="text" placeholder="Envato token or purchase code">
                            <button type="submit">Save</button>

                    </form>
                    <p class="thmv-help-text" <?= ($isRegistered ? 'style="display:none"' : '') ?>>Enter in your <a href="#" class="th-modal-link" attr-popup="envatoModal">API Token</a> or your <a href="#" class="th-modal-link" attr-popup="codeModal">Purchase Code</a>.</p>

                    <form <?= (!$isRegistered ? 'style="display:none"' : '') ?> class="thmv_email_form" action="" id="thmv_unregister_form" >
                        <button class="secondary-button" type="submit">Unregister</button>
                    </form>

                </div>
                <div class="thmv-column-3"></div>
            </div>
        </div>
    </section>
    <!-- end Registration form -->
    <?php
}
