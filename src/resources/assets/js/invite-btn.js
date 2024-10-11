import Alert from './Alert';
;(function (window, $) {

    'use strict';

    $(document).ready(function () {
        let
            modal = $('#invite-dialog'),
            clearModal = function() {
                modal.find('#email').val('');
                modal.find('input').parents('.form-group').removeClass('has-error').find('.help-block').html('');
            },
            send = function (email, callback) {
                $.ajax({
                    type: 'post',
                    url: '/dashboard/invite',
                    data: {email:email},
                    cache: false,
                    error: function(response) {
                        $.each(response.responseJSON, function(key, value) {
                            let el = modal.find('#'+key);
                            el.parents('.form-group').addClass('has-error').find('.help-block').html('<strong>'+value+'</strong>');
                        });
                    },
                    success: function(response) {
                        Alert.show('#status-alert', response.message, 'alert-success');
                        if(callback instanceof Function) {
                            callback();
                            modal.modal('hide');
                        }
                    }
                });
            };

        $('#invite').on('click', function (e) {
            modal.modal('show');
        });

        modal.find('#send').on('click', function () {
            send(modal.find('#email').val(), clearModal);
        });
        modal.find('#cancel').on('click', function () {
            console.log('cancel');
        });
        
        modal.on('hidden.bs.modal', function() {
            clearModal();
        });
    })

})(window, jQuery);