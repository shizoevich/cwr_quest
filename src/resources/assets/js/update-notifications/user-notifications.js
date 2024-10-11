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
                data: 'opened_at',
                orderable: true,
                searchable: true,
                render: function (data, type) {
                    if (type === 'display') {
                        return data && moment(data).format('MM/DD/YYYY hh:mm A');
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
});
