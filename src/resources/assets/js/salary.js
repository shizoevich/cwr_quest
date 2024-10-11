$(document).ready(function() {
    $('.edit-table').on('click', function () {
        var tabPanel = $(this).closest('.tab-pane');
        tabPanel.addClass('edit');

        tabPanel.find('.inline-edit > span').each(function(i,item) {
            $(item).prev().val($(item).text().trim());
        });

    });

    $('.cancel-edit-table').on('click', function () {
        console.log(this);
        var tabPanel = $(this).closest('.tab-pane');
        console.log(tabPanel);
        tabPanel.removeClass('edit');
    });

    $('.confirm-save-table').on('click', function () {
        $('#confirm-save-fee-modal').modal('show');
    });

    $('.password').on('keyup change', function() {
        $('.password-form-group').removeClass('has-error')
        $('.password-form-group span.with-errors strong').html('')
        $(this).prop('disabled', false);
    });

    function closeConfirmSaveTableModal()
    {
        $('#confirm-save-fee-modal').modal('hide');
        $('#confirm-save-missing-pn-fee-modal').modal('hide');
        $('.password-form-group').removeClass('has-error')
        $('.password-form-group span.with-errors strong').html('')
        $('input.password').val('');
    }

    $('.close-confirm-save-fee-modal').on('click', function() {
        closeConfirmSaveTableModal();
    });

    $('#confirm-save-fee-modal .save-table').on('click', function () {
        $('#confirm-save-fee-modal .form-group').each((formGroup) => {
            $(formGroup).removeClass('has-error');
            $(formGroup).find('span.with-erorrs strong').html('');
        })
        $(this).prop('disabled', true);
        $('.close-confirm-save-fee-modal').prop('disabled', true);
        var tabPanel = $('.tab-pane.active');
        var data = [];
        tabPanel.find('.inline-edit > input').each(function(i,item) {
            var newValue = $(item).val().trim();
            var oldValue = $(item).next().text().replace(' - ','').trim();

            if(oldValue != newValue) {
                var key = $(item).data('plan_id') + '-' + $(item).data('tariff_plan_id') + '-' + $(item).data('procedure_id') + '-' + $(item).data('type');
                var priceKey = 'price';
                if($(item).data('is_telehealth') === 1) {
                    priceKey = 'telehealth_price';
                }
                if(!data[key]) {
                    data[key] = {
                        plan_id:$(item).data('plan_id'),
                        tariff_plan_id:$(item).data('tariff_plan_id'),
                        procedure_id:$(item).data('procedure_id'),
                        type:$(item).data('type'),
                    }
                }
                data[key][priceKey] = $(item).val();
            }
        });

        // $('#insurances-wrapper .panel').addClass('loading');

        axios.post('/dashboard/tariffs-plans/prices',{
            prices: Object.values(data),
            password: $('#confirm-save-fee-modal input.password').val(),
            date_from: $('#confirm-save-fee-modal input[name="date"]').val(),
        }).then(function(response) {
            if(response.status == 200) {
                tabPanel.find('.inline-edit > input').each(function(i,item) {
                    var span = $(item).next();
                    var val = $(item).val();
                    span.text(val != '' ? val : ' - ');
                });
            }

            tabPanel.removeClass('edit');
            // $('#insurances-wrapper .panel').removeClass('loading');
            closeConfirmSaveTableModal();
        }).catch(function(error) {
            var response = error.response;
            var message = 'Error! Please try again.';
            if (response && response.data) {
                for (let errorKey in response.data) {
                    $(`.${errorKey}-form-group`).addClass('has-error');
                    $(`.${errorKey}-form-group span.with-errors strong`).html(response.data[errorKey][0]);
                }
            }
        }).finally(() => {
            $(this).prop('disabled', false);
            $('.close-confirm-save-fee-modal').prop('disabled', false);
        });
    });

    $('.insurance-selector').on('change', function(item) {
        $('a[href="#insurance_'+$(this).val()+'"]').click();
    });

    $('.tariff-plan-delete-button').on('click', function () {
       var plan_id = $(this).closest('td').data('tariff_plan_id');
       var plan_name = $(this).closest('tr').find('td:first-child').text().trim();
       var text = 'You want to delete the tariff plan "'+plan_name+'". Are you sure?';
       var url = $('#delete-tariff-plan-modal form').data('default_url') + '/' + plan_id;
       $('#delete-tariff-plan-modal form').attr('action', url);
       $('#delete-tariff-plan-modal .modal-body span').text(text);
    });

    $('.tariff-plan-clone-button').on('click', function () {
        var plan_id = $(this).closest('td').data('tariff_plan_id');
        var plan_name = $(this).closest('tr').find('td:first-child').text().trim();
        var text = 'You want to clone the tariff plan "'+plan_name+'".';

        $('#clone-tariff-plan-modal .modal-body span').text(text);
        $('#clone-tariff-plan-modal .modal-body input[name="name"]').val(plan_name + ' (duplicate)');
        $('#clone-tariff-plan-modal .modal-body input[name="tariff_plan_id"]').val(plan_id);
    });

    $('#insurances-wrapper input[name="tariff_plan_name"]').on('keyup', function () {
        if($(this).val() == $(this).data('old_value')) {
            $('#save_plan_name').prop('disabled', true);
        } else {
            $('#save_plan_name').prop('disabled', false);
        }
    });
    $('#insurances-wrapper input[name="missing_progress_note_fee"]').on('keyup change', function () {
        if($(this).val() == $(this).data('old_value') || $(this).val() < 0) {
            $('#confirm_save_missing_progress_note_fee').prop('disabled', true);
        } else {
            $('#confirm_save_missing_progress_note_fee').prop('disabled', false);
        }
    });

    $('#save_plan_name').on('click', function () {
        var tariff_plan_id = $(this).data('tariff_plan_id');
        axios.put('/dashboard/tariffs-plans/'+tariff_plan_id,{
            name: $('#insurances-wrapper input[name="tariff_plan_name"]').val()
        }).then(function(response) {
           console.log(response);
            if(response.status === 200) {
                $('#insurances-wrapper input[name="tariff_plan_name"]').data('old_value',$('#insurances-wrapper input[name="tariff_plan_name"]').val());
                $('#save_plan_name').prop('disabled', true);
            }
        });

    });

    $('#confirm_save_missing_progress_note_fee').on('click', function() {
        $('#confirm-save-missing-pn-fee-modal').modal('show');
    });

    $('#confirm-save-missing-pn-fee-modal .save-table').on('click', function () {
        $('#confirm-save-missing-pn-fee-modal .form-group').each((formGroup) => {
            $(formGroup).removeClass('has-error');
            $(formGroup).find('span.with-erorrs strong').html('');
        })
        $(this).prop('disabled', true);
        $('.close-confirm-save-fee-modal').prop('disabled', true);
        var tariff_plan_id = $('input#tariff_plan_id').val();
        axios.put('/dashboard/tariffs-plans/'+tariff_plan_id,{
            fee_per_missing_pn: $('#insurances-wrapper input[name="missing_progress_note_fee"]').val(),
            password: $('#confirm-save-missing-pn-fee-modal input.password').val(),
            date_from: $('#confirm-save-missing-pn-fee-modal input[name="date"]').val(),
        }).then(function(response) {
            $('#insurances-wrapper input[name="missing_progress_note_fee"]').data('old_value',$('#insurances-wrapper input[name="missing_progress_note_fee"]').val());
            $('#confirm_save_missing_progress_note_fee').prop('disabled', true);
            closeConfirmSaveTableModal();
        }).catch(function(error) {
            var response = error.response;
            var message = 'Error! Please try again.';
            if (response && response.data) {
                for (let errorKey in response.data) {
                    $(`.${errorKey}-form-group`).addClass('has-error');
                    $(`.${errorKey}-form-group span.with-errors strong`).html(response.data[errorKey][0]);
                }
            }
        }).finally(() => {
            $(this).prop('disabled', false);
            $('.close-confirm-save-fee-modal').prop('disabled', false);
        });

    });

    $('.grouped_plans_checkox').on('click', function() {
        var ids = $('.tab-pane.active .grouped_plans_checkox:checked').map(function(i,item){return $(item).val();});
        if($('.tab-pane.active button.group-plans').hasClass('hidden')) {
            if(ids.length > 1) {
                $('.tab-pane.active button.group-plans').removeClass('hidden')
            }
        } else {
            if(ids.length < 2) {
                $('.tab-pane.active button.group-plans').addClass('hidden')
            }
        }

        if($('.tab-pane.active button.ungroup-plans').hasClass('hidden')) {
            if(ids.length == 1) {
                if($('.tab-pane.active .grouped_plans_checkox:checked').parent().next().find('[data-toggle="tooltip"]').length > 0) {
                    $('.tab-pane.active button.ungroup-plans').removeClass('hidden')
                }
            }
        } else {
            if(ids.length != 1) {
                $('.tab-pane.active button.ungroup-plans').addClass('hidden')
            }
        }
        // $('.tab-pane.active button.group-plans').attr('disabled', ids.length < 2);
    });
    $('button.group-plans').on('click', function() {
        var ids = [];
        var plans = [];
        $('#group-insurance-plans-modal .modal-body ul').text('');
        $('.tab-pane.active .grouped_plans_checkox:checked').each(function(i,item){
            ids.push($(item).val());
            var planName = $(item).parent().next().find('.name').text().trim();
            $('#group-insurance-plans-modal .modal-body ul').append('<li>' + planName+'</li>');
            plans.push(planName);
        });

        $('#group-insurance-plans-modal input[name="name"]').val(plans[0]);
        $('#group-insurance-plans-modal input[name="plans_ids"]').val(ids.join());
        $('#group-insurance-plans-modal input[name="tariff_plan_id"]').val($(this).data('tariff_plan_id'));
        $('#group-insurance-plans-modal input[name="insurance_id"]').val($(this).data('insurance_id'));
        $('#group-insurance-plans-modal').modal('show');
    });

    $('button.ungroup-plans').on('click', function() {
        var ids = [];
        var plans = [];
        $('#ungroup-insurance-plans-modal .modal-body ul').text('');
        $('.tab-pane.active .grouped_plans_checkox:checked').each(function(i,item){
            ids.push($(item).val());
            var planName = $(item).parent().next().find('.name').text().trim();
            $('#ungroup-insurance-plans-modal .modal-body ul').append('<li>' + planName+'</li>');
            plans.push(planName);
        });

        $('#ungroup-insurance-plans-modal input[name="plan_id"]').val(ids.join());
        $('#ungroup-insurance-plans-modal input[name="tariff_plan_id"]').val($(this).data('tariff_plan_id'));
        $('#ungroup-insurance-plans-modal input[name="insurance_id"]').val($(this).data('insurance_id'));
        $('#ungroup-insurance-plans-modal').modal('show');
    });

    $('[data-toggle="tooltip"]').tooltip();
});