import Alert from './Alert'

$(document).ready(function() {
    let $signature = $("#signature");
    let signatureIsEmpty = true;
    $signature.jSignature().bind('change', function(e) {
        signatureIsEmpty = false;
        $('#signature').removeClass('input-error');
        $('hr.signature-line').removeClass('signature-line-error');
        $('#status-alert').empty();
    });

    $('#export-signature').click(function() {
        if (signatureIsEmpty) {
            Alert.show('#status-alert', "Please add your signature.", 'alert-danger');
            $('#signature').addClass('input-error');
            $('hr.signature-line').addClass('signature-line-error');
            return false;
        }

        $(this).prop('disabled', true);
        $('#clear-signature').prop('disabled', true);
        $('#status-alert').empty();
        $('#signature').removeClass('input-error');
        $('hr.signature-line').removeClass('signature-line-error');

        let datapair = $signature.jSignature("getData", "image");
        let imageSrc = "data:" + datapair[0] + "," + datapair[1];
        let data = {
            signature: imageSrc,
            userID: $(this).data('userid') || null,
            _token: $('input[name=_token]').val()
        };

        let url = '/users/save-signature';
        if ($(this).data('token')) {
            url = `/signature/${$(this).data('token')}`
        }

        axios({
            method: 'post',
            url,
            data,
        })
            .then((response) => {
                if (response.data && response.data.redirectTo) {
                    window.location.href = response.data.redirectTo;
                } else {
                    let message = response.data && response.data.message;
                    if (!message) {
                        message = 'Signature successfully uploaded.';
                    }
                    Alert.show('#status-alert', message, 'alert-success');
                }
            })
            .catch((err) => {
                let message = err.response && err.response.data && err.response.data.message;
                if (!message) {
                    message = 'Oops, something went wrong!';
                }
                Alert.show('#status-alert', message, 'alert-danger');
            })
            .finally(() => {
                $(this).prop('disabled', false);
                $('#clear-signature').prop('disabled', false);
            });
    });

    $('#clear-signature').click(function() {
        $signature.jSignature('clear');
        signatureIsEmpty = true;
    });

    let elView = document.getElementById('view');
    if (elView !== null && elView !== undefined) {
        elView.scrollIntoView(false);
    }
});