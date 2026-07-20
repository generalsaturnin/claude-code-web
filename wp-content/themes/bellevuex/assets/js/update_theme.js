var bv;
jQuery(document).ready(function ($) {
    var $button;
    var $version;
    var $hide_on_success;
    var $message;
    $('#update-template-link').on('click', function (e) {
        e.preventDefault();

        $button = $(e.target);
        $version = $($button.data('version-area'));
        $hide_on_success = $($button.data('hide-on-success'));
        if (!$button.next('span').length) {
            $button.after($('<span/>'));
        }

        $message = $button.next('span').show();
        $button.hide();
        bv_updateTheme();
    });
    var bv_updateTheme = function () {
        var data;

        var updatingMessage = 'Updating...';
        $message.html(updatingMessage);


        data = {
            _ajax_nonce: bv._ajax_nonce,
            theme: bv.slug,
            slug: bv.slug,
            action: 'update-theme',

        };
        jQuery.post(
                ajaxurl,
                data)
                .done(updateSuccess)
                .fail(updateError).complete(complete);
        function updateSuccess(msg) {
            console.log(msg);
            if (msg.success) {
                $message.html('Success');
                $version.html('v' + msg.data.newVersion);
                $hide_on_success.hide();
            } else {
                $message.html('Error: ' + msg.data.errorMessage);
                setTimeout(function () {
                    $message.empty().hide();
                    $button.show();
                }, 3000);

            }

        }
        function updateError(msg) {
            console.log(msg);
            $message.html('Error: ' + msg.statusText);
        }
        function complete() {


        }
    };
});
