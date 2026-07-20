var textData;

jQuery(document).ready(function ($) {
    var success = 'code_form_success';
    var error = 'code_form_error';
    var loading = 'code_form_loading';
    var form = $('#thmv_registration_form');
    var formUnregister = $('#thmv_unregister_form');
    var bProcessing = false;

    form.on('submit', function (event) {
        event.preventDefault();
        registerCode();
    });
    formUnregister.on('submit', function (event) {
        event.preventDefault();
        unRegisterCode();
    });
    function unRegisterCode() {
        if (bProcessing)
            return;

        bProcessing = true;
        hideEelement(success);
        hideEelement(error);
        showEelement(formUnregister, loading, '....');
        var remoteResponse;
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: ajaxurl,
            data: {
                // Required
                action: 'bellevue_unregister_theme',
            },
            success: function (response) {
                remoteResponse = response;
                if (!response.success) {
                    hideEelement(success);
                    showEelement(formUnregister, error, response.message, 'error');
                } else {
                    hideEelement(error);
                    showEelement(formUnregister, success, response.message, 'success');
                    formUnregister.closest('.thmv-dash-row').find('.thmv-dash-title svg').show();
                    setTimeout(function () {
                        formUnregister.fadeOut(function () {
                            hideEelement(success);
                            form.fadeIn();
                        });
                    }, 1000);
                }
            }, // success:
            error: function (response) {
                remoteResponse = response;
                if (response.message) {
                    showEelement(formUnregister, error, response.message, 'error');
                }
                bProcessing = false;
            },
            complete: function () {
                bProcessing = false;
                hideEelement(loading);
            }
        });

    }
    function registerCode() {
        if (bProcessing)
            return;

        bProcessing = true;
        hideEelement(success);
        hideEelement(error);
        showEelement(form, loading, '....');
        var code = form.find('input').val();
        runAjax(code, 0);
    }

    function runAjax(code, nextStage = 0) {
        var remoteResponse;
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: ajaxurl,
            data: {
                // Required
                action: 'bellevue_register_theme',
                code: code,
                nextStage: nextStage,
            },
            success: function (response) {
                remoteResponse = response;
                if (!response.success) {
                    hideEelement(success);
                    showEelement(form, error, response.message, 'error');
                } else {
                    hideEelement(error);
                    showEelement(form, success, response.message, 'success');
                }
            }, // success:
            error: function (response) {
                remoteResponse = response;
                if (response.message) {
                    showEelement(form, error, response.message, 'error');
                }
                bProcessing = false;
            },
            complete: function () {
                //if install action retured

                console.log(remoteResponse);
                if (typeof remoteResponse !== 'object') {
                    console.log('big error');
                    bProcessing = false;
                    hideEelement(loading);
                    return;
                }
                
                if (remoteResponse.data !== undefined) {
                    if (remoteResponse.data.stop_exe === true) {
                        bProcessing = false;
                        hideEelement(loading);
                        if (remoteResponse.data.registerSuccess) {
                            form.closest('.thmv-dash-row').find('.thmv-dash-title svg').hide();
                            form.find('input').val('');
                            setTimeout(function () {
                                form.fadeOut(function () {
                                    hideEelement(success);
                                    formUnregister.fadeIn();
                                });
                            }, 1000);
                        }
                    } else {
                        runAjax(code, remoteResponse.data.nextStage);
                    }
                }

                if (remoteResponse.done !== undefined) {
                    if (remoteResponse.done == 1) {
                        var newStage = nextStage+1;
                        runAjax(code, newStage);
                    } else {
                        console.log('some error');
                        bProcessing = false;
                        hideEelement(loading);
                    }

                }
                if (remoteResponse.url !== undefined) {
                    var lastStage = nextStage;
                    $.ajax({
                        type: 'post',
                        dataType: 'html',
                        url: remoteResponse.url,
                        data: remoteResponse
                    }).success(function (response) {
                        console.log('html install');
                        //if must have installed and activated the plugin, let's check
                        runAjax(code, lastStage);
                    }).fail(function () {
                        console.log('html error');
                        bProcessing = false;
                        hideEelement(loading);
                    });

                }





            }
        });
    }
});