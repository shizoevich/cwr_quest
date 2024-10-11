import { Notification } from "element-ui";

const dataURItoBlob = (dataURI) => {
    var byteString = atob(dataURI.split(',')[1]);
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ab], { type: mimeString });
}

const readURL = (e) => {
    const input = e.currentTarget;

    const fileType = input.files[0].type;
    if (!fileType.startsWith('image/')) {
        Notification.error({
            title: "Error",
            message: 'Incorrect file type',
            type: "error",
        });
        $('#photo').val('');
      }
    

    if (input.files && input.files[0]) {
        $('.loader').show();
        $('#canvas-modal').modal('show');

        $("#photo-editor").hide();

        let reader = new FileReader();

        reader.onload = function (e) {
            const canvas = $('<canvas>').attr('id', 'canvas').appendTo('#photo-editor');
            const context = canvas.get(0).getContext("2d")

            let img = new Image();

            img.onload = function () {
                let imgWidth = img.width
                let imgHeight = img.height

                const aspectRatio = imgWidth / imgHeight;

                if (aspectRatio >= 1) {
                    imgWidth = 868
                    imgHeight = imgWidth / aspectRatio
                } else {
                    imgHeight = 500
                    imgWidth = imgHeight * aspectRatio
                }

                context.canvas.width = imgWidth
                context.canvas.height = imgHeight

                $("#photo-editor").width(imgWidth)
                $("#photo-editor").height(imgHeight)

                context.drawImage(img, 0, 0, imgWidth, imgHeight);

                const cropper = canvas.cropper({
                    zoomable: false,
                    dragMode: 'none'
                });

                setTimeout(() => {
                    $('.loader').hide()
                    $("#photo-editor").show()
                }, 500);

                $('#crope-button').click(() => updateCroppedImagePreview(canvas));
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

const updateCroppedImagePreview = (canvas) => {
    const photoName = $("#photo")[0].files[0].name;
    var croppedImageDataURL = canvas.cropper('getCroppedCanvas').toDataURL("image/png");
    $('#preview-image').attr('src', croppedImageDataURL);
    $('#photo_name').val(photoName);
    $('#photo').val('');
    $('#preview').show();
    $('#upload-button').hide();
    $("#photo-editor").empty().hide();

    $('#crope-button').off('click');
    $('#photo-editor canvas').remove();
}

const showSuccessAlert = () => {
    $('#success-alert').fadeIn().delay(3000).fadeOut();
}

const showErrorAlert = () => {
    $('#error-alert').fadeIn().delay(3000).fadeOut();
}

const resetImageInput = () => {
    $('#photo').val('');
    $('#photo_name').val('');
    $('#preview-image').attr('src', '#');
    $('#preview').hide();
    $('#upload-button').show();
}

const cancelButtonClick = () => {
    $("#photo-editor").empty().hide();
    $('#crope-button').off('click');
    $('#photo').val('');
}

const submitFormData = (e) => {
    e.preventDefault();

    const form = e.currentTarget;

    var formData = new FormData(form);

    const img = $('#preview-image').attr('src');

    if (img.startsWith('data:image/')) {
        var blob = dataURItoBlob(img);

        const fileName = $('#photo_name').val();
        const type = img.split(";")[0].split(":")[1];

        var newFile = new File([blob], fileName, { type });
        formData.set('photo', newFile);
    }
    $('#save-button').prop('disabled', true);
    $('#save-button').addClass('is-loading');
    $('#save-button').prepend('<i class="el-icon-loading"></i>');
    
    axios({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formData,
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
        .then(() => {
            showSuccessAlert();
        
            $('html, body').scrollTop(0);
        
            $('.form-group').removeClass('has-error');
            $('.help-block.with-errors').empty();
        })
        .catch((error) => {
            showErrorAlert();
        
            $('html, body').scrollTop(0);
        
            const errors = error.response.data;
            Object.keys(errors).forEach(field => {
                const errorField = $('#' + field).closest('.form-group').addClass('has-error');
                const errorBlock = errorField.find('.help-block.with-errors');
            
                errorBlock.html('<strong>' + errors[field][0] + '</strong>');
            });
        })
        .finally(() => {
            $('#save-button').prop('disabled', false);
            $('#save-button').removeClass('is-loading');
            $('#save-button i.el-icon-loading').remove();
        });
}

$(document).ready(() => {
    $('#phone').mask('000-000-0000');

    $('.multipleSelect').fastselect({
        placeholder: 'Select options'
    });
    $('.fastSelect-fake').addClass('hidden');

    $("#photo").change((e) => readURL(e));

    $('#upload-new-button').click(() => $('#photo').click());

    $('#delete-button').click(resetImageInput);

    $('#cancel-button').click(cancelButtonClick);

    $('#cross').click(cancelButtonClick);

    $('#preview').click((e) => e.preventDefault());

    $('form.profile-form').on('submit', submitFormData);

    let isTrudiuumUrlGuidePipActive = false;
    $('#button-question_tridiuum-url').click(function() {
        $('#tridiuum-url-modal').modal('show');
    });
    $('#tridiuum-url-modal .close').click(function() {
        if (! isTrudiuumUrlGuidePipActive) {
            $("#tridiuum-url-guide")[0].player.pause();
        }
    });
    $("#tridiuum-url-guide")
        .on('enterpictureinpicture', function () {
            isTrudiuumUrlGuidePipActive = true;
        })
        .on('leavepictureinpicture', function () {
            isTrudiuumUrlGuidePipActive = false;
            $('#tridiuum-url-modal').modal('show');
        });
});


