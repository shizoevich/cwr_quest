import Alert from '../Alert';
import moment from 'moment-timezone';

$(document).ready( function () {
    const notificationsTable = $('#notificationsTable').DataTable({
        ajax: {
            url: window.location.pathname + '/api',
            dataSrc: ''
        },
        "searching": true,
        "pageLength": 15,
        "lengthChange": false,
        columns: [
            {data: 'id', orderable: false, searchable: false},
            {data: 'title', orderable: true, searchable: true},
            {
                data: 'show_date',
                orderable: true,
                searchable: true,
                render: function (data, type) {
                    if (type === 'display') {
                        return data && moment(data).format('MM/DD/YYYY hh:mm A');
                    }
 
                    return data;
                },
            },
            {data: 'is_required', orderable: true, searchable: false},
            {
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="/update-notifications/${row.id}/viewed-list" style="text-decoration:none;margin-right:10px;" onclick="event.stopPropagation()">
                            <span class="glyphicon glyphicon-eye-open"></span>
                        </a>
                        <a href="/update-notifications/${row.id}/edit" style="text-decoration:none;margin-right:10px;" onclick="event.stopPropagation()">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>
                        <a id="delete-notification" href="" style="text-decoration:none;" data-id="${row.id}">
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

    $(document).on('click', '#notificationsTable > tbody > tr', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const data = notificationsTable.row(this).data();
        if (!data) {
            return;
        }
        
        const previewDialog = $('#preview-dialog')
        if (!previewDialog.length) {
            return;
        }

        previewDialog.find('.modal-head').text(data.title);
        previewDialog.find('.modal-body').html(data.content);
        previewDialog.modal('show');
    });

    let selectedNotificationId = null;
    $(document).on('click', '#delete-notification', function(event) {
        event.preventDefault();
        event.stopPropagation();
        selectedNotificationId = $(this).data('id');
        $('#confirm-deleting-dialog').modal('show');
    });

    $('#cancel-deleting-btn').on('click', function() {
        $('#confirm-deleting-dialog').modal('hide');
        selectedNotificationId = null;
    });

    $('#confirm-deleting-btn').on('click', function() {
        $('#confirm-deleting-dialog').modal('hide');

        axios({
            method: 'delete',
            url: `/update-notifications/${selectedNotificationId}`,
        })
            .then(() => {
                Alert.show('#status-alert', 'Notification deleted successfully.', 'alert-success');
                selectedNotificationId = null;
                notificationsTable.ajax.reload();
            })
            .catch((err) => {
                console.log('error: ', err);
                Alert.show('#status-alert', 'Oops, something went wrong!', 'alert-danger');
            });
    });
});
