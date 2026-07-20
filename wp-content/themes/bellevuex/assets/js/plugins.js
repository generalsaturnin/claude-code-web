var working = false;
var action = '';
var updateMode = false;
var updateAllPlugins = '#update_all_plugins';
var EnvatoWizard = (function ($) {
    var status;
    function window_loaded() {
        $('.thmv-switch [type="checkbox"]').on('click', function (e) {
            e.preventDefault();
            if ($(this).data('cant-install') || $(this).data('cant-uninstall')) {
                console.log('cant install');
                return false;
            }
            if (working)
                return false;
            working = true;
            action = '';
            updateMode = false;
            var loading_area = $(this).closest('.plugin-container').find('.installation-progress');

            status = dtbaker_loading_button($(this), loading_area);
            if (!status) {
                working = false;
                return false;
            }


            var plugins = new PluginManager();
            plugins.init($(this).data('plugin'), $(this), loading_area);
        });
        $('.update-link').on('click', function (e) {
            e.preventDefault();

            if (working)
                return false;
            working = true;
            action = '';
            updateMode = true;
            var parent = $(this).closest('.plugin-container');
            var loading_area = parent.find('.installation-progress')
            var checkbox = parent.find('[type="checkbox"]');
            status = dtbaker_loading_button(checkbox, loading_area);
            if (!status) {
                working = false;
                return false;
            }


            var plugins = new PluginManager();
            plugins.init(checkbox.data('plugin'), checkbox, loading_area);
        });
        $(updateAllPlugins).on('click', function (e) {
            e.preventDefault();
            $('.update-link:visible').each(function () {
                var parent = $(this).closest('.plugin-container');

                $('html,body').animate({
                    scrollTop: parent.offset().top - $('#wpadminbar').height()
                }, 'slow');
                $(this).click();
            });
        });
    }



    function PluginManager() {

        var complete;
        var current_item = '';
        var $current_node;
        var $status_node;
        var is_success = true;
        var uninstallPermissionGranted = false;
        var $container;
        var $prompt;
        function ajax_callback(response) {
            //from install and activate requests
            console.log(response);
            if (typeof response == 'object' && typeof response.message != 'undefined') {
                $status_node.text(response.message);
                if (typeof response.url != 'undefined') {
                    // we have an ajax url action to perform.

                    $.post(response.url, response, function (response2) {
                        $status_node.text(response.message);
                        complete();
                    }).fail(ajax_callback);

                } else if (typeof response.done != 'undefined') {
                    // finished processing this plugin, move onto next
                    complete();
                } else {
                    // error processing this plugin
                    $status_node.text('Some error');
                    is_success = false;
                    complete();
                }
            } else if (typeof response.success != 'undefined') {
                //for ajax uninstall request
                is_success = response.success;
                if (response.success == false) {
                    alert(response.data.errorMessage);
                }
                complete();


            } else {
                // error - try again with next plugin
                $status_node.text("ajax error");
                is_success = false;
                complete();
            }
        }
        function showPrompt() {
            $prompt = $('<div id="prompt"><p>' + plugins_params.uninstall_prompt + '</p><div class="prompt-controls"><a href="#" id="prompt-no">' + plugins_params.uninstall_prompt_no + '</a><a href="#" id="prompt-yes">' + plugins_params.uninstall_prompt_yes + '</a></div></div>');
            $container.append($prompt);
            $prompt.find('#prompt-no').on('click', function (e) {
                e.preventDefault();
                closePrompt();
                status.done(false);
            });
            $prompt.find('#prompt-yes').on('click', function (e) {
                e.preventDefault();
                closePrompt();
                uninstallPermissionGranted = true;
                action = plugins_params.deactivate_action;
                $status_node.text(plugins_params.uninstall_deactivating);
                process();
            });

        }
        function closePrompt() {
            $prompt.remove();
        }
        function process() {
            if (current_item) {
                console.log(action);

                if (action == plugins_params.deactivate_action) {
                    return $.get($current_node.data('deactivate_url'), function (result) {
                        setTimeout(complete, 500);
                    });
                } else {
                    return $.post(ajaxurl, {
                        action: action,
                        wpnonce: plugins_params.wpnonce,
                        _wpnonce: plugins_params._wpnonce,
                        slug: $current_node.data('slug'),
                        plugin: $current_node.data('plugin')
                    }, ajax_callback).fail(ajax_callback);
                }


            }
        }

        function complete() {
            if (updateMode) {
                if (is_success) {
                    $container.find('.update-link').hide();

                }
                status.done(is_success);
            } else {
                if ($current_node.attr('data-install')) {
                    $current_node.removeAttr('data-install');
                    action = plugins_params.activate_action;
                    process();
                } else if ($current_node.attr('data-activate')) {
                    $current_node.removeAttr('data-activate');
                    status.done(is_success);
                } else if (action == plugins_params.deactivate_action) {
                    action = plugins_params.uninstall_action;
                    $status_node.text(plugins_params.uninstall_uninstalling);
                    process();
                } else if (action == plugins_params.uninstall_action) {
                    $current_node.attr('data-install', '1');
                    $current_node.attr('data-activate', '1');
                    $status_node.text('');
                    status.done(is_success);
                }
            }



        }

        return {
            init: function (slug, $node, $loadingArea) {
                current_item = slug;
                $current_node = $node;
                $status_node = $loadingArea.find('.loading-area');
                $container = $node.closest('.plugin-container');
                if (updateMode) {
                    action = plugins_params.update_action;
                } else {
                    if ($current_node.attr('data-install')) {
                        action = plugins_params.install_action;

                    } else if ($current_node.attr('data-activate')) {
                        action = plugins_params.activate_action;
                    } else {
                        //user wants to deactivate/uninstall it and no permission granted yet
                        //see if this is a required plugin
                        if ($current_node.attr('data-prompt') && !uninstallPermissionGranted) {
                            showPrompt();
                            return true;
                        } else {
                            action = plugins_params.deactivate_action;
                            $status_node.text(plugins_params.uninstall_deactivating);
                        }

                        //and then uninstall
                    }
                }

                return process();
            }
        }
    }



    function dtbaker_loading_button(checkbox, $statusContainer) {
        var existing_text = '';
        var checkbox = checkbox;

        var loading_text = '⡀⡀⡀⡀⡀⡀⡀⡀⡀⡀⠄⠂⠁⠁⠂⠄';
        var completed = false;
        var $status_area = $statusContainer.find('.status-area');

        $status_area.text(loading_text);
        checkbox.attr('disabled', true);
        $statusContainer.addClass('in-progress');

        var anim_index = [0, 1, 2];

        // animate the text indent
        function moo() {
            if (completed)
                return;
            var current_text = '';
            // increase each index up to the loading length
            for (var i = 0; i < anim_index.length; i++) {
                anim_index[i] = anim_index[i] + 1;
                if (anim_index[i] >= loading_text.length)
                    anim_index[i] = 0;
                current_text += loading_text.charAt(anim_index[i]);
            }
            $status_area.text(current_text);
            setTimeout(function () {
                moo();
            }, 60);
        }

        moo();

        return {
            done: function (is_success = true) {
                completed = true;
                $statusContainer.removeClass('in-progress');
                checkbox.attr('disabled', false);
                if (is_success) {
                    $statusContainer.closest('.plugin-container').find('.thmv-dash-title svg').hide();
                    $statusContainer.closest('.plugin-container').find('.update-link').hide();
                    $status_area.text(existing_text);

                    if (!updateMode) {
                        if (checkbox.is(":checked")) {
                            checkbox.prop('checked', false);
                        } else {
                            checkbox.prop('checked', true);
                        }
                    }

                }
                //check how many updated are left
                if (!$('.update-link').is(':visible').length) {
                    $(updateAllPlugins).hide();
                }
                working = false;

            }
        }

    }

    return {
        init: function () {
            $(window_loaded);
        }
    }

})(jQuery);


EnvatoWizard.init();