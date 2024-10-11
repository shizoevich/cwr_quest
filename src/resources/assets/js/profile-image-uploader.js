import Alert from "./Alert";

$(document).ready(function() {

    $("input.user-photo").each(function(i, el) {
        var inputClass = 'danger';
        if($(el).data('exists') !== undefined) {
            inputClass = 'success';
        }
        $(el).fileinput({
            browseClass: "btn btn-primary btn-"+inputClass,
            showCaption: false,
            showRemove: false,
            showUpload: false,
            browseLabel: 'Add Picture',
            browseIcon: '',
            showPreview: false
        });
    }).change(function() {
        $($(this).parents('form.user-photo-form')).trigger('submit');
    });

    $('form.user-photo-form').submit(function() {
        var fd = new FormData(this);
        var photo = $(this).find('input[type=file]');
        fd.append("photo", $(photo).prop('files')[0]);
        fd.append("userID", $(photo).data('userid'));
        var obj = this;

        $.ajax({
            url: '/users/save-photo',
            type: 'POST',
            data: fd,
            cache: false,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#status-alert').empty();
            },
            success: function(response) {
                $($(obj).find('.help-block-xs span')).empty();
                $($(obj).find('.btn-file')).removeClass('btn-danger').addClass('btn-success');
                $(obj).parents('tr').find('td.download-picture-block button').prop('disabled', false);
                Alert.show('#status-alert', response.message, 'alert-success');
            },
            error: function(response) {
                $($(obj).find('.help-block-xs span')).text(response.responseJSON.photo[0]);
            }
        });
        return false;
    });

    $('.file-input .btn-file span.hidden-xs').removeClass('hidden-xs');

});
