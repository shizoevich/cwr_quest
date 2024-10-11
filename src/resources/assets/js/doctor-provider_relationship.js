import Alert from './Alert';

$(document).ready(function() {
    var prevProviderID;
    $('.doctor-provider').on('focus', function() {
        prevProviderID = $(this).val();
        if(prevProviderID === null) {
            prevProviderID = -1;
        }
    }).change(function() {
        $('#user_id').val($(this).data('userid'));
        $('#provider_id').val($(this).val());
        $('#provider-name').text($(this).find('option:selected').text());
        $('#user-name').text($(this).parents('tr').find('.user-email').text());
        $('#confirmDialog').modal('show');
    });

    $('#cancel-selection').click(function() {
        $('.doctor-provider[data-userid='+$('#user_id').val() + "] option[value=" + prevProviderID + ']').prop('selected', true);
    });

    $('#confirm-selection').click(function() {
        var data = {
            userId: $('#user_id').val(),
            providerId: $('#provider_id').val()
        };

        $.ajax({
            type: 'post',
            url: '/dashboard/doctors',
            data: data,
            cache: false,
            error: function(response) {
                console.log(response);
                let responseJSON = response.responseJSON;
                let message = 'Whoops, looks like something went wrong.';
                if(responseJSON.providerId) {
                    message = responseJSON.providerId[0];
                } else if(responseJSON.userId) {
                    message = responseJSON.userId[0];
                }
                $('.doctor-provider[data-userid='+$('#user_id').val() + "]")
                    .parent().children('.error-message')
                    .text(message);
            },
            success: function(response) {
                console.log(response);
                if(!response.success) {
                    $('.doctor-provider[data-userid='+$('#user_id').val() + "]").parent().children('.error-message').text(response.errorMessage);
                } else {
                    $('.doctor-provider[data-userid='+$('#user_id').val() + "]").parent().children('.error-message').empty();
                    $('td[data-userid='+data.userId+']').parents('tr').find('a, button, input, a, .btn-file').each(function(i, el) {
                        $(el).removeClass('disabled');
                        $(el).prop('disabled', false);
                        $(el).removeAttr('disabled');
                        if($(el).hasClass('add-signature')) {
                            $(el).removeClass('btn-danger').addClass('btn-success');
                        }
                    });
                    $('td[data-userid='+data.userId+'] button').remove();
                    $('td[data-userid='+data.userId+'] div.checkbox-disable-user').css({'display': 'block'});
                }
            }
        });
        $('#confirmDialog').modal('hide');
    });

    $('.show-confirm-deletion-user-modal').click(function() {
        var userID = $(this).data('user');
        $('#confirm-deletion-user-modal #user-id').val(userID);
        $('#confirm-deletion-user-modal').modal('show');
    });

    $('#confirm-deletion-user').click(function() {
        var data = {
            user_id: $('#confirm-deletion-user-modal #user-id').val(),
            _token: $('input[name=_token]').val()
        };
        $.ajax({
            type: 'post',
            url: '/dashboard/users/delete',
            cache: false,
            data: data,
            success: function(response) {
                console.log(response);
                if(response.success) {
                    window.location.reload();
                    /*$('.show-confirm-deletion-user-modal[data-user='+data.user_id+']').parents('tr').remove();
                    $('#confirm-deletion-user-modal').modal('hide');
                    if(response.message) {
                        Alert.show('#status-alert', response.message, 'alert-success');
                    }*/
                }
            }
        });
    });

    let $userToDisabling = null;
    $(document).on('click', '.enable-disable', function() {
        $userToDisabling = $(this);
        let mode = $(this).data('mode');
        // $('#disable-mode').text(mode);
        $('#user-name2').text($userToDisabling.parents('tr').find('.user-email').text());
        $('#confirmDisablingDialog').modal('show');
    });

    $('#cancel-disabling-user').on('click', function() {
        let prop = $userToDisabling.prop('checked');
        $userToDisabling.prop('checked', !prop);
        $userToDisabling = null;
    });

    $('#confirm-disabling-user').on('click', function() {
        $('#confirmDisablingDialog').modal('hide');

        axios({
            method: 'post',
            url: '/dashboard/enable-or-disable-user',
            data: {userId: $userToDisabling.data('user')}
        }).then(response => {
            if(response.data.success) {
                Alert.show('#status-alert', response.data.message, 'alert-success');
                if(response.data.enabled === true) {
                    $userToDisabling.attr('data-mode', 'disable');
                    $userToDisabling.find('.glyphicon').removeClass('glyphicon-refresh').addClass('glyphicon-remove');
                    $userToDisabling.parents('tr').find('.status').removeClass('text-danger').addClass('text-success').text('active');
                } else {
                    $userToDisabling.attr('data-mode', 'enable');
                    $userToDisabling.find('.glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-refresh');
                    $userToDisabling.parents('tr').find('.status').removeClass('text-success').addClass('text-danger').text('not active');
                }

                $userToDisabling = null;
                // window.location.reload();
            }

        }).catch(err => {
        });
    });

    $(document).on('click', '.add-tridiuum-credentials',  function() {
        let
            userId = $(this).data('user-id'),
            $modal = $('#tridiuum-credentials-modal'),
            $form = $modal.find('.tridiumm-form');

        $form.find('input[name="user_id"]').val(userId);
        $form.find('input[name="tridiuum_username"]').val('');
        $form.find('input[name="tridiuum_password"]').val('');

        clearError($form.find('input[name="tridiuum_username"]'));
        clearError($form.find('input[name="tridiuum_password"]'));

        $modal.modal('show');
    });

    function setError($field, text) {
        if(!$field) {
            return;
        }

        let $container = $field.parents('.form-group');

        $container.addClass('has-error');
        $container.find('.help-block').html(text);
    }

    function clearError($field) {
        if(!$field) {
            return;
        }

        let $container = $field.parents('.form-group');

        $container.removeClass('has-error');
        $container.find('.help-block').html('');
    }

    $('#tridiuum-save').on('click', function() {
        let $modal = $(this).parents('.modal'),
            $form = $modal.find('.tridiumm-form');

        clearError($form.find('input[name="tridiuum_username"]'));
        clearError($form.find('input[name="tridiuum_password"]'));
        simpleLoader();

        axios({
            method: $form.attr('method'),
            url: $form.attr('action'),
            data: new FormData($form.get(0))
        }).then(response => {
            if(response.data.success) {
                reverseLoader();
                $modal.modal('hide');
                window.location.reload();
            }
        }).catch(error => {
            reverseLoader();
            let response = error.response;
            if(response.status === 422) {
                for (let prop in response.data) {
                    setError($(`input[name=${prop}]`), response.data[prop][0]);
                }
            }
        });
    });

});