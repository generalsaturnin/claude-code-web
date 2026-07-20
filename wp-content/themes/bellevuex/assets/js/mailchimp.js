var textData;
jQuery(document).ready(function ($) {
    var bProcessing = false;

    $('#thmv_email_form').on('submit', function (event) {
        event.preventDefault();
        subscribeToMailchimp();
    });

    function subscribeToMailchimp() {
        if (bProcessing)
            return;

        bProcessing = true;
        var form = $('#thmv_email_form');
        var userEmail = form.find('[type="email"]').val();
        hideEelement('email_form_success');
        hideEelement('email_form_error');
        showEelement(form, 'email_form_loading', '....');
        var remoteResponse;
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: ajaxurl,
            data: {
                // Required
                action: 'bellevue_subscribe_to_mailchimp',
                email: userEmail
            },
            success: function (response) {
                remoteResponse = response;
                if(!response.success){
                    showEelement(form, 'email_form_error', response.message, 'error');
                }
                else{
                    showEelement(form, 'email_form_success', response.message, 'success');
                }
            }, // success:
            error: function (response) {
                remoteResponse = response;
                if (response.message) {
                    showEelement(form, 'email_form_error', response.message, 'error');
                }
            },
            complete: function () {
                bProcessing = false;
                hideEelement('email_form_loading');

            }
        }); // ajax - category preview and category list
    }
});