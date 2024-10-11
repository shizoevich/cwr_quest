$(document).ready(function() {
    $(document).on('click', '#tridiuumCredentialsDelete', function(e) {
        axios({
            method: 'delete',
            url: $(this).data('url'),
            data: {user_id: $(this).data('user')}
        }).then(response => {
            $('input[name="tridiuum_username"]').val('');
            $('input[name="tridiuum_password"]').val('');
            $(this).parents('.modal').modal('hide');
            $('#confirm-delete-tridiuum').detach();
        });
    });



    $(document).on('submit', '#profileTridiuum', function(e) {
        simpleLoader();
    });    
});

window.simpleLoader = function() {
    $('.hide-loader').css('display', 'none');
    $('.show-loader').css('display', 'block');
};

window.reverseLoader = function() {
    $('.hide-loader').css('display', 'block');
    $('.show-loader').css('display', 'none');
};