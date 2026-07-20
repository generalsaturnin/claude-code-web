jQuery(document).ready(function ($) {
    var working = false;
    var $button = $('#aloha-installaton-button');
    function error_message() {
        $button.text(aloha_params.error_message);
        setTimeout(function () {
            working = false;
        }, 3000);
    }
    ;
    $button.on('click', function (e) {
        $button = $(this);
        e.preventDefault();

        if (working)
            return;
        working = true;
        var url = $(this).attr('data-href');
        $button.text(aloha_params.installation_message);
        $.get(url, function (response) {
            //check for activation
            $.post({
                url: aloha_params.ajax_url,
                data: {action: aloha_params.install_check},
                success: function (result) {
                    if (true) {
                        //activate it
                        $button.text(aloha_params.activation_message);
                        window.location.href = aloha_params.activation_url;
                    } else {
                        error_message();
                    }
                }
            });
        }).fail(function () {
            error_message();
        });
    });
    if ($('.aloha_popup').length) {
        var $popup = $(".aloha_popup").eq(0);
        $popup.dialog({
            'title': aloha_params.aloha_title,
            'dialogClass': 'wp-dialog',
            'modal': true,
            draggable: true,
            resizable: true,
            'autoOpen': true,
            'closeOnEscape': true,
            open: function (event, ui) {

            }
        }).closest('.ui-dialog').css({
            "z-index": 100001
        });
        $('.ui-widget-overlay').css({
            "z-index": 10000,
            "background": "none",
            "opacity": ".70",
            "background-color": 'black'
        });
        $popup.closest('.ui-dialog').find('.ui-dialog-titlebar').removeClass('ui-corner-all').css({
            "background": "none",
            "border": "none",
            "border-bottom": "1px solid #bfb5b5"
        });
        $popup.closest('.ui-dialog').find('.ui-button').removeClass('ui-corner-all').css({"line-height": "14px"}).text('x');
        $('#aloha-installaton-button-popup').on('click', function (e) {
            e.preventDefault();
            $popup.dialog('close');
            $('#aloha-installaton-button').click();
        });
    }


});
