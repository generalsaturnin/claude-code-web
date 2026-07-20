"use strict";
jQuery(document).ready(function(){
    //code to add validation on "Add to Cart" button

    jQuery( ".ajax_add_to_cart" ).live( "click", function() {

        console.log('ajax add to cart click');
        //code to add validation, if any
        //If all values are proper, then send AJAX request

        var custom_data_1 = false;
        var custom_data_2 = false;
        var custom_data_3 = false;
        var custom_data_4 = false;
        var custom_data_5 = false;

        if (jQuery(this).attr('data-checkin')) {
            custom_data_1 = jQuery(this).attr('data-checkin')
        }

        if (jQuery(this).attr('data-checkout')) {
            custom_data_2 = jQuery(this).attr('data-checkout')
        }


        jQuery.ajax({
         url: th_ajax.ajaxurl, //AJAX file path - admin_url('admin-ajax.php')
         type: "POST",
            data: {
                //action name
                action:'wdm_add_user_custom_data_options',
                custom_data_1 : custom_data_1,
                custom_data_2 : custom_data_2,
                custom_data_3 : custom_data_3,
                custom_data_4 : custom_data_4,
                custom_data_5 : custom_data_5

            },
         async : false,
         success: function(data){
         //Code, that need to be executed when data arrives after
         // successful AJAX request execution
         }
         });
    });
});




