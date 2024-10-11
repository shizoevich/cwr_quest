import initEditor from '../plugins/tinymce-editor';

$(document).ready(function() {
    function initUsersSelect() {
        if (!$('#user_ids').length) {
            return;
        }

        $('#user_ids').multiselect({
            buttonClass: 'btn btn-default btn-multiselect',
            buttonWidth: '100%',
            maxHeight: 390,
            enableCaseInsensitiveFiltering: true,
            onChange: onUserChange,
            onSelectAll: onUserChange,
            onDeselectAll: onUserChange,
        });

        onUserChange();
    }

    initUsersSelect();

    function onUserChange() {
        let htmlContent = '';
        let providersCount = 0;
        let secretariesCount = 0;

        const selectedUsers = $('#user_ids').val();
        $('#user_ids > option').each(function() {
            if (!selectedUsers.includes(this.value)) {
                return;
            }

            if ($(this).data('role') === 'provider') {
                providersCount++;
            } else if ($(this).data('role') === 'secretary') {
                secretariesCount++;
            }

            const userName = $(this).text().trim();
            htmlContent += `
                <li class="users-list-item">
                    <span>${userName}</span>
                    <button type="button" class="remove-user-btn" data-user-id="${this.value}">
                        &times;
                    </button>
                </li>
            `;
        });

        $('.users-list').html(htmlContent);
        $('#select-all-providers + .users-select-count').text(`(${providersCount})`);
        $('#select-all-secretaries + .users-select-count').text(`(${secretariesCount})`);
        
        if (htmlContent) {
            $('.users-list').removeClass('hidden');
        } else {
            $('.users-list').addClass('hidden');
        }
    }

    $(document).on('click', '.remove-user-btn', function() {
        $('#user_ids').multiselect('deselect', $(this).data('user-id'));
        onUserChange();
    });

    $(document).on('click', '#select-all-providers', function() {
        $('#user_ids > option').each(function() {
            if ($(this).data('role') !== 'provider') {
                return;
            }
            $('#user_ids').multiselect('select', this.value);
        });

        onUserChange();
    });

    $(document).on('click', '#select-all-secretaries', function() {
        $('#user_ids > option').each(function() {
            if ($(this).data('role') !== 'secretary') {
                return;
            }
            $('#user_ids').multiselect('select', this.value);
        });

        onUserChange();
    });

    $(document).on('click', '#unselect-all', function() {
        $('#user_ids > option').each(function() {
            $('#user_ids').multiselect('deselect', this.value);
        });

        onUserChange();
    });

    function initTemplatesSelect() {
        if (!$('#template').length) {
            return;
        }

        $('#template').multiselect({
            buttonClass: 'btn btn-default btn-multiselect',
            buttonWidth: '100%',
            maxHeight: 390,
            enableCaseInsensitiveFiltering: true,
            onChange: onTemplateChange,
            optionClass: (option) => $(option).val() === 'none' ? 'hidden' : '',
        });
    }

    initTemplatesSelect();

    function onTemplateChange() {
        const templateId = $('#template').val();

        axios({
            method: 'get',
            url: `/update-notification-templates/${templateId}`,
        })
            .then((response) => {
                const template = response.data.template;

                $('#title').val(template.notification_title);
                tinymce.get('tinymce-editor').setContent(template.notification_content);
            })
            .catch((err) => {
                console.log('error: ', err);
            });
    }

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
        
        const titleValue = $('#title').val();
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

        if ($('#user_ids').length) {
            const usersValue = $('#user_ids').val();
            if (!usersValue || !usersValue.length) {
                errors.push({
                    field: '#user_ids',
                    message: 'The users field is required.'
                });
            }
        }
        
        const titleValue = $('#title').val();
        if (!titleValue) {
            errors.push({
                field: '#title',
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
        $('#notification-form').trigger('submit');
    });

    $(document).on('click', '#cancel-btn', function() {
        window.history.back();
    });
});
