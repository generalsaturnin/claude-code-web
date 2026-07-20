function showEelement(form, elementId, text, type = '') {
        var element;
        if (jQuery('#' + elementId).length < 1) {
            element = jQuery('<p>');
            element.attr('id', elementId);
        }
        else {
            element = jQuery('#' + elementId); 
        }
        if (type !== '') {
            var classToAdd = type === 'error' ? 'error-msg' : 'success-msg';
            element.addClass(classToAdd);
        }
        else {
            element.addClass('loading');
        }
        element.html(text);
        form.after(element);
        element.show()
        return element;
    }
    function hideEelement(elementId) {
        if (jQuery('#' + elementId)) {
            jQuery('#' + elementId).hide()
        }

    }
jQuery(document).ready(function($) {
    $(document).on('click', '.themo-notice-warning .notice-dismiss', function( event ) {
        data = {
            action : 'themo_admin_notice_dismissed',
        };

        $.post(ajaxurl, data, function (response) {
            //console.log(response, 'DONE!');
        });
    });
});