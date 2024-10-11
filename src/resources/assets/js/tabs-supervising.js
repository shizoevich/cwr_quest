

$(document).ready(() => {
    const superviseesTable = $('#supervisees_table').DataTable({
        ajax: {
            url: window.location.pathname + '/supervisees/api',
            dataSrc: ''
        },
        'paging': false,
        'lengthChange': false,
        'searching': false,
        'ordering': true,
        'info': false,
        'autoWidth': false,
        order: [[1, 'asc']],
        columns: [
            {data: 'index', orderable: false},
            {
                data: 'provider_name',
                orderable: true,
                render: function (data, type, row) {
                    if (type === 'display') {
                        if (!row.is_active) {
                            return `${data} <span class='text-danger' style="margin-left:5px;">Access suspended</span>`;
                        }
                    }

                    return data;
                },
            },
            {data: 'attached_at', orderable: true},
            {data: 'patients_count', orderable: true},
        ],
        hideEmptyCols: true,
        createdRow: function (row, data) {
            $(row).addClass(data.rowClass);
        },
        fnDrawCallback: function () {
            updateTableRowNumbers(this);
        },
    });

    function updateTableRowNumbers(el) {
        var api = el.api();
        var startIndex = api.context[0]._iDisplayStart;

        api.column(0, { search: "applied", order: "applied" })
            .nodes()
            .each(function (cell, i) {
                cell.innerHTML = startIndex + i + 1;
            });
    }

    $('#is_supervisor-yes').change(function() {
        storeIsSupervisor(JSON.parse(this.value), this.dataset.providerId);
        toggleSupervisorsSelect(JSON.parse(this.value));
    });

    $('#is_supervisor-no').change(function() {
        storeIsSupervisor(JSON.parse(this.value), this.dataset.providerId);
        toggleSupervisorsSelect(JSON.parse(this.value));
        removeSuperviseesTable();
    });

    function removeSuperviseesTable() {
        $('#supervisees_table_wrapper').remove();
    }

    function storeIsSupervisor(value, providerId) {
        disableIsSupervisorInputs(true);

        axios.post(`/api/providers/${providerId}/is-supervisor`, { 'is_supervisor': value })
            .then(() => {
                let statusString = '';
                if (value) {
                    statusString = 'enabled';
                } else {
                    statusString = 'disabled';
                }

                let message = `Supervisor status was ${statusString}.`;

                setSupervisorMessage(message, 'success');
            })
            .catch(() => {
                let message = 'Whoops, looks like something went wrong.';
                setSupervisorMessage(message, 'error');
            })
            .finally(() => {
                disableIsSupervisorInputs(false);
            });
    }

    function disableIsSupervisorInputs(disabled) {
        $('#is_supervisor-yes').attr('disabled', disabled);
        $('#is_supervisor-no').attr('disabled', disabled);
        $('#supervisors').attr('disabled', disabled);
    }

    function setSupervisorMessage(text, status) {
        let messageBox = $('#supervisor_message');
        messageBox.removeClass();
        messageBox.addClass(status === 'error' ? 'error-message' : 'success-message');
        messageBox.text(text);

        setTimeout(() => {
            resetSupervisorMessage();
        }, 10000);
    }

    function resetSupervisorMessage() {
        let messageBox = $('#supervisor_message');
        messageBox.removeClass();
        messageBox.text('');
    }

    function toggleSupervisorsSelect(value)
    {
        $('#supervisors').val('');
        if (value) {
            $('.supervisors-field').addClass('hidden');
        } else {
            $('.supervisors-field').removeClass('hidden');
        }
    }

    $('#supervisors').change(function() {
        $('#confirmDialogSupervising #provider_id').val($(this).data('providerId'));
        $('#confirmDialogSupervising #supervisor_id').val($(this).val());

        const supervisorName = $(this).find('option:selected').text();
        const userName = $('.profile-control').find('.provider-name').text();
        let message = `Are you sure that you want to assign supervisor <span class="text-bold">${supervisorName}</span> to <span class="text-bold">${userName}</span>?`;
        if (!$(this).val()) {
            message = `Are you sure that you want to unassign supervisor from <span class="text-bold">${userName}</span>?`;
        }

        $('#confirmDialogSupervising #assign_message').html(message);
        $('#confirmDialogSupervising').modal('show');
    });

    $('#confirm-selection-supervising').click(function() {
        let providerId = $('#confirmDialogSupervising #provider_id').val();
        let supervisorId = $('#confirmDialogSupervising #supervisor_id').val();
        let date = $('#confirmDialogSupervising input[name="date"]').val();

        axios.post(`/api/providers/${providerId}/attach-supervisor`, { 'supervisor_id': supervisorId, date })
            .then(() => {
                let statusString = '';
                if (supervisorId) {
                    statusString = 'assigned';
                } else {
                    statusString = 'unassigned';
                }

                let message = `Supervisor was ${statusString} successfully.`;

                setSupervisorMessage(message, 'success');

                $('#supervisors').data('initialValue', supervisorId);
            })
            .catch((error) => {
                let message = 'Whoops, looks like something went wrong.';
                if (error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                }
                setSupervisorMessage(message, 'error');

                $('#supervisors').val($('#supervisors').data('initialValue'));
            });
        
        $('#confirmDialogSupervising').modal('hide');
    });

    $('#cancel-selection-supervising').click(function() {
        $('#supervisors').val($('#supervisors').data('initialValue'));
        $('#confirmDialogSupervising').modal('hide');
    });
});