import Alert from '../Alert';
import moment from 'moment-timezone';

$(document).ready( function () {
    window.notificationsTable = $('#notificationsTable').DataTable({
        ajax: {
            url: window.location.pathname + '/api',
            dataSrc: ''
        },
        "searching": true,
        "pageLength": 15,
        "lengthChange": false,
        columns: [
            {
                data: 'title',
                orderable: true,
                searchable: true,
                render: function (data, type, row) {
                    if (type === 'display') {
                        return row.viewed_at ? data : `<b>${data}</b>`;
                    }
 
                    return data;
                },
            },
            {
                data: 'is_required',
                orderable: true,
                searchable: false,
                render: function (data, type, row) {
                    if (type === 'display') {
                        return row.viewed_at ? data : `<b>${data}</b>`;
                    }
 
                    return data;
                },
            },
            {
                data: 'viewed_at',
                orderable: true,
                searchable: true,
                render: function (data, type) {
                    if (type === 'display') {
                        return data && moment(data).format('MM/DD/YYYY hh:mm A');
                    }
 
                    return data;
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
        
        const data = window.notificationsTable.row(this).data();
        if (!data) {
            return;
        }
        
        const previewDialog = $('#preview-dialog')
        if (!previewDialog.length) {
            return;
        }

        if (data.viewed_at) {
            previewDialog.find('.modal-footer--not-viewed').addClass('hidden');
            previewDialog.find('.modal-footer--viewed').removeClass('hidden');
            $('#notification-viewed-btn').data('id', '');
        } else {
            $('#notification-viewed-checkbox').prop('checked', false);
            $('#notification-viewed-btn').prop('disabled', true);
            $('#notification-viewed-user').text(data.userName);

            previewDialog.find('.modal-footer--not-viewed').removeClass('hidden');
            previewDialog.find('.modal-footer--viewed').addClass('hidden');
            $('#notification-viewed-btn').data('id', data.id);
        }

        previewDialog.find('.modal-head').text(data.title);
        previewDialog.find('.modal-body').html(data.content);
        previewDialog.modal('show');
    });

    $(document).on('click', '#notification-viewed-btn', function() {
        $('#preview-dialog').modal('hide');

        const selectedNotificationId = $(this).data('id');
        if (!selectedNotificationId) {
            return;
        }

        axios({
            method: 'post',
            url: `/update-notifications/${selectedNotificationId}/mark-as-viewed`,
        })
            .then(() => {
                window.notificationsTable.ajax.reload();
            })
            .catch((err) => {
                console.log('error: ', err);
                Alert.show('#status-alert', 'Oops, something went wrong!', 'alert-danger');
            });
    });

    $('#notification-viewed-checkbox').change(function() {
        if (this.checked) {
            $('#notification-viewed-btn').prop('disabled', false);
        } else {
            $('#notification-viewed-btn').prop('disabled', true);
        }
    });
});
