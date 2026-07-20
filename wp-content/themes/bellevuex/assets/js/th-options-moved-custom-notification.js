(function ($) {

    'use strict';

    wp.customize.bind('ready', function () {
        wp.customize.notifications.add(
                'th-options-moved-custom-notification',
                new wp.customize.Notification(
                        'th-options-moved-custom-notification', {
                            dismissible: false,
                            message: th_customizer_notification.msg,
                            type: 'warning'
                        }
                )
                );
    });

})(jQuery);