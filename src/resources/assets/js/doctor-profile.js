$(document).ready(function() {
    var prevProviderID;

    let userName = '';
    let tridiuumProviderName = '';

    function disableSyncCheckboxes(status) {
        if ($('.sync-group').length) {
            $('.sync-checkbox').each(function() {
               $(this).attr('disabled', status);
            });
        }
    }

    function setTridiuumMessage(text, status) {
        let messageBox = $('#tridiuum_message');
        messageBox.removeClass();
        messageBox.addClass(status === 'error' ? 'error-message' : 'success-message');
        messageBox.text(text);
    }

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

    $('.doctor-tridiuum-provider').change(function() {
        $('#confirmDialogTridiuum #user_id').val($(this).data('userid'));
        $('#confirmDialogTridiuum  #tridiuum_provider_id').val($(this).val())
        tridiuumProviderName = $(this).find('option:selected').text();
        userName = $('.profile-control').find('.provider-name').text();
        let message = `Are you sure that you want to assign provider <span class="text-bold">${userName}</span> to <span class="text-bold">${tridiuumProviderName}</span> Tridiuum account?`;
        if (!$(this).val()) {
            message = `Are you sure that you want to unassign provider <span class="text-bold">${userName}</span> from Tridiuum account?`;
        }

        $('#confirmDialogTridiuum #assign_message').html(message);
        $('#confirmDialogTridiuum').modal('show');
    });

    $('.doctor-tariff-plan').change(function() {
        $('#confirmChangeTariffPlanDialog input[name="tariff_plan_id"]').val($(this).val());
        $('#confirmChangeTariffPlanDialog input[name="provider_id"]').val($('.doctor-provider').val());
        $('#confirmChangeTariffPlanDialog .provider-name').text($('h3.provider-name').text().trim());
        $('#confirmChangeTariffPlanDialog .tariff-plan-name').text($(this).find('option:selected').text());
        $('#confirmChangeTariffPlanDialog').modal('show');
    });
    $('.doctor-billing-period').change(function() {
        $('#confirmChangeBillingPeriodDialog input[name="billing_period_type_id"]').val($(this).val());
        $('#confirmChangeBillingPeriodDialog input[name="provider_id"]').val($('.doctor-provider').val());
        $('#confirmChangeBillingPeriodDialog .provider-name').text($('h3.provider-name').text().trim());
        $('#confirmChangeBillingPeriodDialog .billing-period-name').text($(this).find('option:selected').text());
        $('#confirmChangeBillingPeriodDialog').modal('show');
    });
    $('.work-hours-per-week').blur(function () {
        const currentValue = +$(this).val();
        const initialValue = +$(this).data('initial-value');
        
        if (currentValue === initialValue) {
            return;
        }

        $('#confirmChangeWorkHoursDialog input[name="work_hours_per_week"]').val(currentValue);
        $('#confirmChangeWorkHoursDialog input[name="provider_id"]').val($('.doctor-provider').val());
        $('#confirmChangeWorkHoursDialog .provider-name').text($('h3.provider-name').text().trim());
        $('#confirmChangeWorkHoursDialog .work-hours-num').text(currentValue);
        $('#confirmChangeWorkHoursDialog').modal('show');
    });
    $('.license-date').blur(function () {
        const currentValue = $(this).val();
        const initialValue = $(this).data('initial-value');

        if (currentValue === initialValue) {
            return;
        }

        $('#confirmLicenseDateDialog input[name="license_date"]').val(currentValue);
        $('#confirmLicenseDateDialog input[name="provider_id"]').val($('.doctor-provider').val());
        $('#confirmLicenseDateDialog .provider-name').text($('h3.provider-name').text().trim());
        $('#confirmLicenseDateDialog .license-date-dialog-text').text(currentValue);
        $('#confirmLicenseDateDialog').modal('show');
    });
    $('.license-end-date').blur(function () {
        const currentValue = $(this).val();
        const initialValue = $(this).data('initial-value');

        if (currentValue === initialValue) {
            return;
        }

        $('#confirmLicenseEndDateDialog input[name="license_end_date"]').val(currentValue);
        $('#confirmLicenseEndDateDialog input[name="provider_id"]').val($('.doctor-provider').val());
        $('#confirmLicenseEndDateDialog .provider-name').text($('h3.provider-name').text().trim());
        $('#confirmLicenseEndDateDialog .license-end-date-dialog-text').text(currentValue);
        $('#confirmLicenseEndDateDialog').modal('show');
    });

    $('#show-signature-btn').click(function () {
        axios({
            method: "post",
            url: "/provider/get-signature",
            data: {
                provider_id: $(this).data('provider-id'),
            },
        }).then((response) => {
            const signature = response.data && response.data.signature;
            if (signature) {
                $('#signature-preview-modal .modal-body').html(`
                    <img src="${signature}" alt="signature">
                `);
            } else {
                $('#signature-preview-modal .modal-body').html(`
                    <p class="wo-mb">An error occurred, the signature was not loaded.</p>
                `);
            }
        })
        .catch((error) => {
            $('#signature-preview-modal .modal-body').html(`
                <p class="wo-mb">An error occurred, the signature was not loaded.</p>
            `);
            console.log(error);
        })
        .finally(() => {
            $('#signature-preview-modal').modal('show');
        });
    });

    $('#sms-signature-btn').click(function () {
        $('#sendSmsToChangeSignatureDialog').modal('show');
    });

    if ($('.sync-group').length) {
        let providerId = $('.doctor-provider').val();
        $('.sync-checkbox').each(function() {
            $(this).change(() => {
                let formData = {
                    '_method': 'PATCH',
                };
                formData[this.name] = this.checked;
                disableSyncCheckboxes(true);
                axios.post(`/api/providers/${providerId}`, formData)
                    .then((response) => {
                        let statusString = '';
                        let message = '';
                        if (this.checked) {
                            statusString = 'enabled';
                        } else {
                            statusString = 'disabled';
                        }

                        if (this.id === 'appointment_sync') {
                            message = `Appointment synchronization ${statusString}`;
                        } else {
                            message = `Availability synchronization ${statusString}`;
                        }

                        setTridiuumMessage(message, 'success')
                    })
                    .catch((error) => {
                        let message = 'Whoops, looks like something went wrong.';
                        setTridiuumMessage(message, 'error');
                    })
                    .finally(() => {
                        disableSyncCheckboxes(false);
                    })
            });
        })
    }

    $('#tariff-plan-confirm-selection').click(function() {
        var data = {
            tariffPlanId: $('#confirmChangeTariffPlanDialog input[name="tariff_plan_id"]').val(),
            providerId: $('#confirmChangeTariffPlanDialog input[name="provider_id"]').val(),
            date_from: $('#confirmChangeTariffPlanDialog input[name="date"]').val(),
        };

        $('#confirmChangeTariffPlanDialog .form-group').removeClass('has-error');
        $('#confirmChangeTariffPlanDialog .form-group span.with-errors strong').html('');

        axios({
            method: 'post',
            url: '/dashboard/doctors/tariff-plan',
            data: data,
        })
            .then(() => {
                $('#confirmChangeTariffPlanDialog').modal('hide');
            })
            .catch((error) => {
                if (!error.response) {
                    return;
                }
                if(error.response.status === 401) {
                    window.location.href = window.location.href;
                    return;
                }
                let message = 'Whoops, looks like something went wrong.';
                if(error.response.data.providerId) {
                    message = error.response.data.providerId[0];
                } else if(error.response.data.userId) {
                    message = error.response.data.userId[0];
                }
                if (error.response.data.date_from) {
                    $('#confirmChangeTariffPlanDialog .form-group').addClass('has-error');
                    $('#confirmChangeTariffPlanDialog .form-group span.with-errors strong').html(error.response.data.date_from[0]);
                }
                $('.doctor-provider[data-userid='+$('#user_id').val() + "]")
                    .parent().children('.error-message')
                    .text(message);
            });
    });

    $('#billing-period-confirm-selection').click(function() {
        var data = {
            billingPeriodTypeId: $('#confirmChangeBillingPeriodDialog input[name="billing_period_type_id"]').val(),
            providerId: $('#confirmChangeBillingPeriodDialog input[name="provider_id"]').val()
        };

        axios({
            method: 'post',
            url: '/dashboard/doctors/billing-period',
            data: data,
        })
            .then(() => {
                $('#confirmChangeBillingPeriodDialog').modal('hide');
            })
            .catch((error) => {
                if (!error.response) {
                    return;
                }
                if(error.response.status === 401) {
                    window.location.href = window.location.href;
                    return;
                }
                let message = 'Whoops, looks like something went wrong.';
                if(error.response.data.providerId) {
                    message = error.response.data.providerId[0];
                } else if(error.response.data.userId) {
                    message = error.response.data.userId[0];
                }
                $('.doctor-provider[data-userid='+$('#user_id').val() + "]")
                    .parent().children('.error-message')
                    .text(message);
            });
    });

    $('#work-hours-confirm-selection').click(function() {
        const workHoursPerWeek = $('#confirmChangeWorkHoursDialog input[name="work_hours_per_week"]').val();
        const providerId = $('#confirmChangeWorkHoursDialog input[name="provider_id"]').val();

        var data = {
            workHoursPerWeek: workHoursPerWeek,
            providerId: providerId,
        };

        axios({
            method: 'post',
            url: '/dashboard/doctors/work-hours-per-week',
            data: data,
        })
            .then(() => {
                $('#confirmChangeWorkHoursDialog').modal('hide');
                $('.work-hours-per-week').data('initial-value', workHoursPerWeek);
                $('.work-hours-per-week')
                    .parent().children('.error-message')
                    .text('');
            })
            .catch((error) => {
                if (!error.response) {
                    return;
                }
                if(error.response.status === 401) {
                    window.location.href = window.location.href;
                    return;
                }
                let message = 'Whoops, looks like something went wrong.';
                if(error.response.data.providerId) {
                    message = error.response.data.providerId[0];
                } else if(error.response.data.userId) {
                    message = error.response.data.userId[0];
                }
                $('.work-hours-per-week')
                    .parent().children('.error-message')
                    .text(message);
                $('#confirmChangeWorkHoursDialog').modal('hide');
            });
    });

    $('#work-hours-cancel-selection').click(function() {
        $('.work-hours-per-week').val($('.work-hours-per-week').data('initial-value'));
    });

    //------------------------------

    $('#license-date-confirm-selection').click(function() {
        const licenseDate = $('#confirmLicenseDateDialog input[name="license_date"]').val();
        const providerId = $('#confirmLicenseDateDialog input[name="provider_id"]').val();

        var data = {
            licenseDate: licenseDate,
            providerId: providerId,
        };

        axios({
            method: 'post',
            url: '/dashboard/doctors/license-date',
            data: data,
        })
            .then(() => {
                $('#confirmLicenseDateDialog').modal('hide');
                $('.license-date').data('initial-value', licenseDate);
                $('.license-date')
                    .closest('.form-group')
                    .children('.error-message')
                    .text('');
            })
            .catch((error) => {
                if (!error.response) {
                    return;
                }
                if(error.response.status === 401) {
                    window.location.href = window.location.href;
                    return;
                }
                let message = 'Whoops, looks like something went wrong.';
                if(error.response.data.providerId) {
                    message = error.response.data.providerId[0];
                } else if(error.response.data.userId) {
                    message = error.response.data.userId[0];
                }
                $('.license-date')
                    .closest('.form-group')
                    .children('.error-message')
                    .text(message);
                $('#confirmLicenseDateDialog').modal('hide');
            });
    });

    $('#license-date-cancel-selection').click(function() {
        $('.license-date').val($('.license-date').data('initial-value'));
    });

    //------------------------------

    //------------------------------

    $('#license-end-date-confirm-selection').click(function() {
        const licenseEndDate = $('#confirmLicenseEndDateDialog input[name="license_end_date"]').val();
        const providerId = $('#confirmLicenseEndDateDialog input[name="provider_id"]').val();

        var data = {
            licenseEndDate: licenseEndDate,
            providerId: providerId,
        };

        axios({
            method: 'post',
            url: '/dashboard/doctors/license-end-date',
            data: data,
        })
            .then(() => {
                $('#confirmLicenseEndDateDialog').modal('hide');
                $('.license-end-date').data('initial-value', licenseEndDate);
                $('.license-end-date')
                    .closest('.form-group')
                    .children('.error-message')
                    .text('');
            })
            .catch((error) => {
                if (!error.response) {
                    return;
                }
                if(error.response.status === 401) {
                    window.location.href = window.location.href;
                    return;
                }
                let message = 'Whoops, looks like something went wrong.';
                if(error.response.data.providerId) {
                    message = error.response.data.providerId[0];
                } else if(error.response.data.userId) {
                    message = error.response.data.userId[0];
                }
                $('.license-end-date')
                    .closest('.form-group')
                    .children('.error-message')
                    .text(message);
                $('#confirmLicenseEndDateDialog').modal('hide');
            });
    });

    $('#license-end-date-cancel-selection').click(function() {
        $('.license-end-date').val($('.license-end-date').data('initial-value'));
    });

    //------------------------------

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
                if(!response.success) {
                    $('.doctor-provider[data-userid='+$('#user_id').val() + "]").parent().children('.error-message').text(response.errorMessage);
                } else {
                    location.reload();
                    // $('.doctor-provider[data-userid='+$('#user_id').val() + "]").parent().children('.error-message').empty();
                    // $('.provider-name').text($('.doctor-provider').find('option:selected').text());
                    // $('#provider-summary').remove();
                    // $('select[name="doctor-tariff-plan"]').parent().remove();
                    // $('#add-signature-btn').removeClass('disabled')
                    //     .prop('disabled', false)
                    //     .removeAttr('disabled')
                    //     .removeClass('btn-danger')
                    //     .addClass('btn-success');
                    // $('.profile-control').find('a, button, input, a, .btn-file').each(function(i, el) {
                    //     $(el).removeClass('disabled');
                    //     $(el).prop('disabled', false);
                    //     $(el).removeAttr('disabled');
                    //     if($(el).hasClass('add-signature')) {
                    //         $(el).removeClass('btn-danger').addClass('btn-success');
                    //     }
                    // });
                }
            }
        });
        $('#confirmDialog').modal('hide');
    });

    $('#confirm-selection-tridiuum').click(function() {
        let tridiuumProviderId = $('#confirmDialogTridiuum #tridiuum_provider_id').val();
        let tridiuumMessageBox = $('#tridiuum_message');

        var data = {
            tridiuum_provider_id: tridiuumProviderId ? tridiuumProviderId : null,
        };

        axios.post(`/api/providers/${$('#confirmDialogTridiuum #user_id').val()}/tridiuum-provider`, data, {
            headers: {
                'Accept': 'application/json'
            }
        })
          .then((response) => {
              tridiuumMessageBox.removeClass();
              let message = '';
              if (response.data.success) {
                  if (tridiuumProviderId) {
                      message = `The provider ${userName} has been successfully assigned to the ${tridiuumProviderName} Tridiuum account`;
                  } else {
                      message = `The provider ${userName} has been successfully unassigned from Tridiuum account`;
                  }
                  tridiuumMessageBox.addClass('success-message');
              } else {
                  tridiuumMessageBox.addClass('error-message');
                  message = response.data.errorMessage;
              }
              tridiuumMessageBox.text(message);
          })
          .catch((error) => {
              tridiuumMessageBox.removeClass();
              let response = error.response;

              let message = 'Whoops, looks like something went wrong.';
              if (error.response.data && error.response.data.tridiuum_provider_id) {
                  message = error.response.data.tridiuum_provider_id[0];
              }

              tridiuumMessageBox.addClass('error-message');
              tridiuumMessageBox.text(message);
          })

        // $.ajax({
        //     type: 'post',
        //     url: `api/providers/${$('#confirmDialogTridiuum #user_id').val()}/tridiuum-provider`,
        //     data: data,
        //     cache: false,
        //     dataType: 'json',
        //     error: function(response) {
        //         console.log(response);
        //         let responseJSON = response.responseJSON;
        //         let message = 'Whoops, looks like something went wrong.';
        //         if (responseJSON.errorMessage) {
        //             message = responseJSON.errorMessage;
        //         }
        //         $('.doctor-tridiuum-provider[data-userid='+$('#confirmDialogTridiuum #user_id').val() + "]")
        //           .parent().children('.error-message')
        //           .text(message);
        //     },
        //     success: function(response) {
        //         if(!response.success) {
        //             $('.doctor-tridiuum-provider[data-userid='+$('#confirmDialogTridiuum #user_id').val() + "]").parent().children('.error-message').text(response.errorMessage);
        //         } else {
        //             location.reload();
        //         }
        //     }
        // });
        $('#confirmDialogTridiuum').modal('hide');
    });
});