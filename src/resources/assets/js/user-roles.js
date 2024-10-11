(function($){

    $(document).on('change','.select-role',function () {
        let select = $(this);
        let userId = $(this).data('user');
        let role = $(this).val();
        axios.post('/dashboard/users/change-role', {
            user: userId,
            role: role
        }).then(response => {
            let tr = select.parents('tr');
            let user = response.data.user;
            if(user.role == 'user') {
                $(`#user-edit-${userId}`).removeClass('edit-secretary-action');
                if(!user.userName) {
                    tr.addClass('danger');
                }
            }
            else {
                $(`#user-edit-${userId}`).attr('class', 'edit-secretary-action');
                tr.removeClass('danger');
            }
            if(user.modal) {
                secretary_profile(user.id, false)
            }
            tr.find('td:nth-child(2)').text(user.userName);
            if($('#status-alert').length == 0) {
                $('.table-wrapper').before('<div id="status-alert"></div>')
            }
            $('#status-alert').removeAttr('class').addClass('alert  alert-success alert-dismissible').html(
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    response.data.response
                );
        }, error => {
            if($('#status-alert').length == 0) {
                $('.table-wrapper').before('<div id="status-alert"></div>');
            }
            $('#status-alert').removeAttr('class').addClass('alert  alert-danger alert-dismissible').html(
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    'Failed to change the role'
                );
        })

    });
    $('#auth-form.create-user #user_role').change(function() {
        if($(this).val() !== 'provider') {
            $('[name=tariff_plan_id], [name=provider_id]').prop('disabled', true).val('');
        } else {
            $('[name=tariff_plan_id], [name=provider_id]').prop('disabled', false);
        }
    });

    $(document).on('click', '.edit-secretary-action', function(e) {
        e.preventDefault();
        secretary_profile($(this).data('user'));
    })

    function secretary_profile(user, isRequest = true) {
        $('#secretary-profile-form .text-danger').remove();
        $('#secretary-profile').modal('show');
        $('#secretary-profile-form').data('user', user);
        let firstname, lastname;
        if(isRequest) {
            axios.get(`/dashboard/users/${user}/meta`)
                .then(response => {
                    firstname = response.data.firstname;
                    lastname = response.data.lastname;
                    $('#secretary-firstname').val(firstname);
                    $('#secretary-lastname').val(lastname);
                })
        }
        else {
            $('#secretary-firstname').val(firstname);
            $('#secretary-lastname').val(lastname);
        }
    }

    $(document).on('submit', '#secretary-profile-form', function(e) {
        e.preventDefault();
        let formEl = $(this);
        formEl.find('button[type=submit]').prop('disabled', true);
        $('#secretary-profile-form .text-danger').remove();
        let user = $(this).data('user');
        let payload = {
            firstname: $(this).find('#secretary-firstname').val(),
            lastname: $(this).find('#secretary-lastname').val()
        };

        axios.put(`/dashboard/users/${user}/meta`, payload)
            .then(response => {
                formEl.find('button[type=submit]').prop('disabled', false);
                let name = `${response.data.firstname} ${response.data.lastname}`;
                $(`#user-name-${user}`).text(name);
                $('#secretary-profile').modal('hide');
            })
            .catch(function (errors) {
                formEl.find('button[type=submit]').prop('disabled', false);
                let names = Object.keys(errors.response.data);
                for(let name of names) {
                    for(let error of errors.response.data[name]) {
                        let message = `<p class="text-danger">${error}</p>`;
                        $(`#secretary-${name}`).after(message);
                    }
                }
            });
    });
})(jQuery);