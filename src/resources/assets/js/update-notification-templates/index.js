import Alert from '../Alert';

$(document).ready( function () {
    const templatesTable = $('#templatesTable').DataTable({
        ajax: {
            url: window.location.pathname + '/api',
            dataSrc: ''
        },
        "searching": true,
        "pageLength": 15,
        "lengthChange": false,
        columns: [
            {data: 'id', orderable: false, searchable: false},
            {data: 'name', orderable: true, searchable: true},
            {data: 'notification_title', orderable: true, searchable: true},
            {
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="/update-notifications/create?template_id=${row.id}" style="text-decoration:none;margin-right:10px;" onclick="event.stopPropagation()">
                            <span class="glyphicon glyphicon-send"></span>
                        </a>
                        <a href="/update-notification-templates/${row.id}/edit" style="text-decoration:none;margin-right:10px;" onclick="event.stopPropagation()">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>
                        <a id="delete-template" href="" style="text-decoration:none;" data-id="${row.id}">
                            <span class="glyphicon glyphicon-remove"></span>
                        </a>
                    `;
                },
            },
        ],
        hideEmptyCols: true,
        createdRow: function (row, data) {
            $(row).addClass(data.rowClass);
        }
    });

    $(document).on('click', '#templatesTable > tbody > tr', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const data = templatesTable.row(this).data();
        if (!data) {
            return;
        }
        
        const previewDialog = $('#preview-dialog')
        if (!previewDialog.length) {
            return;
        }

        previewDialog.find('.modal-head').text(data.notification_title);
        previewDialog.find('.modal-body').html(data.notification_content);
        previewDialog.modal('show');
    });

    let selectedTemplateId = null;
    $(document).on('click', '#delete-template', function(event) {
        event.preventDefault();
        event.stopPropagation();
        selectedTemplateId = $(this).data('id');
        $('#confirm-deleting-dialog').modal('show');
    });

    $('#cancel-deleting-btn').on('click', function() {
        $('#confirm-deleting-dialog').modal('hide');
        selectedTemplateId = null;
    });

    $('#confirm-deleting-btn').on('click', function() {
        $('#confirm-deleting-dialog').modal('hide');

        axios({
            method: 'delete',
            url: `/update-notification-templates/${selectedTemplateId}`,
        })
            .then(() => {
                Alert.show('#status-alert', 'Template deleted successfully.', 'alert-success');
                selectedTemplateId = null;
                templatesTable.ajax.reload();
            })
            .catch((err) => {
                console.log('error: ', err);
                Alert.show('#status-alert', 'Oops, something went wrong!', 'alert-danger');
            });
    });
});
