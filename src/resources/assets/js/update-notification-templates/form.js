import initEditor from '../plugins/tinymce-editor';

$(document).ready(function() {
    let substitutions = [];
    function fetchEditorData() {
        axios({
            method: 'get',
            url: `/update-notification-substitutions/api`,
        })
            .then((response) => {
                substitutions = response.data || [];
            })
            .catch((err) => {
                console.log('error: ', err);
            })
            .finally(() => {
                initEditor({
                    substitutions,
                    onInit: onEditorInit,
                });
            });
    }

    fetchEditorData();

    function onEditorInit() {
        const preloader = $('.preloader');
        if (preloader.length) {
            preloader.remove();
        }
    }

    $(document).on('click', '#preview-btn', function() {
        if (!validateForm()) {
            return;
        }

        const previewDialog = $('#preview-dialog')
        if (!previewDialog.length) {
            return;
        }
        
        const titleValue = $('#notification_title').val();
        const contentValue = tinymce.get('tinymce-editor').getContent();

        previewDialog.find('.modal-head').text(titleValue);
        previewDialog.find('.modal-body').html(contentValue);
        previewDialog.modal('show');
    });

    function validateForm() {
        $('.form-group.has-error').each(function() {
            $(this).removeClass('has-error');
            $(this).find('.help-block.with-errors').html('');
        });

        let errors = [];
        const nameValue = $('#name').val();
        if (!nameValue) {
            errors.push({
                field: '#name',
                message: 'The name field is required.'
            });
        }
        const titleValue = $('#notification_title').val();
        if (!titleValue) {
            errors.push({
                field: '#notification_title',
                message: 'The title field is required.'
            });
        }
        const contentValue = tinymce.get('tinymce-editor').getContent();
        if (!contentValue) {
            errors.push({
                field: '#tinymce-editor',
                message: 'The content field is required.'
            });
        }

        errors.forEach((error) => {
            const parent = $(error.field).parents('.form-group');
            if (!parent || !parent.length) {
                return;
            }
            parent.addClass('has-error');
            parent.find('.help-block.with-errors').html(`
                <strong>${error.message}</strong>
            `);
        });

        return !errors.length;
    }

    $(document).on('click', '#submit-btn', function() {
        $('#template-form').trigger('submit');
    });

    $(document).on('click', '#cancel-btn', function() {
        window.history.back();
    });
});
