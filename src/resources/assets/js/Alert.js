export default class Alert {

    static show(appendTo, text, className) {
        let html = '<div id="status-alert" class="alert alert-dismissible ' + className + '" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            text +
            '</div>';
        $(appendTo).html(html);
    }

};