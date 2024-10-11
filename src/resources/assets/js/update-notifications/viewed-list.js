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
            {data: 'name', orderable: true, searchable: true},
            {data: 'email', orderable: true, searchable: true},
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
            }
        ],
        hideEmptyCols: true,
        createdRow: function (row, data) {
            $(row).addClass(data.rowClass);
        }
    });
});
